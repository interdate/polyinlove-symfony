<?php

namespace AppBundle\Controller\Frontend;

use AppBundle\Entity\Banner;
use AppBundle\Entity\User;
use AppBundle\Form\Type\QuickSearchHomePageType;
use AppBundle\Services\BannerService;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;



class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
       // var_dump(123);die;
        $mobileDetector = $this->get('mobile_detect.mobile_detector');
        if ($this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {

            if($this->getUser()->getIsFrozen()){
                $em = $this->getDoctrine()->getManager();
                $em->persist($this->getUser()->setIsFrozen(false)->setFreezeReason(null));
                $em->flush();
            }

            return $this->getUser()->isAdmin()
                ? $this->redirect($this->generateUrl('admin_users'))
                : $this->redirect($this->generateUrl('user_homepage'));

        }

        $authenticationUtils = $this->get('security.authentication_utils');

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        $form = $this->createForm(QuickSearchHomePageType::class, new User());

        $banners = $this->get('banners')->getBanners('beforeLogin');

        $users = $this->getDoctrine()->getRepository('AppBundle:User')->onHomepage();

        return $this->render(($mobileDetector->isMobile()) ? 'frontend/index-mobile.html.twig' : 'frontend/index.html.twig', array(
            'last_username'     => $lastUsername,
            'error'             => $error,
            'articles'          => $this->getDoctrine()->getRepository('AppBundle:Article')->getOnHomepage(),
            'homePageBlocks'    => $this->getDoctrine()->getRepository('AppBundle:HomePage')->findAll(),
            'seo'               => $this->getDoctrine()->getRepository('AppBundle:Seo')->findOneByPage('homepage'),
            'slides'            => $this->getDoctrine()->getRepository('AppBundle:Slide')->findByIsActive(true),
            'form'              => $form->createView(),
            'users'             => $users,
            'banners'           => $banners,
            'isAndroid'=>       $mobileDetector->isAndroid(),
            'mobile' => $mobileDetector->isMobile(),
        ));
    }

}
