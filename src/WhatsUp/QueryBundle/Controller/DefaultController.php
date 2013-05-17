<?php

namespace WhatsUp\QueryBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('WhatsUpQueryBundle:Default:index.html.twig');
    }
}
