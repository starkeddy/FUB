<?php
/**
 * Created by IntelliJ IDEA.
 * User: eddy
 * Date: 04/07/2017
 * Time: 14:06
 */

namespace FUB\GeneralBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use FUB\GeneralBundle\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;


class RatioController extends Controller{
    public function loadProfilAction(Request $request){
        return $this->render('FUBGeneralBundle:Reporting:ratio.html.twig');
    }

    public function loadInfoAction(Request $request){
        $sql="SELECT * FROM
        (SELECT r1.id, r2.email, r2.username FROM adhesion r1
        INNER JOIN users r2
        ON r1.mail=r2.email)r3
        WHERE r3.username=?";

        $stmt=$this->getDoctrine()->getConnection()->prepare($sql);
        $stmt->bindValue(1,$this->getUser());
        $stmt->execute();

        $data=$stmt->fetchAll();

        $id=0;
        foreach( $data as $value) {
            $id = $value['id'];
        }

        $sql2="SELECT
                e5.*,
                format(round((e5.ratio*e4.ben_ttc)/100,2),2) ben_ttc,
                format(round((e5.ratio*e4.ben_ht)/100,2),2) ben_ht
            FROM
            (
                SELECT 
                    'a' joint,
                    refinv,
                    refadhesion,
                    format(amount,0) amount,
                    ratio
                FROM f_investissement 
                WHERE flag=1 AND refadhesion=".$id."
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
            ON e5.joint=e4.joint";
        $stmt2=$this->getDoctrine()->getConnection()->prepare($sql2);
        $stmt2->execute();
        $data2=$stmt2->fetchAll();

        $json2=json_encode($data2);
        return new JsonResponse($json2);
    }
}