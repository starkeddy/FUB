<?php
/**
 * Created by IntelliJ IDEA.
 * User: eddy
 * Date: 28/05/2017
 * Time: 11:11
 */

namespace FUB\GeneralBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use FUB\GeneralBundle\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Ob\HighchartsBundle\Highcharts\Highchart;
use Symfony\Component\HttpFoundation\Response;

class CAglobalController extends Controller{
    public function ChiffreAfaireAction(Request $request){
        return $this->render('FUBGeneralBundle:Reporting:chiffre.html.twig');
    }

    public function StockEnCoursAction(Request $request){
        return $this->render('FUBGeneralBundle:Reporting:stock.html.twig');
    }
    
    public function ArticleAction(Request $request){
        return $this->render('FUBGeneralBundle:Reporting:articles.html.twig');
    }

    public function loadArticleListeAction(){
        $sql="SELECT 
                refarticle Référence, 
                a.designation Désignation, 
                description Description, 
                c.designation Catégorie, 
                a.condition 'Condition', 
                prixachat 'Prix d''achat', 
                prixvente 'Prix de vente estimé', 
                observation Observation
            FROM article a 
            LEFT JOIN categorie c ON a.refcategorie=c.refcategorie";

        $stmt=$this->getDoctrine()->getConnection()->prepare($sql);
        $stmt->execute();

        $data=$stmt->fetchAll();
        $json=json_encode($data);

        return new JsonResponse($json);
    }

    public function loadInvListAction(Request $request){
        $sql="SELECT DISTINCT refinv FROM stock ORDER BY 1 DESC";

        $stmt=$this->getDoctrine()->getConnection()->prepare($sql);
        $stmt->execute();

        $data=$stmt->fetchAll();
        $json=json_encode($data);

        return new JsonResponse($json);
    }

    public function loadStockAction($refinv,Request $request){
        $sql="SELECT 
                a.refarticle Référence, 
                b.designation Désignation,               
                a.qty Quantité, 
                b.prixachat 'Prix d''achat', 
                b.prixvente 'Prix de vente estimé', 
                (b.prixachat*a.qty) 'Total achat', 
                (b.prixvente*a.qty) 'Total vente estimé', 
                a.observation 'Observation'
            FROM stock a 
            INNER JOIN article b ON a.refarticle=b.refarticle 
            where a.refinv=?
            order by 1";
        $stmt=$this->getDoctrine()->getConnection()->prepare($sql);
        $stmt->bindValue(1, $refinv);
        $stmt->execute();

        // Chart
        $data=$stmt->fetchAll();
        $json=json_encode($data);

        return new JsonResponse($json);
    }

