<?php

namespace AppBundle\Controller\Frontend;

namespace AppBundle\Controller\Frontend;


use Symfony\Component\HttpFoundation\Response;
use AppBundle\Entity\Payment;
use AppBundle\Entity\PaymentHistory;
use AppBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Validator\Constraints;
use function PhotoAlbum\array_to_table;

class SecurityController extends Controller
{
    /**
     * @Route("/login_check", name="login_check")
     */
    public function loginCheckAction()
    {
        //var_dump(123);die;
    }

    /**
     * @Route("/logout", name="logout")
     */
    public function logoutAction()
    {
        //  return new RedirectResponse('/');
    }

    /**
     * @Route("/password", name="password_recovery")
     */
    public function passwordAction(Request $request)
    {
        $success = false;

        $form = $this->createFormBuilder()
            ->add('email', 'text', array(
                'label' => 'Email:',
                'constraints' => array(
                    new Constraints\NotBlank(),
                    new Constraints\Email(array(
                        'message' => '
                        The email
                        "{{ value }}"
                        is not valid
                        ',
                        'checkMX' => true
                    ))
                )
            ))
            ->getForm();

        if ($request->isMethod('POST')) {
//            $form->submit($request);
            $form->handleRequest($request);
//            var_dump($form->isValid(), $form->isSubmitted());die;
            if ($form->isValid() && $form->isSubmitted()) {
                $success = false;
                $requestData = $request->get('form');
                $email = trim($requestData['email']);

                $user = $this->getDoctrine()->getRepository('AppBundle:User')->findOneByEmail($email);

                if ($user && $user->getIsActive()) {
                    $em = $this->getDoctrine()->getManager();
                    $pass = substr(sha1(uniqid(mt_rand(), true)), 0, 7);
                    $encoder = $this->container->get('security.password_encoder');
                    $encoded = $encoder->encodePassword($user, $pass);
                    $user->setPassword($encoded);
                    $em->persist($user);
                    $em->flush();

                    $settings = $em->getRepository('AppBundle:Settings')->find(1);
                    $text = 'Hello, ';
                    $text .= $user->getUsername();
                    $text .= 'Your new password is: ';
                    $text .= $pass;
                    $text .= '! You can change this password at ' . $this->getParameter('base_url') . '.';
                    //TODO new sms client

                    $sms = "<SMS>
￼<USERNAME>" . $settings->getSmsUsername() . "</USERNAME>
￼<PASSWORD>" . $settings->getSmsPassword() . "</PASSWORD>
￼<SENDER_PREFIX>ALFA</SENDER_PREFIX>
￼<SENDER_SUFFIX>" . $settings->getSmsSufix() . "</SENDER_SUFFIX>
￼<MSGLNG>HEB</MSGLNG>
￼<MSG>" . $text . "</MSG>
￼<MOBILE_LIST>
￼<MOBILE_NUMBER>" . $user->getPhone() . "</MOBILE_NUMBER>
￼</MOBILE_LIST>
￼<UNICODE>False</UNICODE>
￼<USE_PERSONAL>False</USE_PERSONAL>
￼</SMS>";

                    $soapClient = new \SoapClient("http://www.smsapi.co.il/Web_API/SendSMS.asmx?WSDL");
                    $ap_param = array(
                        'XMLString' => $sms);
//                    $info = $soapClient->__call("SUBMITSMS", array($ap_param));
//                    if ($info) {
//                        $success = true;
//                    }

                } else {
                    if ($user && !$user->getIsActive()) {
                        $form->get('email')->addError(new FormError('This user has been blocked by site admins'));
                    } else {
                        $form->get('email')->addError(new FormError('This email is not associated with any account'));
                    }
                }
            }
        }

        return $this->render('frontend/security/password.html.twig', array(
            'form' => $form->createView(),
            'success' => $success,
            'mobile' => $this->detectIsMobile(),
        ));

    }

    /**
     * @Route("/sign_up/photo", name="sign_up_photo")
     */
    public function photoAction()
    {


//        $this->setUpCloudinary();
//        $renderedCloudForm = cl_image_upload_tag('image_id');

        return $this->render('frontend/security/sign_up/index.html.twig', array(
//            'renderedCloudForm' => $renderedCloudForm,
            'mobile' => $this->detectIsMobile(),
        ));
    }

