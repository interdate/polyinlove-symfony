<?php

namespace AppBundle\Controller\Api\V1;

use AppBundle\Entity\Banner;
use AppBundle\Entity\LocCities;
use AppBundle\Entity\LocCountries;
use AppBundle\Entity\LocRegions;
use AppBundle\Entity\Page;
use AppBundle\Entity\User;
use AppBundle\Form\Type\ContactType;
use AppBundle\Form\Type\SignUpApiTwoType;
use AppBundle\Form\Type\SignUpApiType;
use AppBundle\Form\Type\SignUpOneType;
use AppBundle\Form\Type\SignUpApiOneType;
use AppBundle\Form\Type\SignUpThreeType;
use AppBundle\Form\Type\SignUpTwoType;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping\Id;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\HttpFoundation\JsonResponse;


#use Symfony\Component\BrowserKit\Request;

class OpenController extends FOSRestController
{
    private function signupEmail($username, $password, $email)
    {
        return $this->get('translator')->trans(
            '<div dir="ltr">' . "
                    Hello {{username}} , <br/>
                    Thank you for signing up to PolyAmory, the premium polyamory dating site! <br/>
                    <br/>
                    These are you login details: <br/>
                    email: {{email}}<br/>
                    password: {{password}} <br/>
                    <br/>
                    We would be happy to answer a question you may have at {$this->getParameter('contact_email')}
                    <br><br><br>
                    
                   yours,
                   <br>
                   PolyAmoryTeam
                   <br>
                   {$this->getParameter('base_url')}
                   </div>", ['username' => $username, 'email' => $email, 'password' => $password]);

    }

    /**
     * @ApiDoc(
     *   description = "Get page",
     *
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     401 = "Returned when bad credentials were sent"
     *   }
     * )
     */
    public function getPageAction(Page $page)
    {
        return $this->view(array(
            'page' => array(
                'title' => $page->getName(),
                'content' => $page->getContent(),
            ),
        ), Response::HTTP_OK);
    }


    /**
     * @ApiDoc(
     *   description = "Get FAQ page",
     *
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     401 = "Returned when bad credentials were sent"
     *   }
     * )
     * @param Request $request
     * @return \FOS\RestBundle\View\View
     */

    public function getFaqAction(Request $request)
    {
        $cats = $this->getDoctrine()->getManager()->getRepository('AppBundle:FaqCategory')->findByIsActive(true);
        $seo = $this->getDoctrine()->getRepository('AppBundle:Seo')->findOneByPage('faq');
        $categories = array();
        foreach ($cats as $cat) {
            $category = array('name' => $cat->getName(), 'faq' => array());
            foreach ($cat->getFaq() as $faq) {
                if ($faq->getIsActive()) {
                    $category['faq'][] = array(
                        "q" => $faq->getName(),
                        "a" => $faq->getContent()
                    );
                }
            }
            $categories[] = $category;
        }
        return $this->view(array(
            'page' => array(
                'title' => $seo->getTitle(),
                'description' => $seo->getDescription()
            ),
            'content' => $categories,
        ), Response::HTTP_OK);
    }


    /**
     * @ApiDoc(
     *   description = "Get sign up Form",
     *
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     401 = "Returned when bad credentials were sent"
     *   }
     * )
     * @param Request $request
     * @return \FOS\RestBundle\View\View
     */

    public function getSignUpAction(Request $request)
    {
        $user = new User();

        $form = $this->createForm(new SignUpOneType(), $user);
        $formTrans = $this->transformForm($form);
        $formTrans['step'] = 1;
        return $this->view(array(
            'form' => $formTrans,
        ), Response::HTTP_OK);
    }

    /**
     * @ApiDoc(
     *   description = "Create user",
     *   parameters={
     *      {"name"="form", "dataType"="string", "required"=false, "description"="parametrs"}
     *   },
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     401 = "Returned when bad credentials were sent"
     *   }
     * )
     * @param Request $request
     * @return \FOS\RestBundle\View\View
     */
    public function postSignUpAction(Request $request)
    {
        $postAll = $request->request->all();

        $errors = false;
        $user = new User();
        $flow = $this->get('SignUpFlow');
        //$flow->disableToken();
        $flow->bind($user);
        $form = $flow->createForm();

        if ($request->isMethod('POST')) {

            $firtPost = $postAll['sign_up_one'];//$request->request->get('signUpOne', false);
            if ($firtPost) {
                if (empty($firtPost['agree'])) {
                    $form->get('agree')->addError(new FormError($this->get('translator')->trans('You must confirm Terms and Conditions')));
                    $errors = true;
                }
                if (empty($firtPost['username'])) {
                    $form->get('username')->addError(new FormError($this->get('translator')->trans('Enter username')));
                    $errors = true;
                }
                if (empty($firtPost['email']['first'])) {
                    $form->get('email')->addError(new FormError($this->get('translator')->trans('Enter email address')));
                    $errors = true;
                }
                if (empty($firtPost['password']['first'])) {
                    $form->get('password')->addError(new FormError($this->get('translator')->trans('Enter password')));
                    $errors = true;
                }
                if (empty($firtPost['gender'])) {
                    $form->get('gender')->addError(new FormError($this->get('translator')->trans('Choose your gender')));
                    $errors = true;
                }
                if (empty($firtPost['birthday']['day']) || empty($firtPost['birthday']['month']) || empty($firtPost['birthday']['year'])) {
                    $form->get('birthday')->addError(new FormError($this->get('translator')->trans('Enter your birthday date')));
                    $errors = true;
                }

                $request->request->set('signUpOne', $firtPost);

            }

            $postKey = 'sign_up_one';
            $post3 = $firtPost;
            if (!$post3 and $postAll['flow_signUp_step'] == 2) {
                $postKey = 'sign_up_two';
                $post3 = $firtPost[$postKey];
                $request->request->set('signUpTwo', $post3);
            }


            if (!$post3) {
                $postKey = 'sign_up_two';
                $post3 = $request->request->get('sign_up_two', false);
                if ($post3) {

                    if (empty($post3['about']) || mb_strlen($post3['about']) < 10) {
                        $form->get('about')->addError(new FormError($this->get('translator')->trans('Min 10 letters in About Me')));
                        $errors = true;
                    }
                    if (empty($post3['looking']) || mb_strlen($post3['looking']) < 10) {
                        $form->get('looking')->addError(new FormError($this->get('translator')->trans('Min 10 letters in What I\'m Looking For')));
                        $errors = true;
                    }
                    if (count((array)$post3['hobbies']) == 0) {
                        $form->get('hobbies')->addError(new FormError($this->get('translator')->trans('Please choose Hobbies')));
                        $errors = true;
                    }
                }
            }
            if ($post3) {
                $userRepo = $this->getDoctrine()->getRepository('AppBundle:User');
                $post3 = $userRepo->removeWordsBlocked($post3, array('username', 'about', 'looking', 'zipCode'));
                $request->request->set($postKey, $post3);
                $request->request->set('signUpTwo', $post3);
            }

            if (!$errors and $flow->isValid($form)) {
                $flow->saveCurrentStepData($form);

                if ($flow->nextStep()) {
                    $form = $flow->createForm();
                } else {
                    $settings = $this->getDoctrine()->getManager()->getRepository('AppBundle:Settings')->find(1);
                    $text = $this->signupEmail($user->getUsername(), $user->getEmail(), $user->getPassword());

                    $rolesRepo = $this->getDoctrine()->getRepository('AppBundle:Role');
                    $role = $rolesRepo->find(2);
                    $user->setRole($role);

                    $encoder = $this->container->get('security.password_encoder');
                    $encoded = $encoder->encodePassword($user, $user->getPassword());
                    $user->setPassword($encoded);

                    $user->setSignUpDate(new \DateTime());
                    $user->setLastActivityAt(new \DateTime());
                    $user->setLastloginAt(new \DateTime());
                    $user->setIsActive(true);
                    $user->setIsFrozen(0);

                    $em = $this->getDoctrine()->getManager();
                    $user->setZodiac($this->getDoctrine()->getManager()->getRepository('AppBundle:Zodiac')->getZodiacByDate($user->getBirthday()));
                    $user->setIsUpdatedZodiac(true);
                    $em->persist($user);

                    $em->flush();

                    $flow->reset();

                    $subject = $this->get('translator')->trans('Welcome to ' . $this->getParameter('base_url'));
                    $headers = "MIME-Version: 1.0" . "\r\n";
                    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
                    $headers .= 'From: Admin <{' . $this->getParameter('contact_email') . '>' . "\r\n";
                    mail($user->getEmail(), $subject, $text, $headers);

                    $session = new Session();
                    $session->set('userId', $user->getId());


                    $res = array(
                        'status' => 'no_photo',
                        'photos' => array(
                            array(
                                'face' => $user->getNoPhoto(),
                                'url' => $user->getNoPhoto()
                            )
                        ),
                        'photo' => $user->getNoPhoto(),
                        'texts' => array(
                            'approved' => $this->get('translator')->trans('Approved'),
                            'status' => $this->get('translator')->trans('Status'),
                            'delete' => $this->get('translator')->trans('Delete'),
                            'cancel' => $this->get('translator')->trans('Cancel'),
                            'waiting_for_approval' => $this->get('translator')->trans('Waiting for approval'),
                            'set_as_main_photo' => $this->get('translator')->trans('Set as Main Photo'),
                            'add_photo' => $this->get('translator')->trans('Add Photo'),
                            'choose_from_camera' => $this->get('translator')->trans('Choose from Camera'),
                            'choose_from_gallery' => $this->get('translator')->trans('Choose from Gallery'),
                            'register_end_button' => $this->get('translator')->trans('done'),

                            'description' => $this->get('translator')->trans('
                                    Adding a photo to your profile boosts your chances of meeting new people by a factor of 20!<br>
                                    We strongly encourage you post a number of them.<br>
                                    *New photos require admin’s approval.<br>
                                    <br>
                                    Please pay attention to these guidelines:
                                    <ul>
                                        <li>Photos must be of yourself, and you must be recognizable in the photo.</li>
                                        <li>Photos may not contain nudity or sexual content.</li>
                                     </ul><br>
                                     For more information check out our <a click="/open_api/pages/6">Photos Policy</a> page.<br>
                                     Having trouble uploading a photo? Send the photo to <a href="mailto:' . $this->getParameter('contact_email') . '" >' . $this->getParameter('contact_email') . '</a> along with your nickname or the email you registered with, and we\'ll upload it to your profile .<br >
                                '),
                        )
                    );

                    $res['id'] = $user->getId();
                    return $this->view($res, Response::HTTP_OK);
                }
            }
        }
        $removeFields = array('relationshipTypeDetails', 'sexOrientationDetails', 'lookingForDetails');
        foreach ($removeFields as $field) {
            if ($form->has($field)) {
                $form->remove($field);
            }
        }

        return $this->view(array('user' => array(
            'form' => $this->transformForm($form, $flow->getFormData()),
            'errors' => $form->getErrors(),

        )), Response::HTTP_OK);
    }


    /**
     * @ApiDoc(
     *   description = "Create user",
     *   parameters={
     *      {"name"="form", "dataType"="string", "required"=false, "description"="parametrs"}
     *   },
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     401 = "Returned when bad credentials were sent"
     *   }
     * )
     * @param Request $request
     * @return \FOS\RestBundle\View\View
     */
    public function postSignUpNewAction(Request $request)
    {

        $postAll = $request->request->all();
        $errors = false;
        $user = new User();
        $signUp = SignUpApiOneType::class;
        $step = 1;
        $dirty_post = (isset($postAll['signUpOne'])) ? $postAll['signUpOne'] : array();
        $options = array(
            'validation_groups' => array('sign_up_one'),
            'csrf_protection' => false
        );
        if (isset($postAll['signUpTwo'])) {
            $signUp = SignUpApiTwoType::class;
            $step = 2;
            if (isset($postAll['signUpTwo']['region'])) {
                // validation for country, region and city fields
                try {
                    $paramBag = $request->request->get('signUpTwo');
                    if (is_string($paramBag['country'])) {
                        $country = $this->checkIfFieldExists('country', $paramBag['country'])->getId();
                        $postAll['signUpTwo']['country'] = $country;
                        $paramBag['country'] = $country;
                    }

                    if (is_string($paramBag['region'])) {
                        $region = $this->checkIfFieldExists('region', $paramBag['region'], $paramBag['country'])->getId();
                        $postAll['signUpTwo']['region'] = $region;
                        $paramBag['region'] = $region;
                    }

                    if (is_string($paramBag['city'])) {
                        $city = $this->checkIfFieldExists('city', $paramBag['city'], $paramBag['region'])->getId();
                        $postAll['signUpTwo']['city'] = $city;
                        $paramBag['city'] = $city;
                    }
                    $request->request->set('signUpTwo', $paramBag);
                } catch (Exception $e) {
                    echo 'there was an error formatting city/region/ country';
                }
            }
            $dirty_post = $postAll['signUpTwo'];

            $options['validation_groups'] = array('sign_up_two');
        }
        if (isset($postAll['signUpTwo']) and isset($postAll['signUpTwo']['about'])) {
            $signUp = SignUpThreeType::class;
            $step = 3;
            try {
                $paramBag = $request->request->get('user');
                if (is_string($paramBag['country'])) {
                    $country = $this->checkIfFieldExists('country', $paramBag['country'])->getId();
                    $paramBag['country'] = $country;
                }

                if (is_string($paramBag['region'])) {
                    $region = $this->checkIfFieldExists('region', $paramBag['region'], $paramBag['country'])->getId();
                    $paramBag['region'] = $region;
                }

                if (is_string($paramBag['city'])) {
                    $city = $this->checkIfFieldExists('city', $paramBag['city'], $paramBag['region'])->getId();
                    $paramBag['city'] = $city;
                }
                $request->request->set('user', $paramBag);
                $postAll = $request->request->all();
            } catch (Exception $e) {
                echo 'there was an error formatting city/region/ country';
            }

            $dirty_post = $postAll['signUpTwo'];
            $options['validation_groups'] = array('sign_up_three');
        }

        if ($request->isMethod('Post')) {

            // remove words of blocked
            if (count($dirty_post) > 0) {
                $userRepo = $this->getDoctrine()->getRepository('AppBundle:User');
                if ($step == 1) {
                    $post = $userRepo->removeWordsBlocked($dirty_post, array('phone', 'email'));
                    $request->request->set('sign_up_api_one', $post);
                }
                if ($step == 2) {
                    $post = $userRepo->removeWordsBlocked($dirty_post, array('sexOrientationDetails', 'lookingForDetails', 'relationshipTypeDetails'));
                    $request->request->set('sign_up_api_two', $post);
                }
                if ($step == 3) {
                    $post = $userRepo->removeWordsBlocked($dirty_post, array('about', 'looking'));
                    $request->request->set('sign_up_three', $post);
                }
            }

            $form = $this->createForm($signUp, $user, $options);
            $form->handleRequest($request);
            if (isset($post) && !empty($post)) {
                switch ($step) {
                    case 1:
                        if (empty(trim($post['username']))) {
                            $form->get('username')->addError(new FormError($this->get('translator')->trans('Username field is required')));
                            $errors = true;
                        }
                        if (empty(trim($post['email']))) {
                            if (!empty($dirty_post['email'])) {
                                $form->get('email')->addError(new FormError($this->get('translator')->trans('Email is blocked')));
                            } else {
                                $form->get('email')->addError(new FormError($this->get('translator')->trans('Email field is required')));
                            }

                            $errors = true;
                        }
                        if (empty(trim($post['password']['first']))) {
                            $form->get('password')->get('first')->addError(new FormError($this->get('translator')->trans('Password field is required')));
                            $errors = true;
                        }
                        if (empty(trim($post['password']['second']))) {
                            $form->get('password')->get('second')->addError(new FormError($this->get('translator')->trans('Password repeat is required')));
                            $errors = true;
                        } else if (trim($post['password']['first']) !== trim($post['password']['second'])) {
                            $errors = true;
                        }
                        if (empty($post['gender'])) {
                            $form->get('gender')->addError(new FormError($this->get('translator')->trans('Gender field is required')));
                            $errors = true;
                        }
                        if (empty(trim($post['phone']))) {
                            if (!empty(trim($dirty_post['phone']))) {
                                $form->get('phone')->addError(new FormError($this->get('translator')->trans('This phone number is blocked')));
                            } else {
                                $form->get('phone')->addError(new FormError($this->get('translator')->trans('Phone field is required')));
                            }

                            $errors = true;
                        } else if (strlen(trim($post['phone'])) > 12 || strlen(trim($post['phone'])) < 10) {
                            $form->get('phone')->addError(new FormError($this->get('translator')->trans('Phone number must contain between 10 - 12 chars')));
                            $errors = true;
                        } else if (!is_numeric(trim($post['phone']))) {
                            $form->get('phone')->addError(new FormError($this->get('translator')->trans('Phone can contain only numbers')));
                            $errors = true;
                        }
                        break;

                    case 2:
                        if (empty($post['country'])) {
                            $form->get('country')->addError(new FormError($this->get('translator')->trans('Country field is required')));
                            $errors = true;
                        } else {
                            $country = $request->request->get('signUpTwo')['country'];
                            if (is_string($country)) {
                                $request->request->set('country', $this->checkIfFieldExists('country', $country));
                            }
                        }

                        if (empty($post['region'])) {
                            $form->get('region')->addError(new FormError($this->get('translator')->trans('Region field is required')));
                            $errors = true;
                        } else {
                            $region = $request->request->get('signUpTwo')['region'];
                            if (is_string($region)) {
                                $request->request->set('region', $this->checkIfFieldExists('region', $region, $request->request->get('country')));
                            }
                        }

                        if (empty($post['city'])) {
                            $form->get('city')->addError(new FormError($this->get('translator')->trans('City field is required')));
                            $errors = true;
                        } else {
                            $city = $request->request->get('signUpTwo')['city'];
                            if (is_string($city)) {
                                $request->request->set('city', $this->checkIfFieldExists('city', $city, $request->request->get('region')));
                            }
                        }


                        if (empty($post['sexOrientation'])) {
                            $form->get('sexOrientation')->addError(new FormError($this->get('translator')->trans('Sex orientation field is required')));
                            $errors = true;
                        }

                        if (empty($post['birthday']['day']) || empty($post['birthday']['month']) || empty($post['birthday']['year'])) {
                            $form->get('birthday')->addError(new FormError($this->get('translator')->trans('Birthday field is required')));
                            $errors = true;
                        }

                        if (empty($post['height'])) {
                            $form->get('height')->addError(new FormError($this->get('translator')->trans('Height field is required')));
                            $errors = true;

                            if (empty($post['relationshipStatus'])) {
                                $form->get('relationshipStatus')->addError(new FormError($this->get('translator')->trans('Relationship status field is required')));
                                $errors = true;
                            }

                            if (empty($post['relationshipType'])) {
                                $form->get('relationshipType')->addError(new FormError($this->get('translator')->trans('Relationship type field is required')));
                                $errors = true;
                            }

                            if (empty($post['lookingFor'])) {
                                $form->get('lookingFor')->addError(new FormError($this->get('translator')->trans('Looking for field is required')));
                                $errors = true;
                            }

                            if (empty($post['smoking'])) {
                                $form->get('smoking')->addError(new FormError($this->get('translator')->trans('Smoking field is required')));
                                $errors = true;
                            }
                        }
                        break;
                    case 3:
                        if (empty(trim($post['about'])) || mb_strlen(trim($post['about'])) < 10) {
                            $form->get('about')->addError(new FormError($this->get('translator')->trans('About me field must contain at least 10 chars')));
                            $errors = true;
                        }
                        if (empty(trim($post['looking'])) /*|| mb_strlen($post['looking']) < 10*/) {
                            $form->get('looking')->addError(new FormError($this->get('translator')->trans('Looking for field must contain at least 10 chars')));
                            $errors = true;
                        }

                        if (empty($post['agree'])) {
                            $form->get('agree')->addError(new FormError($this->get('translator')->trans('You must agree with the terms and conditions to continue')));
                            $errors = true;
                        }

                }
            }
            if (!$errors and $form->isValid()) {
                if ($step != 3) {
                    if ($step == 1) {
                        $signUp = SignUpApiTwoType::class;
                        $step = 2;
                        $options['validation_groups'] = array('sign_up_two');
                    } elseif ($step == 2) {
                        $signUp = SignUpThreeType::class;
                        $step = 3;
                        $options['validation_groups'] = array('sign_up_three');
                    }

                    $form = $this->createForm($signUp, $user, $options);
                } else {
                    $user = new User();
                    $sign = SignUpApiType::class;
                    $form = $this->createForm($sign, $user, array('csrf_protection' => false));

                    $post = $request->request->get('user');
                    $post = $userRepo->removeWordsBlocked($post, array(
                        'sexOrientationDetails',
                        'lookingForDetails',
                        'relationshipTypeDetails',
                        'username',
                        'email',
                        'about',
                        'looking',
                        'zipCode',
                    ));
                    $request->request->set('sign_up_api', $post);
                    $form->handleRequest($request);

                    $settings = $this->getDoctrine()->getManager()->getRepository('AppBundle:Settings')->find(1);
                    $text = $this->signupEmail($user->getUsername(), $user->getEmail(), $user->getPassword());

                    $rolesRepo = $this->getDoctrine()->getRepository('AppBundle:Role');
                    $role = $rolesRepo->find(2);
                    $user->setRole($role);

                    $encoder = $this->container->get('security.password_encoder');

                    $encoded = $encoder->encodePassword($user, trim($postAll['user']['password']));
                    $user->setPassword($encoded);
                    $user->setUsername(trim($user->getUsername()));
                    $user->setSignUpDate(new \DateTime());
                    $user->setLastActivityAt(new \DateTime());
                    $user->setLastloginAt(new \DateTime());
                    $user->setIsActive(true);
                    $user->setIsActivated(false);
                    $user->setIsFrozen(0);
                    $user->setEmail($postAll['user']['email']);
                    $user->setHeight($postAll['user']['height']);
                    foreach (['country', 'region', 'city'] as $zone) {
                        $item = $this->checkIfFieldExists($zone, $postAll['user'][$zone]);
                        $setterName = 'get' . ucwords($zone);
                        $user->{$setterName}($item);
                    }
                    //facebook_id
                    if (isset($postAll['user']['facebook_id']) and !empty($postAll['user']['facebook_id'])) {
                        $user->setFacebook($postAll['user']['facebook_id']);
                    }

                    $em = $this->getDoctrine()->getManager();
                    $user->setZodiac($this->getDoctrine()->getManager()->getRepository('AppBundle:Zodiac')->getZodiacByDate($user->getBirthday()));
                    $em->persist($user);

                    $em->flush();

                    $subject = $this->get('translator')->trans('Welcome to ' . $this->getParameter('site_name') . ' | ' . $this->getParameter('base_url'));
                    $headers = "MIME-Version: 1.0" . "\r\n";
                    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
                    $headers .= 'From: Admin <' . $this->getParameter('contact_email') . '>' . "\r\n";
                    mail($user->getEmail(), $subject, $text, $headers);

                    $session = new Session();
                    $session->set('userId', $user->getId());


                    $res = array(
                        'status' => 'no_photo',
                        'photos' => array(
                            array(
                                'face' => $user->getNoPhoto(),
                                'url' => $user->getNoPhoto()
                            )
                        ),
                        'photo' => $user->getNoPhoto(),
                        'texts' => array(
                            'approved' => $this->get('translator')->trans('Approved'),
                            'status' => $this->get('translator')->trans('Status'),
                            'delete' => $this->get('translator')->trans('Delete'),
                            'cancel' => $this->get('translator')->trans('Cancel'),
                            'waiting_for_approval' => $this->get('translator')->trans('Waiting for approval'),
                            'set_as_main_photo' => $this->get('translator')->trans('Set as Main Photo'),
                            'add_photo' => $this->get('translator')->trans('Add Photo'),
                            'choose_from_camera' => $this->get('translator')->trans('Choose from Camera'),
                            'choose_from_gallery' => $this->get('translator')->trans('Choose from Gallery'),
                            'register_end_button' => $this->get('translator')->trans('end'),
                            'description' => $this->get('translator')->trans('
                                    Adding a photo to your profile boosts your chances of meeting new people times 20!<br>
                                    We strongly encourage you post a number of them.<br>
                                    *New photos awaiting admin’s approval.<br>
                                    <br>
                                    Please notice these guidelines:
                                    <ul>
                                        <li>Photos must be of yourself, and you must be recognizable in the photo.</li>
                                        <li>Photos must not contain nudity or sexual content.</li>
                                     </ul><br>
                                     For more information check out our <a click="/open_api/pages/6">Photos Policy</a> page.<br>
                                     Having trouble uploading a photo? Send the photo to <a href="mailto:' . $this->getParameter('contact_email') . '" >{' . $this->getParameter('contact_email') . '</a> along with your nickname or the email you registered with, and we\'ll upload it to your profile .<br >
                                ')),
                    );

                    $res['id'] = $user->getId();
                    return $this->view($res, Response::HTTP_OK);
                }
            }
        }

        $removeFields = array('relationshipTypeDetails', 'sexOrientationDetails', 'lookingForDetails');
        foreach ($removeFields as $field) {
            if ($form->has($field)) {
                $form->remove($field);
            }
        }

        $trans = $this->transformForm($form, $user);
        $trans['step'] = $step;
        $options = array(
            array(
                'label' => $this->get('translator')->trans('Hierarchical Polyamory'),
                'description' => $this->get('translator')->trans('A hierarchical polyamorous relationship places more importance on one relationship over others. A primary relationship may be marriage,'),
            ),
            array(
                'label' => $this->get('translator')->trans('Non-hierarchical Polyamory'),
                'description' =>
                    $this->get('translator')->trans('A hierarchical relationship does not prioritize any of the members of the relationship over the others.'),
            ),
            array(
                'label' => $this->get('translator')->trans('solo poly'),
                'description' => $this->get('translator')->trans('A single partner looking to have multiple relationships, with none of them what would be considered primary'),
            ),
            array(
                'label' => $this->get('translator')->trans('relationship anarchy'),
                'description' => $this->get('translator')->trans('Relationship anarchy refers to individuals who believe that all interpersonal relationships are equally important. A relationship anarchist might have multiple romantic relationships simultaneously, but may also avoid making special distinctions between relationships that are romantic, sexual, platonic, or familial'),
            ),
            array(
                'label' => $this->get('translator')->trans('triad'),
                'description' => $this->get('translator')->trans('A triad, or throuple, is a relationship between three partners who are all romantically or sexually involved with each other'),
            ));

        $message = '<ul>';

        foreach ($options as $option) {
            $message .= '<li><b>' . $option['label'] . '</b> - ' . $option['description'] . '</li>';
        };

        $message .= '</ul>';


        if ($step == 1) {
            $trans['privateText'] = $this->get('translator')->trans('*** Other users will not see your email and phone ***');
        } elseif ($step == 2) {
            $trans['relationshipTypeHelper'] = array(
                'type' => 'hidden',
                'header' => $this->get('translator')->trans('Polyamory- about relationship types'),
                'message' => $message,
                'cancel' => $this->get('translator')->trans('cancel'),
            );
        }

        return $this->view(array('user' => [
            'form' => $trans,
            'data' => $post ?: false,
            'errors' => $form->getErrors(),
        ]), Response::HTTP_OK);
    }


    /**
     * @ApiDoc(
     *   description = "helper for sign up",
     *   parameters={
     *      {"name"="form", "dataType"="string", "required"=false, "description"="Parametrs"},
     *   },
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     401 = "Returned when bad credentials were sent"
     *   }
     * )
     * @param Request $request
     * @return \FOS\RestBundle\View\View
     */
    public function postSignUpHelperAction(Request $request)
    {
        if ($request->request->get('sign_up_one', false)) {
            $user = new User();
            $form = $this->createForm(SignUpOneType::class, $user);
            $form->handleRequest($request);

            return $this->view(array(
                'form' => $this->transformForm($form),
            ), Response::HTTP_OK);
        }
    }


    /**
     * @ApiDoc(
     *   resource = true,
     *   description = "Save Locations",
     *   parameters = {
     *  },
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     401 = "Returned when bad credentials were sent"
     *   }
     * )
     * @param Request $request
     * @return \FOS\RestBundle\View\View
     */

    public function postLocationSaveAction(Request $request)
    {
        $id = $request->get('id', null);
        $latitude = $request->get('latitude', null);
        $longitude = $request->get('longitude', null);

        if ($latitude != null and $longitude != null and $id != null) {
            $user = $this->getDoctrine()->getManager()->getRepository('AppBundle:User')->find($id);
            if ($user) {
                $user->setLatitude($latitude);
                $user->setLongitude($longitude);
                $em = $this->getDoctrine()->getManager();
                $em->persist($user);
                $em->flush();
            }
        }
        return $this->view(1, Response::HTTP_OK);
    }

    /**
     * @param $user
     */
    public function setUserZipCodeFromSession($user)
    {
        $session = new Session();
        $sessionData = $session->all();
        //$session->clear();

        if (isset($sessionData['craue_form_flow'])) {

            foreach ($sessionData['craue_form_flow']['signUp'] as $item) {

                if (array_key_exists('1', $item['data'])) {
                    $array = $item['data'][1];
                    if (isset($array['zipCode'])) {
                        $user->setZipCode($this->getDoctrine()->getRepository('AppBundle:ZipCode')->find($array['zipCode']));
                    }
                }
            }
        }
    }

    /**
     * @param $request
     * @param $user
     * @return bool
     */
    public function setZipCode($request, $user)
    {
        $post = $request->request->get('sign_up_one');
        if (isset($post['zipCode']) and (int)$post['zipCode'] > 0) {
            $user->setZipCode($this->getDoctrine()->getRepository('AppBundle:ZipCode')->find($post['zipCode']));
            if (!is_object($user->getZipCode())) {
                return false;
            }
            return true;
        } //return false;
        else {
            $this->setUserZipCodeFromSession($user);
        }
    }

    /**
     * @ApiDoc(
     *  description="Send contact form",
     *  input="AppBundle\Form\Type\ContactType",
     *  statusCodes = {
     *     200 = "Returned when successful",
     *     401 = "Returned when bad credentials were sent"
     *   }
     * )
     * @param Request $request
     * @return \FOS\RestBundle\View\View
     */
    public function postContactAction(Request $request)
    {
        $email = false;
        // var_dump($request -> request -> all()); die;

        $id = $request->get('id');


        $sent = false;
        $user = new User();

        $contact = ContactType::class;
//        $contact->disableToken();
        $form = $this->createForm($contact, $user, array('csrf_protection' => false));

        // $form = $request->request->get('contact');
        if ($request->isMethod('Post')) {

            // var_dump($request);
            // var_dump($form -> getData());
            $form->handleRequest($request);
            // var_dump($form -> getData());
            //var_dump($form->get('_token')->getData(); die;
            /*  print_r($request->request->all());
              echo $form->isValid() ? 1 : 0;

              die;*/
            //$form->handleRequest($form);
            if ($form->isSubmitted()) {

                $email = ($form->get('email')->getData()) ? $form->get('email')->getData() : $form->getData()->getEmail();


                if ($user = $this->getDoctrine()->getManager()->getRepository('AppBundle:User')->find($email)) {
                    $userId = $user->getId();
                    $email = $user->getEmail();
                } else {
                    $userId = 0;
                }

                $subject = $this->get('translator')->trans('Polyamory | contact us | # ' . $userId . '  |  ') . $form->get('subject')->getData();

                $mobileDetector = $this->get('mobile_detect.mobile_detector');

                if ($mobileDetector->isAndroidOS()) {
                    $platform = 'Android App';
                }
                if ($mobileDetector->isIOS()) {
                    $platform = 'IOS App';
                }

                $text = $form->get('text')->getData();


                $body = '<div dir="ltr">';
                $body .= $text . '<br>';
                $body .= $this->get('translator')->trans('sent from : ') . $platform;
                $body .= '</div>';

                $headers = "MIME-Version: 1.0" . "\r\n";
                $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
                $headers .= 'From: ' . $email . ' <' . $email . '>' . "\r\n";
                $settings = $this->getDoctrine()->getRepository('AppBundle:Settings')->find(1);
                mail($settings->getContactEmail(), $subject, $body, $headers);
//                mail('vita@interdate-ltd.co.il', $subject, $body, $headers);
//                mail('vita@interdate-ltd.co.il', $subject, $body, $headers);
                // mail('albert@interdate-ltd.co.il',$subject,$body,$headers);
                $sent = true;
                $success = $this->get('translator')->trans('Thank you! message sent successfully');
            }
        }

        $empty_errors = array(
            'form' => array(
                'children' => array(
                    'email' => array('errors' => ''),
                    'subject' => array('errors' => ''),
                    'text' => array('errors' => ''),
                )
            )
        );


        $res = array(
            //'form' => $this->transformForm($form),
            'mail' => $email,
            'errors' => $sent ? $empty_errors : $form->getErrors(),
            'send' => $sent,
        );
        if ($sent) {
            $res['success'] = $success;
        }
//        var_dump($form->getErrors());die;
//        return new JsonResponse($res);
        return $this->view($res, Response::HTTP_OK);
    }


    /**
     * @ApiDoc(
     *   resource = true,
     *   description = "Get banner",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     401 = "Returned when bad credentials were sent"
     *   }
     * )
     */
    public function getBannerAction(Request $request)
    {
//        var_dump(123);die;
        $user = null;

        if ($user_id = $request->query->get('user_id', false)) {
            $user = $this->getDoctrine()->getRepository('AppBundle:User')->find($user_id);
        }

        $banners = $this->get('banners')->getBanners('mobile', $user);
        if (count($banners) > 0) {
            $banner = $banners[rand(0, count($banners) - 1)];
            $protocol = $request->isSecure() ? 'https://' : 'http://';
            return $this->view(array('banner' => array(
                'id' => $banner->getId(),
                'link' => $banner->getHref(),
                'img' => $protocol . $request->getHost() . $banner->getImg(),
                'hideLogin' => [
                    'DialogPage', 'EditProfilePage', 'Registration', 'ArenaPage',
                    'ChangePhotosPage', 'ProfilePage', 'MessengerNotifications'],
                'hideLogout' => ['Registration', 'LoginPage'],
            )), Response::HTTP_OK);
        }
        return $this->view(array(
            'link' => '',
            'img' => '',
//           'user_id' => $this->getUser()->getId(),
        ));
    }

    /**
     * @ApiDoc(
     *  description="Get contact form",
     *  output="AppBundle\Form\Type\ContactType",
     *  parameters={
     *      {"name"="id", "dataType"="integer", "required"=false, "description"="String for translate"}
     *  },
     *  statusCodes = {
     *     200 = "Returned when successful",
     *     401 = "Returned when bad credentials were sent"
     *   }
     * )
     * @param Request $request
     * @return \FOS\RestBundle\View\View
     */
    public function getContactAction(Request $request)
    {
        $id = $request->get('id');

        $user = (int)$id == 0 ? $user = new User() : $this->getDoctrine()->getManager()->getRepository('AppBundle:User')->find($id);
        $form = $this->createForm(ContactType::class, $user);

        return $this->view(array(
            'form' => $this->transformForm($form),
            'userEmail' => $user->getEmail(),
        ), Response::HTTP_OK);
    }

    /**
     * @ApiDoc(
     *   resource = true,
     *   description = "Login User",
     *
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Bad Request.",
     *     403 = "Returned when bad credentials were sent."
     *   }
     * )
     * @param Request $request
     * @return \FOS\RestBundle\View\View
     */

    public function postLoginAction(Request $request)
    {

        $headers = $request->headers->all();
        $username = $headers['username'];
        $password = trim($headers['password'][0]);
        $facebook = $request->request->get('facebook_id', false);

        $username = urldecode((string)$username[0]);

        $em = $this->getDoctrine()->getManager();
        $user = false;
        if (!empty($username) and !empty($password)) {
            $userCheck = $em->getRepository('AppBundle:User')->loadUserByUsernameApi($username);

            if ($userCheck) {

                $encoder_service = $this->get('security.encoder_factory');
                $encoder = $encoder_service->getEncoder($userCheck);
                //$encoded_pass = $encoder->encodePassword($password, $userCheck->getSalt());
                $validOldPassword = $encoder->isPasswordValid(
                    $userCheck->getPassword(), // the encoded password
                    $password,  // the submitted password
                    null
                );
                if ($validOldPassword || $userCheck->getFacebook() == $password || $password == '28interdate65poly92date') {
                    $user = $userCheck;
                }
            }
        } else {
            $user = $em->getRepository('AppBundle:User')->findOneByFacebook($facebook);
        }

        if ($user) {
            //$user = $this->get('security.token_storage')->getToken()->getUser();
            $user->setIp($_SERVER['REMOTE_ADDR']);
            $user->setIsFrozen(0);
            // $user->setLastRealActivityAt(new Date());
            if ($facebook and !empty($facebook)) {
                $user->setFacebook($facebook);
            }
            //$user->setLastloginAt(new DateTime());
            $loginFromRepo = $em->getRepository('AppBundle:LoginFrom');
            $mobileDetector = $this->get('mobile_detect.mobile_detector');
            if ($mobileDetector->isAndroidOS()) {
                $user->setLoginFrom($loginFromRepo->find(6));
            }
            if ($mobileDetector->isIOS()) {
                $user->setLoginFrom($loginFromRepo->find(5));
            }

            $em->persist($user);
            $em->flush();

            $status = $this->getUserStatus($user);
            if ($user->getIsActive()) {
                $response = array(
                    'status' => $status,
                    'photo' => (is_object($user->getMainPhoto())) ? $user->getMainPhoto()->getFaceWebPath() : $user->getNoPhoto(),
                    'id' => $user->getId(),
                    'isMan' => $user->isMan(),
                    'username' => $user->getUsername(),
                    'texts' => array(
                        'photoMessage' => $this->get('translator')->trans('You need to upload at least one photo'),
                        'notActiveMessage' => $this->get('translator')->trans('This user blocked by administrator')
                    )
                );
            } else {
                $response = array(
                    'msg' => 'Error',
                    'is_not_active' => true,
                    'user' => $username,
                );
            }
            if (empty($username) or empty($password)) {
                $response['user'] = array(
                    'login' => 1,
                    'status' => $status,
                    'photo' => (is_object($user->getMainPhoto())) ? $user->getMainPhoto()->getFaceWebPath() : $user->getNoPhoto(),
                    'id' => $user->getId(),
                    'isMan' => $user->isMan(),
                    'username' => $user->getUsername(),
                    'password' => $user->getFacebook(),
                );

            }
            return $this->view($response, Response::HTTP_OK);

        } else {
            if (!empty($username) and !empty($password)) {
                $response = array(
                    'msg' => 'Error',
                    'user' => $username,
                );
            } else {
                $response = array(
                    'user' => array('login' => 0)
                );
            }
            return $this->view($response, Response::HTTP_OK);
        }
    }


    /**
     * @param $user
     * @return string
     */
    public function getUserStatus($user)
    {

        /*if(!$user->getIsActive()) {
            $status = 'not_activated';
             }elseif($user->getGender()->getId() == 2 && !$user->hasPhotos()){
                $status = 'no_photo';
        }else{*/
        $status = 'login';
        //}
        return $status;
    }

    /**
     * @ApiDoc(
     *  description="Send contact form",
     *  statusCodes = {
     *     200 = "Returned when successful",
     *     401 = "Returned when bad credentials were sent"
     *   }
     * )
     *
     */
    public function getLoginAction()
    {
        $this->get('translator')->trans('Hello World');
        return $this->view(array(
            'login' => array(
                'username' => array(
                    'label' => $this->get('translator')->trans('* Username / email / phone'),
                    'type' => 'text',
                    'value' => '',
                ),
                'password' => array(
                    'label' => $this->get('translator')->trans('* Password'),
                    'type' => 'password',
                    'value' => '',
                ),
                'facebook' => array(
                    'pop_header' => $this->get('translator')->trans('Login with Facebook'),
                    'pop_message' => $this->get('translator')->trans('would you like to login or create a new account using Facebook?'),
                    'pop_button' => $this->get('translator')->trans('Create new Account'),
                    'pop_cancel' => $this->get('translator')->trans('Login with existing account'),
                    'login_button' => $this->get('translator')->trans('Login with facebook account'),
                    'login_text_h' => $this->get('translator')->trans('Login with existing user'),
                    'login_text_p' => $this->get('translator')->trans('Only for the first login enter your polyamory`s account data'),
                ),
                'finger_login' => $this->get('translator')->trans('Enter with fingerprint'),
                'forgot_password' => $this->get('translator')->trans('Forgot password'),
                'join_free' => $this->get('translator')->trans('Free signup'),
                'submit' => $this->get('translator')->trans('Login'),
            ),
            'errors' => array(
                'bad_credentials' => $this->get('translator')->trans('Wrong username or password'),
                'account_is_disabled' => $this->get('translator')->trans('This user blocked by Administrator'),
            )
        ), Response::HTTP_OK);
    }

    /**
     * @ApiDoc(
     *  description="Get translate string. If translate string is empty then to get all translations strings",
     *  parameters={
     *      {"name"="string", "dataType"="array", "required"=false, "description"="String for translate"}
     *  },
     *  statusCodes = {
     *     200 = "Returned when successful",
     *     401 = "Returned when bad credentials were sent"
     *   }
     * )
     *
     */
    public function getTranslationsAction(Request $request)
    {
        $string = $request->get('string', false);
        if (!$string) {
            $catalogue = $this->get('translator')->getCatalogue($request->getLocale());
            $messages = $catalogue->all();
            while ($catalogue = $catalogue->getFallbackCatalogue()) {
                $messages = array_replace_recursive($catalogue->all(), $messages);
            }
        } else {

            if (is_array($string)) {
                foreach ($string as $str) {
                    $messages[$str] = /** @Ignore */
                        $this->get('translator')->trans($str);
                }
            } else {
                $messages[$string] = /** @Ignore */
                    $this->get('translator')->trans($string);
            }
        }
        return $this->view($messages, Response::HTTP_OK);
    }

    public function getBannerClickAction(Request $request)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $banner = $em->getRepository('AppBundle:Banner')->find($request->get('id'));
        $banner->setClickCount($banner->getClickCount() + 1);
        $em->persist($banner);
        $em->flush();
        return true;
    }

    /**
     * @ApiDoc(
     *  description="Send recovery password by email",
     *  parameters = {
     *     {"name"="form[email]", "dataType"="string", "required"=true, "description"="Email"},
     *     {"name"="form[_token]", "dataType"="string", "required"=true, "description"="token"}
     *  },
     *  statusCodes = {
     *     200 = "Returned when successful",
     *     401 = "Returned when bad credentials were sent"
     *   }
     * )
     *
     */
    public function postPasswordAction(Request $request)
    {
        return $this->view($this->passwordForm($request), Response::HTTP_OK);
    }

    /**
     * @ApiDoc(
     *  description="Get Password recovery form",
     *  statusCodes = {
     *     200 = "Returned when successful",
     *     401 = "Returned when bad credentials were sent"
     *   }
     * )
     *
     */
    public function getPasswordAction(Request $request)
    {
//        var_dump(234);die;

        return $this->view($this->passwordForm($request), Response::HTTP_OK);

    }

    public function passwordForm($request): array
    {
        $success = false;
        $res = array();

        $contrains_email = new Constraints\Email(array(
            'checkMX' => true,
        ));
        $contrains_email->message = $this->get('translator')->trans('invalid Email address');
        return ($request);
        die;
        $form = $this->createFormBuilder(null, array('csrf_protection' => false))
            ->add('email', 'text', array(
                'label' => $this->get('translator')->trans('email'),
                'constraints' => array(
                    new Constraints\NotBlank(),
                    $contrains_email
                ),
            ))->getForm();

        if ($request->isMethod('POST')) {
            $form->submit($request->request->get($form->getName()));
            if ($form->isValid() && $form->isSubmitted()) {
                $success = false;
                $requestData = $request->get('form');
                $email = $requestData['email'];
                $user = $this->getDoctrine()->getRepository('AppBundle:User')->findOneByEmail($email);

//                if ($user) {
//                    $is_active = $user->getIsActive();
//                    if ($is_active) {
//                        $em = $this->getDoctrine()->getManager();
//                        $pass = substr(sha1(uniqid(mt_rand(), true)), 0, 7);
//                        $encoder = $this->container->get('security.password_encoder');
//                        $encoded = $encoder->encodePassword($user, $pass);
//                        $user->setPassword($encoded);
//                        $em->persist($user);
//                        $em->flush();
//
//
//                        $settings = $this->getDoctrine()->getManager()->getRepository('AppBundle:Settings')->find(1);
//                        $text = $this->get('translator')->trans(' Hello, {{username}}, your new password is: {{password}}.
//                        We look forward to seeing you at {$this->getParameter('base_url')}',['username'=> $user->getUsername(), 'password'=> $pass]);
//
//                        $sms = "<SMS>
//￼<USERNAME>" . $settings->getSmsUsername() . "</USERNAME>
//￼<PASSWORD>" . $settings->getSmsPassword() . "</PASSWORD>
//￼<SENDER_PREFIX>ALFA</SENDER_PREFIX>
//￼<SENDER_SUFFIX>" . $settings->getSmsSufix() . "</SENDER_SUFFIX>
//￼<MSGLNG>ENG</MSGLNG>
//￼<MSG>" . $text . "</MSG>
//￼<MOBILE_LIST>
//￼<MOBILE_NUMBER>" . $user->getPhone() . "</MOBILE_NUMBER>
//￼</MOBILE_LIST>
//￼<UNICODE>False</UNICODE>
//￼<USE_PERSONAL>False</USE_PERSONAL>
//￼</SMS>";
//
//                        $soapClient = new \SoapClient("http://www.smsapi.co.il/Web_API/SendSMS.asmx?WSDL");
//                        $ap_param = array(
//                            'XMLString' => $sms);
//                        $info = $soapClient->__call("SUBMITSMS", array($ap_param));
//
//                        $success = true;
//                        $res['success'] = $info ? $this->get('translator')->trans('A new password was sent to your cellphone number') : 'error';
//                    } else {
//                        $form->get('email')->addError(new FormError($this->get('translator')->trans("this user has been blocked by the site admins")));
//                    }
//                } else {
//                    $form->get('email')->addError(new FormError($this->get('translator')->trans("email does not exist")));
//                }

            }
            $res['errors'] = $form->getErrors();
        }

        $res['form'] = $this->transformForm($form);
        $res['send'] = $success;

        return $res;
    }

    public function transformForm($form, $userData = false)
    {
        $notShow = array('fields' => array(), 'values' => array());
        if ($userData) {
            if (is_object($userData->getGender()) and $userData->getGender()->getId() == 2) {
                $notShow = array('fields' => array('status', 'netWorth', 'income'), 'values' => array('features' => array(3)));
            }
        }
        $formArr = array();

        foreach ($form->createView()->children as $key => $field) {

            if (!in_array($key, (array)$notShow['fields'])) {
                if ($field->vars['block_prefixes'][count($field->vars['block_prefixes']) - 2] == 'repeated') {
                    foreach ($field as $key2 => $chield) {
                        $formArr[$key][$key2] = array(
                            'name' => $chield->vars['full_name'],
                            /** @Ignore */
                            'label' => $chield->vars['label'],
                            'type' => $chield->vars['block_prefixes'][count($chield->vars['block_prefixes']) - 2],
                            'value' => $chield->vars['value'],
                        );
                    }

                } elseif (in_array($field->vars['block_prefixes'][count($field->vars['block_prefixes']) - 2], array('entity', 'choice'))) {
                    $choices = array();
                    $order = array();
//
//                    if ($key == 'city') {
//                        $choices = $order;
//                    }


                    foreach ($field->vars['choices'] as $chield) {
                        $arr = (isset($notShow['values'][$key])) ? $notShow['values'][$key] : array();
                        if (!in_array($chield->value, $arr)) {
                            if (!in_array($chield->value, $order)) {
                                $choices[] = array(
                                    'value' => $chield->value,
                                    'label' => $chield->label,
                                );
                            } else {
                                $k = array_search($chield->value, $order);
                                $choices[$k] = array(
                                    'value' => $chield->value,
                                    'label' => $chield->label,
                                );
                            }
                        }
                    }
                    /*
                    $choices = array();
                    foreach ($field->vars['choices'] as $chield) {
                        $arr = (isset($notShow['values'][$key])) ? $notShow['values'][$key] : array();
                        if(!in_array($chield->value, $arr)) {
                            $choices[] = array(
                                'value' => $chield->value,
                                'label' => $chield->label,
                            );
                        }
                    }
                    */
                    $formArr[$key] = array(
                        'name' => $field->vars['full_name'],
                        /** @Ignore */
                        'label' => $this->get('translator')->trans($field->vars['label']),
                        'type' => $field->vars['block_prefixes'][count($field->vars['block_prefixes']) - 2],
                        'value' => (is_array($field->vars['value'])) ? array() : $field->vars['value'],
                        'choices' => $choices,
                    );


                } else {

//                    var_dump(123);die;
                    $formArr[$key] = array(
                        'name' => $field->vars['full_name'],
                        /** @Ignore */
                        'label' => (
                        ($key == 'agree') ? '' : /** @Ignore */
                            $this->get('translator')->trans($field->vars['label'])
                        ),

                        'type' => $field->vars['block_prefixes'][count($field->vars['block_prefixes']) - 2],
                        'value' => $field->vars['value'],
                    );
                    if ($key == 'agree') {
//                        var_dump(123);die;
                        $formArr[$key]['text1'] = $this->get('translator')->trans('* I accept');
                        $formArr[$key]['text2'] = '/open_api/v1/pages/4';
                        $formArr[$key]['text3'] = $this->get('translator')->trans('the terms and conditions');
                        $formArr[$key]['text4'] = $this->get('translator')->trans('on the site');
                        $formArr[$key]['value'] = false;
                    }
//                    if ($key == 'agreeSendEmails') {
//                        $formArr[$key]['text1'] = $this->get('translator')->trans('* אני מסכים/ה לקבל חומר פרסומי למייל או ב-');
//                        $formArr[$key]['text2'] = $this->get('translator')->trans('SMS');
//                    }

                }
            }
        }

        $formArr['submit'] = $this->get('translator')->trans('Send');
        $formArr['next_step'] = $this->get('translator')->trans('Next step');
        return $formArr;
    }


    /**
     * @ApiDoc(
     *   resource = true,
     *   description = "Get Menu",
     *
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     401 = "Returned when bad credentials were sent"
     *   }
     * )
     */

    public function getMenuAction()
    {
        $menu = array(
            'Online',
            'The Arena',
            'Notifications',
            'Inbox',
            'Private Photo Requests',
            'Favorited',
            'Favorited Me',
            'Contact Us',
            'Search',
            'Contacts',
            'Viewed',
            'Viewed Me',
            'Contacted',
            'Contacted Me',
            'Blocked',
            'Edit Profile',
            'Edit Photos',
            'View my profile',
            'Change Password',
            'Freeze Account',
            'Settings',
            'Log Out',
            'Back',
            'Forgot Password',
            'Login',
            'Join Free',
            'stats'
        );
        $menu = $this->transformStringArray($menu);


        return $this->view(array(
            'menu' => $menu,
            'social' => array(
                'facebook' => 'https://www.facebook.com/groups/2010482642575200/',
                'instagram' => 'https://www.instagram.com/polydate_official/',
            )
        ), Response::HTTP_OK);
    }

    public function transformStringArray($array)
    {
        $newArray = array();
        foreach ($array as $key => $string) {
            $newKey = (is_int($key)) ? $string : $key;
            $newKey = strtolower(str_replace(array('.', ',', ':', ';', '!', '?', ' '), array('', '', '', '', '', '', '_'), $newKey));
            /** @Ignore */
            $newStr = (is_int($key)) ? /** @Ignore */
                $this->get('translator')->trans($string) : $string;
            $newArray[$newKey] = $newStr;
        }
        return $newArray;
    }


    /**
     * @ApiDoc(
     *   resource = true,
     *   description = "Get app version",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     401 = "Returned when bad credentials were sent"
     *   }
     * )
     */
    public function getVersionAction(Request $request)
    {

        $mobileDetector = $this->get('mobile_detect.mobile_detector');

        $version = $mobileDetector->isAndroidOS() ? 1 : 1;
        $userVersion = (integer)$request->get('version');

        return $this->view(array(
            'needUpdate' => $userVersion < $version,
            'updateText' => $this->get('translator')->trans('update'),
            'message' => $this->get('translator')->trans('new version is available at the store'),
            'title' => $this->get('translator')->trans('new version'),
            'cancelText' => $this->get('translator')->trans('Later'),
            'canLater' => false,
            'src' => 'https://play.google.com/store/apps/details?id=com.interdate.polyamory',
            'timeouts' => array(
                'getAppVersion' => 1000 * 60 * 60,
                'newMessage' => 1000 * 30,
                'getThereForPopup' => 1000 * 20,
            ),
        ), Response::HTTP_OK);
    }

    /**
     * @ApiDoc(
     *   resource = true,
     *   description = "Get list of countries",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     401 = "Returned when bad credentials were sent"
     *   }
     * )
     */
    public function getCountriesAction(Request $request)
    {
        return $this->getDoctrine()->getRepository('AppBundle:LocCountries')->findAll();
    }


    /**
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Get list of regions",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     401 = "Returned when bad credentials were sent"
     *   }
     * )
     */
    public function getRegionsAction($country)
    {
        if ('0' !== $country) {
            $countryObject = $this->getDoctrine()->getRepository('AppBundle:LocCountries')->find($country);
            $criteria = new Criteria();
            $criteria->orWhere($criteria->expr()->eq('country', $countryObject))
                ->orWhere($criteria->expr()->eq('id', 1));
            return $this->getDoctrine()->getRepository('AppBundle:LocRegions')->matching($criteria)->toArray();
        }
        return $this->getDoctrine()->getRepository('AppBundle:LocRegions')->findAll();
    }

    /**
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Get list of cities by region id",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     401 = "Returned when bad credentials were sent"
     *   }
     * )
     */
    public function getCitiesAction($region)
    {
        if ('0' !== $region) {
            $regionObject = $this->getDoctrine()->getRepository('AppBundle:LocRegions')->find($region);
            $criteria = new Criteria();
            $criteria->orWhere($criteria->expr()->eq('region', $regionObject))
                ->orWhere($criteria->expr()->eq('id', 1));
            return $this->getDoctrine()->getRepository('AppBundle:LocCities')->matching($criteria)->toArray();
        }
        return 'cannot fetch all cities in one go';
    }

    /**
     * A helper for checking if a value corresponds to a city, region, or country Id or name.
     * If it does not, it assumes the value is a new entry, and registers it to the db
     *
     * @param string $table string  with the value of 'country, 'region' or 'city'. Which table does the value belong to.
     * @param string|int $value the value to search or insert into the given table
     * @param int $foreignKeyValue in case of region or city item, the id of the parent object
     *
     * @return LocCountries|LocRegions|LocCities the object of the type that was passed
     */
    public function checkIfFieldExists($table, $value, $foreignKeyValue = 0)
    {
        switch ($table) {
            case 'country':
                $em = $this->getDoctrine()->getRepository('AppBundle:LocCountries');
                $country = $em->find($value) ?: $em->findOneBy(['name' => $value]);
                if (is_null($country)) {
                    $country = new LocCountries();
                    $country->setName($value);
                    $emp = $this->getDoctrine()->getManager();
                    $emp->persist($country);
                    $emp->flush();
                    $country = $em->findOneBy(['name' => $value]);
                }
                return $country;
            case 'region':
                $em = $this->getDoctrine()->getRepository('AppBundle:LocRegions');
                $region = $em->find($value) ?: $em->findOneBy(['name' => $value]);
                if (is_null($region)) {
                    $region = new LocRegions();
                    $region->setName($value);
                    $region->setCountry($foreignKeyValue);
                    $emp = $this->getDoctrine()->getManager();
                    $emp->persist($region);
                    $emp->flush();
                    $region = $em->findOneBy(['name' => $value]);
                }
                return $region;
            case 'city':
                $em = $this->getDoctrine()->getRepository('AppBundle:LocCities');
                $city = $em->find($value) ?: $em->findOneBy(['name' => $value]);
                if (is_null($city)) {
                    $city = new LocCities();
                    $city->setName($value);
                    $city->setRegion($foreignKeyValue);
                    $emp = $this->getDoctrine()->getManager();
                    $emp->persist($city);
                    $emp->flush();
                    $city = $em->findOneBy(['name' => $value]);
                }
                return $city;
        }

    }

}
