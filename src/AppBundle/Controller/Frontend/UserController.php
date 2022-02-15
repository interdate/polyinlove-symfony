<?php

namespace AppBundle\Controller\Frontend;


use AppBundle\Entity\Banner;
use AppBundle\Entity\Notifications;
use AppBundle\Entity\ShowPhoto;
use AppBundle\Entity\User;
use AppBundle\Entity\Photo;

use AppBundle\Entity\UserMessengerNotifications;
use AppBundle\Entity\Verify;
use AppBundle\Entity\Video;
use AppBundle\Form\Type\AdvancedSearchType;
use AppBundle\Form\Type\ChangePasswordType;
use AppBundle\Form\Type\ContactType;
use AppBundle\Form\Type\MobileQuickSearchType;
use AppBundle\Form\Type\ProfileOneType;
use AppBundle\Form\Type\ProfileSiteSettingsType;
use AppBundle\Form\Type\ProfileTwoType;
use AppBundle\Form\Type\ProfileThreeType;
use AppBundle\Form\Type\QuickSearchType;
use AppBundle\Form\Type\ProfileSettingsType;

use     Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Component\HttpFoundation\Cookie;


class UserController extends Controller
{


    /**
     * @Route("/user/edit/old/photos", name="user_profil43521e")
     */

    function testfnct()
    {

        $qb = $this->getDoctrine()->getRepository('AppBundle:User')->createQueryBuilder('u')
            //->where('u.id <= 9000')
            //->andWhere('u.id > 8000')
            ->where('u.id <= 4000')
            //->where('u.email LIKE :email')
            //->andWhere('u.username = :username')
            //->setParameter('email', '%interdate%')
            //->setParameter('username', 'claire')
        ;
        $qb->join('u.photos', 'ph', 'WITH',
            $qb->expr()->eq('ph.isValid', true)
        );
        $users = $qb->getQuery()
            ->getResult();
//        var_dump(3414);die;


        $u = 1;
        $i = 1;
        //$dataManager = $this->container->get('liip_imagine.data.manager');
        foreach ($users as $user) {
            echo '<h1> ---------------------' . $u . ': ' . $user->getId() . '------------------</h1>';
            foreach ($user->getPhotos() as $photo) {
                $photo->resize(600);
                $photo->resizeFace(150);
//                if(is_file($face = $photo->getFaceAbsolutePath()) && filesize($face) > 0) {
//                    $optimize_face = $dataManager->find('optimize_face', $photo->getFaceWebPath(false));
//
//                    $this->savePhoto($optimize_face->getContent(), $face);
//                }
//
//                if (is_file($original_photo = $photo->getAbsoluteWebPath()) && filesize($original_photo) > 0) {
//                    $optimize_original = $dataManager->find('optimize_original', $photo->getWebPath());
//
//                    $this->savePhoto($optimize_original, $original_photo);
//                }

            }
            $u++;

        }

        return new JsonResponse(array('users' => $users));
    }


    /**
     * @Route("/user/profile/{tab}", defaults={"tab" = 1}, name="user_profile")
     */
    public function profileAction(Request $request, $tab)
    {

        if ($tab == 4) {
            return $this->render('frontend/user/profile/index.html.twig', array(
                'tab' => $tab,
                'mobile' => $this->detectIsMobile(),
            ));

        }


        $user = $this->getUser();
        $form = $this->createForm($this->getProfileType($tab), $user);
        $errors = false;
        $userRepo = $this->getDoctrine()->getRepository('AppBundle:User');
        $errors_text = [];

        if ($request->isMethod('POST')) {


            if ($submitted = $request->request->get('profile_one')) {
                $post = $userRepo->removeWordsBlocked($submitted, array('username'));
                $request->request->set('profile_one', $post);
                if (mb_strlen(trim($post['username'])) <= 0) {
                    $errors = true;
                    $errors_text['username'] = $this->get('translator')->trans('Please type in username');
                }
            } elseif ($submitted = $request->request->get('profile_two')) {
                $post = $userRepo->removeWordsBlocked($submitted, array('relationshipTypeDetails', 'sexOrientationDetails', 'lookingForDetails'));
                $request->request->set('profile_two', $post);
                if (!is_numeric($post['height'])) {
//                    dump(is_numeric($post['height']));die;
                    $errors = true;
//                    var_dump($errors);die;
                    $errors_text['height'] = $this->get('translator')->trans('Height must contain only numbers');
                } else if ((int)$post['height'] > 230 || (int)$post['height'] < 40) {
                    $errors = true;
                    $errors_text['height'] = $this->get('translator')->trans('Please type in your real height');
                }
            } elseif ($submitted = $request->request->get('profile_three')) {

                $post = $userRepo->removeWordsBlocked($submitted, array('about', 'looking'));
                $request->request->set('profile_three', $post);

                if (mb_strlen(trim($post['about'])) <= 0) {
                    $errors = true;
                    $errors_text['about'] = $this->get('translator')->trans('"About me" must be at least ten letters long');
                }

                if (mb_strlen(trim($post['looking'])) <= 0) {
                    $errors = true;
                    $errors_text['looking'] = $this->get('translator')->trans('"Looking for" must be at least ten letters long');
                }
            }

            $form->handleRequest($request);
            if (!$errors && $form->isSubmitted() && $form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($user);
                $em->flush();
                $this->addFlash('success', $this->get('Translator')->trans('Changes saved'));
            } else {
                $errors = true;
            }
        }

        return $this->render('frontend/user/profile/index.html.twig', array(
            'tab' => $tab,
            'form' => $form->createView(),
            'errors' => $errors,
            'errors_text' => $errors_text,
            'mobile' => $this->detectIsMobile(),
        ));
    }


    /**
     * @Route("/activation/send", name="user_activation_send")
     */
    public function sendActivationMessage(Request $request)
    {
        // var_dump(234);die;
        $repeat = $request->request->get('repeat', false);
        // var_dump($request->request->all());die;
        $user = $this->getUser(); //$this->container->get('security.token_storage')->getToken()->getUser();
//        var_dump($user);die;
        $settings = $this->getDoctrine()->getRepository('AppBundle:Settings')->find(1);
        $em = $this->getDoctrine()->getManager();


        if ($user->getCodeCount() >= 2) {
            //  $error = true;
            $text = $this->get('Translator')->trans('Max code sent, contact us');
        } else
            if ($user->getCodeCount() !== 0 && !$repeat) {
                // $error = true;
            } else {

//                if (($phone = $user->getPhone()) && !$user->getIsActivated()) {
//
//
//                    $code = rand(100000, 999999);
////                    for ($i = 0; $i < 6; $i++) {
////                        $code .= $str[rand(0, 61)];
////                    }
//
//                    $sms = "<SMS>
//￼<USERNAME>" . $settings->getSmsUsername() . "</USERNAME>
//￼<PASSWORD>" . $settings->getSmsPassword() . "</PASSWORD>
//￼<SENDER_PREFIX>ALFA</SENDER_PREFIX>
//￼<SENDER_SUFFIX>" . $settings->getSmsSufix() . "</SENDER_SUFFIX>
//￼<MSGLNG>HEB</MSGLNG>
//￼<MSG> קוד ההפעלה שלך בפולידייט הוא " . $code . "</MSG>
//￼<MOBILE_LIST>
//￼<MOBILE_NUMBER>" . $phone . "</MOBILE_NUMBER>
//￼</MOBILE_LIST>
//￼<UNICODE>False</UNICODE>
//￼<USE_PERSONAL>False</USE_PERSONAL>
//￼</SMS>";
//
//                    $soapClient = new \SoapClient("http://www.smsapi.co.il/Web_API/SendSMS.asmx?WSDL");
//                    $ap_param = array(
//                        'XMLString' => $sms
//                    );
//                    $info = $soapClient->__call("SUBMITSMS", array($ap_param));
//
//
//                    if ($info) {
//
//                        $user->setCodeCount($user->getCodeCount() + 1);
//                        $user->setCode($code);
//                        $user->setCodeDate(new \DateTime());
//                        $em->persist($user);
//                        $em->flush();
//
//                        $text = $this->get('Translator')->trans('Code sent');
//
//                    }
//
//                }
            }

        //  var_dump($text);die;

        return new JsonResponse(array(
            //  'error' => $error,
            'text' => $text ?? '',
            'mobile' => $this->detectIsMobile(),
        ));


//        $str = 'qwertyuiopasdfghjklzxcvbnm1234567890QWERTYUIOPASDFGHJKLZXCVBMN';
//        $code = '';
//        for ($i = 0; $i < 6; $i++ ) {
//            $n = $str[rand(0, 61)];
//            $code .= $n;
//        }

    }


    /**
     * @Route("/activation/sendEmail", name="user_activation_send_email")
     */
    public function sendActivationEmail(Request $request)
    {

        $repeat = $request->request->get('repeat', false);
        $user = $this->getUser();
        $settings = $this->getDoctrine()->getRepository('AppBundle:Settings')->find(1);
        $em = $this->getDoctrine()->getManager();

        if ($user->getCodeCount() >= 4) {
            //  $error = true;
            $text = $this->get('Translator')->trans('Max code sent, contact us');
        } else
            if ($user->getCodeCount() !== 0 && !$repeat) {
                // $error = true;
            } else {

                $email = $user->getEmail();

                $code = 111111;
                try {
                    $code = random_int(100000, 999999);
                } catch (\Exception $e) {
                    return $e;
                }
                $emailHeader = "MIME-Version: 1.0" . "\r\n";
                $emailHeader .= "Content-type:text/html;charset=UTF-8" . "\r\n";
                $settings = $this->getDoctrine()->getRepository('AppBundle:Settings')->find(1);
                $emailHeader .= 'From: ' . $settings->getContactEmail() . ' <' . $settings->getContactEmail() . '>' . "\r\n";
                $emailHeader .= "{$this->getParameter('site_name')} activation code";

                $emailBody = $this->get('translator')->trans(
                    "Your activation code for {$this->getParameter('site_name')} is {{code}}"
                    , ['{{code}}' => $code]);

                $wasSent = mail($email, $emailHeader, $emailBody);
                if ($wasSent) {

                    $user->setCodeCount($user->getCodeCount() + 1);
                    $user->setCode($code);
                    $user->setCodeDate(new \DateTime());
                    $em->persist($user);
                    $em->flush();

                    $text = $this->get('Translator')->trans('Code sent');
                }
            }
        return new JsonResponse(array(
            //  'error' => $error,
            'text' => $text ?? '',
            'mobile' => $this->detectIsMobile(),
        ));


    }


