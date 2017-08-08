<?php
/**
 * Created by IntelliJ IDEA.
 * User: eddy
 * Date: 04/07/2017
 * Time: 17:56
 */


namespace FUB\GeneralBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use FUB\GeneralBundle\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class InvestController extends Controller{
    public function loadMainAction(Request $request){
        return $this->render('FUBGeneralBundle:Dashboard:createInvest.html.twig');
    }

    public function addInvestAction($refinv,$id,$amount,Request $request){
        $sql4="delete from f_investissement where refadhesion=? and refinv=?";
        $stmt4=$this->getDoctrine()->getConnection()->prepare($sql4);
        $stmt4->bindValue(1,$id);
        $stmt4->bindValue(2,$refinv);
        $stmt4->execute();

        $sql5="INSERT INTO f_investissement VALUES(?,?,?,0,1)";
        $stmt5=$this->getDoctrine()->getConnection()->prepare($sql5);
        $stmt5->bindValue(1,$refinv);
        $stmt5->bindValue(2,$id);
        $stmt5->bindValue(3,$amount);
        $stmt5->execute();

        //recalculer le ratio
        $sql="SELECT
                refadhesion,          
                ROUND((amount*100)/(SELECT SUM(amount) amount FROM f_investissement WHERE flag=1),2) ratio
            FROM f_investissement WHERE flag=1";

        $stmt=$this->getDoctrine()->getConnection()->prepare($sql);
        $stmt->execute();
        $data=$stmt->fetchAll();
        foreach( $data as $value) {
            $refadh = $value['refadhesion'];
            $ratio=$value['ratio'];

            $sql2="UPDATE f_investissement SET ratio=? WHERE refadhesion=?";
            $stmt2=$this->getDoctrine()->getConnection()->prepare($sql2);
            $stmt2->bindValue(1,$ratio);
            $stmt2->bindValue(2,$refadh);
            $stmt2->execute();
        }

        return new Response('Done');
    }

    public function loadAmountAction($id,Request $request){
        $sql="SELECT 
                format(montant_chiffre,0) amount
            FROM adhesion
            WHERE flag=1 AND id=?";

        $stmt=$this->getDoctrine()->getConnection()->prepare($sql);
        $stmt->bindValue(1,$id);
        $stmt->execute();
        $data=$stmt->fetchAll();
        $json=json_encode($data);
        return new JsonResponse($json);
    }

    public function loadAllInvestisorAction(){
        $sql="SELECT 
                id,
                CONCAT(nom,' ',prenom) nom,
                format(montant_chiffre,0) amount
            FROM adhesion
            WHERE flag=1 order by id desc";
        $stmt=$this->getDoctrine()->getConnection()->prepare($sql);
        $stmt->execute();

        $data=$stmt->fetchAll();
        $json=json_encode($data);

        return new JsonResponse($json);
    }

    public function loadLastInvestisorAction(){
        $sql="SELECT
            e7.id,
            CONCAT(e7.nom,' ',e7.prenom) nom,
            CONCAT(format(amount,0),' Ar')  amount,
            ratio,
            CONCAT(format(ben_ttc,2),' Ar')ben_ttc,
            CONCAT(format(ben_ht,2),' Ar')ben_ht
         FROM           
        (SELECT
            e5.refadhesion,
            e5.amount,
            e5.ratio,
            ROUND((e5.ratio*e4.ben_ttc)/100,2) ben_ttc,
            ROUND((e5.ratio*e4.ben_ht)/100,2) ben_ht
            FROM
            (
            SELECT 
                'a' joint,
                refinv,
                refadhesion,
                amount,
                ratio
            FROM f_investissement 
            WHERE flag=1
            )e5
            LEFT JOIN
            (
            SELECT 
                e1.refinv,
                e1.joint,
                (IFNULL(e1.benefice,0)-IFNULL(e3.amount,0))/2 ben_ttc, 
                (IFNULL(e1.benefice,0)-IFNULL(e3.amount,0))/1.2/2 ben_ht
            FROM
                (SELECT
                'a' joint,
                r1.refinv,
                IFNULL(SUM(r2.benefice),0) benefice
                FROM
                (SELECT 
                    refinv,
                    start_upd_dt,
                    CASE WHEN end_upd_dt='0000-00-00' THEN DATE_FORMAT(NOW(),'%Y-%m-%d') ELSE end_upd_dt END end_upd_dt
                FROM rf_investissement WHERE statut=1) r1
                LEFT JOIN 
                vente r2
                ON r1.refinv=r2.refinv AND (r2.upd_dt BETWEEN r1.start_upd_dt AND r1.end_upd_dt)
                GROUP BY r1.refinv)e1
            LEFT JOIN 
            (	SELECT
                'a' joint,	
                IFNULL(SUM(amount),0) amount 	
                FROM
                (
                SELECT
                    IFNULL(SUM(valeurpaye+endamnite),0) amount 
                FROM 
                    (SELECT 
                    refinv,
                    start_upd_dt,
                    CASE WHEN end_upd_dt='0000-00-00' THEN DATE_FORMAT(NOW(),'%Y-%m-%d') ELSE end_upd_dt END end_upd_dt
                    FROM rf_investissement WHERE statut=1) t1
                LEFT JOIN journalpaiesalaire t2
                ON t2.upd_dt BETWEEN t1.start_upd_dt AND t1.end_upd_dt
                UNION ALL
                SELECT
                    IFNULL(SUM(montant),0) amount 
                FROM 
                    (SELECT 
                    refinv,
                    start_upd_dt,
                    CASE WHEN end_upd_dt='0000-00-00' THEN DATE_FORMAT(NOW(),'%Y-%m-%d') ELSE end_upd_dt END end_upd_dt
                    FROM rf_investissement WHERE statut=1) z1
                LEFT JOIN journalpaielogistique z2
                ON z2.upd_dt BETWEEN z1.start_upd_dt AND z1.end_upd_dt
                )e2
            )e3
            ON e1.joint=e3.joint
            )e4
            ON e5.joint=e4.joint
        )e6
        LEFT JOIN adhesion e7
        ON e6.refadhesion=e7.id
";

        $stmt=$this->getDoctrine()->getConnection()->prepare($sql);
        $stmt->execute();

        $data=$stmt->fetchAll();
        $json=json_encode($data);

        return new JsonResponse($json);
    }