    public function loadArticleRepAction($name,$debut,$fin,$refinv,Request $request){
        $sql="";
        if($name=='daily'){
            $sql="SELECT 
                  t1.*, 
            SUM(IFNULL(t2.qty,0)) qty
                  FROM 
            ( 
                  SELECT * 
                    FROM (SELECT * FROM rf_date WHERE upd_dt BETWEEN '".$debut."' AND '".$fin."') r1, 
              (SELECT	DISTINCT b.designation 
                  FROM (SELECT * FROM vente WHERE refinv='".$refinv."') a 
                  LEFT JOIN article b ON a.refarticle=b.refarticle 
            WHERE a.upd_dt BETWEEN '".$debut."' AND '".$fin."' 
              GROUP BY a.upd_dt,a.refarticle)r2 
            )t1 
            LEFT JOIN 
                  ( 
                    SELECT 
                      a.upd_dt, 
            b.designation, 
            SUM(a.qty) qty
            FROM (SELECT * FROM vente WHERE refinv='".$refinv."') a 
              LEFT JOIN article b ON a.refarticle=b.refarticle 
            WHERE a.upd_dt BETWEEN '".$debut."' AND '".$fin."' 
            GROUP BY a.upd_dt,a.refarticle 
            ORDER BY 1 DESC 
            )t2 
            ON t1.upd_dt=t2.upd_dt AND t1.designation=t2.designation 
            GROUP BY t1.designation
            ORDER BY t1.designation DESC";
        }
        else if($name=='weekly'){
            $sql="SELECT 
                      t1.designation, 
                SUM(IFNULL(t2.qty,0)) qty
                      FROM 
                ( 
                      SELECT * 
                        FROM (SELECT DISTINCT WEEK(upd_dt,1) weekly FROM rf_date WHERE upd_dt BETWEEN '".$debut."' AND '".$fin."') r1, 
                (SELECT	DISTINCT b.designation 
                      FROM (SELECT * FROM vente WHERE refinv='".$refinv."') a 
                      LEFT JOIN article b ON a.refarticle=b.refarticle 
                WHERE a.upd_dt BETWEEN '".$debut."' AND '".$fin."' 
                GROUP BY a.upd_dt,a.refarticle)r2 
                )t1 
                LEFT JOIN 
                      ( 
                        SELECT 
                          WEEK(a.upd_dt,1) weekly, 
                        b.designation, 
                        SUM(a.qty) qty
                          FROM (SELECT * FROM vente WHERE refinv='".$refinv."') a 
                          LEFT JOIN article b ON a.refarticle=b.refarticle 
                          WHERE a.upd_dt BETWEEN '".$debut."' AND '".$fin."' 
                GROUP BY weekly,a.refarticle 
                ORDER BY 1 DESC 
                )t2 
                ON t1.weekly=t2.weekly AND t1.designation=t2.designation 
                GROUP BY t1.designation
                ORDER BY t1.designation DESC";
        }else if($name=='monthly'){
            $sql="SELECT 
              t1.designation, 
              SUM(IFNULL(t2.qty,0)) qty
              FROM 
              ( 
              SELECT * 
                      FROM (SELECT DISTINCT DATE_FORMAT(upd_dt,'%Y-%m') monthly FROM rf_date WHERE upd_dt BETWEEN  '".$debut."' AND '".$fin."' ) r1, 
              (SELECT	DISTINCT b.designation 
              FROM (SELECT * FROM vente WHERE refinv='".$refinv."') a 
              LEFT JOIN article b ON a.refarticle=b.refarticle 
              WHERE a.upd_dt BETWEEN  '".$debut."' AND '".$fin."'  
              GROUP BY a.upd_dt,a.refarticle)r2 
              )t1 
              LEFT JOIN 
              ( 
                      SELECT 
                        DATE_FORMAT(a.upd_dt,'%Y-%m') monthly, 
              b.designation, 
              SUM(a.qty) qty
              FROM (SELECT * FROM vente WHERE refinv='".$refinv."') a 
              LEFT JOIN article b ON a.refarticle=b.refarticle 
              WHERE a.upd_dt BETWEEN  '".$debut."' AND '".$fin."'  
              GROUP BY monthly,a.refarticle 
              ORDER BY 1 DESC 
              )t2 
              ON t1.monthly=t2.monthly AND t1.designation=t2.designation 
              GROUP BY t1.designation
              ORDER BY t1.designation DESC";
        }

        $stmt=$this->getDoctrine()->getConnection()->prepare($sql);
        $stmt->execute();

        // Chart
        $data=$stmt->fetchAll();
        $json=json_encode($data);

        return new JsonResponse($json);
    }