    /**
     * @Route("/sign_up/photos", name="sign_up_photos")
     */
    public function photosAction()
    {
        $this->setUpCloudinary();
        $renderedCloudForm = cl_image_upload_tag('image_id');

        return $this->render('frontend/security/sign_up/index.html.twig', array(
            'renderedCloudForm' => $renderedCloudForm,
            'photos' => $this->getUser()->getPhotos(),
            'mobile' => $this->detectIsMobile(),
        ));
    }


    /**
     * @Route("/sign_up/{facebook_id}", defaults={"facebook_id"=0}, name="sign_up")
     */
    public function indexAction(Request $request, $facebook_id = 0)
    {
        if ($this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirect($this->generateUrl('user_homepage'));
        }

        $userRepo = $this->getDoctrine()->getRepository('AppBundle:User');

        $errors = false;
        $user = new User();
        if ($facebook_id > 0) {
            $user->setFacebook($facebook_id);
        }

        $flow = $this->get('SignUpFlow');

        $flow->bind($user);
        $form = $flow->createForm();

        $agree_error = '';
        $errors_text = [];
        $agree = true;
        if ($request->isMethod('POST')) {

            if ($submitted = $request->request->get('sign_up_one')) {

                $post = $userRepo->removeWordsBlocked($submitted, array('phone', 'email'));
                $request->request->set('sign_up_one', $post);
                if ($submitted['phone'] && !$post['phone']) {
                    $errors = true;
                    $errors_text['phone'] = $this->get('translator')->trans('This number is blocked');
                } elseif (!(strlen($post['phone']) >= 10 && strlen($post['phone']) <= 12)) {
                    $errors = true;
                    $errors_text['phone'] = $this->get('translator')->trans('* Phone number must contain between 10 - 12 numbers');

                } elseif (!is_numeric($post['phone'])) {
                    $errors = true;
                    $errors_text['phone'] = $this->get('translator')->trans('* Phone number must contain only numbers');
                }

                if (!strlen($post['username'])) {
                    $errors_text['username'] = $this->get('translator')->trans('*Please fill in username');
                    $errors = true;
                }

                if (empty($post['gender'])) {
                    $errors_text['gender'] = $this->get('translator')->trans('*Please select your gender');
                    $errors = true;

                }

                if (empty($post['password']['first'])) {
                    $errors_text['password'] = $this->get('translator')->trans('*Please choose a password');
                    $errors = true;
                }

                if (empty($post['password']['second'])) {
                    $errors = true;
                }

                if (empty($post['email'])) { //if email after clean blocked words is empty but true in origin then email is blocked
                    $errors = true;
                    if ($submitted['email']) {
                        $errors_text['email'] = $this->get('translator')->trans('This email is blocked');
                    } else {
                        $errors_text['email'] = $this->get('translator')->trans('*Please fill your email');
                    }
                }
            } elseif ($submitted = $request->request->get('sign_up_two')) {

                $post = $userRepo->removeWordsBlocked($submitted, array('relationshipTypeDetails', 'sexOrientationDetails', 'lookingForDetails'));
                $request->request->set('sign_up_two', $post);
                if (!is_numeric($post['height'])) {
                    $errors = true;
                    $errors_text['height'] = $this->get('translator')->trans('Height must be a number');
                } else if ((int)$post['height'] > 230 || (int)$post['height'] < 40) {
                    $errors = true;
                    $errors_text['height'] = $this->get('translator')->trans('Please choose your real height in cm');
                }

                if (empty($post['birthday']['day']) || empty($post['birthday']['month']) || empty($post['birthday']['year'])) {
                    $errors_text['birthday'] = $this->get('translator')->trans('Please select your birthdate');
                    $errors = true;
                }
                if (empty($post['country'])) {
                    $errors_text['country'] = $this->get('translator')->trans('Please choose your country');
                    $errors = true;
                }
                if (empty($post['region'])) {
                    $errors_text['region'] = $this->get('translator')->trans('Please choose your region');
                    $errors = true;
                }
                if (empty($post['city'])) {
                    $errors_text['city'] = $this->get('translator')->trans('Please choose your city');
                    $errors = true;
                }
                if (empty($post['zipCode'])) {
                    $errors_text['zipcode'] = $this->get('translator')->trans('Please fill in your zipcode');
                    $errors = true;
                }
                if (empty($post['relationshipStatus'])) {
                    $errors_text['relationshipStatus'] = $this->get('translator')->trans('Please select personal status');
                    $errors = true;
                }
                if (empty($post['relationshipType'])) {
                    $errors_text['relationshipType'] = $this->get('translator')->trans('Please choose relationship type');
                    $errors = true;
                }
                if (empty($post['sexOrientation'])) {
                    $errors_text['sexOrientation'] = $this->get('translator')->trans('Please select your sexual orientation');
                    $errors = true;
                }
                if (empty($post['smoking'])) {
                    $errors_text['smoking'] = $this->get('translator')->trans('Do you smoke?');
                    $errors = true;
                }
            }
        } elseif ($submitted = $request->request->get('sign_up_three')) {

            $post = $userRepo->removeWordsBlocked($submitted, array('about', 'looking'));
            $request->request->set('sign_up_three', $post);

            if (mb_strlen(trim($post['about'])) <= 0) {
                $errors = true;
                $errors_text['about'] = $this->get('translator')->trans('Please write a bit more about yourself');
            }

            if (mb_strlen(trim($post['looking'])) <= 0) {
                $errors = true;
                $errors_text['looking'] = $this->get('translator')->trans('Tell us a bit more about what you are looking for.');
            }

            if (!isset($submitted['agree']) || $submitted['agree'] != '1') {
                $agree = false;
                $agree_error = $this->get('translator')->trans('Agree to Terms and Conditions to continue.');
            }
        }

        $form->handleRequest($request);
        $formErrors = $form->getErrors(true);
        foreach ($formErrors as $formError) {
            $errors = true;
//           $errors_text[] = $formError->getMessage();
        }
        $flow->saveCurrentStepData($form);

        if (!$errors && $agree && $form->isValid($form)) {
            $flow->saveCurrentStepData($form);
            if ($flow->nextStep()) {
                $form = $flow->createForm();
            } else {

                $text = '
                <div dir="ltr">Hello ' . $user->getUsername() . ",<br />
 Thank you for joining {$this->getParameter('site_name')}<br>
                    <br>
Here are your login details <br/>
username: '" . $user->getUsername() . "'<br/>
password: ' . $user->getPassword() . '<br/><br/>
                    Please feel free to drop us a line at {$this->getParameter('contact_email')}
                    <br><br><br>
                    Good luck,
                    <br>
Team {$this->getParameter('site_name')}

                    <br>
                    {$this->getParameter('base_url')}
                </div>";

                $rolesRepo = $this->getDoctrine()->getRepository('AppBundle:Role');
                $role = $rolesRepo->find(2);
                $user->setRole($role);
                $user->setIsActivated(0);
                $user->setZodiac($this->getDoctrine()->getManager()->getRepository('AppBundle:Zodiac')->getZodiacByDate($user->getBirthday()));
                $user->setIsUpdatedZodiac(true);

                $encoder = $this->container->get('security.password_encoder');
                $encoded = $encoder->encodePassword($user, $user->getPassword());
                $user->setPassword($encoded);
                $user->setSignUpDate(new \DateTime());
                $user->setLastActivityAt(new \DateTime());
                $user->setLastloginAt(new \DateTime());
                $em = $this->getDoctrine()->getManager();
                $em->persist($user);
                $em->flush();

                $subject = "Welcome to {$this->getParameter('base_url')}!";
                $headers = "MIME-Version: 1.0" . "\r\n";
                $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
                $headers .= "From:  {$this->getParameter('contact_email')}  <{$this->getParameter('contact_email')}>" . "\r\n";
                mail($user->getEmail(), $subject, $text, $headers);
                $session = new Session();
                $session->set('userId', $user->getId());

                $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
                $this->get('security.token_storage')->setToken($token);
                return $this->redirect($this->generateUrl('sign_up_photo'));
            }
        } elseif (!$request->request->get('flow_signUp_transition')) {

            if ($new_form = $request->request->get('sign_up_one', false)) {
                $passwords = $new_form['password'];
            }
        }

        $removeFields = array('relationshipTypeDetails', 'sexOrientationDetails', 'lookingForDetails');
        foreach ($removeFields as $field) {
            if ($form->has($field)) {
                $form->remove($field);
            }
        }

        return $this->render('frontend/security/sign_up/index.html.twig', array(
            'form' => $form->createView(),
            'flow' => $flow,
            'errors' => $errors,
            'errors_text' => $errors_text,
            'agree_error' => $agree_error,
            'passwords' => $passwords ?? ['first' => '', 'second' => ''],
            'mobile' => $this->detectIsMobile(),
        ));

    }