    /**
     * @Route("/user/bingo/{likeMeId}", defaults={"likeMeId" = 0}, name="user_bingo")
     */
    public function bingoAction($likeMeId)
    {

//        var_dump(123);die;
        if ($this->getUser()) {
            if ($likeMeId > 0) {
//            var_dump($likeMeId);die;
                $result = $this->getDoctrine()->getManager()->getRepository('AppBundle:User')->setSplashShowBingo($likeMeId, $this->getUser()->getId());
            } else {
                $result = $this->getDoctrine()->getManager()->getRepository('AppBundle:User')->getSplashBingo($this->getUser());
            }

            $result2 = array(
                'bingo' => $result,
            );

            return new Response(json_encode($result2));
        }
    }


    /**
     * @Route("/user/show/photo/allowed/{id}", name="view_allowed_show_photo")
     */
    public function viewShowPhotoAction(ShowPhoto $request)
    {
        $em = $this->getDoctrine()->getManager();
        $request->setIsNotificated(true);
        $em->persist($request);
        $em->flush();
        return true;

    }

    /**
     * @Route("/activation", name="user_activation")
     */
    public function activationAction(Request $request)
    {

        /* $error =*/

//        This is the code we use in polydate etc. to send local Israeli sms. It has been deactivated so we can send
//        emails for the international community

//        $this->sendActivationMessage($request);

        $this->sendActivationEmail($request);

        $user = $this->getUser();

        $form = $this->createForm(ContactType::class, $user);


        if ($request->isMethod('Post')) {
            //var_dump($request->request->get('contact'));die;

            if ($request->request->get('contact')) {
                $sent = $this->sendEmail($request);
//                var_dump($request->request->all());die;
            } else if ($code = $request->request->get('code')) {
                if ($user->getCode() !== $code) {
                    $error = $this->get('Translator')->trans('Wrong activation code');
                } else {
                    $lastSendDate = $user->getCodeDate();

                    $now = new \DateTime();
                    $hours = $now->diff($lastSendDate)->h;

                    if ($hours) {
                        $error = $this->get('Translator')->trans('Sorry, this request took to long. Please try again later');
                    } else {
                        $em = $this->getDoctrine()->getManager();
                        $user->setIsActivated(true);
                        $em->persist($user);
                        $em->flush();
                        return $this->redirectToRoute('user_homepage');
                    }
                }
            }

        }

        return $this->render('frontend/activation.html.twig', array(
            'form' => $form->createView(),
            'sent' => $sent ?? false,
            'error' => $error ?? '',
            'mobile' => $this->detectIsMobile(),
        ));
    }


    private
    function sendEmail($request)
    {

        $sent = false;
        $user = $this->getUser();

        $form = $this->createForm(ContactType::class);

        $form->handleRequest($request);

        if ($form->isValid() && $form->isSubmitted()) {


            $mobileDetector = $this->get('mobile_detect.mobile_detector');

            if ($mobileDetector->isMobile()) {
                $platform = 'Mobile';
            } else {
                $platform = 'Desktop';
            }

            $email = ($form->get('email')->getData()) ? $form->get('email')->getData() : $form->getData()->getEmail();
            $subject = "{$this->getParameter('site_name')} | contact us (activation) | " . '#' . $user->getId() . " | " . $form->get('subject')->getData();
            $text = $form->get('text')->getData();
            $body = '<html lang="he">
						<head>
							<meta http-equiv="content-type" content="text/html; charset=utf-8">
						</head>
						<body>
                		<div dir="rtl">';
            $body .= $text . '<br>';
            $body .= 'sent from: ' . $platform;
            $body .= '</div>
                		</body>
					</html>';
            $headers = "MIME-Version: 1.0" . "\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
            $headers .= 'From: ' . $email . ' <' . $email . '>' . "\r\n";
            $settings = $this->getDoctrine()->getRepository('AppBundle:Settings')->find(1);
            mail($settings->getContactEmail(), $subject, $body, $headers);
//            mail('vita@interdate-ltd.co.il, hillel@interdate-ltd.co.il', $subject, $body, $headers);
            $sent = true;

        }

        return $sent;
    }


    /**
     * @Route("/user/photos/homepage", name="user_photo_on_homepage")
     */
    public function photoIsOnHomepageAction()
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $user->setIsOnHomepage(!$user->getIsOnHomepage());
        $em->persist($user);
        $em->flush();
    }

    /**
     * @Route("/user/subscription-old", name="user_payment")
     */
    public function userSubscriptionAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $textBefore = $em->getRepository('AppBundle:TextBeforePayment')->findBy(array('isActive' => true), array('order' => 'asc'));
        $paymentSubscriptions = $em->getRepository('AppBundle:PaymentSubscription')->findBy(array('isActive' => true), array('order' => 'asc'));
        $tableTexts = $em->getRepository('AppBundle:TableTextPayment')->findBy(array('isActive' => true), array('order' => 'asc'));
        $textAfter = $em->getRepository('AppBundle:TextAfterPayment')->findBy(array('isActive' => true), array('order' => 'asc'));

        $banners = $this->get('banners')->getBanners('subscriptionPage', $this->getUser());
//        dump($banners);die;
        return $this->render('frontend/user/subscription.html.twig', array(
            'textBefore' => $textBefore,
            'payments' => $paymentSubscriptions,
            'tableTexts' => $tableTexts,
            'textAfter' => $textAfter,
            'banners' => $banners,
            'mobile' => $this->detectIsMobile(),
        ));
    }

    /**
     * @Route("/user/like/{id}", name="user_like", defaults={"id" = null})
     */
    public function usersLikeAction(User $toUser)
    {
        $currentUser = $this->get('security.token_storage')->getToken()->getUser();
        $user = $this->getDoctrine()->getManager()->getRepository('AppBundle:User');
        $result = $user->sendUserLike($currentUser, $toUser, $this->getParameter('base_url'), $this->getParameter('contact_email'), $this->getParameter('site_name'));

        if ($result == 'bingo' && !$toUser->isBingoPushToday()
            && ($user->getUsername() == 'elad' || $currentUser->getUsername() == 'elad')) {
            $data = [
                'contact_id' => $toUser->getId(),
                'message' => $currentUser->getUsername() . 'Bingo with  ',
                'user_id' => $currentUser->getId(),
                'url' => '/bingo',
                'type' => 'linkIn',
                'android_channel_id' => 'polyArena',
            ];

            $this->get('messenger')->pushNotification1($data, $this->getParameter('fcm_auth_key'), $this->getParameter('site_name'), $this->getParameter('base_url'));
        }
        echo $result;
        die;
//        return $this->view( $result, Response::HTTP_OK);

    }


    /**
     * @Route("/user/settings", name="user_settings")
     */
    public function settingsAction(Request $request)
    {
        $user = $this->getUser();
        $form = $this->createForm(ProfileSiteSettingsType::class, $user);
        $errors = false;

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isSubmitted() /*&& $form->isValid()*/) {
                $em = $this->getDoctrine()->getManager();
                //$user->setIsSentEmail(boolval($form->getData()));
                // dump($user);die;
                $em->persist($user);
                $em->flush();
                $this->addFlash("success", "settings saved");

            } else {
                $errors = true;
            }
        }

        return $this->render('frontend/user/profile/settings.html.twig', array(
            'form' => $form->createView(),
            'errors' => $errors,
            'mobile' => $this->detectIsMobile(),
        ));
    }


    /**
     * @Route("/user/photo/data", name="user_photo_data", defaults={"id" = null})
     * @Route("/admin/users/user/{id}/photos/photo", name="admin_users_user_photos_photo")
     */
    public function photoDataAction(Request $request, $id)
    {
        $uploadedPhoto = $request->files->get('photo');
        // var_dump($uploadedPhoto);die;
        if (!$uploadedPhoto instanceof UploadedFile) {
//            var_dump('fds');die;
            return new Response();
        } else {
//            var_dump(123);die;
            $mobileDetector = $this->container->get('mobile_detect.mobile_detector');
            if ($mobileDetector->isMobile()) {
//
                $orientation = exif_read_data($uploadedPhoto->getRealpath())['Orientation'] ?? false;
//                var_dump(123);die;
            }
        }


        if ($request->get('_route') == 'admin_users_user_photos_photo') {
            $user = $this->getDoctrine()->getRepository('AppBundle:User')->find($id);
            $isValid = true;
        } else {
            $user = $this->getUser();
            $isValid = false;
        }


        if ((count($user->getPhotos()) < 8) || ($user->isPaying() && count($user->getPhotos()) < 16)) {

            $isMain = !$user->getMainPhoto(true);
            $photo = new Photo();
            $photo->setUser($user);

            $fileUrl = $request->get('file_url', false);

            if ($fileUrl !== false) {
                $data = base64_decode($fileUrl);
//                var_dump(123);die;
                $filePath = $_SERVER['DOCUMENT_ROOT'] . '/media/' . $uploadedPhoto->getClientOriginalName();


                $physicalfile = fopen($filePath, "w");
                fwrite($physicalfile, $data);
                fclose($physicalfile);
                $file = new UploadedFile($filePath, $uploadedPhoto->getClientOriginalName(), $uploadedPhoto->getClientMimeType(), null, null, true);

                $photo->setFile($file);

            } else {
                $photo->setFile($uploadedPhoto);

            }
            $photo->setIsValid($isValid);
            $photo->setIsMain($isMain);

            //$photo->setIsPrivate(false);

            $em = $this->getDoctrine()->getManager();
            $em->persist($photo);
            $em->flush();

            $optimized = $this->applyFilterToPhoto('optimize_original', $photo->getWebPath(false));
            $this->savePhoto($optimized, $photo->getAbsolutePath());
            // 800, 800
            //$photo->resize();


//            $this->fnim($request, $photo->getId());
//            var_dump(123);die;
//            $this->fnim($request, $photo->getId());

//             var_dump(312);

            if (isset($orientation) && !empty($orientation)) {
                switch ($orientation) {
                    case 3:
                        $photo->rotate(180);
                        break;
                    case 6:
                        $photo->rotate(-90);
                        break;
                    case 8:
                        $photo->rotate(90);
                        break;
                    default:
                        break;
                }
            }

        } else {

            echo 'maxCount';
            die;
        }
//        var_dump(123);die;

        return new JsonResponse(array(
            'id' => $photo->getId()
        ));
    }

    /**
     * @Route("/user/photo/data/add/{id}", name="puser_photo_data_add")
     *
     */
    public function fnim(Request $request, $id)
    {

        $photo = $this->getDoctrine()->getManager()->getRepository('AppBundle:Photo')->find($id);
        $params = $photo->testDetectFace($request->getHost());

        if (null !== $params) {

            $face = $this->getFace($photo, $params);

            $this->savePhoto($face, $photo->getFaceAbsolutePath());

            $optimized = $this->applyFilterToPhoto('optimize_face', $photo->getFaceWebPath());
            $this->savePhoto($optimized, $photo->getFaceWebPath(false));

        }

        return new JsonResponse(array(
            'id' => $photo->getId()
        ));

    }

