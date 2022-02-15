<?php
//namespace AppBundle\Controller\Api\V1;
//
//use AppBundle\Form\Type\ProfileOneType;
//use AppBundle\Form\Type\ProfileTwoType;
//use AppBundle\Form\Type\ProfileThreeType;
//use AppBundle\Form\Type\ChangePasswordType;
//use FOS\RestBundle\Controller\Annotations as Rest;
//use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
//use Nelmio\ApiDocBundle\Annotation\ApiDoc;
//use FOS\RestBundle\Controller\FOSRestController;
//use Symfony\Component\HttpFoundation\Request;
//use Symfony\Component\HttpFoundation\Response;
//use AppBundle\Entity\User;
//use AppBundle\Entity\Photo;
//use AppBundle\Form\Type\AdvancedSearchType;
//use AppBundle\Form\Type\QuickSearchType;
//use Symfony\Component\HttpFoundation\File\UploadedFile;
//use Symfony\Component\Form\FormError;
//
//#use Symfony\Component\BrowserKit\Request;
//
//session_write_close();
//
//class UserController extends FOSRestController
//{
//
//    /**
//     * @ApiDoc(
//     *   resource = true,
//     *   description = "Get Users Results",
//     *   parameters={
//     *      {"name"="action", "dataType"="string", "required"=true, "description"="Action or Name of Page (online, new, list, search, arena)"},
//     *      {"name"="page", "dataType"="string", "required"=false, "description"="Page number (default value: 1)"},
//     *      {"name"="per_page", "dataType"="string", "required"=false, "description"="Total number results on one page (default value: 10)"},
//     *      {"name"="list", "dataType"="string", "required"=false, "description"="List name. Required if action=='list'. (favorited, favorited_me, viewed, viewed_me, connected, connected_me, black)"},
//     *
//     *      {"name"="quick_search", "dataType"="string", "required"=false, "description"="Parameters from quick search form for action: 'search'"},
//     *      {"name"="advanced_search", "dataType"="string", "required"=false, "description"="Parameters from advanced search form for action: 'search'"},
//     *      {"name"="user_id", "dataType"="integer", "required"=false, "description"="First user id in results for action: 'arena'"},
//     *
//     *     {"name"="filter", "dataType"="string", "required"=false, "description"="Filter for search. (distance,new,photo,lastActivity,popularity)"}
//     *
//     *   },
//     *   statusCodes = {
//     *     200 = "Returned when successful",
//     *     401 = "Returned when bad credentials were sent"
//     *   }
//     * )
//     * @param Request $request
//     * @return \FOS\RestBundle\View\View
//     */
//
//    public function postUsersResultAction(Request $request){
//        $action = $request->get('action', false);
//        $page = $request->get('page', 1);
//        $usersRepo = $this->getDoctrine()->getManager()->getRepository('AppBundle:User');
//        $settings = $this->getDoctrine()->getManager()->getRepository('AppBundle:Settings')->find(1);
//        $per_page = $request->get('per_page', 10);//$settings->getUsersPerPage();
//        $currentUser = $this->get('security.token_storage')->getToken()->getUser();
//        $paginator = $this->get('knp_paginator');
//        $filter = $request->get('filter', false);
//        $users = array();
//        if($action){
//            switch ($action){
//                case 'online':
//                    if(!$filter){ $filter = 'lastActivity'; }
//                    $users = $usersRepo->getOnline(
//                        array(
//                            'current_user' => $currentUser,
//                            'data' => array('filter' => $filter),
//                            'paginator' => $paginator,
//                            'page' => $page,
//                            'per_page' => $per_page,
//                            'considered_as_online_minutes_number' => $settings->getUserConsideredAsOnlineAfterLastActivityMinutesNumber(),
//                        )
//                    );
//                break;
//                case 'search':
//                    $quickSearch = $request->get('quick_search', null);
//                    $advancedSearch = $request->get('advanced_search', null);
//                    $data = ($quickSearch !== null) ? $quickSearch : $advancedSearch;
//                    if($filter != false){
//                        $data['filter'] = $filter;
//                    }else{
//                        $filter = $data['filter'];
//                    }
//                    if($data !== null){
//                        $users = $usersRepo->search(array(
//                            'current_user' => $currentUser,
//                            'data' => $data,
//                            'paginator' => $paginator,
//                            'page' => $page,
//                            'per_page' => $per_page,
//                        ));
//                    }
//                break;
//                case 'list':
//                    $list = $request->get('list', null);
//                    switch ($list){
//                        case 'favorited':
//                            $inverse_list = 'favoritedMe';
//                            $type = 'owner';
//                        break;
//                        case 'favorite_me':
//                            $inverse_list = 'favorited';
//                            $type = 'member';
//                        break;
//                        case 'viewed':
//                            $inverse_list = 'viewedMe';
//                            $type = 'owner';
//                            break;
//                        case 'viewed_me':
//                            $inverse_list = 'viewed';
//                            $type = 'member';
//                        break;
//                        case 'connected':
//                            $inverse_list = 'connectedMe';
//                            $type = 'owner';
//                        break;
//                        case 'connected_me':
//                            $inverse_list = 'connected';
//                            $type = 'member';
//                        break;
//
//                        case 'black':
//                            $inverse_list = 'blackListedMe';
//                            $type = 'owner';
//                        break;
//                    }
//                    $users = $usersRepo->getList(array(
//                        'current_user' => $currentUser,
//                        'request_data' => null,
//                        'paginator' => $paginator,
//                        'page' => $page,
//                        'per_page' => $per_page,
//                        'inverse_list' => $inverse_list,
//                        'type' => $type,
//                    ));
//                break;
//                case 'new':
//                    if(!$filter){ $filter = 'new'; }
//                    $users = $usersRepo->getNew(
//                        array(
//                            'considered_as_new_days_number' => $settings->getUserConsideredAsNewAfterDaysNumber(),
//                            'paginator' => $paginator,
//                            'data' => array('filter' => $filter),
//                            'page' => $page,
//                            'per_page' => $per_page,
//                            'current_user' => $currentUser,
//                        )
//                    );
//                break;
//                case 'arena':
//                    $id = $request->get('user_id', 0);
//                    $firstUser = ((int)$id > 0) ? $usersRepo->find($id) : false;
//                    $users = $usersRepo->getUsersForLike($currentUser, $firstUser);
//                break;
//            }
//        }
//
//        return $this->view(
//            $this->transformUsers($users, $settings, $filter)
//        , Response::HTTP_OK);
//    }
//
//
//    public function transformUsers($users, $settings, $filter = false){
//        $currentUser = $this->get('security.token_storage')->getToken()->getUser();
//        $usersRepo = $this->getDoctrine()->getManager()->getRepository('AppBundle:User');
//        $newUsers = array();
//
//        $texts = array('like'=>'לייק','message'=>'שלח','no_results'=>'אין תוצאות','add'=>'הוסף','remove'=>'להסיר','unblock'=>'בטל חסימה');
//
//
//        if(is_array($users) and isset($users['photos'])){
//             if($users['photos'] == 0) {
//                return array(
//                    'users' => $newUsers,
//                    'texts' => $texts,
//                    'arenaStatus' => $this->get('translator')->trans('עליך להעלות לפחות תמונה אחת כדי להיכנס אל הזירה'),
//                );
//            }
//
//            //$newUsers = $users['online'];
//
//        	$photo = new Photo();
//        	$photo->setCloudinary();
//
//            foreach (array('online','other') as $key) {
//                foreach ($users[$key] as $user) {
//                    $user['age'] = date_diff(date_create($user['birthday']), date_create('today'))->y;
//
//                    $user['image'] = cloudinary_url($user['image']);
//                    $newUsers[] = $user;
//                }
//            }
//        }else {
//
//            foreach ($users as $user) {
//                if (is_object($user)) {
//                    $distance = $usersRepo->getDistance($currentUser,$user);
//                    $distance = ($distance == '0') ? '' : $distance . ' ' . $this->get('translator')->trans('ק"מ');
//                    $mainPhoto = (is_object($user->getMainPhoto())) ? $user->getMainPhoto()->getFaceWebPath() : $user->getNoPhoto();
//                    $fotoMain = (is_object($user->getMainPhoto())) ? $user->getMainPhoto()->getWebPath() : $user->getNoPhoto();
//                    $newUsers[] = array(
//                        'id' => $user->getId(),
//                        'isPaying' => $user->isPaying(),
//                        'isNew' => $user->isNew($settings->getUserConsideredAsNewAfterDaysNumber()),
//                        'isOnline' => $user->isOnline($settings->getUserConsideredAsOnlineAfterLastActivityMinutesNumber()),
//                        'isAddLike' => $currentUser->isAddLike($user),
//                        'isAddFavorite' => $currentUser->isAddFavorite($user->getId()),
//                        'isAddBlackListed' => $currentUser->isAddBlackListed($user->getId()),
//                        'photo' => $mainPhoto,
//                        'url' => $fotoMain,
//                        'username' => $user->getUsername(),
//                        'age' => $user->age(),
//                        'distance' => $distance,
//                        'region_name' => $user->getRegion()->getName(),
//                        'area_name' => $user->getCity()->getName(),
//                        'gender' => $user->getGender()->getId(),
//                    );
//                }
//            }
//        }
//
//
//        $filters = array(
//            array(
//                'label' => $this->get('translator')->trans('מרחק'),
//                'value' => 'distance',
//            ),
//            array(
//                'label' => $this->get('translator')->trans('חברים חדשים
//                		'),
//                'value' => 'new',
//            ),
//            array(
//                'label' => $this->get('translator')->trans('עם תמונה'),
//                'value' => 'photo',
//            ),
//            array(
//                'label' => $this->get('translator')->trans('ביקור אחרון'),
//                'value' => 'lastActivity',
//            ),
//            array(
//                'label' => $this->get('translator')->trans('פופולריות'),
//                'value' => 'popularity',
//            )
//        );
//
//
//        return array(
//            'users' => $newUsers,
//            'texts' => $texts,
//            'filters' => $filters,
//            'filter' => $filter
//        );
//    }
//
//
//    /**
//     * @ApiDoc(
//     *   resource = true,
//     *   description = "Save settings",
//     *   parameters={
//     *      {"name"="is_sent_email", "dataType"="string", "required"=false, "description"="Sent to user notification by E-mail"},
//     *      {"name"="is_sent_push", "dataType"="string", "required"=false, "description"="Sent to user push notification."},
//     *
//     *   },
//     *   statusCodes = {
//     *     200 = "Returned when successful",
//     *     401 = "Returned when bad credentials were sent"
//     *   }
//     * )
//     */
//
//    public function postSettingsAction(Request $request){
//        $user = $this->get('security.token_storage')->getToken()->getUser();
//        $post = $request->request->all();
//        $success = '';
//        if(isset($post['is_sent_email']) or isset($post['is_sent_push'])){
//
//            $is_sent_email = isset($post['is_sent_email']) ? (boolean)$post['is_sent_email'] : $user->getIsSentEmail();
//            $is_sent_push = isset($post['is_sent_push']) ? (boolean)$post['is_sent_push'] : $user->getIsSentPush();
//            $user->setIsSentEmail($is_sent_email);
//            $user->setIsSentPush($is_sent_push);
//            $em = $this->getDoctrine()->getManager();
//            $em->persist($user);
//            $em->flush();
//            $success = $this->get('translator')->trans('Your settings saved successfully');
//
//        }
//        return $this->view(array(
//            'form' => array(
//                'is_sent_email' => array(
//                    'label' => $this->get('translator')->trans('שלח לי הודעה בדואר אלקטרוני'),
//                    'value' => $user->getIsSentEmail(),
//                    'type' => 'checkbox',
//                    'name' => 'is_sent_email',
//                ),
//                'is_sent_push' => array(
//                    'label' => $this->get('translator')->trans('שלח לי הודעת דחיפה'),
//                    'value' => $user->getIsSentPush(),
//                    'type' => 'checkbox',
//                    'name' => 'is_sent_push',
//                ),
//                'submit' => $this->get('translator')->trans('שמור'),
//            ),
//            'success' => $success,
//        ), Response::HTTP_OK);
//
//        //return $this->render('frontend/user/settings.html.twig', array());
//    }
//
//
//    /**
//     * @ApiDoc(
//     *   resource = true,
//     *   description = "Login User",
//     *
//     *   statusCodes = {
//     *     200 = "Returned when successful",
//     *     400 = "Bad Request.",
//     *     403 = "Returned when bad credentials were sent."
//     *   }
//     * )
//     */
//
//    public function postLoginAction(){
//
//        $user = $this->get('security.token_storage')->getToken()->getUser();
//        $user->setIp($_SERVER['REMOTE_ADDR']);
//        $user->setIsFrozen(0);
//        //$user->setLastloginAt(new \DateTime());
//    	$mobileDetector = $this->get('mobile_detect.mobile_detector');
//	    if($mobileDetector->isAndroidOS()){
//	        $user->setLoginFrom($loginFromRepo->find(6));
//	    }
//	    if($mobileDetector->isIOS()){
//	        $user->setLoginFrom($loginFromRepo->find(5));
//	    }
//        $em = $this->getDoctrine()->getManager();
//        $em->persist($user);
//        $em->flush();
//        $status = $this->getUserStatus($user);
//        return $this->view(array(
//            'status' => $status,
//            'photo' => (is_object($user->getMainPhoto())) ? $user->getMainPhoto()->getFaceWebPath() : $user->getNoPhoto(),
//            'id' => $user->getId(),
//            'texts' => array(
//                'photoMessage' => $this->get('translator')->trans('You need to upload at least one photo'),
//            )
//        ), Response::HTTP_OK);
//    }
//
//
//    public function transformStringArray($array){
//        $newArray = array();
//        foreach ($array as $key => $string){
//            $newKey = (is_int($key)) ? $string : $key;
//            $newKey = strtolower(str_replace(array('.',',',':',';','!','?',' '), array('','','','','','','_'), $newKey));
//            /** @Ignore */
//            $newStr = (is_int($key)) ? /** @Ignore */$this->get('translator')->trans($string) : $string;
//            $newArray[$newKey] = $newStr;
//        }
//        return $newArray;
//    }
//
//
//    /**
//     * @ApiDoc(
//     *   resource = true,
//     *   description = "Get User Statistics",
//     *
//     *   statusCodes = {
//     *     200 = "Returned when successful",
//     *     401 = "Returned when bad credentials were sent"
//     *   }
//     * )
//     */
//
//    public function getStatisticsActions()
//    {
//        $user = $this->get('security.token_storage')->getToken()->getUser();
//        $settings = $this->getDoctrine()->getManager()->getRepository('AppBundle:Settings')->find(1);
//        $delay = new \DateTime();
//        $delay->setTimestamp(strtotime(
//                $settings->getUserConsideredAsOnlineAfterLastActivityMinutesNumber() . ' minutes ago')
//        );
//        $conn = $this->getDoctrine()->getManager()->getConnection();
//        return $this->view(array('statistics' =>
//            $conn->query("CALL get_statistics ('"
//            . $delay->format('Y-m-d H:i:s.000000') . "', '"
//            . $user->getId() . "', '"
//            . $user->getGender()->getId() . "')")
//            ->fetch()
//        ), Response::HTTP_OK);
//    }
//
//    public function getUserStatus($user){
//
//    /*	if($user->getGender()->getId() == 1 && !$user->getIsActive()) {
//            $status = 'not_activated';
//        }elseif($user->getGender()->getId() == 2 && !$user->hasPhotos()){
//            $status = 'no_photo';
//        }else{
//        */
//            $status = 'login';
//      //  }
//        return $status;
//    }
//
//
//    /**
//     * @ApiDoc(
//     *   resource = true,
//     *   description = "Get logged user data",
//     *
//     *   statusCodes = {
//     *     200 = "Returned when successful",
//     *     401 = "Returned when bad credentials were sent"
//     *   }
//     * )
//     * @param User $user
//     * @param Request $request
//     * @return \FOS\RestBundle\View\View
//     */
//
//    public function getUserAction(User $user, Request $request)
//    {
//        $em = $this->getDoctrine()->getManager();
//        $currentUser = $this->get('security.token_storage')->getToken()->getUser();
//        $usersRepo = $this->getDoctrine()->getManager()->getRepository('AppBundle:User');
//        $request->request->set('getUser', $user);
//        $settings = $this->getDoctrine()->getManager()->getRepository('AppBundle:Settings')->find(1);
//        $purposes = '';
//        foreach ($user->getPurposes() as $purpose){
//            $purposes .= ((empty($purposes)) ? '' : ', ') . $purpose->getName();
//        }
//        $languages = '';
//        /*foreach ($user->getLanguages() as $language){
//            $languages .= ((empty($languages)) ? '' : ', ') . $language->getName();
//        }*/
//
//        $hobbies = '';
//        /*foreach ($user->getHobbies() as $hobby){
//            $hobbies .= ((empty($hobbies)) ? '' : ', ') . $hobby->getName();
//        }*/
//
//        $features = '';
//        /*foreach ($user->getFeatures() as $feature){
//            $features .= ((empty($features)) ? '' : ', ') . $feature->getName();
//        }*/
//        $photos = '';
//        $photos = $this->getAllUserPhotos($user);//$this->getPhotosAction($request, false);
//        $zodiac = $user->getZodiac();
//        if(!is_object($zodiac)){
//            $zodiac = $this->getDoctrine()->getManager()->getRepository('AppBundle:Zodiac')->getZodiacByDate($user->getBirthday());
//            $user->setZodiac($zodiac);
//        }
//        $user->setViews($user->getViews() + 1);
//
//        $distance = $usersRepo->getDistance($currentUser,$user);
//        $distance = ($distance == '0') ? '' : $distance . ' ' . $this->get('translator')->trans('ק"מ');
//
//        $em->persist($user);
//        $em->flush();
//        $this->updateList('View', $currentUser, $user);
//
//        $veggieReasons = '';
//        foreach ($user->getVeggieReasons() as $veggieReason){
//        	$veggieReasons .= ((empty($veggieReasons)) ? '' : ', ') . $veggieReason->getName();
//        }
//
//        $veggieReasons = '';
//        foreach ($user->getVeggieReasons() as $veggieReason){
//        	$veggieReasons .= ((empty($veggieReasons)) ? '' : ', ') . $veggieReason->getName();
//        }
//
//        $interests = '';
//        foreach ($user->getInterests() as $interest){
//        	$interests .= ((empty($interests)) ? '' : ', ') . $interest->getName();
//        }
//
//        $res = array(
//            'id' => $user->getId(),
//            'isPaying' => $user->isPaying(),
//            'isNew' => $user->isNew($settings->getUserConsideredAsNewAfterDaysNumber()),
//            'isOnline' => $user->isOnline($settings->getUserConsideredAsOnlineAfterLastActivityMinutesNumber()),
//            'isAddLike' => $currentUser->isAddLike($user),
//            'isAddFavorite' => $currentUser->isAddFavorite($user->getId()),
//            'isAddBlackListed' => $currentUser->isAddBlackListed($user->getId()),
//            'photos' => $photos,
//            'username' => $user->getUsername(),
//            'age' => $user->age(),
//            'region_name' => $user->getRegion()->getName(),
//            'city' => $user->getCity()->getName(),
//            'gender' => $user->getGender()->getId(),
//            'looking' => array('label' => $this->get('translator')->trans('מה אני מחפש/ת?'), 'value' => $user->getLooking()),
//            'about' => array('label' => $this->get('translator')->trans('אני בכמה מילים'), 'value' => $user->getAbout()),
//            'purposes' => array('label' => $this->get('translator')->trans('כאן למטרת'), 'value' => $purposes),
//            'height' => array('label' => $this->get('translator')->trans('גובה'), 'value' => $user->getHeight(true)),
//            'body' => array('label' => $this->get('translator')->trans('מבנה גוף'), 'value' => $user->getBody()->getName()),
//            'hair' => array('label' => $this->get('translator')->trans('צבע שיער'), 'value' => $user->getHair()->getName()),
//            'eyes' => array('label' => $this->get('translator')->trans('צבע עיניים'), 'value' => $user->getEyes()->getName()),
//            'education' => array('label' => $this->get('translator')->trans('השכלה'), 'value' => $user->getEducation()->getName()),
//            'children' => array('label' => $this->get('translator')->trans('ילדים'), 'value' => $user->getChildren()->getName()),
//            'smoking' => array('label' => $this->get('translator')->trans('הרגלי עישון:'), 'value' => $user->getSmoking()->getName()),
//            'drinking' => array('label' => $this->get('translator')->trans('הרגלי שתיה'), 'value' => $user->getDrinking()->getName()),
//            'religion' => array('label' => $this->get('translator')->trans('דת'), 'value' => $user->getReligion()->getName()),
//            'languages' => array('label' => $this->get('translator')->trans('Languages'), 'value' => $languages),
//            'relationshipStatus' => array('label' => $this->get('translator')->trans('מצב משפחתי'), 'value' => $user->getRelationshipStatus()->getName()),
//            'features' => array('label' => $this->get('translator')->trans('Special Features'), 'value' => $features),
//            'zodiac' => array('label' => $this->get('translator')->trans('מזל'), 'value' => $zodiac->getName()),
//        	'music' => array('label' => $this->get('translator')->trans('music'), 'value' => $user->getMusic()),
//        	'books' => array('label' => $this->get('translator')->trans('הספרים האהובים עליי'), 'value' => $user->getFavoriteBooks()),
//        	'restaurant' => array('label' => $this->get('translator')->trans('מסעדה ידידותית לצמחונים שאני אוהב/ת'), 'value' => $user->getFavoriteRestaurant()),
//        	'dish' => array('label' => $this->get('translator')->trans('מנה צמחונית אהובה'), 'value' => $user->getFavoriteDish()),
//        	'perfectdate' => array('label' => $this->get('translator')->trans('הדייט המושלם עבורי הוא'), 'value' => $user->getPerfectDate()),
//        	'politicalaffiliation' => array('label' => $this->get('translator')->trans('השקפה פוליטית'), 'value' => $user->getPoliticalAffiliation()->getName()),
//        	'interests' => array('label' => $this->get('translator')->trans('תחומי עניין'), 'value' => $interests),
//        	'animals' => array('label' => $this->get('translator')->trans('בע״ח'), 'value' => $user->getAnimals()->getName()),
//       		'green' => array('label' => $this->get('translator')->trans('כמה ירוק אני'), 'value' => $user->getGreen()->getName()),
//       		'sport' => array('label' => $this->get('translator')->trans('הרגלי ספורט'), 'value' => $user->getSport()->getName()),
//
//       		'veggiereasons' => array('label' => $this->get('translator')->trans('אני צמחוני/טבעוני כי'), 'value' => $veggieReasons),
//      		'nutrition' => array('label' => $this->get('translator')->trans('תזונה'), 'value' => $user->getNutrition()->getName()),
//       		'occupation' => array('label' => $this->get('translator')->trans('תחום עיסוק'), 'value' => $user->getOccupation()->getName()),
//       		'sexorientation' => array('label' => $this->get('translator')->trans('העדפה'), 'value' => $user->getSexorientation()->getName()),
//
//            'distance' => $distance,
//            'formReportAbuse' => array(
//                'title' => $this->get('translator')->trans('דווח על כרטיס לא תקין'),
//                'text' => array(
//                    'name' => $this->get('translator')->trans('text'),
//                    'type' => 'textarea',
//                    'label' => $this->get('translator')->trans('הערות'),
//                    'value' => '',
//                ),
//                'buttons' => array(
//                    'submit' => $this->get('translator')->trans('שלח'),
//                    'cancel' => $this->get('translator')->trans('ביטול'),
//                )
//            ),
//               'texts' => array(
//                  'lock' => $this->get('translator')->trans('חסימה'),
//                  'unlock' => $this->get('translator')->trans('בטל חסימה'),
//              ),
//
//        );
//
//
//        if(!empty($hobbies)){
//            $res['hobbies'] = array('label' => $this->get('translator')->trans('Hobbies'), 'value' => $hobbies);
//        }
///*
//        if ($user->getOccupation()) {
//            $res['occupation'] = array('label' => $this->get('translator')->trans('Occupation'), 'value' => $user->getOccupation());
//        }
//        */  if ($user->getGender()->getId() == 1) {
//              //$res['status'] = array('label' => $this->get('translator')->trans('Status'), 'value' => $user->getStatus()->getName());
//             // $res['netWorth'] = array('label' => $this->get('translator')->trans('Net Worth'), 'value' => $user->getNetWorth()->getName());
//             // $res['income'] = array('label' => $this->get('translator')->trans('Annual Income'), 'value' => $user->getIncome()->getName());
//          }
//
//        return $this->view($res, Response::HTTP_OK);
//    }
//
//
//
//    public function photosAction($request){
//        $getUser = $request->get('getUser', false);
//        if($getUser->getGender()->getId() == 1 and !$getUser->getIsActivated()){
//            $res = $this->getActivationAction($request, false);
//        }else{
//            $res = $this->getPhotosAction($request, false);
//        }
//        return $this->view($res, Response::HTTP_OK);
//    }
//
//
//    /**
//     * @ApiDoc(
//     *   resource = true,
//     *   description = "Get user photos",
//     *
//     *   statusCodes = {
//     *     200 = "Returned when successful",
//     *     401 = "Returned when bad credentials were sent"
//     *   }
//     * )
//     * @param Request $request
//     * @param bool $json
//     * @return array|\FOS\RestBundle\View\View
//     */
//
//    public function getPhotosAction(Request $request, $json = true){
//        $getUser = $request->get('getUser', false);
//        $user = ($getUser) ? $getUser : $user = $this->get('security.token_storage')->getToken()->getUser();
//
//        $userPhotos = $this->getAllUserPhotos($user, true);//array();
//
//        $texts = array(
//            'approved' => $this->get('translator')->trans('מאושרת'),
//            'status' => $this->get('translator')->trans('סטטוס'),
//            'delete' => $this->get('translator')->trans('מחיקה'),
//            'cancel' => $this->get('translator')->trans('ביטול'),
//            'waiting_for_approval' => $this->get('translator')->trans('ממתינה לאישור'),
//            'set_as_main_photo' => $this->get('translator')->trans('בחר כתמונה ראשית'),
//            'add_photo' => $this->get('translator')->trans('הוסף תמונה'),
//            'choose_from_camera' => $this->get('translator')->trans('בחר מתוך מצלמה'),
//            'choose_from_gallery' => $this->get('translator')->trans('בחר מתוך גלריה'),
//            'register_end_button' => $this->get('translator')->trans('סיום'),
//            /*'description' => array(
//                'text1' => $this->get('translator')->trans('Adding a photo to your profile boosts your chances of meeting new people times 20!<br>We strongly encourage you post a number of them.<br>*New photos awaiting admin’s approval.<br><br>Please notice these guidelines:<ul><li>Photos must be of yourself, and you must be recognizable in the photo.</li><li>Photos must not contain nudity or sexual content.</li></ul><br>For more information check out our'),
//                'text2' => '/open_api/pages/6',
//                'text3' => $this->get('translator')->trans('Photos Policy'),
//                'text4' => $this->get('translator')->trans('page.<br>Having trouble uploading a photo? Send the photo to info@nyrichdate.com along with your nickname or the email you registered with, and we\'ll upload it to your profile.<br>'),
//            ),*/
//            'description' => $this->get('translator')->trans('
//             אם נתקלת בבעיה בהעלאת תמונה לאתר, באפשרותך לשלוח תמונה לכתובת:&nbsp;<br>
//            <a href="mailto:info@GreenDate.co.il">info@greendate.co.il</a><br>
//            במייל יש לציין את כתובת הדואל עמה נרשמת לאתר, וסיסמה.
//
//                <br>
//                <h5>הקריטריונים לאישור התמונה</h5>
//                <ul>
//            <li>התמונה חייבת להיות תמונה שלך</li>
//            <li>יש להעלות תמונה איכותית וברורה, הכוללת פנים</li>
//            <li>אין להעלות תמונות בעירום חלקי או מלא</li>
//            <li>אין להעלות תמונות עם ילדים</li>
//        	</ul>'),
//        );
//        if(!$json){
//            return array('photos' => $userPhotos, 'texts' => $texts, 'noPhoto' => $user->getNoPhoto() );
//        }
//
//        return $this->view(array(
//            'photos' => $userPhotos,
//            'texts' => $texts,
//            'noPhoto' => $user->getNoPhoto(),
//        ), Response::HTTP_OK);
//    }
//
//    public function getAllUserPhotos($user, $is_curent_user = false){
//        $userPhotos = array(array('face' => $user->getNoPhoto(), 'url' => $user->getNoPhoto()));
//        if(count($user->getPhotos()) > 0){
//            $i = 1;
//            foreach ($user->getPhotos() as $photo) {
//                if($photo->getIsValid() or $is_curent_user) {
//                    $key = (int)(($photo->getIsMain()) ? 0 : $i);
//                    $userPhotos[$key] = array(
//                        'id' => $photo->getId(),
//                        'isMain' => $photo->getIsMain(),
//                        'isValid' => $photo->getIsValid(),
//                        'face' => $photo->getFaceWebPath(),
//                        'url' => $photo->getWebPath(),
//                    );
//                    if (!$photo->getIsMain()) {
//                        $i++;
//                    }
//                }
//            }
//        }
//
//        return $userPhotos;
//    }
//
//
//    /**
//     * @ApiDoc(
//     *   resource = true,
//     *   description = "User photo action",
//     *   parameters={
//     *      {"name"="photo", "dataType"="file", "required"=false, "description"="Image file to upload"},
//     *      {"name"="delete", "dataType"="string", "required"=false, "description"="Image id for detete photo"},
//     *      {"name"="setMain", "dataType"="string", "required"=false, "description"="Image id for set as main photo"},
//     *
//     *   },
//     *   statusCodes = {
//     *     200 = "Returned when successful",
//     *     401 = "Returned when bad credentials were sent"
//     *   }
//     * )
//     */
//
//    public function postPhotoAction(Request $request)
//    {
//        $user = $this->get('security.token_storage')->getToken()->getUser();
//        $uploadedPhoto = $request->files->get('photo');
//        $em = $this->getDoctrine()->getManager();
//        if($uploadedPhoto instanceof UploadedFile) {
//            $photo = new Photo();
//            $photo->setCloudinary();
//            $res = \Cloudinary\Uploader::upload($uploadedPhoto);
//
//            $isMain = ($user->getMainPhoto()) ? true : false;
//            $isValid = false;
//
//            $photo->setName($res['public_id']);
//            $photo->setUser($user);
//            //$photo->setFile($uploadedPhoto);
//            $photo->setIsValid($isValid);
//            $photo->setIsMain($isMain);
//
//            $em->persist($photo);
//            $em->flush();
//
//            $user->addPhoto($photo);
//            $em->persist($user);
//            $em->flush();
//
//
//        }
//
//        if($request->get('delete', false)){
//
//            $photo = $this->getDoctrine()->getRepository('AppBundle:Photo')->find($request->get('delete', false));
//
//            if($user->getId() == $photo->getUser()->getId()) {
//
//                $wasMainPhoto = $photo->getIsMain();
//                $user->removePhoto($photo);
//                $photo->setCloudinary();
//                \Cloudinary\Uploader::destroy($photo->getName());
//                $em->remove($photo);
//                $em->flush();
//
//                $photos = $user->getPhotos();
//                if(count($photos) > 0) {
//                    if ($wasMainPhoto) {
//                        foreach ($photos as $userPhoto) {
//                            if ($userPhoto->getIsValid()) {
//                                $userPhoto->setIsMain(true);
//                                $em->persist($userPhoto);
//                                $em->flush();
//                                $wasMainPhoto = false;
//                                break;
//                            }
//                        }
//                    }
//                    if ($wasMainPhoto) {
//                        foreach ($photos as $userPhoto) {
//                            $userPhoto->setIsMain(true);
//                            $em->persist($userPhoto);
//                            $em->flush();
//                            break;
//                        }
//                    }
//                }
//            }
//        }
//
//        if($request->get('setMain', false)){
//
//            $photo = $this->getDoctrine()->getRepository('AppBundle:Photo')->find($request->get('setMain', false));
//
//            if($user->getId() == $photo->getUser()->getId()) {
//
//                $photos = $user->getPhotos();
//
//                foreach ($photos as $userPhoto) {
//                    if ($userPhoto->getIsMain()) {
//                        $userPhoto->setIsMain(false);
//                        $em->persist($userPhoto);
//                    }
//                }
//
//                $photo->setIsMain(true);
//                $em->persist($photo);
//                $em->flush();
//            }
//        }
//
//        return $this->view($this->getPhotosAction($request, false), Response::HTTP_OK);
//    }
//
//    private function applyFilterToPhoto($filterName, $webPath)
//    {
//        $dataManager = $this->container->get('liip_imagine.data.manager');
//        $image = $dataManager->find($filterName, $webPath);
//        return $this->container->get('liip_imagine.filter.manager')->applyFilter($image, $filterName)->getContent();
//    }
//
//    public function savePhoto($photo, $path)
//    {
//        $f = fopen($path, 'w');
//        fwrite($f, $photo);
//        fclose($f);
//    }
//
//    private function getFace(Photo $photo, $params)
//    {
//        $dataManager = $this->container->get('liip_imagine.data.manager');
//        $image = $dataManager->find('optimize_original', $photo->getWebPath());
//        return $this->container->get('liip_imagine.filter.manager')
//            ->applyFilter($image, 'face', array(
//                'filters' => array(
//                    'crop' => array(
//                        'start' => array($params['x'], $params['y']),
//                        'size' => array($params['w'], $params['h'])
//                    )
//                )
//            ))->getContent();
//    }
//
///*
//    /**
//     * @ApiDoc(
//     *   resource = true,
//     *   description = "Get user phone activation",
//     *
//     *   statusCodes = {
//     *     200 = "Returned when successful",
//     *     401 = "Returned when bad credentials were sent"
//     *   }
//     * )
//     */
///*
//    public function getActivateAction(Request $request, $json = true){
//
//        $user = $this->get('security.token_storage')->getToken()->getUser();
//        if(!$json){
//            return $this->activateAction($request, $user);
//        }
//        //var_dump(2345);die;
//        return $this->view( $this->activateAction($request, $user), Response::HTTP_OK);
//    }
//*/
//
//    /**
//     * @ApiDoc(
//     *   resource = true,
//     *   description = "Send activation form. (Send SMS/Activate account). This route only for men.",
//     *   parameters = {
//     *     {"name"="phone", "dataType"="string", "required"=true, "description"="user phone number"},
//     *     {"name"="code", "dataType"="string", "required"=true, "description"="code from SMS"},
//     *     {"name"="resent", "dataType"="boolean", "required"=false, "description"="Resend SMS. Default: 'false'."}
//     *  },
//     *   statusCodes = {
//     *     200 = "Returned when successful",
//     *     401 = "Returned when bad credentials were sent"
//     *   }
//     * )
//     */
//
//    public function postActivationsAction(Request $request){
//        $res = $this->activateAction($request, $this->get('security.token_storage')->getToken()->getUser());
//        //var_dump($res);die;
//        return $this->view( $res, Response::HTTP_OK);
//    }
//
//    public function activateAction($request, $user)
//    {
//        $error = false;
//        $errorMessage = null;
//        $sent = false;
//        $success = '';
//        $res = array();
//        if($user->getGender()->getId() == 1) {
//
//            $phone = $request->get('phone', null);
//            $code = $request->get('code', null);
//            $resent = (boolean)$request->request->get('resent', false);
//            $em = $this->getDoctrine()->getManager();
//
//            if (null !== $phone) {
//                if (preg_match('/[^0-9,]/', $phone)) {
//                    $error = true;
//                    $errorMessage = $this->get('translator')->trans('The phone must contain only numbers');
//                }
//                if (!$error) {
//                    if (substr($phone, 0, 1) == '0') {
//                        $phone = '972' . substr($phone, 1);
//                    }
//                    if (substr($phone, 0, 1) != '0') {
//                        $phone = '+1' . ((substr($phone, 0, 1) != '1') ? $phone : substr($phone, 1));
//                    }
//                    $phoneInBlocked = $em->getRepository('AppBundle:PhoneBlocked')->findOneByValue($phone);
//                    $existsPhone = $em->getRepository('AppBundle:User')->findOneByPhone($phone);
//
//                    if (is_object($existsPhone) and $existsPhone->getId() == $user->getId()) {
//                        $existsPhone = false;
//                    }
//
//                    if ($phoneInBlocked or $existsPhone) {
//                        $error = true;
//                        $errorMessage = $this->get('translator')->trans('Phone already exists in the system');
//                    } else {
//                        if($user->getSmsCount() == 3){
//                            $error = true;
//                            $errorMessage = $this->get('translator')->trans('Option Resent SMS is no longer available. For activate your account, please contact us');
//                        }
//                        $code = rand(10000, 99999);
//
//                        $url = 'https://rest.nexmo.com/sms/json?' . http_build_query(
//                                [
//                                    'api_key' => 'eadca325',
//                                    'api_secret' => '5e5205c0',
//                                    'to' => $phone,
//                                    'from' => '12082254688',//'NYSD',
//                                    'text' => 'Activation code: ' . $code,
//                                ]
//                            );
//
//                        $ch = curl_init($url);
//                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//                        $response = curl_exec($ch);
//                        $response = json_decode($response);
//                        $status = $response->messages[0]->status;
//
//                        if ($status == 0) {
//                            $user->setCode($code);
//                            $user->setPhone($phone);
//                            $user->setSmsCount((int)$user->getSmsCount() + 1);
//                            $sent = true;
//                            $success = $this->get('translator')->trans('Activation code has been successfully sent');
//                            $is_code = false;
//                        } else {
//                            $error = true;
//                            $errorMessage = $this->get('translator')->trans('Phone number is incorrect, please try again');
//                        }
//                    }
//                }
//            } elseif (null !== $code) {
//
//                if ($code == $user->getCode()) {
//                    $user->setIsActivated(true);
//                    $success = $this->get('translator')->trans('Your account has been successfully activated');
//                    $sent = true;
//                    $is_code = true;
//                    $error = false;
//                } else {
//                    $error = true;
//                    $errorMessage = $this->get('translator')->trans('Activation code is incorrect, please try again');
//                }
//
//            }
//
//            if (!$error) {
//                $em->persist($user);
//                $em->flush();
//            }
//
//
//
//            if($resent and $user->getSmsCount() == 3){
//                $error = true;
//                $errorMessage = $this->get('translator')->trans('Option Resent SMS is no longer available. For activate your account, please contact us');
//            }
//            if (empty($user->getPhone($phone)) or ($resent and $user->getSmsCount() < 3)) {
//                $form = array(
//                    'phone' => array(
//                        'label' => $this->get('translator')->trans('* Phone'),
//                        'name' => 'phone',
//                        'type' => 'text',
//                    ),
//                    'submit' => $this->get('translator')->trans('Send Me SMS'),
//                    'description' => $this->get('translator')->trans('Activation code will be sent by SMS'),
//                );
//            } else {
//                $form = array(
//                    'code' => array(
//                        'label' => $this->get('translator')->trans('* Activation Code'),
//                        'name' => 'code',
//                        'type' => 'text',
//                    ),
//                    'submit' => $this->get('translator')->trans('Activate'),
//                    'resentButton' => $this->get('translator')->trans('Resend SMS'),
//                    'description' => $this->get('translator')->trans('Enter your activation code')
//                );
//            }
//
//            $res = array(
//                'sent' => $sent,
//                'error' => $error,
//                'errorMessage' => $errorMessage,
//                'form' => $form,
//
//            );
//
//            if ($sent) {
//                $res['success'] = $success;
//                $res['code'] = $is_code;
//                $res['register_end_button'] = $this->get('translator')->trans('Finish');
//            }
//
//        }
//
//        return $res;
//    }
//
//
//    /**
//     * @ApiDoc(
//     *   resource = true,
//     *   description = "Set like to user",
//     *
//     *   statusCodes = {
//     *     200 = "Returned when successful",
//     *     401 = "Returned when bad credentials were sent"
//     *   }
//     * )
//     */
//
//    public function postLikeAction(User $toUser)
//    {
//        $result = $this->getDoctrine()->getManager()->getRepository('AppBundle:User')->sendUserLike($this->get('security.token_storage')->getToken()->getUser(), $toUser);
//        return $this->view( $result, Response::HTTP_OK);
//    }
//
//
//    /**
//     * @ApiDoc(
//     *   resource = true,
//     *   description = "Get bingo",
//     *   parameters = {
//     *     {"name"="likeMeId", "dataType"="integer", "required"=false, "description"="LikeMe id for set splash bingo as show"}
//     *  },
//     *   statusCodes = {
//     *     200 = "Returned when successful",
//     *     401 = "Returned when bad credentials were sent"
//     *   }
//     * )
//     * @param Request $request
//     * @return \FOS\RestBundle\View\View
//     */
//
//    public function getBingoAction(Request $request)
//    {
//        $likeMeId = $request->get('likeMeId', 0);
//        $user = $this->get('security.token_storage')->getToken()->getUser();
//        $userRepo = $this->getDoctrine()->getManager()->getRepository('AppBundle:User');
//
//        if((int)$likeMeId > 0){
//            $result = $userRepo->setSplashShowBingo($likeMeId, $user->getId());
//        }else {
//            $result = $userRepo->getSplashBingo($user);
//        }
//
//        $res['user'] = $result;
//        $res['texts'] = array(
//            'text' => $this->get('translator')->trans('USERNAME אהבתי גם את התמונה שלך'),
//            'send' => $this->get('translator')->trans('שלח הודעה'),
//            'cancel' => $this->get('translator')->trans('המשך ללכת הבא / להתעלם'),
//            'photo' => (is_object($user->getMainPhoto())) ? $user->getMainPhoto()->getFaceWebPath() : $user->getNoPhoto(),
//            'photoMessage' => $this->get('translator')->trans('עליך להעלות תמונה אחת לפחות'),
//        );
//
//        $res['status'] = $this->getUserStatus($user);
//        return $this->view( $res, Response::HTTP_OK);
//    }
//
//
//    /**
//     * @ApiDoc(
//     *   resource = true,
//     *   description = "Get user notifications/Set as read notification",
//     *   parameters = {
//     *     {"name"="id", "dataType"="integer", "required"=false, "description"="Notification id for set as read."}
//     *  },
//     *   statusCodes = {
//     *     200 = "Returned when successful",
//     *     401 = "Returned when bad credentials were sent"
//     *   }
//     * )
//     * @param Request $request
//     * @return \FOS\RestBundle\View\View
//     */
//
//    public function postNotificationAction(Request $request)
//    {
//        $id = $request->get('id', 0);
//        $result = array();
//        $user = $this->get('security.token_storage')->getToken()->getUser();
//        if((int)$id > 0) {
//            $em = $this->getDoctrine()->getManager();
//            $notification = $em->getRepository('AppBundle:UserNotifications')->find($id);
//            if($notification){
//                $notification->setIsRead(true);
//                $em->persist($notification);
//                $em->flush();
//            }
//        }
//        foreach ($user->getNotifications() as $notification){
//            $sendUser = ($notification->getLikeMe()->getUserFrom()->getId() == $user->getId()) ? $notification->getLikeMe()->getUserTo() : $notification->getLikeMe()->getUserFrom();
//            $result[] = array(
//                'id' => $notification->getId(),
//                'isRead' => $notification->getIsRead(),
//                'user_id' => $sendUser->getId(),
//                'username' => $sendUser->getUsername(),
//                'photo' => ($sendUser->getMainPhoto()) ? $sendUser->getMainPhoto()->getFaceWebPath() :  $sendUser->getNoPhoto(),
//                'text' => str_replace('[USERNAME]', $sendUser->getUsername(), $notification->getNotification()->getTemplate()),
//                'bingo' => $notification->getLikeMe()->getIsBingo(),
//                'date' => $notification->getDate()->format('d F Y'),
//            );
//        }
//        $result = array(
//            'users' => $result,
//            'texts' => array(
//                'no_results' => $this->get('translator')->trans('אין תוצאות'),
//                'description' => $this->get('translator')->trans('This is the place where you can learn who liked your photo on The Arena.<br>If someone you like likes you back - both of you will receive a Bingo! notification.'
//                )
//            )
//        );
//
//        return $this->view( $result, Response::HTTP_OK);
//    }
//
//
//    /**
//     * @ApiDoc(
//     *   resource = true,
//     *   description = "Get search",
//     *   parameters = {
//     *     {"name"="advanced", "dataType"="boolean", "required"=false, "description"="Show Advanced search. If 'false' get show Quick search. Default value: 'false'."},
//     *     {"name"="advanced_search[region]", "dataType"="string", "required"=false, "description"="Get Area choices if parameter advanced = 'true'."}
//     *  },
//     *   statusCodes = {
//     *     200 = "Returned when successful",
//     *     401 = "Returned when bad credentials were sent"
//     *   }
//     * )
//     * @param Request $request
//     * @return \FOS\RestBundle\View\View
//     */
//
//    public function getSearchAction(Request $request)
//    {
//        $searchType = new QuickSearchType();
//        $options = array();
//        if($request->get('advanced', false)){
//            $searchType = new AdvancedSearchType();
//            if($request->get('advanced_search', false)) {
//                $options = array(
//                    'do_not_create_ethnicity' => true,
//                    'do_not_create_zodiac' => true,
//                );
//            }
//        }
//        //var_dump($searchType, $options);die;
//        $form = $this->createForm($searchType, new User(), $options);
//        if($request->get('advanced_search', false)) {
//            //$form->handleRequest($request);
//            $form->submit($request->get($form->getName()));
//        }
//        $result = $this->transformForm($form);
//        $result['submit'] = $this->get('translator')->trans('חפש');
//
//        return $this->view( $result, Response::HTTP_OK);
//    }
//
//    public function transformForm($form, $userData = false){
//        $notShow = array('fields' => array(), 'values' => array());
//        if($userData) {
//            if(is_object($userData->getGender()) and $userData->getGender()->getId() == 2){
//                $notShow = array('fields' => array('status','netWorth','income'), 'values' => array('features' => array(3)));
//            }
//        }
//        $formArr = array();
//        foreach ($form->createView()->children as $key => $field) {
//            if(!in_array($key, (array)$notShow['fields'])) {
//                if ($field->vars['block_prefixes'][count($field->vars['block_prefixes']) - 2] == 'repeated') {
//                    foreach ($field as $key2 => $chield) {
//                        $formArr[$key][$key2] = array(
//                            'name' => $chield->vars['full_name'],
//                            /** @Ignore */
//                            'label' => $this->get('translator')->trans($chield->vars['label']),
//                            'type' => $chield->vars['block_prefixes'][count($chield->vars['block_prefixes']) - 2],
//                            'value' => $chield->vars['value'],
//                        );
//                    }
//                } elseif (in_array($field->vars['block_prefixes'][count($field->vars['block_prefixes']) - 2], array('entity','choice'))) {
//                    $choices = array();
////                    if($key == 'heightFrom'){
////                        $choices[] = array(
////                            'value' => '',
////                            'label' => $this->get('translator')->trans('From'),
////                        );
////                        $field->vars['label'] = 'Height';
////                    }
////                    if($key == 'heightTo'){
////                        $choices[] = array(
////                            'value' => '',
////                            'label' => $this->get('translator')->trans('To'),
////                        );
////                    }
//                    if($form->getName() == 'advanced_search' and $key == 'area'){
//                        $areaValues = array();
//                    }
//                    if($key == 'distance'){
//                        $field->vars['label'] = 'Distance';
//                        $choices[] = array(
//                            'value' => '',
//                            'label' => $this->get('translator')->trans('Choose'),
//                        );
//                    }
//
//
//                    $order = ($key == 'city') ? array(1229,509,421,130,61) : array();
//                    if($key == 'city'){ $choices = $order; }
//
//                    foreach ($field->vars['choices'] as $chield) {
//                        $arr = (isset($notShow['values'][$key])) ? $notShow['values'][$key] : array();
//                        if(!in_array($chield->value, $arr)) {
//                            /*if($form->getName() == 'advanced_search' and $key == 'children' and $chield->label == 'Choose'){
//                                $chield->label = '';
//                            }*/
//                        	if(!in_array($chield->value,$order)){
//	                            $choices[] = array(
//	                                'value' => $chield->value,
//	                                'label' => $chield->label,
//	                            );
//                        	}else{
//                        		$k = array_search($chield->value,$order);
//                        		$choices[$k] = array(
//                        				'value' => $chield->value,
//                        				'label' => $chield->label,
//                        		);
//                        	}
//                        }
//                        if($form->getName() == 'advanced_search' and $key == 'area'){
//                            //$areaValues[] = $chield->value;
//                        }
//                    }
//                    //var_dump($field->vars['value']->getId());
//
//                    $value = (is_array($field->vars['value']) && isset($field->vars['value'][0]) ) ? $field->vars['value'][0]->getId() : $field->vars['value'];
//
//                    $formArr[$key] = array(
//
//                        'name' => $field->vars['full_name'],
//                        /** @Ignore */
//                        'label' => $this->get('translator')->trans($field->vars['label']),
//                        'type' => $field->vars['block_prefixes'][count($field->vars['block_prefixes']) - 2],
//                        'value' => $value,
//                        'choices' => $choices,
//                   );
//
//                    if($key == 'ageFrom'){
//                        $formArr[$key]['label0'] = $formArr[$key]['label'];
//                        $formArr[$key]['label'] = $this->get('translator')->trans('From');
//                    }
//                    if($form->getName() == 'advanced_search' and $key == 'area'){
//                        $formArr[$key]['value'] = $areaValues;
//                    }
//                    if($key == 'distance'){
//                        $formArr[$key]['description'] = $this->get('translator')->trans('miles from my Location');
//                    }
//                } else {
//                    if($key == 'withPhoto'){
//                        $field->vars['value'] = 0;
//                    }
//                    $formArr[$key] = array(
//                        'name' => $field->vars['full_name'],
//                        /** @Ignore */
//                        'label' => (
//                            /** @Ignore */ $this->get('translator')->trans($field->vars['label'])
//                        ),
//                        'type' => $field->vars['block_prefixes'][count($field->vars['block_prefixes']) - 2],
//                        'value' => $field->vars['value'],
//                    );
//                }
//            }
//        }
//
//        $formArr['submit'] = $this->get('translator')->trans('שמור');
//
//        return $formArr;
//    }
//
//
//    /**
//     * @ApiDoc(
//     *   resource = true,
//     *   description = "Save note of member",
//     *   parameters = {
//     *     {"name"="text", "dataType"="string", "required"=false, "description"="Note text. Default value: ''."}
//     *  },
//     *   statusCodes = {
//     *     200 = "Returned when successful",
//     *     401 = "Returned when bad credentials were sent"
//     *   }
//     * )
//     * @param Request $request
//     * @param User $member
//     * @return \FOS\RestBundle\View\View
//     */
//
//    public function postNoteAction(Request $request, User $member)
//    {
//        $this->updateList(
//            'Note',
//            $this->get('security.token_storage')->getToken()->getUser(),
//            $member,
//            'create',
//            array('text' => $request->request->get('text',''))
//        );
//
//        return $this->view( array('success' => $this->get('translator')->trans('Your note saved successfully!'),), Response::HTTP_OK);
//    }
//
//
//    /**
//     * @ApiDoc(
//     *   resource = true,
//     *   description = "Add/Remove member to list Favorite/BlackList",
//     *   parameters = {
//     *     {"name"="list", "dataType"="string", "required"=true, "description"="Name of list. (Favorite,BlackList)"},
//     *     {"name"="action", "dataType"="string", "required"=false, "description"="Actions Add/Remove member from list. Default value: 'create'.(delete,create)"},
//     *  },
//     *   statusCodes = {
//     *     200 = "Returned when successful",
//     *     401 = "Returned when bad credentials were sent"
//     *   }
//     * )
//     * @param Request $request
//     * @param User $member
//     * @return \FOS\RestBundle\View\View
//     */
//
//    public function postListsAction(Request $request, User $member)
//    {
//        $list = $request->get('list', false);
//        $action = $request->get('action', 'create');
//        if(empty($action)){ $action = 'create'; }
//        if($list !== 'Favorite' and $list !== 'BlackList'){
//            return $this->view( array('error' => 'Parameter list in not valid!'), Response::HTTP_BAD_REQUEST);
//        }
//        $this->updateList(
//            $list,
//            $this->get('security.token_storage')->getToken()->getUser(),
//            $member,
//            $action
//        );
//
//
//        if($list == 'BlackList') {
//        	$txt = "לרשימת החסומים";
//
//        }else if($list == 'Favorite') {
//        	$txt = "למרשימת המועדפים";
//        }
//
//        $message =  $member->getUsername() . ' ' . (($action == 'create') ? 'נוסף בהצלחה '.$txt : ' הוסר בהצלחה '.$txt);
//        return $this->view( array('test'=> $action,'success' => /** @Ignore */$this->get('translator')->trans($message),), Response::HTTP_OK);
//    }
//
//
//    /*
//     * $entityName - list name;
//     *
//     * */
//    public function updateList($entityName, $owner, $member, $action = 'create', $fields = array())
//    {
//        if($owner->getId() == $member->getId() or ($action !== 'create' and $action !== 'delete')){
//            return;
//        }
//
//        $repo = $this->getDoctrine()->getRepository('AppBundle:' . $entityName);
//        $entity = $repo->findOneBy(array(
//            'owner' => $owner,
//            'member' => $member
//        ));
//
//        $em = $this->getDoctrine()->getManager();
//        if($action == 'delete'){
//            if(null !== $entity){
//                $em->remove($entity);
//                $em->flush();
//            }
//        }else {
//
//            if (null === $entity) {
//                $className = 'AppBundle\Entity\\' . $entityName;
//                $entity = new $className();
//                $entity->setOwner($owner);
//                $entity->setMember($member);
//            } elseif (count($fields) == 0) {
//                return;
//            }
//
//
//            foreach ($fields as $key => $value) {
//                $method = 'set' . ucfirst($key);
//                $entity->$method($value);
//            }
//
//
//            $em->persist($entity);
//            $em->flush();
//        }
//    }
//
//
//    /**
//     * @ApiDoc(
//     *   resource = true,
//     *   description = "Get Freeze form",
//     *   parameters = {
//     *     {"name"="freeze_account_reason", "dataType"="string", "required"=false, "description"="Freeze account reason."}
//     *  },
//     *   statusCodes = {
//     *     200 = "Returned when successful",
//     *     401 = "Returned when bad credentials were sent"
//     *   }
//     * )
//     */
//
//    public function getFreezeAction()
//    {
//        //$user = $this->get('security.token_storage')->getToken()->getUser();
//
//        return $this->view( array(
//            'form' => array(
//                'freeze_account_reason' => array(
//                    'name' => 'freeze_account_reason',
//                    'type' => 'textarea',
//                    'label' => $this->get('translator')->trans('הסיבה להקפיא'),
//                    'value' => ''
//                )
//            ),
//        	'error' => $this->get('translator')->trans('יש להשלים את השדות המסומנים '),
//            'description' => $this->get('translator')->trans('Please note: freezing account does not cancel the subscription fee when you already have one. To cancel the subscription fee freeze has yet to send us an email through the contact page, requesting the cancellation of subscription fee.'),
//
//        ), Response::HTTP_OK);
//    }
//
//
//    /**
//     * @ApiDoc(
//     *   resource = true,
//     *   description = "Freeze account",
//     *   parameters = {
//     *     {"name"="freeze_account_reason", "dataType"="string", "required"=false, "description"="Freeze account reason."}
//     *  },
//     *   statusCodes = {
//     *     200 = "Returned when successful",
//     *     401 = "Returned when bad credentials were sent"
//     *   }
//     * )
//     */
//
//    public function postFreezeAction(Request $request)
//    {
//        $user = $this->get('security.token_storage')->getToken()->getUser();
//        if ($request->get('freeze_account_reason', false)) {
//            $user->setIsFrozen(true);
//            $user->setFreezeReason($request->get('freeze_account_reason', null));
//            $em = $this->getDoctrine()->getManager();
//            $em->persist($user);
//            $em->flush();
//        }
//        return $this->view( array(), Response::HTTP_OK);
//    }
//
//
//    /**
//     * @ApiDoc(
//     *   resource = true,
//     *   description = "Get Report Abuse form",
//     *
//     *   statusCodes = {
//     *     200 = "Returned when successful",
//     *     401 = "Returned when bad credentials were sent"
//     *   }
//     * )
//     */
//
//    public function getReportAbuseAction(Request $request)
//    {
//
//        //send success
//        return $this->view( array(
//            'form' => array(
//                'text' => array(
//                    'name' => $this->get('translator')->trans('text'),
//                    'type' => $this->get('translator')->trans('textarea'),
//                    'label' => $this->get('translator')->trans('Comments'),
//                    'value' => ''
//                ),
//                'buttons' => array(
//                    'submit' => $this->get('translator')->trans('שלח'),
//                    'cancel' => $this->get('translator')->trans('לבטל')
//                )
//        )), Response::HTTP_OK);
//    }
//
//
//    /**
//     * @ApiDoc(
//     *   resource = true,
//     *   description = "Send Report Abuse",
//     *   parameters = {
//     *     {"name"="text", "dataType"="string", "required"=true, "description"="Text Report Abuse."}
//     *  },
//     *   statusCodes = {
//     *     200 = "Returned when successful",
//     *     401 = "Returned when bad credentials were sent"
//     *   }
//     * )
//     */
//
//    public function postReportAbuseAction(Request $request, User $member)
//    {
//        $text = $request->get('text', false);
//        $user = $this->get('security.token_storage')->getToken()->getUser();
//        if(!$text){
//            return $this->view( array('error' => $this->get('translator')->trans('Parameter text in not valid!')), Response::HTTP_OK);
//        }
//
//        $mobileDetector = $this->get('mobile_detect.mobile_detector');
//        if($mobileDetector->isAndroidOS()){
//        	$platform = 'Android App';
//        }
//        if($mobileDetector->isIOS()){
//        	$platform = 'IOS App';
//        }
//
//        $subject = "GreenDate | Desktop | Report Abuse | Username: " . $member->getUsername();
//
//        $body = '<div dir="rtl">';
//        $body .= '
//			שם משתמש: ' . $member->getUsername() . '<br />
//			מספר משתמש: ' . $member->getId() . '<br>
//			טקסט: ' . $text . '<br /><br />
//			משתמש מדווח: ' . $user->getUsername() . '(#' . $user->getId() . ') <br>
//			נשלח מ: ' .$platform;
//        $body .= '</div>';
//
//        $headers = "MIME-Version: 1.0" . "\r\n";
//        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
//        $headers .= 'From: ' . $user->getEmail() . ' <' . $user->getEmail() . '>' . "\r\n";
//
//        $settings = $this->getDoctrine()->getRepository('AppBundle:Settings')->find(1);
//
//        mail($settings->getReportEmail(),$subject,$text,$headers);
//        //mail('albert@interdate-ltd.co.il',$subject,$body,$headers);
//        //send success
//        return $this->view( array( 'text'=> $text,'success' => $this->get('translator')->trans('ההודעה נשלחה לצוות גרינדייט')), Response::HTTP_OK);
//    }
//
//
//    /**
//     * @ApiDoc(
//     *   resource = true,
//     *   description = "Change password",
//     *   parameters = {
//     *     {"name"="change_password", "dataType"="string", "required"=true, "description"="Text Report Abuse."}
//     *  },
//     *   statusCodes = {
//     *     200 = "Returned when successful",
//     *     401 = "Returned when bad credentials were sent"
//     *   }
//     * )
//     * @param Request $request
//     * @return \FOS\RestBundle\View\View
//     */
//
//    public function postPasswordAction(Request $request)
//    {
//        $changed = false;
//        $user = $this->get('security.token_storage')->getToken()->getUser();
//        $form = $this->createForm(new ChangePasswordType(), $user);
//
//        if($request->isMethod('POST') and $request->get('changePassword', false)){
//            $post = $request->request->all();
//            //echo json_encode($post);
//
//            $validOldPassword = false;
//            if(!empty($post['changePassword']['oldPassword'])) {
//                //var_dump($post);die;
//                $originalEncodedPassword = $user->getPassword();
//                $encoder = $this->get('security.encoder_factory')->getEncoder($user);
//
//                $validOldPassword = $encoder->isPasswordValid(
//                    $originalEncodedPassword, // the encoded password
//                    $post['changePassword']['oldPassword'],  // the submitted password
//                    null
//                );
//            }
//            if($validOldPassword){
//                $form->handleRequest($request);
//                if($form->isValid() && $form->isSubmitted()){
//                    $encodedPassword = $encoder->encodePassword($user->getPassword(), null);
//                    $user->setPassword($encodedPassword);
//
//                    $em = $this->getDoctrine()->getManager();
//                    $em->persist($user);
//                    $em->flush();
//                    $changed = true;
//                }
//
//            }
//            else{
//                $form->get('oldPassword')->addError(new FormError('סיסמה ישנה שגויה'));
//                //$form->handleRequest($request);
//            }
//        }
//
//        return $this->view( array(
//            'form' => $this->transformForm($form),
//            'errors' => $form->getErrors(),
//            'changed' => $changed,
//        ), Response::HTTP_OK);
//    }
//
//
//    /**
//     * @ApiDoc(
//     *   resource = true,
//     *   description = "Edit Profile",
//     *   parameters = {
//     *     {"name"="step", "dataType"="integer", "required"=false, "description"="Step of edit profile. Default: '1'."}
//     *  },
//     *   statusCodes = {
//     *     200 = "Returned when successful",
//     *     401 = "Returned when bad credentials were sent"
//     *   }
//     * )
//     * @param Request $request
//     * @return \FOS\RestBundle\View\View
//     */
//
//    public function getEditProfileAction(Request $request){
//        $step = $request->get('step', 1);
//        $user = $this->get('security.token_storage')->getToken()->getUser();
//        switch ($step){
//            case '1':
//                $formType = new ProfileOneType();
//            break;
//            case '2':
//                $formType = new ProfileTwoType();
//            break;
//            case '3':
//                $formType = new ProfileThreeType();
//            break;
//        }
//        $form = $this->createForm($formType, $user, array(
//            //'is_male' => $user->getGender()->getId() == 1
//        ));
//
//        return $this->view(array(
//            'form' => $this->transformForm($form),
//        ), Response::HTTP_OK);
//    }
//
//
//    /**
//     * @ApiDoc(
//     *   resource = true,
//     *   description = "Save Profile fields",
//     *   parameters = {
//     *     {"name"="profile_step[field]", "dataType"="string", "required"=true, "description"="Field name: value for save.(Name: profile_one[] or profile_two[] or profile_three[])"}
//     *  },
//     *   statusCodes = {
//     *     200 = "Returned when successful",
//     *     401 = "Returned when bad credentials were sent"
//     *   }
//     * )
//     * @param Request $request
//     * @return \FOS\RestBundle\View\View
//     */
//
//    public function postEditProfileAction(Request $request){
//
//    	//$user = $this->getUser();
//
//    	$user = $this->get('security.token_storage')->getToken()->getUser();
//    	$userRepo = $this->getDoctrine()->getRepository('AppBundle:User');
//    	$post = $request->get('profile_one',false);
//
//    	$errors = false;
//
//        if($post) {
//            $formType = new ProfileOneType();
//            $postKey = 'profileOne';
//        }elseif(!$post){
//            $post = $request->get('profile_two',false);
//            $formType = new ProfileTwoType();
//            $postKey = 'profileTwo';
//        }
//        if(!$post){
//            $post = $request->get('profile_three',false);
//            $formType = new ProfileThreeType();
//            $postKey = 'profileThree';
//        }
//
//
//        $form = $this->createForm($formType, $user);
//
//        $post = $userRepo->removeWordsBlocked($post, array('username','occupation','about','looking'));
//        $request->request->set($postKey, $post);
//
//        $form->handleRequest($request);
//
//        //return $this->view($form->isValid(), Response::HTTP_OK);
//
//        if($form->has('email')) {
//        	$emailInBlocked = $this->getDoctrine()->getManager()->getRepository('AppBundle:EmailBlocked')->findOneByValue(strtolower($form->get('email')->get('first')->getData()));
//        	if($emailInBlocked) {
//        		$form->get('email')->get('first')->addError(new FormError('Email already exists in the system'));
//        		$errors = true;
//        	}
//        }
//        if($form->has('phone')) {
//        	$phoneInBlocked = $this->getDoctrine()->getManager()->getRepository('AppBundle:PhoneBlocked')->findOneByValue($form->get('phone')->getData());
//        	if($phoneInBlocked) {
//        		$form->get('phone')->addError(new FormError('Phone already exists in the system'));
//        		$errors = true;
//        	}
//        }
//
//        //looking
//        if($form->has('about') and empty($post['about'])){
//            $form->get('about')->addError(new FormError($this->get('translator')->trans('Min 10 letters in About Me')));
//            $errors = true;
//        }
//        //looking
//        if($form->has('looking') and empty($post['looking'])){
//            $form->get('looking')->addError(new FormError($this->get('translator')->trans('Min 10 letters in What I\'m Looking For')));
//            $errors = true;
//        }
//        //hobbies
//        if($form->has('hobbies') and count((array)$post['hobbies']) == 0){
//            $form->get('hobbies')->addError(new FormError($this->get('translator')->trans('Please choose Hobbies')));
//            $errors = true;
//        }
//
//        //$fieldName = array_keys($post)[0];
//        if(!$errors and $form->isSubmitted() /*and $form->isValid()*/){
//
//            $em = $this->getDoctrine()->getManager();
//            $em->persist($user);
//            $em->flush();
//
//        }
//        else{
//            $errorsText = $form->getErrors();
//        }
//
//
//        $formOut = $this->createForm($formType, $user, array(
//            //'is_male' => $user->getGender()->getId() == 1
//        ));
//
//        $texts = array(
//            'textSuccess' => $this->get('translator')->trans('השינויים נשמרו')
//        );
//
//
//        return $this->view(array(
//            'form' => $this->transformForm($formOut),
//            'errors' => $form->getErrors(),
//            'texts' => $texts,
//        	'post' => json_encode($post),
//        ), Response::HTTP_OK);
//    }
//
//
//    /**
//     * @ApiDoc(
//     *   resource = true,
//     *   description = "Save Locations",
//     *   parameters = {
//     *     {"name"="latitude", "dataType"="string", "required"=true, "description"="Location latitude"},
//     *     {"name"="longitude", "dataType"="string", "required"=true, "description"="Location longitude"}
//     *  },
//     *   statusCodes = {
//     *     200 = "Returned when successful",
//     *     401 = "Returned when bad credentials were sent"
//     *   }
//     * )
//     * @param Request $request
//     * @return \FOS\RestBundle\View\View
//     */
//
//    public function postLocationsAction(Request $request){
//
//        $latitude = $request->get('latitude', null);
//        $longitude = $request->get('longitude', null);
//
//        if($latitude != null and $longitude != null) {
//            $user = $this->get('security.token_storage')->getToken()->getUser();
//            $user->setLatitude($latitude);
//            $user->setLongitude($longitude);
//            $em = $this->getDoctrine()->getManager();
//            $em->persist($user);
//            $em->flush();
//
//            return $this->view($user->getUsername(), Response::HTTP_OK);
//
//        }
//        return $this->view(1, Response::HTTP_OK);
//    }
//
//
//
//
//}