    /**
     * @Route("/sign_up/subscription", name="sign_up_subscription")
     * @Route("/user/subscription", name="subscription")
     */
    public function subscriptionAction(Request $request)
    {

        $route = $request->attributes->get('_route');

        if ($this->getUser()->isPaying()) {
            return $this->redirectToRoute('user_homepage');
        }

        $em = $this->getDoctrine()->getManager();

        $banners = $this->get('banners')->getBanners('subscriptionPage');
        $textBefore = $em->getRepository('AppBundle:TextBeforePayment')->findBy(array('isActive' => true), array('order' => 'asc'));
        $paymentSubscriptions = $em->getRepository('AppBundle:PaymentSubscription')->findBy(array('isActive' => true), array('order' => 'asc'));
        $tableTexts = $em->getRepository('AppBundle:TableTextPayment')->findBy(array('isActive' => true), array('order' => 'asc'));
        $textAfter = $em->getRepository('AppBundle:TextAfterPayment')->findBy(array('isActive' => true), array('order' => 'asc'));

        if ($request->query->get('coupon')) {
            $coupon = $em->getRepository('AppBundle:Coupon')->findOneBy(array('code' => $request->query->get('coupon'), 'isActive' => true));


            if ($coupon) {
                if ($coupon->getType() == 'nominal') {
                    for ($i = 0; $i < count($paymentSubscriptions); $i++) {
                        if ($i >= 1) {
                            $paymentSubscriptions[$i]->setPrice(round(($paymentSubscriptions[$i]->getAmount() - $coupon->getValue()) / $paymentSubscriptions[$i]->getPeriod()));
                        } else {
                            $paymentSubscriptions[$i]->setPrice($paymentSubscriptions[$i]->getAmount() - $coupon->getValue());
                        }

                        $amountWithDiscount = $paymentSubscriptions[$i]->getAmount() - $coupon->getValue();

                        $paymentSubscriptions[$i]->setText(str_replace($paymentSubscriptions[$i]->getAmount(), $amountWithDiscount, $paymentSubscriptions[$i]->getText()));

                        $paymentSubscriptions[$i]->setAmount($amountWithDiscount);

                    }

                } else if ($coupon->getType() == 'percentage') {

                    for ($i = 0; $i < count($paymentSubscriptions); $i++) {
                        $amountWithDiscount = round($paymentSubscriptions[$i]->getAmount() * $coupon->getValue() / 100);
                        if ($paymentSubscriptions[$i]->getPeriod() != '-1') {
                            $paymentSubscriptions[$i]->setPrice(round(($paymentSubscriptions[$i]->getAmount() - $amountWithDiscount) / $paymentSubscriptions[$i]->getPeriod()));
                        } else {
                            $paymentSubscriptions[$i]->setPrice(round($paymentSubscriptions[$i]->getAmount() - $amountWithDiscount));
                        }

                        $paymentSubscriptions[$i]->setText(str_replace($paymentSubscriptions[$i]->getAmount(), round($paymentSubscriptions[$i]->getAmount() - $amountWithDiscount), $paymentSubscriptions[$i]->getText()));

                        $paymentSubscriptions[$i]->setAmount(round($paymentSubscriptions[$i]->getAmount() - $amountWithDiscount));

                    }
                }

            }


        }

        //return $this->render('frontend/' . (($route == 'subscription') ? 'user' : 'security/sign_up') . '/subscription.html.twig',  array(
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
     * @Route("/payment/subscribe", name="payment_subscribe")
     */
    public function paymentSubscribeAction(Request $request)
    {
        //https://www.greendate.co.il/payment/subscribe?formId=5265d2d0-cdc7-e911-b80c-ecebb8951f7e&userId=111&product=64&payPeriod=-1&prc=NjQ%3D&amount=64&orderId=5caa8075-582c-42b1-8ee5-64414cf4a095&last4Digits=2888&recordId=F07552DF-C6DE-E911-B80C-ECEBB8951F7E&payments=0&tempRef=82525109&cardHolderId=032254757&resultRecord=0000XXXXXXXXXXXXXXX288804000511210000006400++++++++0000000040210150+52713433100000000000000000024000314++אמריקן+אקספרס0+++++++++++++++++++&firstName=דניאל+ברוניצקי&phone=052-5567959
        $params = $request->query->all();
        if (!isset($params['recordId']) or !isset($params['userId'])) {
            $params = $request->request->all();
        }
        $appLink = '';
        $device = $this->get('mobile_detect.mobile_detector');
        $message = 'תשלום לא עובר.';
        $user = null;
        $error = 1;
        if (isset($params['recordId']) and (int)$params['userId'] > 0) {
            $userRepo = $this->getDoctrine()->getRepository('AppBundle:User');

//            $period = $params['payPeriod'];
            $real_price = $this->getRealAmount($params['payPeriod'], $request->query->get('coupon', ''));

            $em = $this->getDoctrine()->getManager();
            $user = $userRepo->find((int)$params['userId']);
            if ($user) {
                //save telepay data
                $payment = $em->getRepository('AppBundle:Payment')->findOneBy(array('transactionId' => trim($params['recordId']), 'user' => $user));
                if (!$payment) {
                    $params['ip'] = $_SERVER['REMOTE_ADDR'];
                    $payment = new Payment();
                    $payment->setUser($user);
                    $payment->setTransactionId(trim($params['recordId']));
                    $payPeriod = $params['payPeriod'];
                    $params['amount'] = $real_price;
                    $amount = (int)$real_price . ".00";
                    $payment->setAmount($amount);
                    $payment->setName(urldecode($params['firstName']));
                    $payment->setFullData($params);

                    if ($payPeriod == '-1') {
                        $period = '2 week';
                    } else {
                        $period = (int)$payPeriod . 'month' . (((int)$payPeriod == 1) ? '' : 's');
                    }
                    $payment->setPayPeriod($period);
                    $payment->setPhone($params['phone']);
                    $date = new \DateTime();
                    if ($params['payPeriod'] == '-1') {
                        $strPer = 'P14D';
                    } elseif ($params['payPeriod'] == '12') {
                        $strPer = 'P1Y';
                    } else {
                        $strPer = 'P' . (int)$params['payPeriod'] . 'M';
                    }
                    $payment->setNextPaymentDate($date->add(new \DateInterval($strPer)));
                    $em->persist($payment);
                    $em->flush();
                }
                $error = $this->telepayCharge($payment);
                $user = $userRepo->find((int)$params['userId']);
                if ($error == 0) {
                    $message = 'תשלום עבר בהצלחה.';
                } else {
                    $message = 'תשלום לא עובר.';
                }

            }
        }
        if (isset($params['app'])) {
            $appLink = "{$this->getParameter('site_name')}://";
        }
        return $this->render('frontend/message.html.twig', array(
            //'error' => $error,
            'mobile' => $this->detectIsMobile(),
            'title' => 'רכישת מנוי',
            'message' => $message,
            'link' => $appLink,
            'user' => $user,
            'error_pay' => $error,
        ));
    }


    public function telepayCharge($payment)
    {
        $em = $this->getDoctrine()->getManager();
        $params = $payment->getFullData();
        $user = $payment->getUser();

        $userXML = "<?xml version='1.0' encoding='utf-8' ?>" .
            "<fields>" .
            "<field>" .
            "<fieldName>userId</fieldName>" .
            "<fieldValue>" . $user->getId() . "</fieldValue>" .
            "</field>" .
            "<field>" .
            "<fieldName>firstName</fieldName>" .
            "<fieldValue>" . $params['firstName'] . "</fieldValue>" .
            "</field>" .
            "</fields>";

        if ($params['payPeriod'] == '-1') {
            $prodDesc = 'מנוי דו שבועי';
            $prodCode = 'PD0014D00';
        } elseif ($params['payPeriod'] == '12') {
            $prodDesc = 'מנוי שנתי';
            $prodCode = 'PD001Y000';
        } elseif ($params['payPeriod'] == '6') {
            $prodDesc = 'מנוי חצי שנתי';
            $prodCode = 'PD006M000';
        } elseif ($params['payPeriod'] == '3') {
            $prodDesc = 'מנוי תלת חודשי';
            $prodCode = 'PD003M000';
        } elseif ($params['payPeriod'] == '1') {
            $prodDesc = 'מנוי חודשי';
            $prodCode = 'PD001M000';
        }
        $amount = (int)$payment->getAmount();
        if ($amount == 1) {
            if (isset($params['amount'])) {
                $amount = $params['amount'];
            } else {  //TODO use functions for recalculate as in function in 1 top function
                $amount = (!in_array($params['payPeriod'], array('1', '-1'))) ? ((int)$params['payPeriod'] * (int)$params['product']) : (int)$params['product'];
            }
        }
        $vat = number_format(($amount / 117) * 17, 2, '.', '');

        $invoceXML = "<?xml version='1.0' encoding='utf-8' ?>" .
            "<invoice>" .
            "<topLogoUrl>https://{$this->getParameter('base_url')}/images/logo.png</topLogoUrl>" .
            "<supplierNumber>{$this->getParameter('invoice.supplier_number')}</supplierNumber>" .
            "<supplierName>{$this->getParameter('invoice.supplier_name')}</supplierName>" .
            "<supplierAddress>{$this->getParameter('invoice.supplier_Address')}</supplierAddress>" .
            "<supplierCity>{$this->getParameter('invoice.supplier_city')}</supplierCity>" .
            "<supplierPhone>{$this->getParameter('invoice.supplier_phone')}</supplierPhone>" .
            "<supplierEmail>{$this->getParameter('invoice.supplier_email')}</supplierEmail>" .
            "<to>" .
            "<companyName></companyName>" .
            "<contactPerson>" . $params['firstName'] . "</contactPerson>" .
            "<address></address>" .
            "<city></city>" .
            "<zipCode></zipCode>" .
            "<country></country>" .
            "<phone>" . $payment->getPhone() . "</phone>" .
            "<email> </email>" .
            "</to>" .
            "<invoiceLines>" .
            "<line>" .
            "<productCode>" . $prodCode . "</productCode>" .
            "<description>" . $prodDesc . "</description>" .
            "<quantity>1.0</quantity>" .
            "<price>" . ($amount - $vat) . "</price>" .
            "<total>" . ($amount - $vat) . "</total>" .
            "</line>" .
            "</invoiceLines>" .
            "<total>" . ($amount - $vat) . "</total>" .
            "<discount></discount>" .
            "<discountDescription></discountDescription>" .
            "<totalAfterDiscount></totalAfterDiscount >" .
            "<vat>" . $vat . "</vat>" .
            "<grandTotal>" . $amount . "</grandTotal>" .
            "<notes></notes>" .
            "<activeManager></activeManager>" .
            "<bottomLogoUrl>http://{$this->getParameter('company_site')}/wp-content/uploads/2018/02/cropped-logo.jpg</bottomLogoUrl>" .
            "</invoice>";
        $soapClient = new \SoapClient("https://secure.telepay.co.il/WS2/TokensWS.asmx?WSDL");
        $ap_param = array(
            'recordId' => $payment->getTransactionId(),
            'terminalName' => $this->getParameter('telepay_terminal'),
            'clientIp' => $params['ip'],
            'amount' => $payment->getAmount(),
            'creditTerms' => 1,
            'transactionDate_yyyyMMdd' => '',
            'transactionTime_HHmm' => '',
            'uniqueTransactionNumber_SixDigits' => '',
            'authNum' => '',
            'uniqNum' => '',
            'paramJ' => '',
            'userFieldsXML' => $userXML,
            'invoiceXML' => $invoceXML
        );

        // Call RemoteFunction ()
        $error = 0;
        $errorObject = $info = array();
        try {
            $info = $soapClient->__call("ChargeTokenBasic", array($ap_param));
        } catch (SoapFault $fault) {
            $error = 1;
            $errorObject = (array)$fault;
            //error
//                    print("
//                        alert('Sorry, blah returned the following ERROR: ".$fault->faultcode."-".$fault->faultstring.". We will now take you back to our home page.');
//                    ");
        }

        if ($info->ChargeTokenBasicResult != 0) {
            $error = 1;
            $errorObject = $info;
        }

        $date = new \DateTime();
        $endDate = new \DateTime();
        if ($params['payPeriod'] == '-1') {
            $strPer = 'P14D';
        } elseif ($params['payPeriod'] == '12') {
            $strPer = 'P1Y';
        } else {
            $strPer = 'P' . (int)$params['payPeriod'] . 'M';
        }
        $endDate->add(new \DateInterval($strPer));

        unset($soapClient);
        $payHistory = new PaymentHistory();
        $payHistory->setPaymentDate($date);
        $payHistory->setEndPaymentDate($endDate);
        $payHistory->setPayment($payment);
        $fullData = ($error == 0) ? (array)$info : $errorObject;
        $payHistory->setFullData($fullData);
        $em->persist($payHistory);
        $em->flush();
        $newEndDate = new \DateTime();
        $nextPayDate = ($error == 0) ? $endDate : $newEndDate->add(new \DateInterval('P1D'));
        $payment->setNextPaymentDate($nextPayDate);
        if ($error == 1) {
            $payment->setError();
        }
        //$payment->addPaymentHistory($payHistory);
        $em->persist($payment);
        $em->flush();
        //$user->addPayment($payment);
        if ($error == 0) {
            $user->setStartSubscription($date);
            $user->setEndSubscription($endDate);
            $em->persist($user);
            $em->flush();
        }
        return $error;
    }


    /**
     * @Route("/payment/auto/renewable", name="payment_auto_renewable", defaults={"payment_id" = null})
     * @Route("/payment/renewable/{payment_id}", name="payment_auto_renewable_id", defaults={"payment_id" = null})
     */
    public function paymentAutoRenewableAction(Request $request, $payment_id)
    {
        $em = $this->getDoctrine()->getManager();
        $paymentRepo = $em->getRepository('AppBundle:Payment');
        if ($payment_id !== null and (int)$payment_id > 0) {
            $payment_id = (int)$payment_id;
            $pay = $paymentRepo->find($payment_id);
            if ($pay) {
                $this->telepayCharge($pay);
            }
        } else {
            $date = new \DateTime();
            $from = new \DateTime($date->format("Y-m-d") . " 00:00:00");
            $to = new \DateTime($date->format("Y-m-d") . " 23:59:59");

            $qb = $paymentRepo->createQueryBuilder("p");
            $qb->where('p.isActive = 1')
                ->andWhere('p.nextPaymentDate BETWEEN :from AND :to')
                ->setParameter('from', $from)
                ->setParameter('to', $to);
            $payments = $qb->getQuery()->getResult();
            foreach ($payments as $payment) {
                $this->telepayCharge($payment);
            }
        }
        return $this->render('frontend/message.html.twig', array(
            'mobile' => $this->detectIsMobile(),
            'title' => '',
            'message' => '',
            'link' => '',
            'user' => null,
            'error_pay' => 0,
        ));

    }


    public function setUpCloudinary()
    {
        \Cloudinary::config(array(
            "cloud_name" => "interdate",
            "api_key" => "771234826869846",
            "api_secret" => "-OWKuCgP1GtTjIgRhwfOUVu1jO8",
        ));
    }

    private function detectIsMobile()
    { // 'mobile' => $this->detectIsMobile(),
        $mobileDetector = $this->get('mobile_detect.mobile_detector');
        return $mobileDetector->isMobile();
    }

    private function getRealAmount($period, $coupon_code)
    {

        $amount = $this->getSubscriptionAmountByPeriod($period);

        $code = $this->getDoctrine()->getRepository('AppBundle:Coupon')->findOneBy([
            'isActive' => true,
            'code' => $coupon_code,
        ]);
        if ($code) {
            if ($code->getType() == 'percentage') {
                return (int)$amount - ((int)$amount / 100 * (int)$code->getValue());
            } else {
                return (int)$amount - (int)$code->getValue();
            }
        } else {
            return $amount;
        }
    }

    private function getSubscriptionAmountByPeriod($period)
    {
        $subs = $this->getDoctrine()->getRepository('AppBundle:PaymentSubscription')->findAll();

        foreach ($subs as $sub) {
            if ($sub->getPeriod() == $period) {
                return $sub->getAmount();
            }
        }
    }
    /*
        public function getCurrentUser()
        {
            if ($this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
                return $this->getUser();
            } else {
                $session = new Session();
                //$session->start();
                $userId = $session->get('userId');
                $usersRepo = $this->getDoctrine()->getRepository('AppBundle:User');
                return $usersRepo->find($userId);
            }

            return null;
        }
    */


}