//    public function fnim (Request $request, Photo $photo) {
//        $params = $photo->detectFace($request->getHost());
//
//        if (null !== $params) {
//            $face = $this->getFace($photo, $params);
//            $this->savePhoto($face, $photo->getFaceAbsolutePath());
//
//            $optimized = $this->applyFilterToPhoto('optimize_face', $photo->getFaceWebPath());
//            //  var_dump(312);die;
//
//
//            $this->savePhoto($optimized, $photo->getFaceAbsolutePath());
//        }
//
//        $optimized = $this->applyFilterToPhoto('optimize_original', $photo->getWebPath());
//        $this->savePhoto($optimized, $photo->getAbsolutePath());
//        //var_dump(123);die;
//    }

    /**
     * Apply filter to photo using the LiipImagineBundle
     *
     * @param Photo $photo an Entity that represents an image in the database
     * @param string $filterName the Imagine filter to use
     */
    private
    function applyFilterToPhoto($filterName, $webPath)
    {
        $dataManager = $this->container->get('liip_imagine.data.manager');

        try {
            $image = $dataManager->find($filterName, $webPath);
        } catch (\Exception $e) {
            // send error message if you can
            var_dump($filterName, $webPath);
            echo 'Caught exception: ', $e->getMessage(), "\n";
            return false;
        }

        //var_dump($image); die;

        return $this->container->get('liip_imagine.filter.manager')->applyFilter($image, $filterName)->getContent();
    }

    private
    function getFace(Photo $photo, $params)
    {
        $dataManager = $this->container->get('liip_imagine.data.manager');
//        var_dump(231);
        $image = $dataManager->find('optimize_original', $photo->getWebPath(false));
//        var_dump(231);die;
        $face = $this->container->get('liip_imagine.filter.manager')
            ->applyFilter($image, 'face', array(
                'filters' => array(
                    'crop' => array(
                        'start' => array($params['x'], $params['y']),
                        'size' => array($params['w'], $params['h'])
                    )
                )
            ));

        return $this->container->get('liip_imagine.filter.manager')
            ->applyFilter($face, 'optimize_face')->getContent();
    }

    public function savePhoto($photo, $path)
    {
//        var_dump(342);
        chmod($path, 0777);
        $f = fopen($path, 'w');
        fwrite($f, $photo);
        fclose($f);
        /*var_dump($path, $photo);
        die;*/
    }


    /**
     * @Route("/user/photo/delete/{id}", name="user_photo_delete")
     */
    public function deletePhotoAction(Photo $photo)
    {

        $user = $photo->getUser();
        if ($user->getId() != $this->getUser()->getId()) {
            throw $this->createAccessDeniedException();
        }


//        if ($user->getId() == 4944) {
        return $this->testDeletePhotoAction($photo);
//        }

//        $wasMainPhoto = $photo->getIsMain();
//        $em = $this->getDoctrine()->getManager();
//
//        $photos = $user->getPhotos();
//
//        $has_not_private_photos = false;
//
//        if (count($photos) > 1) {
//            foreach ($photos as $photo2) {
//                if ($photo2->getId != $photo->getId() && !$photo->getIsPrivate()) {
//                    $new_main = $photo2->getId();
//                    $has_not_private_photos = true;
//                }
//            }
//        }
//
//        $em->remove($photo);
//        $em->flush();
//
//        if($wasMainPhoto){
//            $photos = $user->getPhotos();
//            if(isset($photos[0])){
//                echo $photos[0]->getId();
//            }
//
//        }
//
//        return $this->render('frontend/security/empty.html.twig');
    }


    public function testDeletePhotoAction(Photo $photo)
    {

        $user = $photo->getUser();
        if ($user->getId() != $this->getUser()->getId()) {
            throw $this->createAccessDeniedException();
        }

        $allPhotos = $user->getPhotos();
        $canDelete = false;

        if (count($allPhotos) == 1) {
            $canDelete = true;
        } else {
            foreach ($allPhotos as $allPhoto) {
                if ($allPhoto->getId() != $photo->getId()) {

                    if ($allPhoto->getId() !== $photo->getId() && !$allPhoto->getIsPrivate()) {
                        $canDelete = true;

//                        if (!isset($newMain)) {
//                            $newMain = $allPhoto;
//                        }

                        if ($allPhoto->getIsValid()) {
                            $newMain = $allPhoto->getId();
                            break;

                        }

                    }

                }
            }

            if (!isset($newMain)) {
                $newMain = $allPhoto->getId();
            }
        }


        if ($canDelete) {

            $em = $this->getDoctrine()->getManager();

            $em->remove($photo);

            $em->flush();
            return new JsonResponse(array(
                'success' => true,
                'newMain' => $newMain,
            ));
        } else {
            return new JsonResponse(array(
                'success' => false,
                'message' => 'צריכה להיות לפחות תמונה אחת גלויה',
            ));
        }
    }


    /**
     * @Route("/user/photo/main/{id}", name="user_set_main_photo")
     */
    public function setMainPhotoAction(Photo $photo)
    {
        $user = $this->getUser();

        if (!$user || $user->getId() != $photo->getUser()->getId()) {
            throw $this->createAccessDeniedException();
        }

        $em = $this->getDoctrine()->getManager();
        $photos = $user->getPhotos();
//        $hasValidPhoto = false;
        if (!$photo->getIsPrivate() && (!$user->getMainPhoto(true) || !$user->getMainPhoto(true)->getIsValid()) || $photo->getIsValid()) {
            foreach ($photos as $userPhoto) {
                if ($userPhoto->getIsMain()) {
                    $userPhoto->setIsMain(false);
                    $em->persist($userPhoto);
                }
            }
            $photo->setIsMain(true);
            $em->persist($photo);
            $em->flush();
        }

        /*else if ($action == 'private'){
            if ($photo -> getIsPrivate()){
                $photo->setIsPrivate(false);
                $em->persist($photo);
                $em->flush();
            } else {
                if (!$photo->getIsMain()) {
                    $photo->setIsPrivate(true);
                    $em->persist($photo);
                    $em->flush();
                } else {
                    foreach ($photos as $photo2) {
                        if ($photo2->getName() !== $photo->getName()) {
                            $photo2 -> setIsMain(true);
                            $em->persist($photo);
                            $em->flush();
                        }
                    }
                }
            }
        }*/


        return $this->render('frontend/security/empty.html.twig');
    }

    /**
     * @Route("/user/photo/private/{id}", name="user_set_private_photo")
     */
    public function setPrivatePhotoAction(Photo $photo)
    {
        $user = $this->getUser();

        if (!$user || $user->getId() != $photo->getUser()->getId()) {
            throw $this->createAccessDeniedException();
        }

        $em = $this->getDoctrine()->getManager();

        /*
                 $is_charging = $em->getRepository('AppBundle:Settings')->find(1)->getIsCharge();

                if ($user->getUsername() == 'admin') {
                    $is_charging = true;
                }

                if ($is_charging && !$user->isPaying() && !$photo->getIsPrivate()) {
                    return new JsonResponse([
                        'success' => false,
                        'message' => 'רק משתמשים במנוי יכולים להסתיר את התמונות שלהם',
                    ]);
                }
        */


//        if ($user->isPay) { /** only when site is charge */
        if (!$photo->getIsMain()) {
            $photo->setIsPrivate(!$photo->getIsPrivate());
            $state = $photo->getIsPrivate() ? 'uncheck' : 'check';
            $em->persist($photo);
            $em->flush();
        }

        return new JsonResponse(array(
            'success' => true,
            'user' => array(
                'hasValidMain' => $photo->getUser()->getMainPhoto(true)->getIsValid(),
            ),
            'validPhoto' => $photo->getIsValid(),
            'state' => $state ?? false
        ));


    }

    /**
     * @Route("/user/users/online/{page}", defaults={"page" = 1}, name="user_users_online")
     */
    public function usersOnlineAction(Request $request, $page)
    {
        $data = array();
        $advancedSearch = $request->request->get('advancedSearch');
        $data['filter'] = $advancedSearch['filter'];
        $settings = $this->getDoctrine()->getManager()->getRepository('AppBundle:Settings')->find(1);
        //var_dump($this->get('knp_paginator'));die;
        $users = $this->getDoctrine()->getManager()->getRepository('AppBundle:User')->getOnline(
            array(
                'current_user' => $this->getUser(),
                'data' => $data,
                'paginator' => $this->get('knp_paginator'),
                'page' => $page,
                'per_page' => $settings->getUsersPerPage(),
                'considered_as_online_minutes_number' => $settings->getUserConsideredAsOnlineAfterLastActivityMinutesNumber(),
            )
        );

        return $this->render('frontend/user/users.html.twig', array(
            'users' => $users,
            'data' => $data,
            'header' => 'online members',
            'mobile' => $this->detectIsMobile(),
        ));
    }

