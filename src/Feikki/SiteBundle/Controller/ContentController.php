<?php

namespace Feikki\SiteBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

// these import the "@Route" and "@Template" annotations
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class ContentController extends Controller
{
    /**
     * 
     * @Template()
     */
    public function indexAction()
    {
        //return $this->render('FeikkiSiteBundle:content:index.html.twig');
        return array('pagetitle' => 'Feikki.fi - Frontpage');
    }
}