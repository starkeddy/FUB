<?php
/**
 * Created by IntelliJ IDEA.
 * User: root
 * Date: 4/1/17
 * Time: 9:57 PM
 */
namespace FUB\GeneralBundle\Controller;

use FUB\GeneralBundle\Entity;
use FUB\GeneralBundle\Forms\AdhesionType;
use FUB\GeneralBundle\Forms\DownloadType;
use FUB\GeneralBundle\Forms\UploadType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;

class AdhesionController extends Controller
{
    public function createContrat(){
        $ctr=date("Ymd");
        $count=1;
        //recuperer derniere enregistrement
        $stmt=$this->getDoctrine()->getConnection()->prepare('select max(id) as id from adhesion');
        $stmt->execute();
        foreach ($stmt->fetchAll() as $invest){
            $stmt2=$this->getDoctrine()->getConnection()->prepare('select * from contrats order by id_contrat desc limit 1');
            $stmt2->execute();
            foreach($stmt2->fetchAll() as $value) {
                $res= explode("-",$value['id_contrat']);
                $count=$res[1];
                $count++;
            }


            $ctr=$ctr.'-'.$count;
            $sql="insert into contrats values('".date('Y-m-d')."','".$ctr."','".$invest['id']."')";
            $stmt3=$this->getDoctrine()->getConnection()->prepare($sql);
            $stmt3->execute();
        }
    }
    public function validAction(Request $request)
    {
        $adhesion=new Entity\Adhesion();

        $form=$this->createForm(AdhesionType::class, $adhesion);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file=$adhesion->getPicture();
            $fileName = md5(uniqid()).'.'.$file->guessExtension();
            $file->move(
                $this->getParameter('pictures_directory'),
                $fileName
            );
            $adhesion->setPicture($fileName);
            $adhesion->setFlag(0);
            $em = $this->getDoctrine()->getManager();
            $em->persist($adhesion);
            $em->flush();echo '<script>alert("Votre demande a été envoyé")</script>';
            

            //creation contrat
            $this->createContrat();

            return $this->redirect($this->generateUrl('fub_general_adhesionFrom'));
        }

        $upload=new Entity\Upload();
        $form2=$this->createForm(UploadType::class,$upload);

        $form2->handleRequest($request);

        if ($form2->isSubmitted() && $form2->isValid()) {
            $file=$upload->getFile();
            $fileName = md5(uniqid()).'.'.$file->guessExtension();
            $file->move(
                $this->getParameter('pdf_directory'),
                $fileName
            );
            $upload->setFile($fileName);
            $em = $this->getDoctrine()->getManager();
            $em->persist($upload);
            $em->flush();
            echo '<script>alert("Votre demande a été envoyé")</script>';
        }


        return $this->render('FUBGeneralBundle:Default:adhesionForm.html.twig',array(
            'form2'=>$form2->createView(),
            'form'=>$form->createView(),
            'user'=>$adhesion
        ));
    }
}
