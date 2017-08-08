<?php
/**
 * Created by IntelliJ IDEA.
 * User: root
 * Date: 4/9/17
 * Time: 9:03 PM
 */
namespace FUB\GeneralBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use FUB\GeneralBundle\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;



class DashBoardController extends Controller{
    public function returnPDFResponseFromHTML($html){
        //set_time_limit(30); uncomment this line according to your needs
        // If you are not in a controller, retrieve of some way the service container and then retrieve it
        //$pdf = $this->container->get("white_october.tcpdf")->create('vertical', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        //if you are in a controlller use :
        $pdf = $this->get("white_october.tcpdf")->create('vertical', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->SetAuthor('FUB');
        $pdf->SetTitle(('Contrat dinvestissement'));
        $pdf->SetSubject('Contrat investissement');
        $pdf->setFontSubsetting(true);
        $pdf->SetFont('helvetica', '', 11, '', true);
        $pdf->SetMargins(5,5,5,5);
        $pdf->AddPage();

        $filename = 'contrat_d_investissement_FUB';

        $pdf->writeHTMLCell($w = 0, $h = 0, $x = '', $y = '', $html, $border = 0, $ln = 1, $fill = 0, $reseth = true, $align = '', $autopadding = true);
        $pdf->Output($filename.".pdf",'I'); // This will output the PDF as a response directly
    }

    public function dashboardAction(){
        // Chart
        $series = array(
            array("name" => "Data Serie Name",    "data" => array(1,2,4,5,6,3,8))
        );

        $ob = new Highchart();
        $ob->chart->renderTo('linechart');  // The #id of the div where to render the chart
        $ob->title->text('Chart Title');
        $ob->xAxis->title(array('text'  => "Horizontal axis title"));
        $ob->yAxis->title(array('text'  => "Vertical axis title"));
        $ob->series($series);

        return $this->render('FUBGeneralBundle:Dashboard:dashboard.html.twig', array(
            'chart' => $ob
        ));
    }

    public function adhesionListAction(){
        //affiche la liste des demande d'adhesion
        $demande=$this->getDoctrine()
            ->getRepository('FUBGeneralBundle:Adhesion')
            ->findBy(array('flag'=>0));

        /*if(!$demande){
            throw $this->createNotFoundException("Aucune demande d'adhésion en ligne effectuée");
        }*/

        $upload=$this->getDoctrine()
            ->getRepository('FUBGeneralBundle:Upload')
            ->findAll();

        return $this->render('FUBGeneralBundle:Dashboard:adhesionList.html.twig',array(
            'demande'=>$demande,
            'upload'=>$upload
        ));
    }

    public function uploadAdhesionDeleteAction($uploadId){
        $em = $this->getDoctrine()->getManager();
        $upload=$em->getRepository('FUBGeneralBundle:Upload')
            ->findBy(array('file'=>$uploadId));
        foreach ($upload as $res=>$obj){
            $em->remove($obj);
            $em->flush();
            unlink($this->getParameter('pdf_directory')."/".$uploadId);
            echo '<script>alert("Le fichier PDF a été supprimé")</script>';
        }
        return $this->adhesionListAction();
    }

    public function investisseurDeleteAction($investId){
        $em = $this->getDoctrine()->getManager();
        $investisor=$em->getRepository('FUBGeneralBundle:Adhesion')
            ->findBy(array('id'=>$investId));
        foreach ($investisor as $res=>$obj){
            $em->remove($obj);
            $em->flush();
            unlink($this->getParameter('pictures_directory')."/".$obj->getPicture());
            echo '<script>alert("Investisseur résilié")</script>';
        }
        return $this->listInvestisseurAction();
    }

    public function listInvestisseurAction(){
        $invest=$this->getDoctrine()
            ->getRepository('FUBGeneralBundle:Adhesion')
            ->findBy(array('flag'=>1));

        return $this->render('FUBGeneralBundle:Dashboard:listInvestisseur.html.twig',array(
            'investisseurs'=>$invest
        ));
    }

    public function detailInvestisseurAction($investId, Request $request){
        $em = $this->getDoctrine()->getManager();
        $investisseur=$em->getRepository('FUBGeneralBundle:Adhesion')
            ->findBy(array('id'=>$investId));

        $form = $this->createFormBuilder()
            ->add('view',SubmitType::class)
            ->getForm();
        $form->handleRequest($request);

        $form2 = $this->createFormBuilder()
            ->add('delete',SubmitType::class)
            ->getForm();
        $form2->handleRequest($request);

        if ($form->isValid()) {
            if ($form->get('view')->isClicked()) {
                return $this->redirectToRoute('fub_general_dashboard_detailInvestisseurPDF',array(
                    'investId'=>$investId
                ));
            }
        }
        if($form2->isValid()){
            if($form2->get('delete')->isClicked()){
                return $this->redirectToRoute('fub_general_dashboard_resilierInvestisseur',array(
                    'investId'=>$investId
                ));
            }
        }
        return $this->render('FUBGeneralBundle:Dashboard:detailInvestisseur.html.twig',array(
            'demande'=>$investisseur,
            'form'=>$form->createView(),
            'form2'=>$form2->createView()
        ));
    }

    public function adhesionViewAction($demande, Request $request){
        $em = $this->getDoctrine()->getManager();
        $adhesion=$em->getRepository('FUBGeneralBundle:Adhesion')
            ->findBy(array('id'=>$demande));

        $form = $this->createFormBuilder()
            ->add('view',SubmitType::class)
            ->add('save', SubmitType::class)
            ->add('denie', SubmitType::class)
            ->getForm();

        $form->handleRequest($request);

        if ($form->isValid()) {
            if($form->get('view')->isClicked()){
                return $this->redirectToRoute('fub_general_dashboard_viewcontrat',array(
                    'demande'=>$demande
                ));
                echo '<script>alert("La demande a été validé")</script>';
            }

            if($form->get('denie')->isClicked()){
                foreach ($adhesion as $res=>$obj){
                    $em=$this->getDoctrine()
                        ->getManager();
                    $em->remove($obj);
                    unlink($this->getParameter('pictures_directory')."/".$obj->getPicture());
                    $em->flush();


                    //suppression contrat
                    $base='fubmad';

                    $stmt=$this->getDoctrine()->getConnection()->prepare('delete from '.$base.'.contrats where id_invest='.$demande.' order by id_contrat desc limit 1');
                    $stmt->execute();

                    echo '<script>alert("La demande a été supprimé")</script>';
                    return $this->redirectToRoute('fub_general_dashboard_listadhesion');
                }
            }
            if($form->get('save')->isClicked()){
                foreach ($adhesion as $res=>$obj){
                    $obj->setFlag(1);
                    $em->flush();
                }
                echo '<script>alert("La demande a été cloturé")</script>';
                return $this->redirectToRoute('fub_general_dashboard_listadhesion');
            }
        }


        return $this->render('FUBGeneralBundle:Dashboard:adhesionView.html.twig',array(
            'demande'=>$adhesion,
            'form'=>$form->createView()
        ));
    }

    public function detailInvestisseurPDFAction($investId){
        $adhesion=$this->getDoctrine()
            ->getRepository('FUBGeneralBundle:Adhesion')
            ->findBy(array('id'=>$investId));

        $html = $this->renderView('FUBGeneralBundle:Dashboard:detailInvestisseurPDF.html.twig',array(
            'demande'=>$adhesion
        ));

        $this->returnPDFResponseFromHTML($html);
    }

    public function contrattopdfAction($demande){
        $adhesion=$this->getDoctrine()
            ->getRepository('FUBGeneralBundle:Adhesion')
            ->findBy(array('id'=>$demande));

        $contratID='';

        $stmt=$this->getDoctrine()->getConnection()->prepare('select * from contrats where id_invest='.$demande.' order by id_contrat desc limit 1');
        $stmt->execute();
        foreach($stmt->fetchAll() as $value) {
            $contratID=$value['id_contrat'];
        }

        $html = $this->renderView('FUBGeneralBundle:Dashboard:adhesionViewPDF.html.twig',array(
            'demande'=>$adhesion,
            'contratID'=>$contratID
        ));

        $this->returnPDFResponseFromHTML($html);
    }

    public function annonceAction(Request $request){
        $annonce=new Annonces();
        $form=$this->createForm(AnnonceType::class, $annonce);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $annonce->setStatus('enable');
            $em->persist($annonce);
            $em->flush();
            echo '<script>alert("Votre annonce a été publié")</script>';
            return $this->redirect($this->generateUrl('fub_general_dashboard_annonce'));
        }

        $form2 = $this->createFormBuilder()
            ->add('disable',SubmitType::class)
            ->getForm();

        $form2->handleRequest($request);

        if ($form2->isValid()) {
            if ($form2->get('disable')->isClicked()) {
                $this->deleteAnnonce();
            }
        }

        $listAnnonce=$this->getDoctrine()
            ->getRepository('FUBGeneralBundle:Annonces')
            ->findBy(array(),array('id'=>'DESC'))
            ;

        return $this->render('FUBGeneralBundle:Dashboard:annonce.html.twig',array(
            'form'=>$form->createView(),
            'form2'=>$form2->createView(),
            'annonceHistorique'=>$listAnnonce
        ));
    }
    public function deleteAnnonce(){
        $stmt=$this->getDoctrine()->getConnection()->prepare("update annonces set status='disable' where status='enable'");
        $stmt->execute();
    }

    public function getNombreInvestisseurAction(){
        $sql="select count(*)nb from adhesion where flag=1";

        $stmt=$this->getDoctrine()->getConnection()->prepare($sql);
        $stmt->execute();
        $data=$stmt->fetchAll();
        $json=json_encode($data);
        return new JsonResponse($json);
    }

    public function getNombreDemandeAction(){
        $sql="select
            sum(nb) nb_demande
        from 
        (select count(*)nb from adhesion where flag=0
        union all
        select count(*)nb from uploaded) res";

        $stmt=$this->getDoctrine()->getConnection()->prepare($sql);
        $stmt->execute();
        $data=$stmt->fetchAll();
        $json=json_encode($data);
        return new JsonResponse($json);
    }
}