    public function loadTotalAction(){
        $sql="SELECT
                concat(format(SUM(r2.amount),0),' Ar') amount,
                concat(round(SUM(r2.ratio),2),'%') ratio
            FROM adhesion r1
            INNER JOIN 
            (SELECT * FROM f_investissement WHERE flag=1) r2
            ON r1.id=r2.refadhesion";

        $stmt=$this->getDoctrine()->getConnection()->prepare($sql);
        $stmt->execute();

        $data=$stmt->fetchAll();
        $json=json_encode($data);

        return new JsonResponse($json);
    }

    public function validerCampagnerAction($upd_dt,Request $request){
        $sql="INSERT INTO rf_investissement VALUES (0,?,'',1)";
        $stmt=$this->getDoctrine()->getConnection()->prepare($sql);
        $stmt->bindValue(1,$upd_dt);
        $stmt->execute();

        return new Response("done");
    }

    public function cloturerAction($refinv,Request $request){
        $res=intval($refinv);
        $sql="UPDATE rf_investissement SET statut=0, end_upd_dt=now() WHERE refinv=?";

        $stmt=$this->getDoctrine()->getConnection()->prepare($sql);
        $stmt->bindValue(1,$res);
        $stmt->execute();

        $sql="UPDATE f_investissement SET flag=0";

        $stmt=$this->getDoctrine()->getConnection()->prepare($sql);
        $stmt->execute();

        return new Response("done");
    }

    public function loadLastRefAction(Request $request){
        $sql="SELECT max(refinv)+1 newref, date_format(now(),'%Y-%m-%d')upd_dt FROM rf_investissement";

        $stmt=$this->getDoctrine()->getConnection()->prepare($sql);
        $stmt->execute();

        $data=$stmt->fetchAll();
        $json=json_encode($data);

        return new JsonResponse($json);
    }

    public function loadEncoursAction(Request $request){
        $sql="SELECT * FROM rf_investissement WHERE statut=1";

        $stmt=$this->getDoctrine()->getConnection()->prepare($sql);
        $stmt->execute();

        $data=$stmt->fetchAll();
        $json=json_encode($data);

        return new JsonResponse($json);
    }

    public function loadHistoryAction(Request $request){
        $sql="SELECT
            r.refinv 'Référence',
            start_upd_dt 'Début de l''opération',
            end_upd_dt 'Fin de l''opération',              
            IFNULL(t.nb,0) 'Nombre d''investisseur',
            concat(format(IFNULL(t.amount,0),0),' Ar') 'Montant total investi',
            (CASE WHEN statut=1 THEN 'Encours' ELSE 'Terminé' END)'Status'
            FROM rf_investissement r 
            LEFT JOIN (SELECT refinv, COUNT(*)nb, SUM(amount)amount FROM f_investissement
            GROUP BY refinv) t
            ON r.refinv=t.refinv
            ORDER BY r.refinv DESC";

        $stmt=$this->getDoctrine()->getConnection()->prepare($sql);
        $stmt->execute();

        $data=$stmt->fetchAll();
        $json=json_encode($data);

        return new JsonResponse($json);
    }

    
}