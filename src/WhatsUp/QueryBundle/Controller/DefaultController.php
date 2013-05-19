<?php

namespace WhatsUp\QueryBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        $prefilled = $this->getRequest()->get('name') ?: null;
        return $this->render(
            'WhatsUpQueryBundle:Default:index.html.twig', array(
                'pagetitle' => 'Availability search',
                'prefilled' => $prefilled
            )
        );
    }
}
