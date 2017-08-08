<?php

namespace FUB\GeneralBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Ob\HighchartsBundle\Highcharts\Highchart;


class DefaultController extends Controller
{
    public function indexAction()
    {
        $listAnnonce=$this->getDoctrine()
            ->getRepository('FUBGeneralBundle:Annonces')
            ->findBy(array('status'=>'enable'),array('id'=>'DESC'),array('limit'=>'1'))
        ;
        return $this->render('FUBGeneralBundle:Default:index.html.twig',array(
            'annonces'=>$listAnnonce
        ));
    }

    public function ruleAction()
    {
        return $this->render('FUBGeneralBundle:Default:rules.html.twig');
    }

    public function adhesionAction()
    {
        return $this->render('FUBGeneralBundle:Default:adhesionForm.html.twig');
    }

    public function contactAction()
    {
        return $this->render('FUBGeneralBundle:Default:contact.html.twig');
    }
}