//    /**
//     * @Route("/banner/{id}/count", name="banner_click123")
//     */
//    public function bannerCount(Banner $banner) {
//        $em = $this->getDoctrine()->getManager();
//        $banner->setClickCount($banner->getClickCount() + 1);
//        $em->persist($banner);
//        $em->flush();
//    }


    /**
     * @Route("/user/search/advanced/{remove_cookie}", defaults={"remove_cookie" = false}, name="user_search_advanced")
     */
    public function searchAction(Request $request, $remove_cookie)
    {
        $form = $this->createForm(AdvancedSearchType::class, new User());

//        if ($this->getUser()->getUsername() == 'tester1') {
        if ($remove_cookie) {
            $response = $this->render('frontend/user/advanced_search.html.twig', array(
                'form' => $form->createView(),
                'cookieData' => $test ?? null,
                'mobile' => $this->detectIsMobile(),
            ));
            $response->headers->removeCookie('advanceSearch');
            return $response;
        }


        $test = unserialize($request->cookies->get('advanceSearch'));
//            if ($this->getUser()->getUsername() == 'tester1') {
//                dump($test);die;
//            }
//        }
        return $this->render('frontend/user/advanced_search.html.twig', array(
            'form' => $form->createView(),
            'cookieData' => $test ?? null,
            'mobile' => $this->detectIsMobile(),
        ));
    }

//user_users_photo_requests

    /**
     * @Route("/user/users/photo/request", name="user_users_photo_request")
     */
    public function getPhotoRequestAction()
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $repository = $this->getDoctrine()->getRepository(ShowPhoto::class);
        $entityManager = $this->getDoctrine()->getManager();
        $requests = $entityManager->getRepository(ShowPhoto::class)->findBy(array('member' => $user->getId()), array('isCancel' => 'ASC', 'isAllow' => 'ASC', 'id' => 'DESC'));
        foreach ($requests as $request) {
            if (!$request->getIsView()) {
                $request->setIsView(true);
            }
        }
        $entityManager->flush();
        return $this->render('frontend/user/photo_requests.html.twig', array(
            'requests' => $requests,
            'text' => 'No new requests',
            'mobile' => $this->detectIsMobile(),
        ));
    }

//    post_photo_request

    /**
     * @Route("/user/users/photo/requests/{id}/{action}", defaults={"id" = 0, "action" = 0}, name="user_photo_requests")
     */
    public function PhotoRequstAction($id, $action)
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $entityManager = $this->getDoctrine()->getManager();
        $product = $entityManager->getRepository(ShowPhoto::class)->findOneBy(array('owner' => $id, 'member' => $user->getId()));


//        dump($product);die;

        if ($action == 'allow') {
            $notif = $entityManager->getRepository('AppBundle:Notifications')->find(3);
            $product->setIsAllow(true);
            $product->setIsCancel(false);

//            var_dump($product->getIsCancel());die;
        } else if ($action == 'cancel') {
            $notif = $entityManager->getRepository('AppBundle:Notifications')->find(4);
            $product->setIsCancel(true);
            $product->setIsAllow(false);
        }

        $entityManager->persist($product);

        if (isset($notif)) {
            $notification = new UserMessengerNotifications();
            $notification->setFromUser($user);
//            $notification->setUser($product->getOwner());
            $notification->setNotification($notif);
            $notification->setDate(new \DateTime());
            $entityManager->persist($notification);
//            var_dump(123);die;

        }

        $entityManager->flush();
        return $this->redirectToRoute('user_users_photo_request');
    }

    /**
     * @Route("/user/search/results/{page}/{ajax}", defaults={"page" = 1, "ajax" = 0}, name="user_search_results")
     * @Route("/user/users/online/{page}/{ajax}", defaults={"page" = 1, "ajax" = 0}, name="online_results")
     * @Route("/public/search/results/{page}/{ajax}", defaults={"page" = 1, "ajax" = 0}, name="public_search_results")
     * @Route("/{ajax}/online/{page}", defaults={"page" = 1, "ajax" = 0}, name="users_public_list")
     */
    public function searchResultsAction(Request $request, $page, $ajax)
    {
        $quickSearchHomePage = $request->request->get('quick_search_home_page', null);
        $quickSearchSidebar = $request->request->get('quick_search_sidebar', null);
        $quickSearch = $request->request->get('quick_search', null) ?? $request->request->get('mobile_quick_search', null);
        $advancedSearch = $request->request->get('advancedSearch', null) ?? $advancedSearch = $request->request->get('advanced_search', null);
        $ajax_data = $request->request->get('ajax_data', false);

        $data = $quickSearchHomePage ?? $quickSearchSidebar ?? $quickSearch ?? $advancedSearch ?? $ajax_data ?? null;
        if ($_COOKIE['searchPage']) {
            $cookPage = $_COOKIE['searchPage'];
        }
        $perPage = 1;
        $data['current_route'] = $request->get('_route');
        $usersRepo = $this->getDoctrine()->getManager()->getRepository('AppBundle:User');
        $settings = $this->getDoctrine()->getManager()->getRepository('AppBundle:Settings')->find(1);

        $fromCook = false;
        if (isset($cookPage)) {
            $page = $cookPage;
            $fromCook = true;
        }

//        if ($this->getUser()->getUsername() == 'tester1') {
//            var_dump($data);die;
//        }

        $users = $usersRepo->search(array(
            'current_user' => $this->getUser(),
            'data' => $data,
            'paginator' => $this->get('knp_paginator'),
            'page' => $page,
            'per_page' => $settings->getUsersPerPage(),
            'from_cook' => $fromCook,
            //'data' => array('filter' => $request->get('filter', false))
        ));


//        if ($this->getUser()->getUsername() == 'tester1') {
//            dump(unserialize($request->cookies->get('advanceSearch')));die;

// $newUser['canWriteTo'] = $this->get('messenger')->CheckIfCanWriteTo($currentUser, $user);
        if (!$ajax) {
            $response = $this->render('frontend/user/users.html.twig', array(
                'users' => $users,
                'data' => $usersRepo->getData(),
                'header' => 'Search Results',
                'noResults' => $this->get('Translator')->trans('No results'),
                'mobile' => $this->detectIsMobile(),
                'page' => $page,
                'messenger' => $this->get('messenger'),
            ));
            if ($advancedSearch) {
                $response->headers->setCookie(new Cookie('advanceSearch', serialize($advancedSearch), time() + (86400 * 365 * 10))); // 86400 = 1 day
            }
//                if ($this->getUser()->getUsername() == 'tester1') {
//                    dump($response->headers->getCookies());die;
//                }
            return $response;
        } else {
            $response = new JsonResponse();
            $users = $this->transformUsers($users, $settings);
//                $settings['page'] = $page;
            $response->setData(array(
                'users' => $users,
                'data' => $usersRepo->getData(),
                'page' => $page,
//                    'sideUsers' => $usersRepo->getOnline($settings)
            ));
            return $response;
        }

//        }


//       if(!$ajax) {
//           return $this->render('frontend/user/users.html.twig', array(
//               'users' => $users,
//               'data' => $usersRepo->getData(),
//               'header' => 'תוצאות חיפוש',
//               'noResults' => $this->get('Translator')->trans('No results'),
//               'mobile' => $this->detectIsMobile(),
//               'page' => $page,
//           ));
//       } else {
//
//
////           $session->set('searchPage', 'test');
////           if ($this->getUser()->getUsername() == 'Ben') {
////               var_dump($session->get('searchPage'));die;
////           }
//           $users = $this->transformUsers($users, $settings);
//           return new JsonResponse(array(
//               'users' => $users,
//               'data' => $usersRepo->getData(),
//               'page' => $page,
//           ));
//
//       }

    }

    /**
     * @Route("/user/users/{id}", name="view_user")
     */
    public function viewUserAction(User $user)
    {

        $currentUser = $this->get('security.token_storage')->getToken()->getUser();
//        if ($currentUser->getId() == 5883) {
//            dump($user->getVerifyMe());die;
//        }
        $em = $this->getDoctrine()->getManager();
        $user->setViews($user->getViews() + 1);
        if (!$user->getIsUpdatedZodiac()) {
            $user->setZodiac($this->getDoctrine()->getManager()->getRepository('AppBundle:Zodiac')->getZodiacByDate($user->getBirthday()));
            $user->setIsUpdatedZodiac(true);
        }
        $em->persist($user);
        $em->flush();
        $this->createUpdateList('View', $this->getUser(), $user);
        $repository = $this->getDoctrine()
            ->getRepository(ShowPhoto::class);
        $bannerServie = $this->get('banners');
        $topBanners = $bannerServie->getBanners('profileTop', $user);
        $bottomBanners = $bannerServie->getBanners('profileBottom', $user);

        $status = 'notSent';
        if ($sendRequest = $repository->findBy(array('member' => $user->getId(), 'owner' => $currentUser->getId()))) {
            $status = 'waiting';
            if ($sendRequest[0]->getIsAllow()) {
                $status = 'allow';
            } else if ($sendRequest[0]->getIsCancel()) {
                $status = 'cancel';
            }

        }

        $text = array(
            'allow' => $this->get('Translator')->trans('You can see') . '<br>' . $this->get('Translator')->trans('private photos'),
            'cancel' => $this->get('Translator')->trans('Your ask rejected'),
            'waiting' => $this->get('Translator')->trans('Waiting for answer'),
            'notSent' => $this->get('Translator')->trans('Click to ask see confidential photos'),
        );
        $canWriteTo = $this->get('messenger')->CheckIfCanWriteTo($currentUser, $user);
        return $this->render('frontend/user/user.html.twig', array(
            'user' => $user,
            'text' => $text,
            'status' => $status,
            'messages' => $em->getRepository('AppBundle:InlineMessages')->findAll(),
            'isAddLike' => $currentUser->isAddLike($user),
            'topBanners' => $topBanners,
            'bottomBanners' => $bottomBanners,
            'mobile' => $this->detectIsMobile(),
            'savePageCookie' => true,
            'canWriteTo' => $canWriteTo,
            'messenger' => $this->get('messenger')
        ));
    }

    /**
     * @Route("user/verify/{id}",  defaults={"id" = 0}, name="user_verify")
     */
    public function userVerifyAction(User $user)
    {
        $currentUser = $this->getUser();
        $em = $this->getDoctrine()->getManager();
        $verify = $em->getRepository('AppBundle:Verify')->findBy(array('userTo' => $user->getId(), 'userFrom' => $currentUser->getId()));
//        $currentUserGive = ($currentUser->isAdmin() || $currentUser->getIsVerify()) ? 3 : 1;
//        $countNow = $user->getVerifyCount();

        if (!$verify && !$user->getIsVerify()) {
            //var_dump(123);die;
            $user->setVerifyCount($user->getVerifyCount() + ($currentUser->isAdmin() || $currentUser->getIsVerify()) ? 3 : 1);
            $ver = new Verify();
            $ver->setUserFrom($currentUser);
            $ver->setUserTo($user);
            $em->persist($ver);
            $em->persist($user);

            $notification = new UserMessengerNotifications();
            $notif = $em->getRepository('AppBundle:Notifications')
                ->find($user->getVerifyCount() < 3 ? 5 : 7);;
            $notification->setFromUser($currentUser);
            $notification->setUser($user);
            $notification->setNotification($notif);
            $notification->setDate(new \DateTime());
            $notification->setLeftVerifies(3 - (int)$user->getVerifyCount());
            $em->persist($notification);
            $em->flush();

            //$verifyRepo = $em->getRepository('AppBundle:Verify');


            if ($user->getIsVerify()) {
                echo 'userIsVerify';
                die;
            }
            echo 'set';
            die;
        }
        echo false;
        die;
    }

    /**
     * @Route("/arena/{id}",  defaults={"id" = 0}, name="arena")
     */
    public function getArenaAction($id)
    {
        $settings = $this->getDoctrine()->getManager()->getRepository('AppBundle:Settings')->find(1);
        $currentUser = $this->get('security.token_storage')->getToken()->getUser();
        $usersRepo = $this->getDoctrine()->getManager()->getRepository('AppBundle:User');
        $firstUser = ((int)$id > 0) ? $usersRepo->find($id) : false;
        $users = $usersRepo->getUsersForLike($currentUser, $firstUser, 50);
        $users2 = $this->transformUsers($users, $settings);


//        var_dump($users2['users']);die;
        return $this->render('frontend/arena.html.twig', array(
            'users' => $users2['users'],
            'mobile' => $this->detectIsMobile(),
        ));
    }