    public function loadArticleAction($name,$debut,$fin,$refinv,Request $request){
        $sql="";
        $sql_temp="SELECT	DISTINCT b.designation 
          FROM (SELECT * FROM vente WHERE refinv='".$refinv."') a 
          LEFT JOIN article b ON a.refarticle=b.refarticle 
          WHERE a.upd_dt BETWEEN  '".$debut."' AND '".$fin."'  
          GROUP BY a.upd_dt,a.refarticle";

        if($name=='daily'){
            $sql="SELECT 
                          t1.*, 
                    IFNULL(t2.qty,0) qty, 
                    IFNULL(t2.amount,0) amount, 
                    IFNULL(t2.benef,0) benef 
                          FROM 
                    ( 
                          SELECT * 
                            FROM (SELECT * FROM rf_date WHERE upd_dt BETWEEN '".$debut."' AND '".$fin."') r1, 
                      (SELECT	DISTINCT b.designation 
                          FROM (SELECT * FROM vente WHERE refinv='".$refinv."') a 
                          LEFT JOIN article b ON a.refarticle=b.refarticle 
                    WHERE a.upd_dt BETWEEN '".$debut."' AND '".$fin."' 
                      GROUP BY a.upd_dt,a.refarticle)r2 
                    )t1 
                    LEFT JOIN 
                          ( 
                            SELECT 
                              a.upd_dt, 
                    b.designation, 
                    SUM(a.qty) qty, 
                    SUM(a.montanttotal)amount, 
                    SUM(a.benefice) benef 
                    FROM (SELECT * FROM vente WHERE refinv='".$refinv."') a 
                      LEFT JOIN article b ON a.refarticle=b.refarticle 
                    WHERE a.upd_dt BETWEEN '".$debut."' AND '".$fin."' 
                    GROUP BY a.upd_dt,a.refarticle 
                    ORDER BY 1 DESC 
                    )t2 
                    ON t1.upd_dt=t2.upd_dt AND t1.designation=t2.designation
                     where t1.designation=?
                    ORDER BY t1.upd_dt ASC, t1.designation DESC";
        }else if($name=='weekly'){
            $sql="SELECT 
                          t1.*, 
                    IFNULL(t2.qty,0) qty, 
                    IFNULL(t2.amount,0) amount, 
                    IFNULL(t2.benef,0) benef 
                          FROM 
                    ( 
                          SELECT * 
                            FROM (SELECT DISTINCT WEEK(upd_dt,1) weekly FROM rf_date WHERE upd_dt BETWEEN '".$debut."' AND '".$fin."') r1, 
                    (SELECT	DISTINCT b.designation 
                          FROM (SELECT * FROM vente WHERE refinv='".$refinv."') a 
                          LEFT JOIN article b ON a.refarticle=b.refarticle 
                    WHERE a.upd_dt BETWEEN '".$debut."' AND '".$fin."' 
                    GROUP BY a.upd_dt,a.refarticle)r2 
                    )t1 
                    LEFT JOIN 
                          ( 
                            SELECT 
                              WEEK(a.upd_dt,1) weekly, 
                            b.designation, 
                            SUM(a.qty) qty, 
                            SUM(a.montanttotal)amount, 
                            SUM(a.benefice) benef 
                              FROM (SELECT * FROM vente WHERE refinv='".$refinv."') a 
                              LEFT JOIN article b ON a.refarticle=b.refarticle 
                              WHERE a.upd_dt BETWEEN '".$debut."' AND '".$fin."' 
                    GROUP BY weekly,a.refarticle 
                    ORDER BY 1 DESC 
                    )t2 
                    ON t1.weekly=t2.weekly AND t1.designation=t2.designation
                     where t1.designation=?
                    ORDER BY t1.weekly ASC, t1.designation DESC";
        }else if($name=='monthly'){
            $sql="SELECT 
                  t1.*, 
                  IFNULL(t2.qty,0) qty, 
                  IFNULL(t2.amount,0) amount, 
                  IFNULL(t2.benef,0) benef 
                  FROM 
                  ( 
                  SELECT * 
                          FROM (SELECT DISTINCT DATE_FORMAT(upd_dt,'%Y-%m') monthly FROM rf_date WHERE upd_dt BETWEEN  '".$debut."' AND '".$fin."' ) r1, 
                  (SELECT	DISTINCT b.designation 
                  FROM (SELECT * FROM vente WHERE refinv='".$refinv."') a 
                  LEFT JOIN article b ON a.refarticle=b.refarticle 
                  WHERE a.upd_dt BETWEEN  '".$debut."' AND '".$fin."'  
                  GROUP BY a.upd_dt,a.refarticle)r2 
                  )t1 
                  LEFT JOIN 
                  ( 
                          SELECT 
                            DATE_FORMAT(a.upd_dt,'%Y-%m') monthly, 
                  b.designation, 
                  SUM(a.qty) qty, 
                  SUM(a.montanttotal)amount, 
                  SUM(a.benefice) benef 
                  FROM (SELECT * FROM vente WHERE refinv='".$refinv."') a 
                  LEFT JOIN article b ON a.refarticle=b.refarticle 
                  WHERE a.upd_dt BETWEEN  '".$debut."' AND '".$fin."'  
                  GROUP BY monthly,a.refarticle 
                  ORDER BY 1 DESC 
                  )t2 
                  ON t1.monthly=t2.monthly AND t1.designation=t2.designation
                  where t1.designation=?
                  ORDER BY t1.monthly ASC, t1.designation DESC";
        }

        $stmt=$this->getDoctrine()->getConnection()->prepare($sql_temp);
        $stmt->execute();

        // Chart
        $data=$stmt->fetchAll();
        $list_result=array();

        foreach( $data as $value) {
            $des=$value['designation'];

            $stmt2=$this->getDoctrine()->getConnection()->prepare($sql);
            $stmt2->bindValue(1, $des);
            $stmt2->execute();

            // Chart
            $data2=$stmt2->fetchAll();

            array_push($list_result, array("designation"=>$des,"donnee"=>$data2));
        }
        $json2=json_encode($list_result);
        return new JsonResponse($json2);
    }

