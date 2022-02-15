<?php

namespace AppBundle\Controller\Backend;

use AppBundle\Entity\Payment;
use AppBundle\Entity\PaymentHistory;
use AppBundle\Entity\Photo;
use AppBundle\Entity\Report;
use AppBundle\Entity\User;
use AppBundle\Entity\Zodiac;
use AppBundle\Form\Type\AdminAdvancedSearchType;
use AppBundle\Form\Type\AdminPropertiesType;
use AppBundle\Form\Type\ProfileOneAdminType;
use AppBundle\Form\Type\ProfileOneType;
use AppBundle\Form\Type\ProfileThreeType;
use AppBundle\Form\Type\ProfileTwoType;
use AppBundle\Form\Type\ChangePasswordType;
use FOS\RestBundle\Controller\Annotations\Post;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\MimeType\FileinfoMimeTypeGuesser;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class UsersController extends Controller
{
    private $filters = array(
        'total' => array(
            'title' => 'total',
            'icon' => 'users',
        ),

        'active_and_not_frozen' => array(
            'title' => 'active',
            'icon' => 'idea',
        ),

        'male' => array(
            'title' => 'male',
            'icon' => 'male',
        ),

        'female' => array(
            'title' => 'female',
            'icon' => 'female',
        ),

        'MtoF' => array(
            'title' => 'trans woman',
            'icon' => 'm2f'
        ),

        'FtoM' => array(
            'title' => 'trans man',
            'icon' => 'f2m'
        ),

        'aBinary' => array(
            'title' => 'non-binary',
            'icon' => 'non-binary'
        ),

        'with_photos' => array(
            'title' => 'with-images',
            'icon' => 'photo',
        ),

        'frozen' => array(
            'title' => 'frozen',
            'icon' => 'asterisk',
        ),

        'inactive' => array(
            'title' => 'inactive',
            'icon' => 'ban',
        ),

        'flagged' => array(
            'title' => 'flagged',
            'icon' => 'flag',
        ),

        'paying' => array(
            'title' => 'paying',
            'icon' => 'shekel',
        ),

        'search' => array(
            'title' => 'search results',
            'icon' => 'search',
        ),

        'error_paying' => array(
            'title' => 'payment error',
            'icon' => 'credit card red'
        ),

        'active_paying' => array(
            'title' => 'active subscriptions',
            'icon' => 'credit card'
        ),

        'today_paying' => array(
            'title' => 'payments today',
            'icon' => 'money bill alternate outline'
        ),
    );


    /**
     * @Route("/admin/users/photofilter/{page}", defaults={"page" = 1}, name="admin_users_photofilter")
     */
    public function changeBigPhotos($page){
        //if($user) {

            $em = $this->getDoctrine()->getManager();
            $count = 100;
            $offset = ($page - 1) * $count;
            $users = $em->getRepository('AppBundle:User')->findBy(array(),array(),$count,$offset);
            foreach ($users as $user) {
                foreach ($user->getPhotos() as $photo) {
                    // var_dump($photo->getId());die;
                    $optimized = $this->applyFilterToPhoto('optimize_original', $photo->getWebPath(false));
                    $this->savePhoto($optimized, $photo->getAbsolutePath());
                    $photo->setUpdated($photo->getUpdated() + 1);

                    $em->persist($photo);
                    $em->flush();
                }
            }
        //}
        if(count($users) > 0) {
            return $this->redirectToRoute('admin_users_photofilter', array('page' => $page + 1));
        }else {
//            var_dump(123);die;
        }
    }


    private function applyFilterToPhoto($filterName, $webPath)
    {
        $dataManager = $this->container->get('liip_imagine.data.manager');

        try {
            $image = $dataManager->find($filterName, $webPath);
        } catch ( \Exception $e ) {
            // send error message if you can
//            var_dump($filterName, $webPath);
            echo 'Caught exception: ',  $e->getMessage(), "\n";
            return false;
        }

        //var_dump($image); die;

        return $this->container->get('liip_imagine.filter.manager')->applyFilter($image, $filterName)->getContent();
    }


    /**
     * @Route("/admin/users/list/{filter}/{page}", defaults={"page" = 1, "filter" = "total"}, name="admin_users")
     */
    public function indexAction(Request $request, $page, $filter)
    {
        $reportsRepo = $this->getDoctrine()->getManager()->getRepository('AppBundle:Report');

        $quickSearchSidebar = $request->request->get('admin_quick_search_sidebar', null);
        $advancedSearch = $request->request->get('admin_advanced_search', null);
        if (!$advancedSearch) {
            $advancedSearch = $request->request->get('advancedSearch', false);
        }
        $report = null;

        if($filter == 'report'){
            $report = $reportsRepo->find($request->request->get('reportId'));
            $data = json_decode($report->getParams(), true);
        }
        else{
            $data = null !== $quickSearchSidebar
                ? $quickSearchSidebar
                : $advancedSearch
            ;
        }

        $data['filter'] = $filter;
        $usersRepo = $this->getDoctrine()->getManager()->getRepository('AppBundle:User');
        $stat = $usersRepo->getAdminStat();
        $this->bindFiltersWithStat($stat);

        $users = $usersRepo->setAdminMode()->search(
            array(
                'current_user' => $this->getUser(),
                'data' => $data,
                'paginator' => $this->get('knp_paginator'),
                'page' => $page,
                'per_page' => 20,
            )
        );

        $users->setTemplate('backend/pagination.html.twig');

        return $this->render('backend/users/index.html.twig', array(
            'users' => $users,
            'data' => $data,
            'stat' => $stat,
            'reports' => $reportsRepo->findByIsFlagged(true),
            'current_report' => $report,
            'filters' => $this->filters,
            'current_filter_data' => $this->getFilterData($filter, $report),
        ));
    }

    /**
     * @Route("/admin/ajax/users/list", name="admin_helper_users")
     */
    public function helperIndexAction(Request $request)
    {
        $reportsRepo = $this->getDoctrine()->getManager()->getRepository('AppBundle:Report');
        $data = json_decode($request->get('data'),true);
        $usersRepo = $this->getDoctrine()->getManager()->getRepository('AppBundle:User');

        $reports = $reportsRepo->findByIsFlagged(true);
        $reports = $usersRepo->setCountReport($reports, false);
        //var_dump($data);die;
        //$count = 0;
        $count = $usersRepo->setAdminMode()->search(
            array(
                'current_user' => $this->getUser(),
                'data' => $data,
                'getCount' => true
            )
        );
        return new Response(
            json_encode(array(
                'count'=>$count,
                'reports'=>$reports
            )),
            Response::HTTP_OK,
            array('content-type' => 'application/json')
        );
    }

    /**
     * @Route("/admin/users/user/photo/rotate", name="admin_users_user_photo_rotate")
     */
    function photoRotateAction(Request $request){
        $res = '';
        $rotate = (int)$request->get('rotate',0);
//        var_dump($rotate);
        $photo = $this->getDoctrine()->getRepository('AppBundle:Photo')->find($request->get('id',0));
        if($photo and $rotate != 0){
            //var_dump($rotate);
            $res = $photo->rotate($rotate);
//            var_dump((int)$photo->getUpdated());
            $photo->setUpdated($photo->getUpdated() + 1);
            $em = $this->getDoctrine()->getManager();
            $em->persist($photo);
            $em->flush();
        }
//        if($photo){
//            $res = $photo->getWebPath();
//        }
        return new Response(json_encode(array(
            'fullPhoto' => $res->getWebPath(),
            'facePhoto' => $res->getFaceWebPath(),
        )), Response::HTTP_OK, array('content-type' => 'application/json'));
    }

    private function bindFiltersWithStat($stat)
    {
        foreach($stat as $key => $val){
            if(isset($this->filters[$key])) {
                $this->filters[$key]['total_number'] = $val;
            }
        }
    }

    private function getFilterData($filter, $report)
    {
        if(null !== $report){
            return array(
                'name' => 'report',
                'title' => $report->getName(),
                'icon' => 'bar chart',
            );
        }

        $filterData = $this->filters[$filter];
        $filterData['name'] = $filter;
        return $filterData;
    }

    /**
     * @Route("/admin/users/user/verify/remove/{id}", name="admin_users_user_verify_remove")
     */

    function removeVerifyAction(User $user) {
        $em = $this->getDoctrine()->getManager();
        $user->setVerifyCount(0);
        $em->persist($user);
        $em->flush();
        return new JsonResponse();
    }

    /**
     * @Route("/admin/search/advanced", name="admin_search_advanced")
     */
    public function searchAction()
    {
        $form = $this->createForm(AdminAdvancedSearchType::class, new User());
        return $this->render('backend/users/advanced_search.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/admin/user/{id}/{property}/{value}", name="admin_user_active")
     */
    public function setPropertyAction(User $user,$property, $value)
    {
        $em = $this->getDoctrine()->getManager();
        $setter = 'set' . ucfirst($property);
        $user->$setter($value);
        $em->persist($user);
        $em->flush();
        return new Response();
    }

    /**
     * @Route("/admin/user/{id}/delete", name="admin_user_delete")
     */
    public function deleteAction(User $user)
    {

        $em = $this->getDoctrine()->getManager();
        $em->remove($user);
        $em->flush();
        return new Response();
    }

    /**
     * @Route("/admin/users/save/ban/reason", name="admin_users_save_ban_reason")
     */
    public function saveBanReasonAction(Request $request)
    {
        $this->getDoctrine()->getManager()->getRepository('AppBundle:User')->saveBanReason(
            $request->request->get('users'),
            $request->request->get('reason')
        );

        return new Response();
    }

    /**
     * @Route("/admin/users/edit/profile/{id}/{tab}", defaults={"tab" = 1}, name="admin_users_edit_profile")
     */
    public function editProfileAction(Request $request, User $user, $tab){
        $errors = false;
        $edited_form_view = null;

        $form_1 = $this->createForm(ProfileOneAdminType::class, $user);
        $form_2 = $this->createForm(ProfileTwoType::class, $user);
        $form_3 = $this->createForm(ProfileThreeType::class, $user);
        $form_4 = $this->createForm(AdminPropertiesType::class, $user);
        $form_5 = $this->createForm(ChangePasswordType::class, $user);
        $form_5->remove('oldPassword');
        if($request->isMethod('POST')){
			
        	if($tab == 5){
        		$edited_form = $this->createForm($this->getProfileType($tab), $user);
        		$edited_form->remove('oldPassword');
        	}else{
//                var_dump(123);die;
            	$edited_form = $this->createForm($this->getProfileType($tab), $user);
        	}

            $edited_form->handleRequest($request);
        	if ($tab == 4) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($user);
                $em->flush();
            }
            if($edited_form->isSubmitted() && $edited_form->isValid()){
                $zodiacRepo = $this->getDoctrine()->getRepository('AppBundle:Zodiac');
                $user->setZodiac($zodiacRepo->getZodiacByDate($user->getBirthday()));
                if($tab == 5){
                	$encoder = $this->get('security.encoder_factory')->getEncoder($user);
                	$encodedPassword = $encoder->encodePassword($user->getPassword(), null);
                	$user->setPassword($encodedPassword);
                }
                $em = $this->getDoctrine()->getManager();
                $em->persist($user);
                $em->flush();
            }
            else{
                $errors = true;
            }
            $edited_form_view = $edited_form->createView();

        }

        return $this->render('backend/users/edited_profile.html.twig', array(
            'user' => $user,
            'tab' => $tab,
            'edited_form' => $edited_form_view,
            'form_1' => $form_1->createView(),
            'form_2' => $form_2->createView(),
            'form_3' => $form_3->createView(),
            'form_4' => $form_4->createView(),
        	'form_5' => $form_5->createView(),
            'errors' => $errors,
        ));
    }

    /**
     * @Route("/admin/users/view/profile/{id}", name="admin_users_view_profile")
     */
    function viewProfileAction(User $user)
    {
        return $this->render('backend/users/profile.html.twig', array(
            'user' => $user,
        ));
    }

    /**
     * @Route("/admin/users/user/{id}/photos", name="admin_users_user_photos")
     */
    function userPhotosAction(User $user)
    {
//        $this->setUpCloudinary();
//        $renderedCloudForm = cl_image_upload_tag('image_id');

//        $photos = $user->getPhotos();
//         var_dump($photos[0]->getId());die;
//        var_dump($user);die;

        return $this->render('backend/users/photos.html.twig', array(
            'user' => $user,
//            'renderedCloudForm' => $renderedCloudForm,
        ));
    }

    /**
     * @Route("/admin/users/user/{id}/photos/photo/data", name="admin_users_user_photos_photo_data")
     */
    public function photoDataAction(User $user, Request $request)
    {
        $name = $request->request->get('name');
        $isMain = $request->request->get('mainPhotoAlreadyExists') == 1 ? false : true;

        $photo = new Photo();
        $photo->setUser($user);
        $photo->setName($name);
        $photo->setIsValid(true);
        $photo->setIsMain($isMain);

        $em = $this->getDoctrine()->getManager();
        $em->persist($photo);
        $em->flush();

        return new Response();
    }

    /**
     * @Route("/admin/users/user/photos/{id}/{property}/{value}", name="admin_users_user_photos_property")
     */
    public function setPhotoPropertyAction(Photo $photo, $property, $value)
    {
        $em = $this->getDoctrine()->getManager();
        $setter = 'set' . ucfirst($property);

        if ($photo->getIsMain() && $property == 'isPrivate') {
            return array(
                'success' => false,
                'errorMessage' => $this->get('Translator')->trans('תמונה ראשית לא יכולה להיות חסויה'),
            );
        }

        if($property == 'isMain'){
            $photos = $photo->getUser()->getPhotos();

            foreach($photos as $userPhoto){
                if($userPhoto->getIsMain()){
                    $userPhoto->setIsMain(false);
                    $em->persist($userPhoto);
                }
            }
        }

        $photo->$setter($value);
        $em->persist($photo);
        $em->flush();

        return new Response();
    }

    /**
     * @Route("/admin/users/user/photos/{id}/delete", name="admin_users_user_photo_delete")
     */
    public function deletePhotoAction(Photo $photo)
    {
//        $this->setUpCloudinary();
//        \Cloudinary\Uploader::destroy($photo->getName());
//
//        $user = $photo->getUser();
//        $wasMainPhoto = $photo->getIsMain();
//        $em = $this->getDoctrine()->getManager();
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

        $user = $photo->getUser();
        $wasMainPhoto = $photo->getIsMain();
        $em = $this->getDoctrine()->getManager();
        $em->remove($photo);
        $em->flush();

        if($wasMainPhoto){
            $photos = $user->getPhotos();
//            if(isset($photos[0])){
//                echo $photos[0]->getId();
//            }
            if(count($photos) > 0) {
                foreach ($photos as $photo) {
                    if (!$photo->getIsPrivate()) {
                        echo $photo->getId();
                        $wasMainPhoto = false;
                        break;
                    }
                }

                if ($wasMainPhoto) {
                    $photos[0]->setIsPrivate(false);
                    $em->persist($photos[0]);
                    $em->flush();
                    echo $photos[0]->getId();
                }
            }

        }

        return $this->render('frontend/security/empty.html.twig');

    }

    /**
     * @Route("/admin/users/user/history/edit", name="admin_users_user_history_edit")
     */
    function userPaymentHistoryEditAction(Request $request){
        $id = (int)$request->get('id', 0);
        $entity = $request->get('entity', null);
        $field = $request->get('field', null);
        $val = $request->get('value', null);
        $entityStr = ($entity == 'payment') ? 'AppBundle:Payment' : 'AppBundle:PaymentHistory';

        if($id > 0){
            $em = $this->getDoctrine()->getManager();
            $repo = $em->getRepository($entityStr)->find($id);
            //$history = $this->getDoctrine()->getRepository('AppBundle:PaymentHistory')->find($id);
            if($repo){
                if($field == 'note') {
                    $repo->setNote($val);
                }
                if($field == 'nextPaymentDate'){
                    $valArr = explode("-", $val);
                    $val = implode("-", array_reverse($valArr));
                    $repo->setNextPaymentDate(new \DateTime($val));
                }
                if($field == 'isActive' and ($val == '1' or $val == '0')){
                    $repo->setIsActive((boolean)$val);
                }
                $em->persist($repo);
                $em->flush();
            }
        }
        return new Response();
        /*$this->view(array(
            'success' => 1,
        ), Response::HTTP_OK);*/
    }
    
    /**
     * @Route("/admin/users/user/charge/one/time", name="admin_users_user_charge_one_time")
     * @Route("/admin/users/user/charge/repay", name="admin_users_user_charge_one_time")
     */

    function userChargeCustomAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $id = $request->request->get('payment_id', 0);
        $payment = $id > 0 ? $em->getRepository('AppBundle:Payment')->find($id) : null;
        $is_repay = ($request->request->get('summ', NULL) == NULL);
        $data = $payment->getFullData();
        $amount = $request->request->get('summ', (int)$payment->getAmount());
        if($payment and $amount){
            $data['amount'] = $amount;
            $prodDesc = 'חיוב חד פעמי';
            $prodCode = 'PD00C1T00';
            if($is_repay){
                if ($data['payPeriod'] == '-1') {
                    $prodDesc = 'מנוי דו שבועי';
                    $prodCode = 'PD0014D00';
                } elseif ($data['payPeriod'] == '12') {
                    $prodDesc = 'מנוי שנתי';
                    $prodCode = 'PD001Y000';
                } elseif ($data['payPeriod'] == '6') {
                    $prodDesc = 'מנוי חצי שנתי';
                    $prodCode = 'PD006M000';
                } elseif ($data['payPeriod'] == '3') {
                    $prodDesc = 'מנוי תלת חודשי';
                    $prodCode = 'PD003M000';
                } elseif ($data['payPeriod'] == '1') {
                    $prodDesc = 'מנוי חודשי';
                    $prodCode = 'PD001M000';
                }
            }

            $data['ip'] = '82.166.213.13';
            $user = $payment->getUser();

            $userXML = "<?xml version='1.0' encoding='utf-8' ?>" .
                "<fields>" .
                "<field>" .
                "<fieldName>userId</fieldName>" .
                "<fieldValue>" . $user->getId() . "</fieldValue>" .
                "</field>" .
                "<field>" .
                "<fieldName>firstName</fieldName>" .
                "<fieldValue>" . $data['firstName'] == 'Yuval Katz' ? $user->getUsername() : $data['firstName'] . "</fieldValue>" .
                "</field>" .
                "</fields>";

            $vat = number_format(($amount / 117) * 17, 2, '.', '');

            $invoceXML = "<?xml version='1.0' encoding='utf-8' ?>" .
                "<invoice>" .
                "<topLogoUrl>https://www.polydate.co.il/images/logo.png</topLogoUrl>" .
                "<supplierNumber>513453340</supplierNumber>" .
                "<supplierName>אינטרדייט בעיימ</supplierName>" .
                "<supplierAddress>המלאכה 6</supplierAddress>" .
                "<supplierCity>לוד</supplierCity>" .
                "<supplierPhone>08-9214729</supplierPhone>" .
                "<supplierEmail>contact@interdate-ltd.co.il</supplierEmail>" .
                "<to>" .
                "<companyName></companyName>" .
                "<contactPerson>" . $data['firstName'] == 'Yuval Katz' ? $user->getUsername() : $data['firstName'] . "</contactPerson>" .
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
                "<bottomLogoUrl>http://www.interdate-ltd.co.il/wp-content/uploads/2018/02/cropped-logo.jpg</bottomLogoUrl>" .
                "</invoice>";

            $soapClient = new \SoapClient("https://secure.telepay.co.il/WS2/TokensWS.asmx?WSDL");
            $ap_param = array(
                'recordId' => $payment->getTransactionId(),
                'terminalName' => 'interdate1_ben',
                'clientIp' => $data['ip'],
                'amount' => $amount . '.00',
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

            $errorObject = $info = array();
            try {
                $info = $soapClient->__call("ChargeTokenBasic", array($ap_param));
            } catch (SoapFault $fault) {
                $error = 1;
                $errorObject = (array)$fault;

            }
            unset($soapClient);

            $date = new \DateTime();
            $payHistory = new PaymentHistory();
            $payHistory->setPaymentDate($date);
            $payHistory->setEndPaymentDate($date);
            $payHistory->setPayment($payment);
            $fullData = ($error == 0) ? (array)$info : $errorObject;
            $fullData['amount'] = $amount;
            $fullData['prodDesc'] = $prodDesc;
            $fullData['prodCode'] = $prodCode;
            $payHistory->setFullData($fullData);
            $em->persist($payHistory);
            $em->flush();
        }
        return new JsonResponse(array('success'=> ($payment and $amount) ? 1 : 0), 200);
    }

    /**
     * @Route("/admin/users/user/{id}/subscription", name="admin_users_user_subscription")
     */
    function userSubscriptionAction(Request $request, User $user){

        //$interval = new \DateInterval('P1D');
        if(is_object($user->getEndSubscription()) and $user->getEndSubscription()->getTimestamp() > time()){
            $startDateObj = new \DateTime($user->getStartSubscription()->format('d-m-Y'));
            // $startDateObj->add($interval);
            $startDate = $startDateObj->format('d-m-Y');
        }else{
            $startDate = date('d-m-Y');
        }

        $endDate = (is_object($user->getEndSubscription()) and $user->getEndSubscription()->getTimestamp() > time()) ? $user->getEndSubscription()->format('d-m-Y') : date('d-m-Y');

        $form = $this->createFormBuilder()
            ->add('id', 'hidden', array(
                'data' => $user->getId()
            ))
            ->add('points', 'text', array(
                'label' => 'נקודות',
                'data' => $user->getPoints(),
            ))
            ->add('startSubscription', 'text', array(
                'label' => 'תאריך התחלת מנוי',
                'data' => $startDate,
                'required' => false
            ))
            ->add('period', 'choice', array(
                'label' => 'תקופת מנוי',
                'multiple' => false,
                'expanded' => false,
                'choices' => array('0' => 'בחר תקופה','-1' => 'שבועיים', '1' => 'חודש','3' => '3 חודשים', '6' => '6 חודשים', '12' => 'שנה'),
                'data' => '0',
                'required' => false,
            ))
            ->add('endSubscription', 'text', array(
                'label' => 'תאריך סיום מנוי',
                'data' => $endDate,
                'required' => false
            ))
            ->add('transactionId', 'text', array('label' => 'מס\' עסקה','required' => false,))
            ->add('name', 'text', array('label' => 'שם מבצע העסקה','required' => false,))
            ->add('note', 'textarea', array('label' => 'Note','required' => false,))
            ->getForm();
        $form->handleRequest($request);
        //$form = $this->createForm(new SubscriptionType($user, $this->getDoctrine()));
        $save = '0';

        if ($request->isMethod('POST') and $request->get('remove',0) == 0/* and $user->getGender()->getId() == 1 */) {
            if ($form->isSubmitted() && $form->isValid()) {
                // validate subscribe
                $data = $form->getData();
                if($data['startSubscription'] == $data['endSubscription'] and $user->getPoints() == $data['points']){
                    $form->get('endSubscription')->addError(new FormError('Choose Subscription Period'));
                }
                if(!empty($data['transactionId']) or !empty($data['name'])){
                    if(empty($data['transactionId'])){
                        $form->get('transactionId')->addError(new FormError('Input Transaction ID from telepay (recordId)'));
                    }
                    if(empty($data['name'])){
                        $form->get('name')->addError(new FormError('Input Name from telepay'));
                    }
                    if($data['period'] == '0'){
                        $form->get('period')->addError(new FormError('Choose Subscription Period'));
                    }
                }
                if($form->isValid()){
                    $em = $this->getDoctrine()->getManager();
                    $valArr = explode("-", $data['startSubscription']);
                    $data['startSubscription'] = implode("-", array_reverse($valArr));
                    $startDate = new \DateTime($data['startSubscription']);
                    if($user->getStartSubscription() != $startDate){
                        $user->setStartSubscription($startDate);
                    }
                    $valArr = explode("-", $data['endSubscription']);
                    $data['endSubscription'] = implode("-", array_reverse($valArr));
                    $endDate = new \DateTime($data['endSubscription']);
                    if($user->getEndSubscription() != $endDate){
                        $user->setEndSubscription($endDate);
                    }
                    if($user->getPoints() != $data['points']){
                        $user->setPoints($data['points']);
                    }

                    $paymentRepo = $this->getDoctrine()->getRepository('AppBundle:Payment');
                    if(!empty($data['note']) and (empty($data['transactionId']) or empty($data['name']))){
//                        if(empty($data['transactionId'])){
//                            $lastHist = $historyRepo->findOneBy(array(),array('id' => 'desc'));
//                            $data['transactionId'] = 'admin-' . $user->getId() . '-' . $lastHist->getId();
//                        }
//                        if(empty($data['name'])){
//                            $data['name'] = 'admin-' . $user->getId();
//                        }
                    }
                    if(!empty($data['transactionId']) and $paymentRepo->findOneBy(array('transactionId' => $data['transactionId'])) == null){
                        $payment = new Payment();
                        $payment->setUser($user);
                        $payment->setTransactionId(trim($data['transactionId']));
                        $payPeriod = $data['payPeriod'] = $data['period'];

                        if($payPeriod == '1'){
                            $data['amount'] = 95;
                        }elseif ($payPeriod == '3'){
                            $data['amount'] = 199;
                        }elseif ($payPeriod == '6'){
                            $data['amount'] = 360;
                        }elseif ($payPeriod == '12'){
                            $data['amount'] = 660;
                        }else{
                            $data['amount'] = 70;
                        }
                        $data['ip'] = '82.166.213.13';
                        $data['recordId'] = $data['transactionId'];
                        $amount = (int)$data['amount'] . ".00";
                        $payment->setAmount($amount);
                        $payment->setName(urldecode($data['name']));
                        $payment->setFullData($data);

                        if ($payPeriod == '-1') {
                            $period = '2 week';
                        } else {
                            $period = (int)$payPeriod . 'month' . (((int)$payPeriod == 1) ? '' : 's');
                        }
                        $payment->setPayPeriod($period);
                        $em->persist($payment);
                        $em->flush();

                        $userXML = "<?xml version='1.0' encoding='utf-8' ?>" .
                            "<fields>" .
                            "<field>" .
                            "<fieldName>userId</fieldName>" .
                            "<fieldValue>" . $user->getId() . "</fieldValue>"+
                            "</field>" .
                            "<field>" .
                            "<fieldName>firstName</fieldName>" .
                            "<fieldValue>" . $data['name'] . "</fieldValue>" .
                            "</field>" .
                            "</fields>";
                        $soapClient = new SoapClient("https://secure.telepay.co.il/WS2/TokensWS.asmx?WSDL");
                        $ap_param = array(
                            'recordId'     => $data['recordId'],
                            'terminalName' => 'interdate1_term',
                            'clientIp' => $data['ip'],
                            'amount' => $amount,
                            'creditTerms' => 1,
                            'userFieldsXML' => $userXML
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
                        $date = new \DateTime();
                        $endDate = $date;
                        if($data['payPeriod'] == '-1') {
                            $strPer = 'P14D';
                        }elseif($data['payPeriod'] == '12'){
                            $strPer = 'P1Y';
                        }else{
                            $strPer = 'P' . (int)$data['payPeriod'] . 'M';
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
                        $payment->addPaymentHistory($payHistory);

                        if ($error == 1) {
                            $payment->setError();
                        }
                        $em->persist($payment);
                        $em->flush();
                        $user->addPayment($payment);
                        if ($error == 0) {
                            $user->setStartSubscription($date);
                            $user->setEndSubscription($endDate);
                        }
                    }
                    $em->persist($user);
                    $em->flush();
                    $save = '1';
                }
            }
            //}elseif($request->isMethod('POST') and $user->getGender()->getId() == 2){
            //  $save = '3';
            //$lastHist = $this->getDoctrine()->getRepository('AppBundle:PaymentHistory')->findOneBy(array(),array('id' => 'desc'));
            //var_dump($lastHist->getId());
        }
        $paymentId = $request->get('payment',0);
        if($request->get('remove',0) == 2 and $paymentId > 0){
            $em = $this->getDoctrine()->getManager();
            $payment = $em->getRepository('AppBundle:Payment')->find($paymentId);
            if($payment->getUser()->getId() == $user->getId()) {
                $payment->setIsActive((boolean)$request->get('val'));
            }
            //var_dump($paymentId, $payment->getId());die;
            $em->persist($payment);
            $em->flush();
            $save = '3';
        }
        if($request->get('remove',0) == 1){
            $em = $this->getDoctrine()->getManager();
            $endDate = new \DateTime(date('Y-m-d H:i:s'));
            $endDate->sub(new \DateInterval('P1D'));
            $user->setEndSubscription($endDate);
            $em->persist($user);
            $em->flush();
            $save = '2';
            $form->remove('endSubscription')
                ->remove('startSubscription')
                ->add('endSubscription', 'text', array(
                    'label' => 'תאריך סיום מנוי',
                    'data' => date('d-m-Y'),
                    'required' => false
                ))->add('startSubscription', 'text', array(
                    'label' => 'תאריך התחלת מנוי',
                    'data' => date('d-m-Y'),
                    'required' => false
                ));
        }

        return $this->render('backend/users/subscription.html.twig', array(
            'user' => $user,
            'form' => $form->createView(),
            'save' => $save,
        ));
    }

    /**
     * @Route("/admin/users/photos/waiting", name="admin_users_photos_waiting")
     */
    function waitingPhotosAction()
    {
        $photos = $this->getDoctrine()->getRepository('AppBundle:Photo')->findByIsValid(false);
        foreach ($photos as $photo) {
            $photo->resize(600);
            $photo->resizeFace(150);
        }
        return $this->render('backend/users/waiting_photos.html.twig', array(
            'photos' => $photos,
        ));
    }

    /**
     * @Route("/admin/users/photos/waiting/{id}/approve/{state}", name="admin_users_photos_waiting_approve")
     */
    function approvePhotoAction($id, $state)
    {

        $ids2 = [];
        $ids = explode(',',$id);
        foreach ($ids as $id2) {
            $ids2[] = $id2;
        }
//        var_dump($ids2);die;
        $em = $this->getDoctrine()->getManager();
        $photos = $em->getRepository('AppBundle:Photo')->findBy(['id' => $ids2]);



       foreach ($photos as $photo) {
           if($state == 1){
               $photo->setIsValid(true);
               $em->persist($photo);
           }
           else {


               $user = $photo->getUser();
               $wasMainPhoto = $photo->getIsMain();
               $em->remove($photo);

               if($wasMainPhoto){
                   $photos = $user->getPhotos();
                   if(isset($photos[0])){
//                    dump($photos[0]);die;
                       $photos[0]->setIsMain(true);
                       $em->persist($photos[0]);
                   }
               }
               $photo->removeUpload();
           }
       }

        $em->flush();

        return new Response();
    }

    /**
     * @Route("/admin/users/reports/create", name="admin_users_reports_create")
     */
    function createReportAction(Request $request)
    {
        $report = new Report();
        $report->setName($request->request->get('name'))
            ->setIsFlagged($request->request->get('flagged', false))
            ->setParams(json_encode($request->request->get('advancedSearch')))
        ;

        $em = $this->getDoctrine()->getManager();
        $em->persist($report);
        $em->flush();

        return new Response();
    }

    /**
     * @Route("/admin/users/reports", name="admin_users_reports")
     */
    function reportsAction()
    {
        return $this->render('backend/users/reports.html.twig', array(
            'reports' => $this->getDoctrine()->getRepository('AppBundle:Report')->findAll(),
        ));
    }

    /**
     * @Route("/admin/users/reports/{id}/show_on_main_page/{state}", name="admin_users_reports_show_on_main_page")
     */
    function showOnMainPageAction(Report $report, $state)
    {
        $em = $this->getDoctrine()->getManager();
        $report->setIsFlagged($state);
        $em->persist($report);
        $em->flush();
        return new Response();
    }

    /**
     * @Route("/admin/users/reports/{id}/delete", name="admin_users_reports_delete")
     */
    function deleteReportAction(Report $report)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($report);
        $em->flush();
        return new Response();
    }

    /**
     * @Route("/admin/users/export", name="admin_users_export")
     */
    public function exportAction(Request $request)
    {
        $data = $request->request->get('advancedSearch');
//        var_dump($request->request->all());
        $usersRepo = $this->getDoctrine()->getManager('messages')->getRepository('AppBundle:User');
        $result = $usersRepo->setAdminMode()->setExportMode()->search(
            array(
                'current_user' => $this->getUser(),
                'data' => $data,
            )
        );

        $originalName = $request->request->get('fileName');


//        var_dump($originalName);die;
        $fileName = 'csv/' . $originalName . rand(1, 999999999) . '.csv';
//        $response = new StreamedResponse();
//        $response->setCallback(function () use($fileName, $result) {
            $handle = fopen($fileName, 'w+');
            $i = 0;
            foreach ($result as $row) {
                if ($i % 20 == 0) {
                    fputcsv($handle, array_keys($row));
                }

                $values = array();
                foreach (array_values($row) as $value) {
                    if ($value instanceof \DateTime) {
                        $value = $value->format("Y-m-d H:i:s");
                    }
                    $values[] = strip_tags(urldecode(nl2br($value)));
                }

                fputcsv($handle, $values);
                $i++;
            }


            fclose($handle);
//        });
        $_SESSION['csv']['originalName'] = $originalName;
        $_SESSION['csv']['fileName'] = $fileName;

        return new JsonResponse(array(
            'fileName' => $fileName,
            'originalName' => $originalName,
        ));
//        return $fileName;

    }

    /**
     * @Route("/admin/users/point/{toAll}", name="admin_users_point")
     */
    public function givePointAction($toAll)
    {
        $this->getDoctrine()->getRepository('AppBundle:User')->givePoint($toAll);
        return new Response();
    }

    /**
     * @Route("/admin/users/login/{id}", name="admin_users_login")
     */
    public function loginAction(User $user)
    {
        $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
        $this->get('security.token_storage')->setToken($token);
        return $this->redirect($this->generateUrl('user_homepage'));
    }





    public function getProfileType($tab)
    {

        switch($tab){
            case 1:
                return ProfileOneAdminType::class;
                break;

            case 2:
                return ProfileTwoType::class;
                break;

            case 3:
                return ProfileThreeType::class;
                break;

            case 4:
                return AdminPropertiesType::class;
                break;
                
            case 5:
            	return ChangePasswordType::class;
            	break;
        }

    }


    /**
     * @Route("/admin/edit/face/{id}", name="admin_users_face")
     */
    public function editFacePhoto(Request $request, Photo $photo) {

        if ($request->getMethod() == 'POST') {

            $em = $this->getDoctrine()->getEntityManager();
//            var_dump($id);

//            $photo = $this->getDoctrine()->getRepository('AppBundle:Photo')->find($id);

           $params = array(
               'x' => $request->get('x'),
               'y' => $request->get('y'),
               'w' => $request->get('w'),
               'h' => $request->get('h')
           );
            $face = $this->editFace($photo, $params);

            $this->savePhoto($face, $photo->getFaceAbsolutePath());
            $photo->setUpdated($photo->getUpdated() + 1);
            $em->persist($photo);
            $em->flush();
//            var_dump($photo->getUpdated());
            return new JsonResponse(['success' => true]);
        }
//        var_dump($photo->getId());
        return $this->render('backend/users/edit_face.html.twig', array(
            'photo' => $photo,
        ));
    }

    /**
     * @Route("/admin/users/there-for-count", name="admin_users_count")
     */

    public function getUsersWithoutThereForCount() {
        $users = $this->getDoctrine()->getRepository('AppBundle:User')->findAll();
        $count = 0;
        foreach ($users as $user) {
            if (!count($user->getLookingFor())) {
                $count++;
            }
        }

        return new Response($count);
    }

//    public function setUpCloudinary()
//    {
//        \Cloudinary::config(array(
//            "cloud_name" => "interdate",
//            "api_key" => "771234826869846",
//            "api_secret" => "-OWKuCgP1GtTjIgRhwfOUVu1jO8",
//        ));
//    }

    private function editFace(Photo $photo, $params)
    {
        $dataManager = $this->container->get('liip_imagine.data.manager');
        $image = $dataManager->find('optimize_original', $photo->getWebPath(false));
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

    private function savePhoto($photo, $path)
    {
        chmod($path,0777);
        $f = fopen($path, 'w');
        fwrite($f, $photo);
        fclose($f);
        /*var_dump($path, $photo);
        die;*/
    }

}
