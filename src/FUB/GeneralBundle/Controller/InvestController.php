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
                montant_chiffre amount
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
                montant_chiffre amount
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
                r1.id,
                CONCAT(r1.nom,' ',r1.prenom) nom,
                r2.amount,
                concat(r2.ratio,'%') ratio
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

    public function loadTotalAction(){
        $sql="SELECT
                SUM(r2.amount) amount,
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
            IFNULL(t.amount,0) 'Montant total investi',
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