    public function loadCategorieRepAction($name,$debut,$fin,$refinv,Request $request){
        $sql="";
        if($name=='daily'){
            $sql="SELECT 
                t1.designation, 
                SUM(IFNULL(t2.qty,0)) qty 
               FROM 
                ( 
                      SELECT * 
                        FROM (SELECT * FROM rf_date WHERE upd_dt BETWEEN '".$debut."' AND '".$fin."' ) r1, 
                      (SELECT	DISTINCT c.designation 
                        FROM (SELECT * FROM vente WHERE refinv='".$refinv."') a 
                        LEFT JOIN article b ON a.refarticle=b.refarticle 
                        LEFT JOIN categorie c ON b.refcategorie=c.refcategorie 
                        WHERE a.upd_dt BETWEEN '".$debut."' AND '".$fin."'  
                GROUP BY a.upd_dt,b.refcategorie)r2 
                )t1 
                LEFT JOIN 
                      ( 
                        SELECT 
                          a.upd_dt, 
                c.designation, 
                SUM(a.qty) qty
                      FROM (SELECT * FROM vente WHERE refinv='".$refinv."') a 
                LEFT JOIN article b ON a.refarticle=b.refarticle 
                LEFT JOIN categorie c ON b.refcategorie=c.refcategorie 
                WHERE a.upd_dt BETWEEN '".$debut."' AND '".$fin."'  
                    GROUP BY a.upd_dt,b.refcategorie 
                ORDER BY 1 DESC 
                )t2 
                ON t1.upd_dt=t2.upd_dt AND t1.designation=t2.designation  
                GROUP BY t1.designation
                ORDER BY t1.designation DESC";
        }else if($name=='weekly'){
            $sql="SELECT 
                t1.designation, 
                SUM(IFNULL(t2.qty,0)) qty
                      FROM 
                ( 
                      SELECT * 
                        FROM (SELECT DISTINCT WEEK(upd_dt,1)weekly FROM rf_date WHERE upd_dt BETWEEN '".$debut."' AND '".$fin."') r1, 
                (SELECT	DISTINCT c.designation 
                      FROM (SELECT * FROM vente WHERE refinv='".$refinv."') a 
                      LEFT JOIN article b ON a.refarticle=b.refarticle 
                LEFT JOIN categorie c ON b.refcategorie=c.refcategorie 
                WHERE a.upd_dt BETWEEN '".$debut."' AND '".$fin."' 
                GROUP BY a.upd_dt,b.refcategorie)r2 
                )t1 
                LEFT JOIN 
                      ( 
                        SELECT 
                          WEEK(a.upd_dt,1)weekly, 
                        c.designation, 
                        SUM(a.qty) qty
                          FROM (SELECT * FROM vente WHERE refinv='".$refinv."') a 
                          LEFT JOIN article b ON a.refarticle=b.refarticle 
                          LEFT JOIN categorie c ON b.refcategorie=c.refcategorie 
                          WHERE a.upd_dt BETWEEN '".$debut."' AND '".$fin."' 
                GROUP BY weekly,b.refcategorie 
                ORDER BY 1 DESC 
                )t2 
                ON t1.weekly=t2.weekly AND t1.designation=t2.designation 
                GROUP BY t1.designation
                ORDER BY t1.designation DESC";
        }else if($name=='monthly'){
            $sql="SELECT 
                  t1.designation, 
                  SUM(IFNULL(t2.qty,0)) qty
                        FROM 
                  ( 
                        SELECT * 
                          FROM (SELECT DISTINCT DATE_FORMAT(upd_dt,'%Y-%m')monthly FROM rf_date WHERE upd_dt BETWEEN '".$debut."' AND '".$fin."') r1, 
                  (SELECT	DISTINCT c.designation 
                        FROM (SELECT * FROM vente WHERE refinv='".$refinv."') a 
                        LEFT JOIN article b ON a.refarticle=b.refarticle 
                  LEFT JOIN categorie c ON b.refcategorie=c.refcategorie 
                  WHERE a.upd_dt BETWEEN '".$debut."' AND '".$fin."' 
                  GROUP BY a.upd_dt,b.refcategorie)r2 
                  )t1 
                  LEFT JOIN 
                        ( 
                          SELECT 
                            DATE_FORMAT(a.upd_dt,'%Y-%m')monthly, 
                  c.designation, 
                  SUM(a.qty) qty
                        FROM (SELECT * FROM vente WHERE refinv='".$refinv."') a 
                  LEFT JOIN article b ON a.refarticle=b.refarticle 
                  LEFT JOIN categorie c ON b.refcategorie=c.refcategorie 
                  WHERE a.upd_dt BETWEEN '".$debut."' AND '".$fin."' 
                  GROUP BY monthly,b.refcategorie 
                  ORDER BY 1 DESC 
                  )t2 
                  ON t1.monthly=t2.monthly AND t1.designation=t2.designation 
                  GROUP BY t1.designation
                  ORDER BY t1.designation DESC";
        }
        $stmt=$this->getDoctrine()->getConnection()->prepare($sql);
        $stmt->execute();

        // Chart
        $data=$stmt->fetchAll();
        $json=json_encode($data);

        return new JsonResponse($json);
    }