//    /**
//     * @Route("/user/user/{id}", name="view_user")
//     */
//    public function viewUserAction(User $user)
//    {
//        // echo "<script> alert(12) </script>";
//        $em = $this->getDoctrine()->getManager();
//        $user->setViews($user->getViews() + 1);
//        $em->persist($user);
//        $em->flush();
//        $this->createUpdateList('View', $this->getUser(), $user);
//
//        return $this->render('frontend/user/user.html.twig', array(
//            'user' => $user,
//        ));
//
//    }

    /**
     * @Route("user/notifications/{id}", defaults={"id" = 0}, name="user_users_notifications")
     */
    public function getArenaNotifications($id)
    {
//        var_dump(123);die;
        // $id = $request->get('id', 0);

        $result = array();
        $user = $this->get('security.token_storage')->getToken()->getUser();
//        if($this->getUser()->getUsername() == 'Ben') {
//            var_dump($id,$id === 0);die;
//        }
        if ($id !== 0) {
//                var_dump(123);die;
            $em = $this->getDoctrine()->getManager();
            if (is_numeric($id)) {
                $notification = $em->getRepository('AppBundle:UserNotifications')->find($id);
                if ($notification) {
                    $notification->setIsRead(true);
                    //   dump($notification);die;
                    $em->persist($notification);
                    $em->flush();
                }
            } else if (in_array($id, ['bingo', 'notifications'])) {
//                    var_dump(123);die;
                $type = $id == 'bingo' ? 2 : 1;
                $notifs = $em->getRepository('AppBundle:UserNotifications')->findBy([
                    'notification' => $type,
                    'user' => $user->getId(),
                ]);

                foreach ($notifs as $notif) {
                    $notif->setIsRead(true);
                    $em->persist($notif);
                }

                $em->flush();

            }

        }

        foreach ($user->getNotifications() as $notification) {
            $sendUser = ($notification->getLikeMe()->getUserFrom()->getId() == $user->getId()) ? $notification->getLikeMe()->getUserTo() : $notification->getLikeMe()->getUserFrom();
            if ($sendUser->getMainPhoto()) {
                $result[] = array(
                    'id' => $notification->getId(),
                    'isRead' => $notification->getIsRead(),
                    'user_id' => $sendUser->getId(),
                    'username' => $sendUser->getUsername(),
                    'photo' => $sendUser->getMainPhoto()->getFaceWebPath(),
                    'text' => str_replace('[USERNAME]', $sendUser->getUsername(), $notification->getNotification()->getTemplate()),
                    'bingo' => $notification->getLikeMe()->getIsBingo(),
                    'date' => $notification->getDate()->format('d/m/Y'),

                );
            }
        }
//            $result = array(
//                'users' => $result,
//                'texts' => array(
//                    'no_results' => $this->get('translator')->trans('אין תוצאות'),
//                    'description' => $this->get('translator')->trans('זה המקום בו תוכלו לקבל התראות בכל פעם שמישהו בזירה פירגן/ה לתמונה שלך. <br> הודעת בינגו! תופיע במידה ושניכם פירגנתם אחד לשני
//                		.'
//                    )
//                )
//            );
//        var_dump($result);die;
//
        return $this->render('frontend/notifications.html.twig', array(
            'users' => $result,
            'mobile' => $this->detectIsMobile(),
            'texts' => array(
                'no_results' => $this->get('translator')->trans('no results'),
                'description' => $this->get('translator')->trans('Bingo! Someone liked your photo back! <br/> This is where you will get updates about bingo matches'
                )
            )
        ));

    }

    /**
     * @Route("online/profile/{id}", name="users_public_list_user")
     */
    public function publicUserAction(User $user)
    {
        return $this->render('frontend/user/public_user.html.twig', array(
            'user' => $user,
            'mobile' => $this->detectIsMobile(),
        ));
    }

    /**
     * @Route("/user/notes/{id}", name="user_notes")
     */
    public function saveNoteAction(Request $request, User $member)
    {
        $this->createUpdateList(
            'Note',
            $this->getUser(),
            $member,
            array('text' => $request->request->get('text'))
        );

        return new Response();
    }

    /**
     * @Route("/user/lists", name="mobile_user_lists")
     */
    public function listsAction()
    {
        return $this->render('frontend/user/lists.html.twig');
    }

    /**
     * @Route("/user/account", name="mobile_user_account")
     */
    public function accountAction()
    {
        return $this->render('frontend/user/account.html.twig');
    }

    /**
     * @Route("/user/users/favorite/{id}", name="user_users_favorite")
     */
    public function favoriteAction(Request $request, User $member)
    {
        $this->createUpdateList('Favorite', $this->getUser(), $member);
        return new Response();
    }

    /**
     *
     * @Route("/user/users/black_list/{id}", name="user_users_black_list")
     */
    public function blackListAction(Request $request, User $member)
    {
        $this->createUpdateList('BlackList', $this->getUser(), $member);
        return new Response();
    }

    /**
     *
     * @Route("/user/users/ask_photo/{id}", name="user_users_ask_photo")
     */
    public function askPhotoAction(Request $request, User $member)
    {
        $this->createUpdateList('ShowPhoto', $this->getUser(), $member);
        return new Response();
    }

    /**
     * @Route("/user/users/favorite/delete/{id}", name="user_users_favorite_delete")
     */
    public function deleteFavoriteAction(Request $request, User $member)
    {
        $this->deleteFromList('Favorite', $this->getUser(), $member);
        return new Response();
    }

    /**
     * @Route("/user/users/black_list/delete/{id}", name="user_users_black_list_delete")
     */
    public function deleteBlackListAction(Request $request, User $member)
    {
        $this->deleteFromList('BlackList', $this->getUser(), $member);
        return new Response();
    }

    /**
     * @Route("/user/list/favorited/{page}/{ajax}", defaults={"page" = 1, "ajax" = false}, name="user_list_favorited")
     * @Route("/user/manage/list/favorited/{page}", defaults={"page" = 1}, name="user_manage_list_favorited")
     */
    public function favoritedAction($page, $ajax = null)
    {
        if ($ajax) {
            $settings = $this->getDoctrine()->getManager()->getRepository('AppBundle:Settings')->find(1);
            $users = $this->getList(array(
                'header' => 'Added to favorites',
                'inverse_list' => 'favoritedMe',
                'type' => 'owner',
                'page' => $page,
            ), $ajax);

            return new JsonResponse(array(
                'users' => $this->transformUsers($users, $settings),
                'page' => $page,
            ));
        }
        return $this->getList(array(
            'header' => 'Added to favorites',
            'inverse_list' => 'favoritedMe',
            'type' => 'owner',
            'page' => $page,
        ));
    }

    /**
     * @Route("/user/list/favorited_me/{page}/{ajax}", defaults={"page" = 1, "ajax" = false}, name="user_list_favorited_me")
     */
    public function favoritedMeAction($page, $ajax)
    {
        if ($ajax) {
            $settings = $this->getDoctrine()->getManager()->getRepository('AppBundle:Settings')->find(1);
            $users = $this->getList(array(
                'header' => 'Added me to favorites',
                'inverse_list' => 'favorited',
                'type' => 'member',
                'page' => $page,
            ), $ajax);


            return new JsonResponse(array(
                'users' => $this->transformUsers($users, $settings),
                'page' => $page,
            ));
        }
        return $this->getList(array(
            'header' => 'Added me to favorites',
            'inverse_list' => 'favorited',
            'type' => 'member',
            'page' => $page,
        ));
    }

    /**
     * @Route("/user/list/viewed/{page}/{ajax}", defaults={"page" = 1, "ajax" = false}, name="user_list_viewed")
     */
    public function viewedAction($page, $ajax)
    {
        if ($ajax) {
            $settings = $this->getDoctrine()->getManager()->getRepository('AppBundle:Settings')->find(1);
            $users = $this->getList(array(
                'header' => 'viewed me',
                'inverse_list' => 'viewedMe',
                'type' => 'owner',
                'page' => $page,
            ), $ajax);

            return new JsonResponse(array(
                    'users' => $this->transformUsers($users, $settings),
                    'page' => $page,
                )
            );
        }
        return $this->getList(array(
            'header' => 'I viewed',
            'inverse_list' => 'viewedMe',
            'type' => 'owner',
            'page' => $page,
        ));
    }

    /**
     * @Route("/user/list/viewed_me/{page}/{ajax}", defaults={"page" = 1, "ajax" = false}, name="user_list_viewed_me")
     */
    public function viewedMeAction($page, $ajax)
    {
        if ($ajax) {
            $settings = $this->getDoctrine()->getManager()->getRepository('AppBundle:Settings')->find(1);
            $users = $this->getList(array(
                'header' => 'viewed me',
                'inverse_list' => 'viewed',
                'type' => 'member',
                'page' => $page,
            ), $ajax);

            return new JsonResponse(array(
                'users' => $this->transformUsers($users, $settings),
                'page' => $page,
            ));
        }
        return $this->getList(array(
            'header' => 'viewed me',
            'inverse_list' => 'viewed',
            'type' => 'member',
            'page' => $page,
        ));
    }

    /**
     * @Route("/user/list/connected/{page}/{ajax}", defaults={"page" = 1, "ajax" = false}, name="user_list_connected")
     */
    public function connectedAction($page, $ajax)
    {
        if ($ajax) {
            $settings = $this->getDoctrine()->getManager()->getRepository('AppBundle:Settings')->find(1);
            $users = $this->getList(array(
                'header' => 'I contacted',
                'inverse_list' => 'connectedMe',
                'type' => 'owner',
                'page' => $page,
            ), $ajax);

            return new JsonResponse(array(
                'users' => $this->transformUsers($users, $settings),
                'page' => $page,
            ));
        }
        return $this->getList(array(
            'header' => 'I contacted',
            'inverse_list' => 'connectedMe',
            'type' => 'owner',
            'page' => $page,
        ));
    }


    /**
     * @Route("/user/list/connectedMe/{page}/{ajax}", defaults={"page" = 1, "ajax" = false}, name="user_list_connected_me")
     */
    public function connectedMeAction($page, $ajax)
    {
        if ($ajax) {
            $settings = $this->getDoctrine()->getManager()->getRepository('AppBundle:Settings')->find(1);
            $users = $this->getList(array(
                'header' => 'Contacted me',
                'inverse_list' => 'connected',
                'type' => 'member',
                'page' => $page,
            ), $ajax);

            return new JsonResponse(array(
                'users' => $this->transformUsers($users, $settings),
                'page' => $page,
            ));

        }

        return $this->getList(array(
            'header' => 'Contacted me',
            'inverse_list' => 'connected',
            'type' => 'member',
            'page' => $page,
        ));
    }

    /**
     * @Route("/user/list/black_listed/{page}/{ajax}", defaults={"page" = 1, "ajax" = false}, name="user_list_black_listed")
     * @Route("/user/manage/list/black_listed/{page}", defaults={"page" = 1}, name="user_manage_list_black_listed")
     */
    public function blackListedAction($page, $ajax = false)
    {
        if ($ajax) {
            $settings = $this->getDoctrine()->getManager()->getRepository('AppBundle:Settings')->find(1);
            $users = $this->getList(array(
                'header' => 'Blocked',
                'inverse_list' => 'blackListedMe',
                'type' => 'owner',
                'page' => $page,
            ), $ajax);

            return new JsonResponse(array(
                'users' => $this->transformUsers($users, $settings),
                'page' => $page,
            ));

        }
        return $this->getList(array(
            'header' => 'Blocked',
            'inverse_list' => 'blackListedMe',
            'type' => 'owner',
            'page' => $page,
        ));
    }

    /**
     * @Route("/user/freeze_account", name="user_freeze_account")
     */
    public function freezeAccountAction(Request $request)
    {
        if ($request->isMethod('POST')) {
            $this->getUser()->setIsFrozen(true);
            $this->getUser()->setFreezeReason($request->request->get('freeze_account_reason', null));
            $em = $this->getDoctrine()->getManager();
            $em->persist($this->getUser());
            $em->flush();
            return $this->redirectToRoute('logout');
        }

        return $this->render('frontend/user/freeze.html.twig', array(
            'mobile' => $this->detectIsMobile(),
        ));
    }

    /**
     * @Route("/user/report/abuse/{id}", name="user_report_abuse")
     */
    public function reportAbuseAction(Request $request, User $member)
    {
        //$subject = "Greendate | Desktop | Report Abuse | Username: " . $member->getUsername();

        $user = $this->get('security.token_storage')->getToken()->getUser();

        $subject = 'PolyInLove | Report abuse | #' . $member->getID();

        $text = $request->get('text', false);

        $mobileDetector = $this->get('mobile_detect.mobile_detector');
        if ($mobileDetector->isMobile()) {
            $platform = 'Mobile';
        } else {
            $platform = 'Desktop';
        }

//        $subject = $request->getHost() . " | Desktop | Contact Form | " . date('d/m/Y H:i') . " | " . $form->get('subject')->getData();
//        $headers = "MIME-Version: 1.0" . "\r\n";
//        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
//        $headers .= 'From: Admin <'.$settings->getContactEmail().'>' . "\r\n";
//        $headers .= "Return-Path: ".$settings->getContactEmail()."\r\n";
//        $headers .= 'Reply-to: ' . $form->getData()->getEmail() . ' <' . $form->getData()->getEmail() . '>' . "\r\n";
//        mail($settings->getContactEmail(),$subject,$form->get('text')->getData(),$headers,'-f'.$settings->getContactEmail());


        $body = '<div dir="rtl">';
        $body .= '
			username: ' . $member->getUsername() . '<br />
			userid: ' . $member->getId() . '<br>
			message: ' . $text . '<br /><br />
			reported by: ' . $user->getUsername() . '(#' . $user->getId() . ') <br>
			sent from: ' . $platform;
        $body .= '</div>';

        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= 'From: ' . $this->getUser()->getEmail() . ' <' . $this->getUser()->getEmail() . '>' . "\r\n";
//        $headers .= "Return-Path: ".$this->getUser()->getEmail()."\r\n";
        $settings = $this->getDoctrine()->getRepository('AppBundle:Settings')->find(1);

        mail($settings->getReportEmail(), $subject, $text, $headers, '-f' . $this->getUser()->getEmail());
//        var_dump(mail('vita@interdate-ltd.co.il',$subject,$body,$headers, '-f' . 'test@gmail.com'));
        return new Response();
    }

    /**
     * @Route("/user/password", name="user_change_password")
     */
    public function changePasswordAction(Request $request)
    {
        $changed = false;
        $user = $this->getUser();
        $form = $this->createForm(ChangePasswordType::class, $user);

        if ($request->isMethod('POST')) {
            $post = $request->request->all();
//            var_dump($post);die;
            $originalEncodedPassword = $user->getPassword();
            $encoder = $this->get('security.encoder_factory')->getEncoder($user);

            $validOldPassword = $encoder->isPasswordValid(
                $originalEncodedPassword, // the encoded password
                $post['change_password']['oldPassword'],  // the submitted password
                null
            );

            if ($validOldPassword) {
                $form->handleRequest($request);
                if ($form->isValid() && $form->isSubmitted()) {
                    $encodedPassword = $encoder->encodePassword($user->getPassword(), null);
                    $user->setPassword($encodedPassword);

                    $em = $this->getDoctrine()->getManager();
                    $em->persist($user);
                    $em->flush();
                    $changed = true;
                }

            } else {
                $form->get('oldPassword')->addError(new FormError('Old password is incorrect'));
            }
        }

        return $this->render('frontend/user/password.html.twig', array(
            'form' => $form->createView(),
            'changed' => $changed,
            'mobile' => $this->detectIsMobile(),
        ));
    }

    /**
     * @Route("/user/like2/{id}", name="user_like2", defaults={"id" = null})
     */
    public function usersLikeAction2($id)
    {
//    var_dump(123);die;
        $firstUser = ((int)$id > 0) ? $this->getDoctrine()->getManager()->getRepository('AppBundle:User')->find($id) : false;

        $users = $this->getDoctrine()->getManager()->getRepository('AppBundle:User')->getUsersForLike($this->getUser(), $firstUser);
        $users['url'] = $this->generateUrl('user_profile', array("tab" => 4));

        $liip_imagine = $this->get('liip_imagine.cache.manager');

        $users_online_length = count($users['online']);
        for ($i = 0; $i < $users_online_length; $i++) {
            $user = &$users['online'][$i];
            $photo = '/media/photos/' . $user['id'] . '/' . $user['imageId'] . '.' . $user['ext'];
//            $user['photo1'] = $photo;
            $user['photo'] = $liip_imagine->getBrowserPath($photo, 'full_mobile_photo');
        }

        $users_other_length = count($users['other']);
        for ($i = 0; $i < $users_other_length; $i++) {
            $user = &$users['other'][$i];
            $photo = '/media/photos/' . $user['id'] . '/' . $user['imageId'] . '.' . $user['ext'];
//            $user['photo1'] = $photo;
            $user['photo'] = $liip_imagine->getBrowserPath($photo, 'full_mobile_photo');
        }


        return new Response(
            json_encode($users)
        );
    }


    /**
     * @Route("/user/search/mobile", name="user_search_mobile")
     */
    public function mobileQuickSearch()
    {
//        $user = $this->getUser();
        $form = $this->createForm(MobileQuickSearchType::class);
        //  var_dump(123);die;
        // 'form' => $form->createView(),
        return $this->render('frontend/user/mobile/quick_search.html.twig', array(
            'form' => $form->createView(),
            'mobile' => $this->detectIsMobile()
        ));
    }

    /**
     * @Route("/user/geolocation", name="user_geolocation")
     */
    public function setLocation(Request $request)
    {
        $user = $this->getUser();
        $coords = $request->request->get('coords');
        $em = $this->getDoctrine()->getManager();
        $user->setLatitude($coords['latitude']);
        $user->setLongitude($coords['longitude']);
        $em->persist($user);
        $em->flush();
        return new JsonResponse();
//        var_dump($request->request->all());die;
    }


    public function getList($settings, $ajax = false)
        //public function getList($inverse_list, $header, $page, $perPage)
    {
        $settings['current_user'] = $this->getUser();
        $settings['paginator'] = $this->get('knp_paginator');
        $settings['per_page'] = $this->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Settings')
            ->find(1)
            ->getUsersPerPage();

        $request = Request::createFromGlobals();
        $data = array();
        $advancedSearch = $request->request->get('advancedSearch');
        $data['filter'] = $advancedSearch['filter'];
        $settings['request_data'] = $data;

        $users = $this->getDoctrine()->getManager()->getRepository('AppBundle:User')->getList($settings);
        if ($ajax) {
            return $users;
        }
        return $this->render('frontend/user/users.html.twig', array(
            'users' => $users,
            'header' => $settings['header'],
            'data' => $data,
            'mobile' => $this->detectIsMobile(),
        ));
    }

    public function getProfileType($tab)
    {

        switch ($tab) {
            case 1:
                return ProfileOneType::class;
                break;

            case 2:
                return ProfileTwoType::class;
                break;

            case 3:
                return ProfileThreeType::class;
                break;
        }

    }

    public function setUpCloudinary()
    {
//        \Cloudinary::config(array(
//            "cloud_name" => "interdate",
//            "api_key" => "771234826869846",
//            "api_secret" => "-OWKuCgP1GtTjIgRhwfOUVu1jO8",
//        ));
    }

    public function createUpdateList($entityName, $owner, $member, $fields = array())
    {
        if ($owner->getId() == $member->getId()) {
            return;
        }

        $repo = $this->getDoctrine()->getRepository('AppBundle:' . $entityName);
        $entity = $repo->findOneBy(array(
            'owner' => $owner,
            'member' => $member
        ));
        $className = 'AppBundle\Entity\\' . $entityName;
        $entity = new $className();
        $entity->setOwner($owner);
        $entity->setMember($member);
        $entity->setDate(new \DateTime('now'));

        foreach ($fields as $key => $value) {
            $method = 'set' . ucfirst($key);
            $entity->$method($value);
        }
        $em = $this->getDoctrine()->getManager();
        $em->persist($entity);
        $em->flush();
    }

    public function deleteFromList($entityName, $owner, $member)
    {
        $repo = $this->getDoctrine()->getRepository('AppBundle:' . $entityName);
        $entity = $repo->findOneBy(array(
            'owner' => $owner,
            'member' => $member
        ));

        if (null !== $entity) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($entity);
            $em->flush();
            // var_dump(23);die;
        }
    }


    function setUserLoginFrom()
    {
        $mobileDetector = $this->get('mobile_detect.mobile_detector');

        $loginFromRepo = $this->getDoctrine()->getRepository('AppBundle:LoginFrom');


        if ($mobileDetector->isMobile()) {
            if ($mobileDetector->isIOS()) {
                $this->getUser()->setLoginFrom($loginFromRepo->find(3));
            } elseif ($mobileDetector->isAndroidOS()) {
                $this->getUser()->setLoginFrom($loginFromRepo->find(4));
            } else {
                $this->getUser()->setLoginFrom($loginFromRepo->find(2));
            }
        } else {
            $this->getUser()->setLoginFrom($loginFromRepo->find(1));
        }

    }

    public function isIOS()
    {
        return (preg_match('/(iphone|ipad|ipaq|ipod)/i', $_SERVER['HTTP_USER_AGENT']))
            ? true
            : false;
    }

    public function isAndroid()
    {
        return (preg_match('/(android)/i', $_SERVER['HTTP_USER_AGENT']))
            ? true
            : false;
    }

    public function isMobile()
    {
        return (preg_match('/(alcatel|amoi|android|avantgo|blackberry|benq|cell|cricket|docomo|elaine|htc|iemobile|iphone|ipad|ipaq|ipod|j2me|java|midp|mini|mmp|mobi|motorola|nec-|nokia|palm|panasonic|philips|phone|playbook|sagem|sharp|sie-|silk|smartphone|sony|symbian|t-mobile|telus|up\.browser|up\.link|vodafone|wap|webos|wireless|xda|xoom|zte)/i', $_SERVER['HTTP_USER_AGENT']))
            ? true
            : false;
    }

    public function transformUsers($users, $settings, $filter = false)
    {
        // var_dump($users);die;
        $currentUser = $this->get('security.token_storage')->getToken()->getUser();
        $usersRepo = $this->getDoctrine()->getManager()->getRepository('AppBundle:User');
        $newUsers = array();
//        var_dump($users[2]->getAbout());die;
        $texts = array('like' => 'like', 'message' => 'send', 'no_results' => 'no results', 'add' => 'add', 'remove' => 'remove', 'unblock' => 'unblock');


        if (is_array($users) and isset($users['photos'])) {

            if ($users['photos'] == 0) {
                //var_dump(123)
                return array(
                    'users' => $newUsers,
                    'texts' => $texts,
                    'arenaStatus' => $this->get('translator')->trans('עליך להעלות לפחות תמונה אחת כדי להיכנס אל הזירה'),
                    'mobile' => $this->detectIsMobile(),
                );
            }

            //$newUsers = $users['online'];

            $photo = new Photo();
//            $photo->setCloudinary();

//            foreach (array('online','other') as $key) {
//                foreach ($users[$key] as $user) {
//                    $user['age'] = date_diff(date_create($user['birthday']), date_create('today'))->y;
//
//                    $user['image'] = cloudinary_url($user['image'],  array("width" => 300, "height" => 300, "crop" => "fill"));
//                    $newUsers[] = $user;
//                }
//            }
        } else {

            foreach ($users as $user) {

                if (is_object($user)) {
                    $newUser = array();
                    if ($currentUser != 'anon.') {
                        $distance = $usersRepo->getDistance($currentUser, $user);
                        $distance = ($distance == '0' || !$distance) ? '' : $distance . ' ' . $this->get('translator')->trans('ק"מ');
                        $newUser['canWriteTo'] = $this->get('messenger')->CheckIfCanWriteTo($currentUser, $user);
                        $newUser['isAddLike'] = $currentUser->isAddLike($user);
                        $newUser['isAddFavorite'] = $currentUser->isAddFavorite($user->getId());
                        $newUser['isAddBlackListed'] = $currentUser->isAddBlackListed($user->getId());
                        $newUser['distance'] = $distance;

                    }
//                   var_dump($currentUser);die;
                    $mainPhoto = (is_object($user->getMainPhoto()) && !is_string($currentUser)) ? $user->getMainPhoto()->getFaceWebPath() : $user->getNoPhoto();
                    $fotoMain = (is_object($user->getMainPhoto()) && !is_string($currentUser)) ? $user->getMainPhoto()->getWebPath() : $user->getNoPhoto();

                    $looking_for_text = '';
                    foreach ($user->getLookingFor() as $lookingFor) {
                        $looking_for_text .= $looking_for_text == '' ? $lookingFor->getName() : ', ' . $lookingFor->getName();
                    }
                    $newUser['id'] = $user->getId();
                    $newUser['isPaying'] = $user->isPaying();
                    $newUser['isNew'] = $user->isNew($settings->getUserConsideredAsNewAfterDaysNumber());
                    $newUser['isOnline'] = $user->isOnline
                    ($settings->getUserConsideredAsOnlineAfterLastActivityMinutesNumber());
                    //($settings->getUserConsideredAsOnlineAfterLastActivityMinutesNumber()),
                    $newUser['photo'] = $mainPhoto;
                    $newUser['url'] = $fotoMain;
                    $newUser['username'] = mb_strlen($user->getUsername(), 'UTF-8') < 12 ? $user->getUsername() :
                        substr($user->getUsername(), 0, 12);
                    $newUser['username'] = $user->getUsername();
                    $newUser['age'] = $user->age();

                    $newUser['region_name'] = $user->getRegion()->getName();
                    $newUser['area_name'] = $user->getCity()->getName();
                    $newUser['gender'] = $user->getGender()->getId();
                    $newUser['relationshipStatus'] = $user->getRelationshipStatus()->getName();
                    $newUser['lookingFor'] = $looking_for_text;
                    $newUser['about'] = $user->getAbout();
//                        $newUser['lookingFor'] = $user->getLookingFor() ? $user->getLookingFor()->getName() : '';
                    $newUsers[] = $newUser;
                }
            }
        }


        $filters = array(
            array(
                'label' => $this->get('translator')->trans('distaance'),
                'value' => 'distance',
            ),
            array(
                'label' => $this->get('translator')->trans('new members'),
                'value' => 'new',
            ),
            array(
                'label' => $this->get('translator')->trans('with photo'),
                'value' => 'photo',
            ),
            array(
                'label' => $this->get('translator')->trans('last visit'),
                'value' => 'lastActivity',
            ),
            array(
                'label' => $this->get('translator')->trans('popularity'),
                'value' => 'popularity',
            )
        );


        //var_dump($newUsers);die;
        return array(
            'users' => $newUsers,
            'texts' => $texts,
            'filters' => $filters,
            'filter' => $filter,
            'mobile' => $this->detectIsMobile(),
        );

    }


    /**
     * @Route("/login/facebook", name="login_facebook")
     */
    public function facebookLogin(Request $request)
    {
        $fbId = $request->request->get('fbId', false);
        $em = $this->getDoctrine()->getManager();

        $repository = $this->getDoctrine()->getRepository(User::class);
        if ($fbId) {
            $user = $repository->findOneBy(array('facebook' => $fbId));
        }


        if (!isset($user) || empty($user)) {
            return new JsonResponse(array(
                'text' => array(
                    'login' => $this->get('Translator')->trans('Sign in with exist account'),
                    'registration' => $this->get('Translator')->trans('Create new account'),
                    'popupHeader' => $this->get('Translator')->trans('Sign in with facebook account'),
                    'popupText' => $this->get('Translator')->trans('Do you want to sign in with an existing account or 
                    create a new one?'),
                    'firstLoginText' => $this->get('Translator')->trans('Only for the fisrt login you need to enter 
                    account '),
                ),

            ));
        } else {
            $token = new UsernamePasswordToken($user, $user->getPassword(), "public", $user->getRoles());

            // For older versions of Symfony, use security.context here
            $this->get("security.token_storage")->setToken($token);
            return $this->redirectToRoute('user_homepage');
            //login
            //var_dump($user->getId());die;
            return new JsonResponse(array(
                'user' => $user
            ));
        }


    }

    /**
     * @Route("/user/{page}/{ajax}", defaults={"page" = 1, "ajax" = 0}, name="user_homepage")
     */
    public function indexAction(Request $request, $page, $ajax)
    {
//        if ($this->getUser()->getUsername() == 'testzodiac') {
//            var_dump(234);die;
//        }
//        if ($this->getUser()->getUsername() == 'tester1') {
//            var_dump($page);die;
//        }

        $filter = $request->request->get('advancedSearch')['filter'] ?? $request->get('filter') ?? 'new';

        $this->setUserLoginFrom();
        $this->getUser()->setIp($_SERVER['REMOTE_ADDR']);
        $em = $this->getDoctrine()->getManager();
        $em->persist($this->getUser());
        $em->flush();

        $form = $this->createForm(QuickSearchType::class, new User());
        $form->handleRequest($request);
        $usersRepo = $this->getDoctrine()->getRepository('AppBundle:User');
        $settings = $this->getDoctrine()->getManager()->getRepository('AppBundle:Settings')->find(1);

        $banners = $this->get('banners')->getBanners('afterLogin', $this->getUser());

//        $fromCook = false;

//        if (isset($_COOKIE['page']) && !empty($_COOKIE['page']) && $_COOKIE['page'] != 1 ) {
//            $page = $_COOKIE['page'];
//            $fromCook = true;
//        }

//        if ($this->getUser()->getUsername() == 'tester1') {
//            var_dump($page);die;
//        }

        if (!$ajax) {
            return $this->render('frontend/user/index.html.twig', array(
                'form' => $form->createView(),
                'newUsers' => $usersRepo->getNew(
                    array(
                        'considered_as_new_days_number' => $settings->getUserConsideredAsNewAfterDaysNumber(),
                        'per_page' => $settings->getUsersPerPage(),
                        'current_user' => $this->getUser(),
                        'paginator' => $this->get('knp_paginator'),
                        'page' => $page,
                        'filter' => $filter,
//                        'from_cook' => $fromCook,
                        'cookUserViewedId' => $_COOKIE['userId'],
                    )
                ),
                'messenger' => $this->get('messenger'),
                'banners' => $banners,
                'noResults' => $this->get('Translator')->trans('No results'),
                'mobile' => $this->detectIsMobile(),
                'filter' => $request->request->get('advancedSearch')['filter'] ?? $request->get('filter') ?? 'new',
                'savePageCookie' => true,
                'page' => $page,
            ));
        } else {

//            if ($this->getUser()->getUsername() == 'testzodiac') {
//            var_dump(count(123));die;
//            }
            return new JsonResponse(array(
                'newUsers' => $this->transformUsers($usersRepo->getNew(
                    array(
                        'considered_as_new_days_number' => $settings->getUserConsideredAsNewAfterDaysNumber(),
                        'per_page' => $settings->getUsersPerPage(),
                        'current_user' => $this->getUser(),
                        'paginator' => $this->get('knp_paginator'),
                        'mobile' => $this->detectIsMobile(),
                        'page' => $page,
                    )
                ),
                    $settings
                ),
                'banners' => $banners,
                'page' => $page,
//                'cookPage' => $cookPage
            ));
        }

    }

//    /**
//     * @Route("/user/settings", name="user_settings_action")
//     */
//    public function userSettingsAction() {
//        var_dump(123);die;
//    }


    private
    function detectIsMobile()
    { // 'mobile' => $this->detectIsMobile(),
        $mobileDetector = $this->get('mobile_detect.mobile_detector');
        return $mobileDetector->isMobile();
    }

    /**
     * @param User $contact object of User    the user that call to him
     *
     * @Route("/video/call/{contact}", name="user_call_start")
     */
    public function startCall(User $contact)
    {

        $userRepo = $this->getDoctrine()->getManager()->getRepository('AppBundle:User');

        if ($this->get('messenger')->CheckIfCanWriteTo($this->getUser(), $contact)) {
            $userRepo->startCall($this->getUser(), $contact);
        }

        return new JsonResponse(array());

    }

    /**
     * @Route("/video/call/push/{contact}", name="user_call_push")
     */
    public function sendCallPush(User $contact)
    {
        $user = $this->getUser();

        if ($contact->getIsSentPush()) {
            $messenger = $this->get('messenger');
            $message = 'incoming video call' . $user->getUsername();

            $image = $user->getMainPhoto() ? $user->getMainPhoto()->getFaceWebPath() : $user->getNoPhoto();
            $image = $this->getParameter('base_url') . $image;

            return $messenger->pushNotification1($message, $contact->getId(), $user->getId(), $image, true);
        } else {
            return false;
        }

    }

    /**
     * @Route("/save/browser/token", name="save_browser_token")
     */
    public function saveBrowserToken(Request $request)
    {
        $mobileDetector = $this->get('mobile_detect.mobile_detector');
        //
        $mob_os = 'browser';

        $token = $request->get('token', false);

        $result = array('success' => false);
        if (!empty($token)) {

            $userId = $this->get('security.token_storage')->getToken()->getUser()->getId();

            $result = array('success' => $this->get('messenger')->setUserDevice($mob_os, $token, $userId));
            //var_dump($result); die;
        }
        return new JsonResponse(array(
            'result' => $result,
        ));
    }


    /**
     * @Route("/user/check/contact/{id}", name="messenger_check_contact")
     *
     * @param User $user object of User    the contact user
     *
     *  Check if current user can write to passed user (by passed user settings + if the passed user writing first or settings like to current)
     *
     * @return JsonResponse
     */
    public function CheckIfCanWriteTo(User $contact)
    {
        $user = $this->getUser();
        $messenger = $this->get('messenger');
        $settings = $this->getDoctrine()->getRepository('AppBundle:Settings')->find(1);

        $canCreateNewChat = true;
        //var_dump(123);die;
        if (!$user->getMainPhoto(true) && in_array($user->getGender()->getId(), [1, 4])) {
            $canCreateNewChat = $messenger->checkIfCanCreateChatToday($user->getId(), $contact->getId(), $settings->getSendMessageUsersNumberWithoutPhoto());
        }
        return new JsonResponse(array(
            'canContact' => $messenger->CheckIfCanWriteTo($user, $contact),
            'canCreateNewChat' => $canCreateNewChat,
        ));
    }


    /**
     * @Route("/update/user/data/", name="user_update_data")
     *
     * //     * @param User $user object of User   the contact user
     *
     *  include all repeatable ajax requests in one.
     *  for now:
     *      - update user statistics
     *      - new user messages #REMOVED because for now not need a notification, so the "new messages number" from statistics do this job.
     *
     * @return JsonResponse
     */
    public function userDataUpdate()
    {
        $user = $this->getUser();
        $manager = $this->getDoctrine()->getManager();
//        $messenger = $this->get('messenger');
        $userRepo = $manager->getRepository('AppBundle:User');

        $options['userId'] = $user->getId();
        $options['lastLoginAt'] = $user->getLastloginAt()->format('Y-m-d H:i:s');

//        $newMessages = $messenger->checkNewMessages($options);

        $statistics = $userRepo->getUserStatistics($options['userId'], $user->getGender()->getId());

//        var_dump(123);
        return new JsonResponse(array(
//            'newMessages' => $newMessages,

            'statistics' => $statistics,
        ));

    }


    /**
     * @Route("/update/user/phone", name="user_update_phone")
     *
     * @return JsonResponse
     */
    public function userPhoneUpdate(Request $request)
    {

        $userRepo = $this->getDoctrine()->getRepository('AppBundle:User');
        $user = $this->getUser();
        $phone = filter_var($request->request->get('phone'), FILTER_SANITIZE_STRING);
        $phone = trim($phone);


        $is_valid = true;
        $error = '';

        if (!is_numeric($phone)) {
            $is_valid = false;
            $error = 'על מספר הטלפון להכיל רק מספרים';
//            var_dump($phone);
        } else if (strlen($phone) > 12 || strlen($phone) < 9) {
            $is_valid = false;
            $error = 'Phone number must be 9 - 12 numbers long';
        }


        if ($is_valid) {

            $user_with_phone = $userRepo->findBy(array(
                'phone' => $phone,
            ));

            if ($user_with_phone) {
                $is_valid = false;
                $error = 'phone number exists';
            } else {
                $post = $userRepo->removeWordsBlocked(array('phone' => $phone), array('phone'));
//                var_dump($post);
                $phone = $post['phone'];
                if (!$phone) {
                    $is_valid = false;
                    $error = 'This number is blocked';
                }
            }

        }


        if ($is_valid) {
            $em = $this->getDoctrine()->getManager();
            $user->setPhone($phone);
            $em->persist($user);
            $em->flush();
        }

        if ($is_valid) {
            $request->request->set('repeat', true);

        }
        return new JsonResponse(array(
            'success' => $is_valid,
            'error' => $error,
            'message' => ' message sent to ' . $phone
        ));


    }


    /**
     * @Route("/update/user/email", name="user_update_email")
     *
     * @return JsonResponse
     */
    public function userEmailUpdate(Request $request)
    {

        $userRepo = $this->getDoctrine()->getRepository('AppBundle:User');
        $user = $this->getUser();
        $email = filter_var($request->request->get('email'), FILTER_SANITIZE_STRING);
        $email = trim($email);


        $is_valid = true;
        $error = '';


        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $is_valid = false;
            $error = 'Not a valid email';
        }
        if ($is_valid) {

            $user_with_email = $userRepo->findBy(array(
                'email' => $email,
            ));

            if ($user_with_email) {
                $is_valid = false;
                $error = 'Someone is already using that email';
            } else {
                $post = $userRepo->removeWordsBlocked(array('email' => $email), array('email'));
//                var_dump($post);
                $email = $post['email'];
                if (!$email) {
                    $is_valid = false;
                    $error = 'That email is blocked';
                }
            }
        }

        if ($is_valid) {
            $em = $this->getDoctrine()->getManager();
            $user->setEmail($email);
            $em->persist($user);
            $em->flush();
        }

        if ($is_valid) {
            $request->request->set('repeat', true);
            $this->sendActivationEmail($request);
        }
        return new JsonResponse(array(
            'success' => $is_valid,
            'error' => $error,
            'message' => 'Message sent to the mail address:' . $email
        ));
    }

//    /**
//     * @Route("/media/photos/{user_id}/{photo_id}", name="user_update_phone")
//     */
//    public function photos(Request $request) {
//        var_dump(123);die;
//    }

}
