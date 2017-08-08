<?php

namespace FUB\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('FUBUserBundle:Default:index.html.twig');
    }
    
}