    public function loadCategorieAction($name,$debut,$fin,$refinv,Request $request){
        $sql="";
        $sql_temp="SELECT	DISTINCT c.designation 
                        FROM (SELECT * FROM vente WHERE refinv='".$refinv."') a 
                        LEFT JOIN article b ON a.refarticle=b.refarticle 
                        LEFT JOIN categorie c ON b.refcategorie=c.refcategorie 
                        WHERE a.upd_dt BETWEEN '".$debut."' AND '".$fin."'  
                    GROUP BY a.upd_dt,b.refcategorie";

        if($name=='daily'){
            $sql="SELECT 
                    t1.*, 
                    IFNULL(t2.qty,0) qty, 
                    IFNULL(t2.amount,0) amount, 
                    IFNULL(t2.benef,0) benef 
                          FROM 
                    ( 
                          SELECT * 
                            FROM (SELECT * FROM rf_date WHERE upd_dt BETWEEN '".$debut."' AND '".$fin."' ) r1, 
                          (SELECT	DISTINCT c.designation 
                            FROM (SELECT * FROM vente WHERE refinv='".$refinv."') a 
                            LEFT JOIN article b ON a.refarticle=b.refarticle 
                            LEFT JOIN categorie c ON b.refcategorie=c.refcategorie 
                            WHERE a.upd_dt BETWEEN '".$debut."' AND '".$fin."'  
                    GROUP BY a.upd_dt,b.refcategorie)r2 
                    )t1 
                    LEFT JOIN 
                          ( 
                            SELECT 
                              a.upd_dt, 
                    c.designation, 
                    SUM(a.qty) qty, 
                    SUM(a.montanttotal)amount, 
                    SUM(a.benefice) benef 
                          FROM (SELECT * FROM vente WHERE refinv='".$refinv."') a 
                    LEFT JOIN article b ON a.refarticle=b.refarticle 
                    LEFT JOIN categorie c ON b.refcategorie=c.refcategorie 
                    WHERE a.upd_dt BETWEEN '".$debut."' AND '".$fin."'  
                        GROUP BY a.upd_dt,b.refcategorie 
                    ORDER BY 1 DESC 
                    )t2 
                    ON t1.upd_dt=t2.upd_dt AND t1.designation=t2.designation
                      WHERE t1.designation=?
                      ORDER BY t1.upd_dt ASC, t1.designation DESC";
        }else if($name=='weekly'){
            $sql="SELECT 
                            t1.*, 
                    IFNULL(t2.qty,0) qty, 
                    IFNULL(t2.amount,0) amount, 
                    IFNULL(t2.benef,0) benef 
                          FROM 
                    ( 
                          SELECT * 
                            FROM (SELECT DISTINCT WEEK(upd_dt,1)weekly FROM rf_date WHERE upd_dt BETWEEN '".$debut."' AND '".$fin."') r1, 
                    (SELECT	DISTINCT c.designation 
                          FROM (SELECT * FROM vente WHERE refinv='".$refinv."') a 
                          LEFT JOIN article b ON a.refarticle=b.refarticle 
                    LEFT JOIN categorie c ON b.refcategorie=c.refcategorie 
                    WHERE a.upd_dt BETWEEN '".$debut."' AND '".$fin."' 
                    GROUP BY a.upd_dt,b.refcategorie)r2 
                    )t1 
                    LEFT JOIN 
                          ( 
                            SELECT 
                              WEEK(a.upd_dt,1)weekly, 
                            c.designation, 
                            SUM(a.qty) qty, 
                            SUM(a.montanttotal)amount, 
                            SUM(a.benefice) benef 
                              FROM (SELECT * FROM vente WHERE refinv='".$refinv."') a 
                              LEFT JOIN article b ON a.refarticle=b.refarticle 
                              LEFT JOIN categorie c ON b.refcategorie=c.refcategorie 
                              WHERE a.upd_dt BETWEEN '".$debut."' AND '".$fin."' 
                    GROUP BY weekly,b.refcategorie 
                    ORDER BY 1 DESC 
                    )t2 
                    ON t1.weekly=t2.weekly AND t1.designation=t2.designation
                     WHERE t1.designation=?
                    ORDER BY t1.weekly ASC, t1.designation DESC";
        }else if($name=='monthly'){
            $sql="SELECT 
                  t1.*, 
                  IFNULL(t2.qty,0) qty, 
                  IFNULL(t2.amount,0) amount, 
                  IFNULL(t2.benef,0) benef 
                        FROM 
                  ( 
                        SELECT * 
                          FROM (SELECT DISTINCT DATE_FORMAT(upd_dt,'%Y-%m')monthly FROM rf_date WHERE upd_dt BETWEEN '".$debut."' AND '".$fin."') r1, 
                  (SELECT	DISTINCT c.designation 
                        FROM (SELECT * FROM vente WHERE refinv='".$refinv."') a 
                        LEFT JOIN article b ON a.refarticle=b.refarticle 
                  LEFT JOIN categorie c ON b.refcategorie=c.refcategorie 
                  WHERE a.upd_dt BETWEEN '".$debut."' AND '".$fin."' 
                  GROUP BY a.upd_dt,b.refcategorie)r2 
                  )t1 
                  LEFT JOIN 
                        ( 
                          SELECT 
                            DATE_FORMAT(a.upd_dt,'%Y-%m')monthly, 
                  c.designation, 
                  SUM(a.qty) qty, 
                  SUM(a.montanttotal)amount, 
                  SUM(a.benefice) benef 
                        FROM (SELECT * FROM vente WHERE refinv='".$refinv."') a 
                  LEFT JOIN article b ON a.refarticle=b.refarticle 
                  LEFT JOIN categorie c ON b.refcategorie=c.refcategorie 
                  WHERE a.upd_dt BETWEEN '".$debut."' AND '".$fin."' 
                  GROUP BY monthly,b.refcategorie 
                  ORDER BY 1 DESC 
                  )t2 
                  ON t1.monthly=t2.monthly AND t1.designation=t2.designation
                   WHERE t1.designation=?
                  ORDER BY t1.monthly ASC, t1.designation DESC";
        }
        $stmt=$this->getDoctrine()->getConnection()->prepare($sql_temp);
        $stmt->execute();

        // Chart
        $data=$stmt->fetchAll();
        $list_result=array();
        foreach( $data as $value) {
            $des=$value['designation'];

            $stmt2=$this->getDoctrine()->getConnection()->prepare($sql);
            $stmt2->bindValue(1, $des);
            $stmt2->execute();

            // Chart
            $data2=$stmt2->fetchAll();

            array_push($list_result, array("designation"=>$des,"donnee"=>$data2));
        }
        $json2=json_encode($list_result);
        return new JsonResponse($json2);
    }

