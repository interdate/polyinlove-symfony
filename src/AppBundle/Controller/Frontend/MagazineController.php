<?php

namespace AppBundle\Controller\Frontend;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MagazineController extends Controller
{
    /**
     * @Route("/magazine/{page}", defaults={"page" = 1}, requirements={"page": "\d+"}, name="magazine")
     */
    public function indexAction(Request $request, $page)
    {
        $articlesRepo = $this->getDoctrine()->getManager()->getRepository('AppBundle:Article');
        $articles = $articlesRepo->findBy(
            array(
                'locale' => $request->getLocale(),
                'isActive' => true
            ),
            array(
                'date' => 'DESC'
            )
        );
        $paginator = $this->get('knp_paginator');
        $articles = $paginator->paginate(
            $articles,
            $page,
            10
        );

        return $this->render('frontend/magazine/index.html.twig', array(
            'articles' => $articles,
            'header' => $this->get('translator')->trans('Magazine'),
            'seo' => $this->getDoctrine()->getRepository('AppBundle:Seo')->findOneByPage('magazine'),
            'mobile' => $this->detectIsMobile(),
        ));
    }

    /**
     * @Route("/magazine/{uri}", name="magazine_article")
     */
    public function articleAction($uri)
    {
        return $this->render('frontend/magazine/index.html.twig', array(
            'article' => $this->getDoctrine()->getRepository('AppBundle:Article')->findOneByUri($uri),
            'mobile' => $this->detectIsMobile(),
        ));
    }

    private function detectIsMobile() {
        $mobileDetector = $this->get('mobile_detect.mobile_detector');
        return $mobileDetector->isMobile();
    }
}
