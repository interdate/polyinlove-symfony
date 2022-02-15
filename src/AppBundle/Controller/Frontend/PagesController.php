<?php

namespace AppBundle\Controller\Frontend;

use AppBundle\Entity\Banner;
use AppBundle\Entity\Page;
use AppBundle\Entity\User;
use AppBundle\Form\Type\ContactType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;


class PagesController extends Controller
{
    /**
     * @Route("pages/{uri}", name="pages_page")
     */
    public function indexAction(Request $request, $uri)
    {
//        dump($this->getDoctrine()->getManager()->getRepository('AppBundle:Page')->findOneByUri($uri));die;
        return $this->render('frontend/pages/index.html.twig', array(
            'page' => $this->getDoctrine()->getManager()->getRepository('AppBundle:Page')->findOneByUri($uri),
            'mobile' => $this->detectIsMobile(),
        ));
    }
    
    /**
     * @Route("FAQ", name="faq")
     */
    public function faqAction()
    {
        return $this->render('frontend/pages/faq.html.twig', array(
            'categories' => $this->getDoctrine()->getManager()->getRepository('AppBundle:FaqCategory')->findByIsActive(true),
            'seo' => $this->getDoctrine()->getRepository('AppBundle:Seo')->findOneByPage('faq'),
            'mobile' => $this->detectIsMobile(),
        ));
    }

    /**
     * @Route("/banner/{id}/count", name="banner_click123")
     */
    public function bannerCount(Banner $banner) {
        $em = $this->getDoctrine()->getManager();
        $banner->setClickCount($banner->getClickCount() + 1);
        $em->persist($banner);
        $em->flush();
        return new Response();
    }

    /**
     * @Route("Contact_us", name="contact")
     */
    public function contactAction(Request $request)
    {
        $sent = false;

        $user = $this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')
            ? $this->getUser()
            : new User()
        ;

        $form = $this->createForm(ContactType::class);

        if($request->isMethod('Post')){

//            var_dump($request->request->all());die;
            $form->handleRequest($request);
            if($form->isValid() && $form->isSubmitted()){
            	$mobileDetector = $this->get('mobile_detect.mobile_detector');
            	
            	if($mobileDetector->isMobile()){
            		$platform = 'Mobile';
            	}else {
            		$platform = 'Desktop';
            	}
            	
            	$email = ($form->get('email')->getData()) ? $form->get('email')->getData() : $form->getData()->getEmail();
           
                $subject = "PolyinLove | contact us | #" .  $user->getId()    . " | " . $form->get('subject')->getData();
                $text = $form->get('text')->getData();
                $body = '<html lang="en">
						<head>
							<meta http-equiv="content-type" content="text/html; charset=utf-8">
						</head>
						<body>
                		<div dir="ltr">';
                $body .= $text . '<br>';
                $body .= 'sent from: '. $platform . '<br>';
                $body .= 'users email: ' .$email .'<br>';
                $body .= '</div>
                		</body>
					</html>';
            	$headers = "MIME-Version: 1.0" . "\r\n";
                $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
                $settings = $this->getDoctrine()->getRepository('AppBundle:Settings')->find(1);
                $headers .= 'From: ' . $settings->getContactEmail() . ' <' . $settings->getContactEmail() . '>' . "\r\n";
                $headers .= 'Reply-To: ' . $email . "\r\n";
                mail($settings->getContactEmail(),$subject,$body,$headers, '-f' . $email);
//                mail('vita@interdate-ltd.co.il',$subject,$body,$headers, '-f' . $email);

//                var_dump(123);die;
//                var_dump(mail('vita@interdate-ltd.co.il',$subject,$body,$headers, '-f' . $email));die;
//                ( mail('vita@interdate-ltd.co.il',$subject,$body,$headers));die;
                $sent = true;
                $form = $this->createForm(ContactType::class);
            } else {
//                var_dump($form->getErrorsAsString());die;
            }
        }

        return $this->render('frontend/pages/contact.html.twig', array(
            'form' => $form->createView(),
            'sent' => $sent,
            'mobile' => $this->detectIsMobile(),
        ));
    }

    private function detectIsMobile() {
        $mobileDetector = $this->get('mobile_detect.mobile_detector');
        return $mobileDetector->isMobile();
    }

    /**
     * @Route("/name-exist/{username}", name="username_exist")
     */

    public function usernameIsExist($username) {
        $users = $this->getDoctrine()->getManager()->getRepository('AppBundle:User');
        $user = $users->findOneBy(array('username' => $username));
        echo $user ? true : false;die;
    }



    /**
     * @Route("/test/zodiac", name="test_zodiac")
     */

    public function zodiac() {
        $em = $this->getDoctrine()->getManager();
        $users = $em->getRepository('AppBundle:User')->findAll();
        $i = 0;
        foreach ($users as $user) {
            var_dump($i++);
            $user->setBirthday($user->getBirthday());
            $em->persist($user);
            $em->flush();
        }

        var_dump('done');

//        $user = $this->getDoctrine()->getManager()->getRepository('AppBundle:User')->find(5815);
//        $date = $user->getBirthday();
//        $user = $this->getDoctrine()->getManager()->getRepository('AppBundle:Zodiac')->testGetZodiacByDate($date);
    }
}