    public function loadBeneficeAction($name,$debut,$fin,$refinv,Request $request){
        $sql="";
        if($name=='daily'){
            $sql="SELECT 
                        t.upd_dt, 
                IFNULL(res1.benefice,0) benef, 
                IFNULL(res2.amount,0) charge, 
                (IFNULL(res1.benefice,0)-IFNULL(res2.amount,0))ben_ttc, 
	            (IFNULL(res1.benefice,0)-IFNULL(res2.amount,0))/1.2 ben_ht 
                FROM rf_date t 
                LEFT JOIN 
                ( 
                SELECT 
                          upd_dt, 
                IFNULL(SUM(benefice),0) benefice 
                          FROM (SELECT * FROM vente WHERE refinv='".$refinv."')a 
                          WHERE upd_dt BETWEEN '".$debut."' AND '".$fin."' 
                GROUP BY upd_dt 
                ) res1 ON t.upd_dt=res1.upd_dt 
                LEFT JOIN 
                ( 
                SELECT 
                          upd_dt, 
                IFNULL(SUM(amount),0) amount 
                          FROM 
                          ( 
                            SELECT 
                              upd_dt, 
                            IFNULL(SUM(valeurpaye+endamnite),0) amount 
                            FROM journalpaiesalaire 
                            WHERE upd_dt BETWEEN '".$debut."' AND '".$fin."' 
              GROUP BY upd_dt 
              UNION ALL 
              SELECT 
              upd_dt, 
              IFNULL(SUM(montant),0) amount 
              FROM journalpaielogistique 
              WHERE upd_dt BETWEEN '".$debut."' AND '".$fin."' 
              GROUP BY upd_dt 
              )t2 
              GROUP BY upd_dt 
              )res2 
              ON t.upd_dt=res2.upd_dt 
              WHERE t.upd_dt BETWEEN '".$debut."' AND '".$fin."' 
              ORDER BY 1 ASC";
        }else if($name=='weekly'){
            $sql="SELECT 
                  WEEK(t.upd_dt,1)weekly, 
                      IFNULL(res1.benefice,0) benef, 
                      IFNULL(res2.amount,0) charge, 
                      (IFNULL(res1.benefice,0)-IFNULL(res2.amount,0))ben_ttc, 
	                  (IFNULL(res1.benefice,0)-IFNULL(res2.amount,0))/1.2 ben_ht
                        FROM rf_date t 
                        LEFT JOIN 
                      ( 
                        SELECT 
                          WEEK(upd_dt,1)weekly, 
                        IFNULL(SUM(benefice),0) benefice 
                          FROM (SELECT * FROM vente WHERE refinv='".$refinv."')a 
                          WHERE upd_dt BETWEEN '".$debut."' AND '".$fin."'  
                GROUP BY weekly 
                ) res1 ON WEEK(t.upd_dt,1)=res1.weekly 
                LEFT JOIN 
                      ( 
                        SELECT 
                          weekly, 
                        IFNULL(SUM(amount),0) amount 
                          FROM 
                          ( 
                            SELECT 
                              WEEK(upd_dt,1)weekly, 
                            IFNULL(SUM(valeurpaye+endamnite),0) amount 
                              FROM journalpaiesalaire 
                              WHERE upd_dt BETWEEN '".$debut."' AND '".$fin."'  
                GROUP BY weekly 
                UNION ALL 
                SELECT 
                WEEK(upd_dt,1)weekly, 
                IFNULL(SUM(montant),0) amount 
                      FROM journalpaielogistique 
                      WHERE upd_dt BETWEEN '".$debut."' AND '".$fin."'  
                GROUP BY weekly 
                )t2 
                    GROUP BY weekly 
                )res2 
                ON WEEK(t.upd_dt,1)=res2.weekly 
                WHERE t.upd_dt BETWEEN '".$debut."' AND '".$fin."'  
                GROUP BY weekly 
                ORDER BY 1 ASC";
        }else if($name=='monthly'){
            $sql="SELECT 
                DATE_FORMAT(t.upd_dt,'%Y-%m')monthly, 
                IFNULL(res1.benefice,0) benef, 
                IFNULL(res2.amount,0) charge, 
                (IFNULL(res1.benefice,0)-IFNULL(res2.amount,0))ben_ttc, 
	            (IFNULL(res1.benefice,0)-IFNULL(res2.amount,0))/1.2 ben_ht 
                      FROM rf_date t 
                LEFT JOIN 
                      ( 
                        SELECT 
                          DATE_FORMAT(upd_dt,'%Y-%m')monthly, 
                IFNULL(SUM(benefice),0) benefice 
                      FROM (SELECT * FROM vente WHERE refinv='".$refinv."')a 
                      WHERE upd_dt BETWEEN '".$debut."' AND '".$fin."'
                GROUP BY monthly 
                ) res1 ON DATE_FORMAT(t.upd_dt,'%Y-%m')=res1.monthly 
                LEFT JOIN 
                      ( 
                        SELECT 
                          monthly, 
                        IFNULL(SUM(amount),0) amount 
                          FROM 
                          ( 
                            SELECT 
                              DATE_FORMAT(upd_dt,'%Y-%m')monthly, 
                IFNULL(SUM(valeurpaye+endamnite),0) amount 
                      FROM journalpaiesalaire 
                      WHERE upd_dt BETWEEN '".$debut."' AND '".$fin."' 
                GROUP BY monthly 
                UNION ALL 
                SELECT 
                DATE_FORMAT(upd_dt,'%Y-%m')monthly, 
                IFNULL(SUM(montant),0) amount 
                      FROM journalpaielogistique 
                      WHERE upd_dt BETWEEN '".$debut."' AND '".$fin."' 
                GROUP BY monthly 
                )t2 
                GROUP BY monthly 
                )res2 
                ON DATE_FORMAT(t.upd_dt,'%Y-%m')=res2.monthly 
                WHERE t.upd_dt BETWEEN '".$debut."' AND '".$fin."' 
                GROUP BY monthly 
                ORDER BY 1 ASC";
        }

        $stmt=$this->getDoctrine()->getConnection()->prepare($sql);
        $stmt->execute();

        // Chart
        $data=$stmt->fetchAll();
        $json=json_encode($data);

        return new JsonResponse($json);
    }

    public function AjaxContentAction($name,$debut,$fin,$refinv,Request $request){
        $sql="";

        if($name=='daily'){
            $sql="SELECT 
              upd_dt, 
              COUNT(a.refvente) nb_vente, 
              SUM(IFNULL(b.montantpaye,0)) paye, 
              SUM(IFNULL(b.montantrestant,0)) rest, 
              SUM(IFNULL(amount,0)) amount, 
              SUM(IFNULL(benef,0)) benef 
              FROM 
              (SELECT 
              r.upd_dt, 
              refvente, 
              SUM(montanttotal) amount, 
              SUM(benefice) benef 
              FROM rf_date r 
              LEFT JOIN (SELECT * FROM vente WHERE refinv='".$refinv."') v ON r.upd_dt=v.upd_dt 
              WHERE r.upd_dt BETWEEN '".$debut."' AND '".$fin."' 
              GROUP BY r.upd_dt,refvente)a 
              LEFT JOIN (SELECT * FROM ventedebitcredit WHERE modepaiement<>'acompte') b ON a.refvente=b.refvente 
              GROUP BY upd_dt 
              ORDER BY upd_dt ASC";
        }else if($name=='weekly'){
            $sql="SELECT 
                weekly, 
                COUNT(a.refvente) nb_vente, 
                SUM(IFNULL(b.montantpaye,0)) paye, 
                      SUM(IFNULL(b.montantrestant,0)) rest, 
                      SUM(IFNULL(amount,0)) amount, 
                      SUM(IFNULL(benef,0)) benef 
                        FROM 
                        (SELECT 
                          WEEK(r.upd_dt,1) weekly, 
                          refvente, 
                          SUM(montanttotal) amount, 
                          SUM(benefice) benef 
                            FROM rf_date r 
                            LEFT JOIN (SELECT * FROM vente WHERE refinv='".$refinv."') v ON r.upd_dt=v.upd_dt 
                            WHERE r.upd_dt BETWEEN '".$debut."' AND '".$fin."' 
                GROUP BY weekly,refvente)a 
                LEFT JOIN (SELECT * FROM ventedebitcredit WHERE modepaiement<>'acompte') b ON a.refvente=b.refvente 
                GROUP BY weekly 
                ORDER BY weekly ASC ";
        }else if($name=='monthly'){
            $sql="SELECT 
                monthly, 
                      COUNT(a.refvente) nb_vente, 
                      SUM(IFNULL(b.montantpaye,0)) paye, 
                      SUM(IFNULL(b.montantrestant,0)) rest, 
                      SUM(IFNULL(amount,0)) amount, 
                      SUM(IFNULL(benef,0)) benef 
                        FROM 
                        (SELECT 
                          DATE_FORMAT(r.upd_dt,'%Y-%m') monthly, 
                refvente, 
                SUM(montanttotal) amount, 
                SUM(benefice) benef 
                      FROM rf_date r 
                LEFT JOIN (SELECT * FROM vente WHERE refinv='".$refinv."') v ON r.upd_dt=v.upd_dt 
                WHERE r.upd_dt BETWEEN '".$debut."' AND '".$fin."'
                GROUP BY monthly,refvente)a 
                 LEFT JOIN (SELECT * FROM ventedebitcredit WHERE modepaiement<>'acompte') b ON a.refvente=b.refvente 
                GROUP BY monthly 
                ORDER BY monthly ASC ";
        }

        $stmt=$this->getDoctrine()->getConnection()->prepare($sql);
        $stmt->execute();

        // Chart
        $data=$stmt->fetchAll();
        $json=json_encode($data);

        return new JsonResponse($json);
    }
}