<?php

namespace AppBundle\Controller\Api\V1;

use AppBundle\Entity\ArenaDislike;
use AppBundle\Entity\ShowPhoto;
use AppBundle\Entity\TextAfterPayment;
use AppBundle\Entity\TextBeforePayment;
use AppBundle\Entity\UserMessengerNotifications;
use AppBundle\Entity\Verify;
use AppBundle\Form\Type\ContactType;
use AppBundle\Form\Type\ProfileOneType;
use AppBundle\Form\Type\ProfileOneApiType;
use AppBundle\Form\Type\ProfileTwoApiType;
use AppBundle\Form\Type\ProfileTwoApiV3Type;
use AppBundle\Form\Type\ProfileTwoType;
use AppBundle\Form\Type\ProfileThreeType;
use AppBundle\Form\Type\ChangePasswordType;
use AppBundle\Form\Type\QuickSearchApiType;
use AppBundle\Services\Messenger\Messenger;
use Doctrine\ORM\EntityManager;
use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Entity\User;
use AppBundle\Entity\Photo;
use AppBundle\Form\Type\AdvancedSearchType;
use AppBundle\Form\Type\QuickSearchType;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\File\UploadedFile;

define('MAX_ACTIVATION_EMAILS', 6);

#use Symfony\Component\BrowserKit\Request;

session_write_close();

class UserController extends FOSRestController
{

    /**
     * @ApiDoc(
     *   description = "Get Users Results",
     *   parameters={
     *      {"name"="action", "dataType"="string", "required"=true, "description"="Action or Name of Page (online, new, list, search, arena)"},
     *      {"name"="page", "dataType"="string", "required"=false, "description"="Page number (default value: 1)"},
     *      {"name"="per_page", "dataType"="string", "required"=false, "description"="Total number results on one page (default value: 10)"},
     *      {"name"="list", "dataType"="string", "required"=false, "description"="List name. Required if action=='list'. (favorited, favorited_me, viewed, viewed_me, connected, connected_me, black)"},
     *
     *      {"name"="quick_search", "dataType"="string", "required"=false, "description"="Parameters from quick search form for action: 'search'"},
     *      {"name"="advanced_search", "dataType"="string", "required"=false, "description"="Parameters from advanced search form for action: 'search'"},
     *      {"name"="user_id", "dataType"="integer", "required"=false, "description"="First user id in results for action: 'arena'"},
     *
     *     {"name"="filter", "dataType"="string", "required"=false, "description"="Filter for search. (distance,new,photo,lastActivity,popularity)"}
     *
     *   },
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     401 = "Returned when bad credentials were sent"
     *   }
     * )
     * @param Request $request
     *
     * @return \FOS\RestBundle\View\View
     */


    public function postUsersResultAction(Request $request)
    {
        $action = $request->get('action', false);
        $page = $request->get('page', 1);
        $usersRepo = $this->getDoctrine()->getManager()->getRepository('AppBundle:User');
        $settings = $this->getDoctrine()->getManager()->getRepository('AppBundle:Settings')->find(1);
        $per_page = $request->get('per_page', 30);//$settings->getUsersPerPage();
        $currentUser = $this->get('security.token_storage')->getToken()->getUser();
        $paginator = $this->get('knp_paginator');
        $filter = $request->get('filter', false);
        $users = array();
        if ($action) {
            switch ($action) {
                case 'online':

                    if (!$filter) {
                        $filter = 'lastActivity';
                    }
                    $users = $usersRepo->getOnline(
                        array(
                            'current_user' => $currentUser,
                            'data' => array('filter' => $filter),
                            'paginator' => $paginator,
                            'page' => $page,
                            'per_page' => $per_page,
                            'considered_as_online_minutes_number' => $settings->getUserConsideredAsOnlineAfterLastActivityMinutesNumber(),
                        )
                    );
                    break;
                case 'search':
                    $quickSearch = $request->get('quick_search', null);
                    $advancedSearch = $request->get('advanced_search', null);
                    $data = ($quickSearch !== null) ? $quickSearch : $advancedSearch;
                    $data['filter'] = '';
                    if ($filter != false) {
                        $data['filter'] = $filter;
                    } else {
                        $filter = $data['filter'];
                    }

                    if ($data !== null) {
                        $users = $usersRepo->search(array(
                            'current_user' => $currentUser,
                            'data' => $data,
                            'paginator' => $paginator,
                            'page' => $page,
                            'per_page' => $per_page,
                        ));
                    }
                    break;
                case 'list':

                    $list = $request->get('list', null);
                    switch ($list) {
                        case 'favorited':
                            $inverse_list = 'favoritedMe';
                            $type = 'owner';
                            break;
                        case 'favorite_me':
                            $inverse_list = 'favorited';
                            $type = 'member';
                            break;
                        case 'viewed':
                            $inverse_list = 'viewedMe';
                            $type = 'owner';
                            break;
                        case 'viewed_me':
                            $inverse_list = 'viewed';
                            $type = 'member';
                            break;
                        case 'connected':
                            $inverse_list = 'connectedMe';
                            $type = 'owner';
                            break;
                        case 'connected_me':
                            $inverse_list = 'connected';
                            $type = 'member';
                            break;

                        case 'black':
                            $inverse_list = 'blackListedMe';
                            $type = 'owner';
                            break;
                    }

                    $users = $usersRepo->getList(array(
                        'current_user' => $currentUser,
                        'request_data' => null,
                        'paginator' => $paginator,
                        'page' => $page,
                        'per_page' => $per_page,
                        'inverse_list' => $inverse_list,
                        'type' => $type,
                    ));
                    break;
                case 'new':
                    if (!$filter) {
                        $filter = 'new';
                    }
                    $users = $usersRepo->getNew(
                        array(
                            'considered_as_new_days_number' => $settings->getUserConsideredAsNewAfterDaysNumber(),
                            'paginator' => $paginator,
                            'data' => array('filter' => $filter),
                            'page' => $page,
                            'per_page' => $per_page,
                            'current_user' => $currentUser,
                        )
                    );
                    break;
                case 'arena':
                    $id = $request->get('user_id', 0);
                    $firstUser = ((int)$id > 0 && (int)$id !== $currentUser->getId()) ? $usersRepo->find($id) : false;
                    $users = $usersRepo->getUsersForLikeNew($currentUser, $firstUser, 50);
                    break;
            }
        }

        return /*is_object($users) ?*/ $this->view(
            $this->transformUsers($users, $settings, $filter)
            , Response::HTTP_OK);
    }


    public function transformUsers($users, $settings, $filter = false)
    {
        $currentUser = $this->get('security.token_storage')->getToken()->getUser();
        $usersRepo = $this->getDoctrine()->getManager()->getRepository('AppBundle:User');
        $newUsers = array();

        $texts = [
            'like' => $this->get('translator')->trans('Like'),
            'message' => $this->get('translator')->trans('Send'),
            'no_results' => $this->get('translator')->trans('No results'),
            'add' => $this->get('translator')->trans('Add'),
            'remove' => $this->get('translator')->trans('Remove'),
            'unblock' => $this->get('translator')->trans('Unlock'),
        ];

        if (is_array($users) and isset($users['photos'])) {

            if ($users['photos'] == 0) {
                return array(
                    'users' => $newUsers,
                    'texts' => $texts,
                    'arenaStatus' => $this->get('translator')->trans('You must upload at least 1 photo for enter the arena'),
                );
            }

            foreach (array('online', 'other') as $key) {
                foreach ($users[$key] as $user) {
                    $user['age'] = date_diff(date_create($user['birthday']), date_create('today'))->y;
                    $user['image'] = $this->getParameter('base_url') . '/media/photos/' . $user['id'] . '/' . $user['imageId'] . '.' . $user['ext'];
                    $newUsers[] = $user;
                }
            }
        } else {
            foreach ($users as $user) {
                if (!is_object($user)) {
                    $user = $usersRepo->find($user['id_0']);
                }
                $genders = $user->getContactGender();

                if (is_object($user) /*&& in_array($currentUser->getGender()->getId(), $canContact)*/) {
                    $distance = $usersRepo->getDistance($currentUser, $user);
                    $distance = ($distance == null) ? '' : $distance . ' ' . $this->get('translator')->trans('km');

                    $updated = '0';
                    if (is_object($user->getMainPhoto())) {
                        $mainPhoto = $user->getMainPhoto()->getFaceWebPath();
                        $updated = $user->getMainPhoto()->getUpdated();
                    } else {
                        $mainPhoto = $user->getNoPhoto();
                    }
                    $fotoMain = (is_object($user->getMainPhoto())) ? $user->getMainPhoto()->getWebPath() : $user->getNoPhoto();
                    $newUsers[] = array(
                        'id' => $user->getId(),
                        'isPaying' => $user->isPaying(),
                        'isNew' => $user->isNew($settings->getUserConsideredAsNewAfterDaysNumber()),
                        'isOnline' => $user->isOnline($settings->getUserConsideredAsOnlineAfterLastActivityMinutesNumber()),
                        'isVerify' => $user->getIsVerify(),
                        'isAddLike' => $currentUser->isAddLike($user),
                        'isAddFavorite' => $currentUser->isAddFavorite($user->getId()),
                        'isAddBlackListed' => $currentUser->isAddBlackListed($user->getId()),
                        'url' => is_object($fotoMain) ? $fotoMain->getWebPath() : '',//$liip_imagine->getBrowserPath($fotoMain, 'full_mobile_photo'),
                        'photo' => $this->getParameter('base_url') . $mainPhoto . '?r=' . $updated,
                        'username' => $user->getUsername(),
                        'age' => $user->age(),
                        'distance' => $distance,
                        'country_name' => $user->getCountry()->getName(),
                        'region_name' => $user->getRegion()->getName(),
                        'area_name' => $user->getCity()->getName(),
                        'gender' => $user->getGender()->getId(),
                        'canWriteTo' => $this->get('messenger')->CheckIfCanWriteTo($currentUser, $user),
                        'textCantWrite' => $this->get('translator')->trans('You can not message this user because of his profile settings')
                    );
                }
            }
        }
        $filters = array(
            array(
                'label' => $this->get('translator')->trans('Distance'),
                'value' => 'distance',
            ),
            array(
                'label' => $this->get('translator')->trans('New users'),
                'value' => 'new',
            ),
            array(
                'label' => $this->get('translator')->trans('With photo'),
                'value' => 'photo',
            ),
            array(
                'label' => $this->get('translator')->trans('Last activity'),
                'value' => 'lastActivity',
            ),
            array(
                'label' => $this->get('translator')->trans('Popularity'),
                'value' => 'popularity',
            ),
            array(
                'label' => $this->get('Translator')->trans('Verified user'),
                'value' => 'verifiedUser',
            )
        );
        return array(
            'users' => $newUsers,
            'texts' => $texts,
            'filters' => $filters,
            'filter' => $filter
        );
    }

    /**
     * @ApiDoc(
     *   resource = true,
     *   description = "Save settings",
     *   parameters={
     *      {"name"="is_sent_email", "dataType"="string", "required"=false, "description"="Sent to user notification by E-mail"},
     *      {"name"="is_sent_push", "dataType"="string", "required"=false, "description"="Sent to user push notification."},
     *
     *   },
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     401 = "Returned when bad credentials were sent"
     *   }
     * )
     */

    public function postSettingsAction(Request $request)
    {

        $user = $this->getUser();
        $post = $request->request->all();
        $success = '';
        if (isset($post['is_sent_email']) or isset($post['is_sent_push'])) {

            $is_sent_email = isset($post['is_sent_email']) ? (boolean)$post['is_sent_email'] : $user->getIsSentEmail();
            $is_sent_push = isset($post['is_sent_push']) ? (boolean)$post['is_sent_push'] : $user->getIsSentPush();
            $user->setIsSentEmail($is_sent_email);
            $user->setIsSentPush($is_sent_push);
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();
            $success = $this->get('translator')->trans('Settings updated');

        }
        return $this->view(array(
            'form' => array(
                'is_sent_email' => array(
                    'label' => $this->get('translator')->trans('send me emails'),
                    'value' => $user->getIsSentEmail(),
                    'type' => 'checkbox',
                    'name' => 'is_sent_email',
                ),
                'is_sent_push' => array(
                    'label' => $this->get('translator')->trans('send me push notifications'),
                    'value' => $user->getIsSentPush(),
                    'type' => 'checkbox',
                    'name' => 'is_sent_push',
                ),
                'submit' => $this->get('translator')->trans('save'),
            ),
            'success' => $success,
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
     */

    public function postLoginAction()
    {

        $user = $this->get('security.token_storage')->getToken()->getUser();
        $user->setIp($_SERVER['REMOTE_ADDR']);
        $user->setIsFrozen(0);
        $mobileDetector = $this->get('mobile_detect.mobile_detector');
        if ($mobileDetector->isAndroidOS()) {
            $user->setLoginFrom($loginFromRepo->find(6));
        }
        if ($mobileDetector->isIOS()) {
            $user->setLoginFrom($loginFromRepo->find(5));
        }
        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();
        $status = $this->getUserStatus($user);
        return $this->view(array(
            'status' => $status,
            'photo' => (is_object($user->getMainPhoto())) ? $user->getMainPhoto()->getFaceWebPath() : $user->getNoPhoto(),
            'id' => $user->getId(),
            'isMan' => $user->isMan(),
            'texts' => array(
                'photoMessage' => $this->get('translator')->trans('You need to upload at least one photo'),
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
     *   description = "Get User Statistics",
     *
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     401 = "Returned when bad credentials were sent"
     *   }
     * )
     */

    public function getStatisticsActions()
    {
        $user = $this->getUser();
        $settings = $this->getDoctrine()->getManager()->getRepository('AppBundle:Settings')->find(1);
        $delay = new \DateTime();
        $delay->setTimestamp(strtotime(
                $settings->getUserConsideredAsOnlineAfterLastActivityMinutesNumber() . ' minutes ago')
        );
        $is_new_delay = new \DateTime();
        $is_new_delay->setTimestamp(strtotime(
                $settings->getUserConsideredAsNewAfterDaysNumber() . ' days ago')
        );
        $conn = $this->getDoctrine()->getManager()->getConnection();
        return $this->view(array('statistics' =>
            $conn->query("CALL get_statistics ('"
                . $delay->format('Y-m-d H:i:s.000000') . "', '"
                . $user->getId() . "', '"
                . $user->getGender()->getId() . "', '"
                . $is_new_delay->format('Y-m-d H:i:s.000000') . "')")
                ->fetch(),
            'isActivated' => $user->getIsActivated(),
            'isPay' => true, //$user->isPaying(),
            'isMan' => $user->isMan(),
            'gender' => $user->getGender()->getId(),
            'mainPhoto' => $user->getMainPhoto() ? $user->getMainPhoto()->getFaceWebPath() : $user->getNoPhoto(),
        ), Response::HTTP_OK);
    }

    public function getUserStatus($user)
    {
        $status = 'login';
        return $status;
    }


    /**
     * @ApiDoc(
     *   resource = true,
     *   description = "Get logged user data",
     *
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     401 = "Returned when bad credentials were sent"
     *   }
     * )
     * @param User $user
     * @param Request $request
     * @return \FOS\RestBundle\View\View
     */


    public function getUserAction(User $user, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $currentUser = $this->getUser();
        $usersRepo = $em->getRepository('AppBundle:User');
        $settings = $em->getRepository('AppBundle:Settings')->find(1);

        $user->setViews($user->getViews() + 1);

        $distance = $usersRepo->getDistance($currentUser, $user);
        $distance = ($distance == '0') ? '' : $distance . ' ' . 'km';

        $em->persist($user);
        $em->flush();
        $this->updateList('View', $currentUser, $user);


        $repository = $em->getRepository(ShowPhoto::class);
        $photoStatus = $this->get('translator')->trans('notSent');
        if ($sendRequest = $repository->findBy(array('member' => $user->getId(), 'owner' => $currentUser->getId()))) {
            $photoStatus = $this->get('translator')->trans('waiting');
            if ($sendRequest[0]->getIsAllow()) {
                $photoStatus = $this->get('translator')->trans('allow');
            } else if ($sendRequest[0]->getIsCancel()) {
                $photoStatus = $this->get('translator')->trans('cancel');
            }

        }

        $photos = $this->getAllUserPhotos($user, false, $photoStatus);

        $there_for = '';
        $lookingFors = $user->getLookingFor();

        foreach ($lookingFors as $lookingFor) {
            $there_for .= $there_for == '' ? $lookingFor->getName() : ', ' . $lookingFor->getName();
        }

        $res = array(
            'id' => $user->getId(),
            'isPaying' => $user->isPaying(),
            'isNew' => $user->isNew($settings->getUserConsideredAsNewAfterDaysNumber()),
            'isOnline' => $user->isOnline($settings->getUserConsideredAsOnlineAfterLastActivityMinutesNumber()),
            'isAddLike' => $currentUser->isAddLike($user),
            'isAddFavorite' => $currentUser->isAddFavorite($user->getId()),
            'isAddBlackListed' => $currentUser->isAddBlackListed($user->getId()),
            'isAddVerify' => $usersRepo->get_is_add_verify($currentUser, $user),
            'isVerify' => $user->getIsVerify(),
            'photos' => $photos,
            'username' => $user->getUsername(),
            'canWriteTo' => $this->get('messenger')->CheckIfCanWriteTo($currentUser, $user),
            'textCantWrite' => $this->get('translator')->trans('you may not contact this user due to their settings'),
            'age' => $user->age(),
            'form' => array(
                'gender' => array('label' => $this->get('translator')->trans('Gender'), 'value' => $user->getGender()->getName()),
                'country_name' => array('label' => $this->get('translator')->trans('Country'), 'value' => $user->getCountry()->getName()),
                'region_name' => array('label' => $this->get('translator')->trans('Region'), 'value' => $user->getRegion()->getName()),
                'city_name' => array('label' => $this->get('translator')->trans('City'), 'value' => $user->getCity()->getName()),
                'relationshipStatus' => array('label' => $this->get('translator')->trans('Status'), 'value' => $user->getRelationshipStatus()->getName()),
                'relationshipType' => array('label' => $this->get('translator')->trans('I`m in'), 'value' => $user->getRelationshipType()->getName()),
                'sexOrientation' => array('label' => $this->get('translator')->trans('Sexual orientation'), 'value' => $user->getSexorientation()->getName()),
                'smoking' => array('label' => $this->get('translator')->trans('Smoking'), 'value' => $user->getSmoking()->getName()),
                'body' => array('label' => $this->get('translator')->trans('Body'), 'value' => $user->getBody() ? $user->getBody()->getName() : false),
                'height' => array('label' => $this->get('translator')->trans('Height'), 'value' => $user->getHeight(true)),
                'lookingFor' => array('label' => $this->get('translator')->trans('Here for'), 'value' => $there_for),
                'origin' => array('label' => $this->get('translator')->trans('Origin'), 'value' => $user->getOrigin() ? $user->getOrigin()->getName() : false),
                'children' => array('label' => $this->get('translator')->trans('Children`s'), 'value' => $user->getChildren() ? $user->getChildren()->getName() : null),
                'religion' => array('label' => $this->get('translator')->trans('Religion'), 'value' => $user->getReligion() ? $user->getReligion()->getName() : null),
                'nutrition' => array('label' => $this->get('translator')->trans('Nutrition'), 'value' => $user->getNutrition() ? $user->getNutrition()->getName() : null),
                'zodiac' => array('label' => $this->get('translator')->trans('Zodiac'), 'value' => $user->getZodiac()),
                'about' => array('label' => $this->get('translator')->trans('About me'), 'value' => $user->getAbout()),
                'looking' => array('label' => $this->get('translator')->trans('What im looking for'), 'value' => $user->getLooking()),
                'distance' => $distance,
            ),
            'formReportAbuse' => array(
                'title' => $this->get('translator')->trans('Invalid card report'),
                'text' => array(
                    'name' => $this->get('translator')->trans('text'),
                    'type' => $this->get('translator')->trans('textarea'),
                    'label' => $this->get('translator')->trans('Notes'),
                    'value' => $this->get('translator')->trans(''),
                ),
                'buttons' => array(
                    'submit' => $this->get('translator')->trans('Send'),
                    'cancel' => $this->get('translator')->trans('Cancel'),
                )
            ),
            'texts' => array(
                'lock' => $this->get('translator')->trans('Block'),
                'unlock' => $this->get('translator')->trans('Unblock'),
                'allow' => $this->get('translator')->trans('You can see<br>private photos'),
                'cancel' => $this->get('translator')->trans('Your ask rejected'),
                'waiting' => $this->get('translator')->trans('Waiting for answer'),
                'notSent' => $this->get('translator')->trans('Click to ask see confidential photos'),
                'privatePhoto' => $this->get('translator')->trans('Private photo'),
            ),
            'photoStatus' => $photoStatus,
            'noPhoto' => $this->getParameter('base_url') . $user->getNoPhoto(),
        );

        if (!empty($hobbies)) {
            $res['hobbies'] = array('label' => $this->get('translator')->trans('Hobbies'), 'value' => $hobbies);
        }
        return $this->view($res, Response::HTTP_OK);
    }


    /**
     * @ApiDoc(
     *   resource = true,
     *   description = "Get user photos",
     *
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     401 = "Returned when bad credentials were sent"
     *   }
     * )
     * @param Request $request
     * @param bool $json
     * @return array|\FOS\RestBundle\View\View
     */

    public function getPhotosAction(Request $request, $json = true)
    {

        $getUser = $request->get('getUser', false);
        $user = ($getUser) ?: $user = $this->get('security.token_storage')->getToken()->getUser();

        $userPhotos = $this->getAllUserPhotos($user, true);//array();

        $texts = array(
            'approved' => $this->get('translator')->trans('approved'),
            'status' => $this->get('translator')->trans('status'),
            'delete' => $this->get('translator')->trans('remove'),
            'setPrivate' => $this->get('translator')->trans('make private'),
            'unsetPrivate' => $this->get('translator')->trans('make public'),
            'cancel' => $this->get('translator')->trans('cancel'),
            'add_photo' => $this->get('translator')->trans('add picture'),
            'deletePhoto' => $this->get('translator')->trans('Delete picture?'),
            'agreeOnHomePage' => $this->get('translator')->trans('I allow my picture to be shown on the main page.'),
            'set_as_main_photo' => $this->get('translator')->trans('make main picture'),
            'choose_from_camera' => $this->get('translator')->trans('chose from camera'),
            'choose_from_gallery' => $this->get('translator')->trans('choose from gallery'),
            'register_end_button' => $this->get('translator')->trans('done'),
            'waiting_for_approval' => $this->get('translator')->trans('awaiting approval'),
            'description' => $this->get('translator')->trans('<br>
If you encounter any problem uploading a picture, please send the image to: 
<a href="mailto:'.$this->getParameter('contact_email').'">'.$this->getParameter('contact_email').'</a><br/>
Please mention the email you used to sign up to the site<br>
<h5>Images must follow these rules</h5>
<ul>
<li>The picture must feature you</li>
<li>The picture must be of good quality</li>
<li>No nudity or partial nudity</li>
<li>No pictures with children</li>
</ul>
<p>
All pictures must be approved by the site admins. Our office hours are Sunday- Thursday, 07:00-14:00 UTC
</p>'),
        );
        if (!$json) {
            return array('photos' => $userPhotos, 'texts' => $texts, 'noPhoto' => $user->getNoPhoto());
        }

        return $this->view(array(
            'photos' => $userPhotos,
            'texts' => $texts,
            'noPhoto' => $user->getNoPhoto(),
            'showOnHomepage' => $user->getIsOnHomePage(),
        ), Response::HTTP_OK);
    }

    public function getAllUserPhotos($user, $is_curent_user = false, $status = false)
    {

        $allowedToViewPrivatePhoto = $status && $status === 'allow';
        $base_url = $this->getParameter('base_url');

        $userPhotos = array(array('cropedImage' => $base_url . $user->getNoPhoto(), 'url' => $user->getNoPhoto()));
        if (count($user->getPhotos()) > 0) {
            $userPhotos = array();
            foreach ($user->getPhotos() as $photo) {
                if ($is_curent_user || $photo->getIsValid()) {

                    $fullImage = $base_url .
                        (($allowedToViewPrivatePhoto || $is_curent_user || !$photo->getIsPrivate())
                            ? $photo->getWebPath()
                            : $user->getNoPhoto());

                    $photo_array = array(
                        'id' => $photo->getId(),
                        'isMain' => $photo->getIsMain(),
                        'isValid' => $photo->getIsValid(),
                        'isPrivate' => $photo->getIsPrivate(),
                        'face' => $photo->getFaceWebPath(),
                        'cropedImage' => $fullImage,
                        'fullImage' => $fullImage,
                        'statusText' => $photo->getIsValid() ? $this->get('translator')->trans('approved picture') : $this->get('translator')->trans('awaiting approval '),
                    );
                    if ($photo->getIsMain()) {
                        array_unshift($userPhotos, $photo_array);
                    } else {
                        $userPhotos[] = $photo_array;
                    }
                }
            }
        }
        return $userPhotos;
    }


    /**
     * @ApiDoc(
     *   resource = true,
     *   description = "User photo action",
     *   parameters={
     *      {"name"="photo", "dataType"="file", "required"=false, "description"="Image file to upload"},
     *      {"name"="delete", "dataType"="string", "required"=false, "description"="Image id for detete photo"},
     *      {"name"="setMain", "dataType"="string", "required"=false, "description"="Image id for set as main photo"},
     *
     *   },
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     401 = "Returned when bad credentials were sent"
     *   }
     * )
     */

    public function postPhotoAction(Request $request)
    {

        $user = $this->get('security.token_storage')->getToken()->getUser();
        $uploadedPhoto = $request->files->get('photo');
        $em = $this->getDoctrine()->getManager();

        $requestStage = '';
        $photoPath = '';


        if ($uploadedPhoto instanceof UploadedFile) {

            $requestStage = 'photo uploaded\n';
            if (count($user->getPhotos()) < 8 || ($user->isPay() && count($user->getPhotos()) < 16)) {
                $isMain = !$user->getMainPhoto(true);
                $isValid = false;


                $requestStage .= 'creating new photo\n';
                $photo = new Photo();
                $photo->setUser($user);
                $photo->setFile($uploadedPhoto);
                $photo->setIsValid($isValid);
                $photo->setIsMain($isMain);


                $em->persist($photo);
                $em->flush();

                $user->addPhoto($photo);
                $em->persist($user);
                $em->flush();

                $params = $photo->testDetectFace($request->getHost());

                if (null !== $params) {
                    $requestStage .= 'saving new photo\n';
                    $face = $this->getFace($photo, $params);
                    $photoPath = $photo->getFaceAbsolutePath();


                    $this->savePhoto($face, $photoPath);

                    $optimized = $this->applyFilterToPhoto('optimize_face', $photo->getFaceWebPath());
                    $this->savePhoto($optimized, $photo->getFaceAbsolutePath());
                }

                $requestStage .= 'saving with no params\n';
                $photoPath .= ' and the webpath is: ' . $photo->getWebPath(false);
                $optimized = $this->applyFilterToPhoto('optimize_original', $photo->getWebPath(false));
                $requestStage .= ' with the result of: ' . $this->savePhoto($optimized, $photo->getAbsolutePath());
            } else {
                return array(
                    'success' => false,
                    'errorMessage' => $this->get('translator')->trans('You may upload up to' . $user->isPay() ? 16 : 8 . ' photos'),
                );
            }

        }
        if ($request->get('delete', false)) {
            $photo = $this->getDoctrine()->getRepository('AppBundle:Photo')->find($request->get('delete', false));
            if ($user->getId() == $photo->getUser()->getId()) {

                $wasMainPhoto = $photo->getIsMain();
                $user->removePhoto($photo);
                $em->remove($photo);
                $em->flush();


                $photos = $user->getPhotos();
                if (count($photos) > 0) {
                    if ($wasMainPhoto) {
                        foreach ($photos as $userPhoto) {
                            if ($userPhoto->getIsValid() && !$userPhoto->getIsPrivate()) {
                                $userPhoto->setIsMain(true);
                                $em->persist($userPhoto);
                                $em->flush();
                                $wasMainPhoto = false;
                                break;
                            }
                        }
                    }
                    if ($wasMainPhoto) {
                        foreach ($photos as $userPhoto) {
                            $userPhoto->setIsMain(true);
                            $userPhoto->setIsPrivate(false);
                            $em->persist($userPhoto);
                            $em->flush();
                            break;
                        }
                    }
                }
            }
        }

        if ($request->get('setMain', false)) {
            $photo = $this->getDoctrine()->getRepository('AppBundle:Photo')->find($request->get('setMain', false));
            if ($user->getId() == $photo->getUser()->getId()) {
                if ($photo->getIsPrivate()) {
                    return array(
                        'success' => false,
                        'errorMessage' => $this->get('translator')->trans('Primary pic may not be private')
                    );
                }

                $photos = $user->getPhotos();
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
        } else if ($request->get('setPrivate', false)) {
            $photo = $this->getDoctrine()->getRepository('AppBundle:Photo')->find($request->get('setPrivate', false));
            if ($user->getId() == $photo->getUser()->getId()) {
                if ($photo->getIsMain()) {
                    return array(
                        'success' => false,
                        'errorMessage' => $this->get('translator')->trans('Primary pic may not be private'),
                    );
                }
                $photo->setIsPrivate(!$photo->getIsPrivate());
                $em->persist($photo);
                $em->flush();
            }
        }
        return $this->view(array('success' => true,'path' => isset($photoPath) ? $photoPath : '', 'message' => $requestStage), Response::HTTP_OK);
    }

    private function applyFilterToPhoto($filterName, $webPath)
    {
        $dataManager = $this->container->get('liip_imagine.data.manager');
        $image = $dataManager->find($filterName, $webPath);
        return $this->container->get('liip_imagine.filter.manager')->applyFilter($image, $filterName)->getContent();
    }

    public function savePhoto($photo, $path)
    {
        $f = fopen($path, 'w');
        $res = fwrite($f, $photo);
        fclose($f);
        return $res;
    }

    private function getFace(Photo $photo, $params)
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


    /**
     * @ApiDoc(
     *   resource = true,
     *   description = "Get form and texts for user phone or email activation",
     *
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     401 = "Returned when bad credentials were sent"
     *   }
     * )
     */
    public function getActivationAction()
    {
        $sent = [];
        $user = $this->getUser();
        if ($user->getCodeCount() == 0 && !$user->getIsActivated()) {
//            $this->sendSms($user);
            $sent = $this->sendActivationEmail($user);
        }
        return array(
            'texts' => array(
                'title' => $this->get('translator')->trans('An activation code has been sent to the email address: '.$user->getEmail().'Please fill in the activation code you received in your email'),
                'send' => $this->get('translator')->trans('send'),
                'resend' => $this->get('translator')->trans('Click to have a new code sent'),
                'onlyOnce' => $this->get('translator')->trans(''),
                'problems' => $this->get('translator')->trans('Having a problem with activation? Contact us'),
                'sendSuccess' => $this->get('translator')->trans('Message sent to site admins'),
                'phone' => array(
                    'header' => $this->get('translator')->trans('update your phone number '),
                    'subheader' => $this->get('translator')->trans('if you did not receive an activation code to your phone, please retype your number here'),
                    'label' => $this->get('translator')->trans('phone'),
                    'cancel' => $this->get('translator')->trans('correct phone, send'),
                    'update' => $this->get('translator')->trans('save and send'),
                ),
                'email' => array(
                    'header' => $this->get('translator')->trans('Update your email address'),
                    'subheader' => $this->get('translator')->trans('if you did not receive an activation code to your email, please retype your address here'),
                    'label' => $this->get('translator')->trans('email'),
                    'cancel' => $this->get('translator')->trans('correct mail, send'),
                    'update' => $this->get('translator')->trans('save and send'),
                ),
            ),
            'canResend' => $user->getCodeCount() < 2,
            'email' => $user->getEmail(),
            'sent' => $sent,
            'contact' => $this->transformForm($this->createForm(ContactType::class, $user, array('csrf_protection' => false)))
        );
    }

    /**
     * @ApiDoc(
     *   resource = true,
     *   description = "update user phone",
     *
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     401 = "Returned when bad credentials were sent"
     *   }
     * )
     */
    public function postUpdatePhonesAction(Request $request)
    {
        $userRepo = $this->getDoctrine()->getRepository('AppBundle:User');
        $user = $this->getUser();
        $phone = filter_var($request->request->get('phone'), FILTER_SANITIZE_STRING);
        $phone = trim($phone);

        $is_valid = true;
        $error = '';

        if (!is_numeric($phone)) {
            $is_valid = false;
            $error = $this->get('translator')->trans('phone number should include numbers only');
        } else if (strlen($phone) > 12 || strlen($phone) < 9) {
            $is_valid = false;
            $error = $this->get('translator')->trans('phone numbers must be 9 - 12 digits long');
        }

        if ($is_valid) {
            $user_with_phone = $userRepo->findBy(array(
                'phone' => $phone,
            ));
            if ($user_with_phone) {
                $is_valid = false;
                $error = $this->get('translator')->trans('phone number already exists');
            } else {
                $post = $userRepo->removeWordsBlocked(array('phone' => $phone), array('phone'));
                $phone = $post['phone'];
                if (!$phone) {
                    $is_valid = false;
                    $error = $this->get('translator')->trans('Phone number is blocked');
                }
            }
        }

        if ($is_valid) {
            $em = $this->getDoctrine()->getManager();
            $user->setPhone($phone);
            $em->persist($user);
            $em->flush();
        }

        return new JsonResponse(array(
            'success' => $is_valid,
            'error' => $error,
            'message' => $this->get('translator')->trans('message sent ' . $phone)
        ));
    }


    /**
     * @ApiDoc(
     *   resource = true,
     *   description = "update user email",
     *
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     401 = "Returned when bad credentials were sent"
     *   }
     * )
     */
    public function postUpdateEmailsAction(Request $request)
    {
        $userRepo = $this->getDoctrine()->getRepository('AppBundle:User');
        $user = $this->getUser();
        $email = filter_var($request->request->get('email'), FILTER_SANITIZE_STRING);
        $email = trim($email);

        $is_valid = true;
        $error = '';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $is_valid = false;
            $error = $this->get('translator')->trans('Not a valid email');
        }
        if ($is_valid) {
            $user_with_email = $userRepo->findBy(array(
                'email' => $email,
            ));
            if ($user_with_email) {
                $is_valid = false;
                $error = $this->get('translator')->trans('email already exists');
            } else {
                $post = $userRepo->removeWordsBlocked(array('email' => $email), array('email'));
                $email = $post['email'];
                if (!$email) {
                    $is_valid = false;
                    $error = $this->get('translator')->trans('email is blocked');
                }
            }
        }

        if ($is_valid) {
            $em = $this->getDoctrine()->getManager();
            $user->setEmail($email);
            $em->persist($user);
            $em->flush();
        }

        return new JsonResponse(array(
            'success' => $is_valid,
            'error' => $error,
            'message' => $this->get('translator')->trans('email was updated to:' . $email)
        ));
    }


    /**
     * @ApiDoc(
     *   resource = true,
     *   description = "Check if code is the true one and activate the user is yes",
     *
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     401 = "Returned when bad credentials were sent"
     *   }
     * )
     */
    public function postActivationAction(Request $request)
    {
        $user = $this->getUser();
        $code = $request->request->get('code', false);

        if ($user->getCode() != $code) {
            return array(
                'success' => false,
                'errorMessage' => $this->get('Translator')->trans('incorrect code, please try again'),
            );
        }

        $lastSendDate = $user->getCodeDate();
        $now = new \DateTime();
        $hours = $now->diff($lastSendDate)->h;
        if ($hours) {
            return array(
                'success' => false,
                'errorMessage' => $this->get('Translator')->
                trans('Activation code expired. Please contact us, or try again (available only once)'),
            );
        } else {
            $em = $this->getDoctrine()->getManager();
            $user->setIsActivated(true);
            $em->persist($user);
            $em->flush();
            return array(
                'success' => true,
            );
        }

    }

    /**
     * @ApiDoc(
     *   resource = true,
     *   description = "Resend code to user (only once)",
     *
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     401 = "Returned when bad credentials were sent"
     *   }
     * )
     */
    public function postResendAction(): array
    {
        $user = $this->getUser();
        if ($user->getCodeCount() < MAX_ACTIVATION_EMAILS && !$user->getIsActivated()) {
            $result = $this->sendActivationEmail($user);
            if ($result) {
                return array(
                    'success' => true,
                    'message' => $this->get('translator')->trans('code sent'),
                    'result' => $result
                );
            }
        }
        return array(
            'success' => false,
            'message' => $this->get('translator')->trans('code could not be sent'),
        );
    }

    /**
     * @ApiDoc(
     *   resource = true,
     *   description = "Send acivation SMS to user",
     *
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     401 = "Returned when bad credentials were sent"
     *   }
     * )
     */
    public function sendSms($user)
    {
        $phone = $user->getPhone();
        $code = rand(100000, 999999);
        $settings = $this->getDoctrine()->getRepository('AppBundle:Settings')->find(1);
        $sms = "<SMS>
￼<USERNAME>" . $settings->getSmsUsername() . "</USERNAME>
￼<PASSWORD>" . $settings->getSmsPassword() . "</PASSWORD>
￼<SENDER_PREFIX>ALFA</SENDER_PREFIX>
￼<SENDER_SUFFIX>" . $settings->getSmsSufix() . "</SENDER_SUFFIX>
￼<MSGLNG>HEB</MSGLNG>
￼<MSG>" . $this->get('translator')->trans('Your PolyAmory activation code is' . $code) . "</MSG>
￼<MOBILE_LIST>
￼<MOBILE_NUMBER>" . $phone . "</MOBILE_NUMBER>
￼</MOBILE_LIST>
￼<UNICODE>False</UNICODE>
￼<USE_PERSONAL>False</USE_PERSONAL>
￼</SMS>";

        $soapClient = new \SoapClient("http://www.smsapi.co.il/Web_API/SendSMS.asmx?WSDL");
        $ap_param = array(
            'XMLString' => $sms);
        $info = $soapClient->__call("SUBMITSMS", array($ap_param));

        if ($info) {
            $user->setCodeCount($user->getCodeCount() + 1);
            $user->setCode($code);
            $user->setCodeDate(new \DateTime());
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            return true;
        }
        return false;
    }

    /**
     *
     *   resource = true,
     *   description = "Send activation email to user",
     *
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     401 = "Returned when bad credentials were sent"
     *   }
     * )
     */


    private function sendActivationEmail($user)
    {
        $email = $user->getEmail();
        $code = rand(100000, 999999);
        $user->setCodeCount($user->getCodeCount() + 1);
        $user->setCode($code);
        $user->setCodeDate(new \DateTime());

        $settings = $this->getDoctrine()->getRepository('AppBundle:Settings')->find(1);
        $subject = "{$this->getParameter('site_name')} | " . $this->get('translator')->trans($this->getParameter('site_name') .'Activation code');


        $body = '<div dir="ltr">';
        $body .= $this->get('translator')->trans("Your activation code for {$this->getParameter('site_name')} is {{code}", ['{{code}}' => $code]) . '<br>';
        $body .= '</div>';
        $body .= '<p dir="ltr">'
            . $this->get('translator')->trans('regards,')
            . '<br>'
            . $this->get('translator')->trans('from the '.$this->getParameter('site_name').' team')
            . '<br><br><a href="https://'.$this->getParameter('base_url').'" target="_blank">'
            . $this->get('translator')->trans('')
            . '</a></p>';

        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= 'From: Admin <' . $settings->getContactEmail() . '>' . "\r\n";
        try {
            $success = mail($email, $subject, $body, $headers);
        } catch (\Exception $e) {
            return $e;
        }
        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();

        return ['success' => $success, 'email' => $email, 'subject' => $subject, 'body' => $body, '$headers' => $headers];
    }

    /**
     * @ApiDoc(
     *   resource = true,
     *   description = "Set like to user",
     *
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     401 = "Returned when bad credentials were sent"
     *   }
     * )
     */

    public function postLikeAction(User $toUser)
    {
        $currentUser = $this->get('security.token_storage')->getToken()->getUser();
        $user = $this->getDoctrine()->getManager()->getRepository('AppBundle:User');
        $result = $user->sendUserLike($currentUser, $toUser, $this->getParameter('base_url'), $this->getParameter('contact_email'), $this->getParameter('site_name'));
        if ($result == 'send_me' && !$toUser->isBingoPushToday()) {
            $this->sendBingoPush($currentUser, $toUser);
        }
        return $this->view($result, Response::HTTP_OK);
    }


    /**
     * @ApiDoc(
     *   resource = true,
     *   description = "Get bingo",
     *   parameters = {
     *     {"name"="likeMeId", "dataType"="integer", "required"=false, "description"="LikeMe id for set splash bingo as show"}
     *  },
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     401 = "Returned when bad credentials were sent"
     *   }
     * )
     * @param Request $request
     * @return \FOS\RestBundle\View\View
     */

    public function getBingoAction(Request $request)
    {
        $likeMeId = $request->get('likeMeId', 0);
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $userRepo = $this->getDoctrine()->getManager()->getRepository('AppBundle:User');

        if ((int)$likeMeId > 0) {
            $result = $userRepo->setSplashShowBingo($likeMeId, $user->getId());
        } else {
            $result = $userRepo->getSplashBingo($user);
        }

        $res['user'] = $result;

        $res['texts'] = array(
            'text' => $this->get('translator')->trans('USERNAME is also interested'),
            'send' => $this->get('translator')->trans('chat with them'),
            'cancel' => $this->get('translator')->trans('keep looking'),
            'photo' => (is_object($user->getMainPhoto())) ? $user->getMainPhoto()->getFaceWebPath() : $user->getNoPhoto(),
            'photoMessage' => $this->get('translator')->trans('you must upload at least one image'),
        );

        $res['status'] = $this->getUserStatus($user);
        return $this->view($res, Response::HTTP_OK);
    }


    /**
     * @ApiDoc(
     *   resource = true,
     *   description = "Get user notifications/Set as read notification",
     *   parameters = {
     *     {"name"="id", "dataType"="integer", "required"=false, "description"="Notification id for set as read."}
     *  },
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     401 = "Returned when bad credentials were sent"
     *   }
     * )
     * @param Request $request
     * @return \FOS\RestBundle\View\View
     */

    public function postNotificationAction(Request $request)
    {
        $id = $request->get('id', 0);
        $result = array();
        $user = $this->get('security.token_storage')->getToken()->getUser();
        if ((int)$id > 0) {
            $em = $this->getDoctrine()->getManager();
            $notification = $em->getRepository('AppBundle:UserNotifications')->find($id);
            if ($notification) {
                $notification->setIsRead(true);
                $em->persist($notification);
                $em->flush();
            }
        }
        foreach ($user->getNotifications() as $notification) {
            $sendUser = ($notification->getLikeMe()->getUserFrom()->getId() == $user->getId()) ? $notification->getLikeMe()->getUserTo() : $notification->getLikeMe()->getUserFrom();
            if ($sendUser->getMainPhoto() && $sendUser->getIsActive() == 1 && $sendUser->getIsFrozen() == 0) {
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
        $result = array(
            'users' => $result,
            'texts' => array(
                'no_results' => $this->get('translator')->trans('no results'),
                'description' => $this->get('translator')->trans('This is the area where the you will see likes you receive. <br>If you both like each others photos, you will receive a Bingo! notification.')
            )
        );

        return $this->view($result, Response::HTTP_OK);
    }


    /**
     * @ApiDoc(
     *   resource = true,
     *   description = "Get search",
     *   parameters = {
     *     {"name"="advanced", "dataType"="boolean", "required"=false, "description"="Show Advanced search. If 'false' get show Quick search. Default value: 'false'."},
     *     {"name"="advanced_search[region]", "dataType"="string", "required"=false, "description"="Get Area choices if parameter advanced = 'true'."}
     *  },
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     401 = "Returned when bad credentials were sent"
     *   }
     * )
     * @param Request $request
     * @return \FOS\RestBundle\View\View
     */

    public function getSearchAction(Request $request)
    {
        $searchType = QuickSearchApiType::class;

        $options = array();
        if ($request->get('advanced', false)) {
            $searchType = AdvancedSearchType::class;
            if ($request->get('advanced_search', false)) {
                $options = array(
                    'do_not_create_ethnicity' => true,
                    'do_not_create_zodiac' => true,
                );
            }
        }

        $form = $this->createForm($searchType, new User(), $options);
        if ($request->get('advanced_search', false)) {
            $form->submit($request->get($form->getName()));
        }
        $result = $this->transformForm($form);

        $result['submit'] = $this->get('translator')->trans('Search');
        $result['showThereFor'] = false; //change to true when must users update hers LookingFor
        return $this->view($result, Response::HTTP_OK);
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

                if ($field->vars['block_prefixes'][count($field->vars['block_prefixes']) - 2] === 'repeated') {
                    foreach ($field as $key2 => $chield) {
                        $formArr[$key][$key2] = array(
                            'name' => $chield->vars['full_name'],
                            /** @Ignore */
                            'label' => $this->get('translator')->trans($chield->vars['label']),
                            'type' => $chield->vars['block_prefixes'][count($chield->vars['block_prefixes']) - 2],
                            'value' => $chield->vars['value'],
                        );
                    }
                } elseif (in_array($field->vars['block_prefixes'][count($field->vars['block_prefixes']) - 2], array('entity', 'choice'))) {

                    $choices = array();
                    if ($form->getName() == 'advanced_search' and $key == 'area') {
                        $areaValues = array();
                    }
                    if ($key == 'distance') {
                        $field->vars['label'] = $this->get('translator')->trans('Distance');
                        $choices[] = array(
                            'value' => '',
                            'label' => $this->get('translator')->trans('Choose'),
                        );
                    }


                    $order = array();
                    if ($key == 'city') {
                        $choices = $order;
                    }
                    if (is_array($field->vars['value']) && isset($field->vars['value'][0])) {
                        $value = is_string($field->vars['value'][0]) ? $field->vars['value'][0] : $field->vars['value'][0]->getId();
                    } else {
                        $value = $field->vars['value'];
                    }
                    $valLab = false;

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
                        if ($key == 'city' and $chield->value == $value) {
                            $valLab = $chield->label;
                        }
                    }

                    if (is_array($field->vars['value'])) {
                        $value = array();
                        foreach ($field->vars['value'] as $valObj) {
                            $value[] = is_object($valObj) ? (string)$valObj->getId() : $valObj;
                        }
                    }

                    $formArr[$key] = array(

                        'name' => $field->vars['full_name'],
                        /** @Ignore */
                        'label' => $this->get('translator')->trans($field->vars['label']),
                        'type' => $field->vars['block_prefixes'][count($field->vars['block_prefixes']) - 2],
                        'value' => $value,
                        'choices' => $choices,
                    );

                    if ($key == 'city') {
                        $formArr[$key]['textValue'] = $valLab;
                    }

                    if ($key == 'ageFrom') {
                        $formArr[$key]['label0'] = $formArr[$key]['label'];
                        $formArr[$key]['label'] = $this->get('translator')->trans('From');
                    }
                    if ($form->getName() == 'advanced_search' and $key == 'area') {
                        $formArr[$key]['value'] = $areaValues;
                    }
                    if ($key == 'distance') {
                        $formArr[$key]['description'] = $this->get('translator')->trans('miles from my Location');
                    }
                } else {
                    if ($key == 'withPhoto') {
                        $field->vars['value'] = 0;
                    }
                    $formArr[$key] = array(
                        'name' => $field->vars['full_name'],
                        /** @Ignore */
                        'label' => (
                            /** @Ignore */
                        $this->get('translator')->trans($field->vars['label'])
                        ),
                        'type' => $field->vars['block_prefixes'][count($field->vars['block_prefixes']) - 2],
                        'value' => $field->vars['value'],
                    );
                }
            }
        }
        $formArr['submit'] = $this->get('translator')->trans('save');

        return $formArr;
    }


    /**
     * @ApiDoc(
     *   resource = true,
     *   description = "Save note of member",
     *   parameters = {
     *     {"name"="text", "dataType"="string", "required"=false, "description"="Note text. Default value: ''."}
     *  },
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     401 = "Returned when bad credentials were sent"
     *   }
     * )
     * @param Request $request
     * @param User $member
     * @return \FOS\RestBundle\View\View
     */

    public function postNoteAction(Request $request, User $member)
    {
        $this->updateList(
            'Note',
            $this->get('security.token_storage')->getToken()->getUser(),
            $member,
            'create',
            array('text' => $request->request->get('text', ''))
        );
        return $this->view(array('success' => $this->get('translator')->trans('Your note has been saved successfully!'),), Response::HTTP_OK);
    }

    /**
     * @ApiDoc(
     *   resource = true,
     *   description = "Add/Remove member to list Favorite/BlackList",
     *   parameters = {
     *     {"name"="list", "dataType"="string", "required"=true, "description"="Name of list. (Favorite,BlackList)"},
     *     {"name"="action", "dataType"="string", "required"=false, "description"="Actions Add/Remove member from list. Default value: 'create'.(delete,create)"},
     *  },
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     401 = "Returned when bad credentials were sent"
     *   }
     * )
     * @param Request $request
     * @param User $member
     * @return \FOS\RestBundle\View\View
     */

    public function postListsAction(Request $request, User $member)
    {
        $list = $request->get('list', false);
        $action = $request->get('action', 'create');
        if (empty($action)) {
            $action = 'create';
        }
        if ($list !== 'Favorite' and $list !== 'BlackList') {
            return $this->view(array('error' => $this->get('translator')->trans('List type ' . $list)), Response::HTTP_BAD_REQUEST);
        }
        $this->updateList(
            $list,
            $this->get('security.token_storage')->getToken()->getUser(),
            $member,
            $action
        );

        if ($list == 'BlackList' && $action == 'create') {
            $txt = $this->get('translator')->trans(" to blocked users list");

        } else if ($list == 'BlackList' && $action == 'delete') {
            $txt = $this->get('translator')->trans("from blocked users list");

        } else if ($list == 'Favorite' && $action == 'create') {
            $txt = $this->get('translator')->trans("to blocked users list");
        } else if ($list == 'Favorite' && $action == 'delete') {
            $txt = $this->get('translator')->trans("from favorite users list");
        }

        $message = $member->getUsername() . ' ' . (($action == 'create') ? $this->get('translator')->trans('successfully added ') . $txt : $this->get('translator')->trans(' successfully removed ') . $txt);
        return $this->view(array('test' => $action, 'success' => /** @Ignore */ $this->get('translator')->trans($message),), Response::HTTP_OK);
    }

    /*
     * $entityName - list name;
     *
     * */
    public function updateList($entityName, $owner, $member, $action = 'create', $fields = array())
    {
        if ($owner->getId() == $member->getId() or ($action !== 'create' and $action !== 'delete')) {
            return;
        }
        $repo = $this->getDoctrine()->getRepository('AppBundle:' . $entityName);
        $entity = $repo->findOneBy(array(
            'owner' => $owner,
            'member' => $member
        ));

        $em = $this->getDoctrine()->getManager();
        if ($action == 'delete') {
            if (null !== $entity) {
                $em->remove($entity);
                $em->flush();
            }
        } else {
                $className = 'AppBundle\Entity\\' . $entityName;
                $entity = new $className();
                $entity->setOwner($owner);
                $entity->setMember($member);
                $entity->setDate(new \DateTime('now'));


            foreach ($fields as $key => $value) {
                $method = 'set' . ucfirst($key);
                $entity->$method($value);
            }
            $em->persist($entity);
            $em->flush();
        }
    }


    /**
     * @ApiDoc(
     *   resource = true,
     *   description = "Get Freeze form",
     *   parameters = {
     *     {"name"="freeze_account_reason", "dataType"="string", "required"=false, "description"="Freeze account reason."}
     *  },
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     401 = "Returned when bad credentials were sent"
     *   }
     * )
     */

    public function getFreezeAction()
    {
        return $this->view(array(
            'form' => array(
                'freeze_account_reason' => array(
                    'name' => 'freeze_account_reason',
                    'type' => 'textarea',
                    'label' => $this->get('translator')->trans('Why do you want to freeze your account'),
                    'value' => ''
                ),
            ),
            'pop' => array(
                'header' => $this->get('translator')->trans('freeze account'),
                'message' => $this->get('translator')->trans('
                Are you sure?
                Clicking \'Accept\' will freeze your account.
                Clicking \'Cancel\' will cancel the action.
                A frozen account may be reactivated by logging in to your account'
                ),
                'btns' => array(
                    'agree' => $this->get('translator')->trans('Accept'),
                    'cancel' => $this->get('translator')->trans('Cancel')
                ),
            ),
            'error' => $this->get('translator')->trans('Please fill in all the required fields'),
            'description' => $this->get('translator')->trans('A frozen account does not appear on the site. Frozen accounts may be reactivated by logging in to the site. Frozen accounts will be deleted after an extended period of inactivity.'),
        ), Response::HTTP_OK);
    }


    /**
     * @ApiDoc(
     *   resource = true,
     *   description = "Freeze account",
     *   parameters = {
     *     {"name"="freeze_account_reason", "dataType"="string", "required"=false, "description"="Freeze account reason."}
     *  },
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     401 = "Returned when bad credentials were sent"
     *   }
     * )
     */

    public function postFreezeAction(Request $request)
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();
        if ($request->get('freeze_account_reason', false)) {
            $user->setIsFrozen(true);
            $user->setFreezeReason($request->get('freeze_account_reason', null));
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();
        }
        return $this->view(array(), Response::HTTP_OK);
    }


    /**
     * @ApiDoc(
     *   resource = true,
     *   description = "Get Report Abuse form",
     *
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     401 = "Returned when bad credentials were sent"
     *   }
     * )
     */

    public function getReportAbuseAction(Request $request)
    {

        //send success
        return $this->view(array(
            'form' => array(
                'text' => array(
                    'name' => $this->get('translator')->trans('text'),
                    'type' => $this->get('translator')->trans('textarea'),
                    'label' => $this->get('translator')->trans('Comments'),
                    'value' => ''
                ),
                'buttons' => array(
                    'submit' => $this->get('translator')->trans('Send'),
                    'cancel' => $this->get('translator')->trans('Cancel')
                )
            )), Response::HTTP_OK);
    }


    /**
     * @ApiDoc(
     *   resource = true,
     *   description = "Send Report Abuse",
     *   parameters = {
     *     {"name"="text", "dataType"="string", "required"=true, "description"="Text Report Abuse."}
     *  },
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     401 = "Returned when bad credentials were sent"
     *   }
     * )
     */

    public function postReportAbuseAction(Request $request, User $member)
    {
        $text = $request->get('text', false);
        $user = $this->get('security.token_storage')->getToken()->getUser();
        if (!$text) {
            return $this->view(array('error' => $this->get('translator')->trans('Parameter text in not valid!')), Response::HTTP_OK);
        }

        $mobileDetector = $this->get('mobile_detect.mobile_detector');
        if ($mobileDetector->isAndroidOS()) {
            $platform = 'Android App';
        }
        if ($mobileDetector->isIOS()) {
            $platform = 'IOS App';
        }

        $subject = "GreenDate | Report Abuse |  " . $member->getID() . '#';

        $bodyInner = $this->get('translator')->trans('
        username: {{username}} <br/>
        user ID: {{userId}}<br/>>
        text: {{text}}<br/>
        reported by: {{reporter}}<br/>
        sent from: {{platform}}', ['username' => $member->getUsername(), 'userId' => $member->getId(), 'text' => $text,
            'reporter' => $user->getUsername() . '(#' . $user->getId(), 'platform' => $platform]);
        $body = '<div>' . $bodyInner . '</div>';


        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= 'From: ' . $user->getEmail() . ' <' . $user->getEmail() . '>' . "\r\n";
        $settings = $this->getDoctrine()->getRepository('AppBundle:Settings')->find(1);

        mail($settings->getReportEmail(), $subject, $body, $headers);

        return $this->view(array('text' => $text, 'success' => $this->get('translator')->trans('Message sent to site admins')), Response::HTTP_OK);
    }


    /**
     * @ApiDoc(
     *   resource = true,
     *   description = "Change password",
     *   parameters = {
     *     {"name"="change_password", "dataType"="string", "required"=true, "description"="Text Report Abuse."}
     *  },
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     401 = "Returned when bad credentials were sent"
     *   }
     * )
     * @param Request $request
     * @return \FOS\RestBundle\View\View
     */

    public function postPasswordAction(Request $request)
    {
        $type = ChangePasswordType::class;
        $changed = false;
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $form = $this->createForm($type, $user, array('csrf_protection' => false));

        if ($request->isMethod('POST') && $request->get('change_password', false)) {
            $post = $request->request->all();
            $validOldPassword = false;
            if (!empty($post['change_password']['oldPassword'])) {
                $originalEncodedPassword = $user->getPassword();
                $encoder = $this->get('security.encoder_factory')->getEncoder($user);
                $validOldPassword = $encoder->isPasswordValid(
                    $originalEncodedPassword, // the encoded password
                    $post['change_password']['oldPassword'],  // the submitted password
                    null
                );
            }
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
                $form->get('oldPassword')->addError(new FormError('Old password incorrect'));
            }
        }
        return $this->view(array(
            'form' => $this->transformForm($form),
            'errors' => $form->getErrors(),
            'changed' => $changed,
        ), Response::HTTP_OK);
    }


    /**
     * @ApiDoc(
     *   resource = true,
     *   description = "Edit Profile",
     *   parameters = {
     *     {"name"="step", "dataType"="integer", "required"=false, "description"="Step of edit profile. Default: '1'."}
     *  },
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     401 = "Returned when bad credentials were sent"
     *   }
     * )
     * @param Request $request
     * @return \FOS\RestBundle\View\View
     */

    public function getEditProfileAction(Request $request)
    {
        $step = $request->get('step', 1);
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $multiple_select = [];
        switch ($step) {
            case '1':
                $formType = ProfileOneApiType::class;
                break;
            case '2':
                $formType = ProfileTwoApiV3Type::class;
                $multiple_select = ['lookingFor'];
                break;
            case '3':
                $formType = ProfileThreeType::class;
                $multiple_select = ['contactGender'];
                break;
        }
        $form = $this->createForm($formType, $user, array(//'is_male' => $user->getGender()->getId() == 1
        ));

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
        }

        $message .= '</ul>';

        return $this->view(array(
            'form' => $this->transformForm($form),
            'user_gender' => $user->getGender()->getId(),
            'multipleFields' => $multiple_select,
            'relationshipTypeHelper' => array(
                'type' => 'hidden',
                'header' => $this->get('translator')->trans('PolyAmory- explanation about relationship types'),
                'message' => $message,
                'cancel' => $this->get('translator')->trans('close'),
            ),
        ), Response::HTTP_OK);
    }

    /**
     * @ApiDoc(
     *   resource = true,
     *   description = "Save Profile fields",
     *   parameters = {
     *     {"name"="profile_step[field]", "dataType"="string", "required"=true, "description"="Field name: value for save.(Name: profile_one[] or profile_two[] or profile_three[])"}
     *  },
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     401 = "Returned when bad credentials were sent"
     *   }
     * )
     * @param Request $request
     * @return \FOS\RestBundle\View\View
     */

    public function postEditProfileAction(Request $request)
    {
        $user = $this->getUser();
        $userRepo = $this->getDoctrine()->getRepository('AppBundle:User');
        $post = $request->get('profile_one', false);
        $errors = false;
        $options = array(
            'csrf_protection' => false
        );
        if ($post) {

            $formType = ProfileOneApiType::class;
            $postKey = 'profile_one_api';
            $post['phone'] = $user->getPhone();
            $post = $userRepo->removeWordsBlocked($post, array('username'));

        } elseif (!$post) {
            $post = $request->get('profile_two', false);
            $post = $userRepo->removeWordsBlocked($post, array('sexOrientationDetails', 'relationshipTypeDetails', 'lookingForDetails'));
            $formType = ProfileTwoApiV3Type::class;
            $postKey = 'profile_two_api_v3';
        }
        if (!$post) {
            $post = $request->get('profile_three', false);
            $post = $userRepo->removeWordsBlocked($post, array('looking', 'about'));
            $formType = ProfileThreeType::class;
            $postKey = 'profile_three';
        }

        $form = $this->createForm($formType, $user, $options);
        $request->request->set($postKey, $post);
        $form->handleRequest($request);

        if ($form->has('email')) {

            if (empty($form->get('email')->getData())) {
                $form->get('email')->addError(new FormError($this->get('translator')->trans('Please fill in email')));
            }
            $emailInBlocked = $this->getDoctrine()->getManager()->getRepository('AppBundle:EmailBlocked')->findOneByValue(strtolower($form->get('email')->getData()));

            if ($emailInBlocked) {
                $form->get('email')->addError(new FormError('Email already exists in the system'));
                $errors = true;
            }
        }

        if ($form->has('about') and empty($post['about'])) {
            $form->get('about')->addError(new FormError($this->get('translator')->trans(' \'About Me\' should be a least 10 characters long')));
            $errors = true;
        }
        if ($form->has('looking') and empty($post['looking'])) {
            $form->get('looking')->addError(new FormError($this->get('translator')->trans('\' What I\'m looking for\' should be at least 10 characters long')));
            $errors = true;
        }

        if ($form->has('username') and empty(trim($post['username']))) {
            $form->get('username')->addError(new FormError($this->get('translator')->trans('Please enter username')));
            $errors = true;
        }

        if (!$errors and $form->isSubmitted() and $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();
            $texts = array(
                'textSuccess' => $this->get('translator')->trans('changes saved'),
            );
            $success = true;
        } else {
            $errorsText = $form->getErrors(true);
            $success = false;

        }

        $formOut = $this->createForm($formType, $user, array());

        return $this->view(array(
            'form' => $this->transformForm($formOut),
            'errors' => $form->getErrors(),
            'texts' => isset($texts) ? $texts : false,
            'success' => $success,
            'post' => json_encode($post),
        ), Response::HTTP_OK);
    }


    /**
     * @ApiDoc(
     *   resource = true,
     *   description = "Save Locations",
     *   parameters = {
     *     {"name"="latitude", "dataType"="string", "required"=true, "description"="Location latitude"},
     *     {"name"="longitude", "dataType"="string", "required"=true, "description"="Location longitude"}
     *  },
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     401 = "Returned when bad credentials were sent"
     *   }
     * )
     * @param Request $request
     * @return \FOS\RestBundle\View\View
     */

    public function postLocationsAction(Request $request)
    {
        $latitude = $request->get('latitude', null);
        $longitude = $request->get('longitude', null);

        if ($latitude != null and $longitude != null) {
            $user = $this->get('security.token_storage')->getToken()->getUser();
            $user->setLatitude($latitude);
            $user->setLongitude($longitude);
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            return $this->view($user->getUsername(), Response::HTTP_OK);

        }
        return $this->view(1, Response::HTTP_OK);
    }

    /**
     * get list of requests to see user's private photos
     */
    public function getShowPhotoAction(): array
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $entityManager = $this->getDoctrine()->getManager();
        $requests = $entityManager->getRepository('AppBundle:ShowPhoto')
            ->findBy(array('member' => $user->getId()), array('isCancel' => 'ASC', 'isAllow' => 'ASC', 'id' => 'DESC'));
        $settings = $this->getDoctrine()->getManager()->getRepository('AppBundle:Settings')->find(1);
        $newRequests = [];
        foreach ($requests as $request) {
            if (!$request->getIsView()) {
                $request->setIsView(true);
            }
            $owner = $request->getOwner();
            $data = array(
                'id' => $request->getId(),
                'ownerId' => $request->getOwner()->getId(),
                'isAllow' => $request->getIsAllow(),
                'isCancel' => $request->getIsCancel(),
                'date' => $request->getDate()->format('d/m/Y'),
                'isView' => $request->getIsView(),
                'user' => array(
                    'id' => $owner->getId(),
                    'isPaying' => $owner->isPaying(),
                    'isNew' => $owner->isNew($settings->getUserConsideredAsNewAfterDaysNumber()),
                    'isOnline' => $owner->isOnline($settings->getUserConsideredAsOnlineAfterLastActivityMinutesNumber()),
                    'isVerify' => $user->getIsVerify(),
                    'isAddLike' => $owner->isAddLike($owner),
                    'isAddFavorite' => $user->isAddFavorite($owner->getId()),
                    'isAddBlackListed' => $user->isAddBlackListed($owner->getId()),
                    'url' => $owner->getMainPhoto() ? $owner->getMainPhoto()->getFaceWebPath() : $owner->getNoPhoto(),
                    'username' => $owner->getUsername(),
                    'age' => $owner->age(),
                    'region_name' => $owner->getRegion()->getName(),
                    'area_name' => $owner->getCity()->getName(),
                    'gender' => $owner->getGender()->getId(),
                ),
                'texts' => array(
                    'description' => $this->get('translator')->trans('User requested to see your private pics'),
                    'allow' => $this->get('translator')->trans('approve'),
                    'cancel' => $this->get('translator')->trans('refuse'),
                    'isAllow' => $this->get('translator')->trans('You approved this request'),
                    'isCancel' => $this->get('translator')->trans('You refused this request'),
                ),
            );

            $newRequests[] = $data;
        }
        return array(
            'requests' => $newRequests,
            'text' => $this->get('translator')->trans('No new requests'),
        );
    }


    /**
     * @ApiDoc(
     *   resource = true,
     *   description = "Set allowed or canceled status for showPhoto request ",
     *
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     401 = "Returned when bad credentials were sent"
     *   }
     * )
     * @param Request $request
     * @return array
     */
    public function postShowPhotoAction(Request $requst)
    {
        $allow = $requst->request->get('isAllow');
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();
        $photoRequest = $em->getRepository('AppBundle:ShowPhoto')->find($requst->request->get('id', false));
        if ($photoRequest && $photoRequest->getMember()->getId() == $user->getId()) {
            $photoRequest->setIsAllow($allow);
            $photoRequest->setIsCancel(!$allow);
            $em->persist($photoRequest);

            $notification = new UserMessengerNotifications();
            $notif = $em->getRepository('AppBundle:Notifications')->find($allow ? 3 : 4);
            $notification->setFromUser($user);
            $notification->setUser($photoRequest->getOwner());
            $notification->setNotification($notif);
            $notification->setDate(new \DateTime());
            $em->persist($notification);
            $em->flush();
            return array(
                'success' => true,
                'isNotificated' => $photoRequest->getIsNotificated(),
            );
        }
        return array(
            'success' => false,
        );
    }


    /**
     * @ApiDoc(
     *   resource = true,
     *   description = "Sent request from current user to see private photos of passed user",
     *
     *
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     401 = "Returned when bad credentials were sent"
     *   }
     * )
     * @param User $user the user that the request sent for
     * @return array
     */
    public function postShowAction(User $user)
    {
        $currentUser = $this->getUser();
        $em = $this->getDoctrine()->getManager();
        $repo = $this->getDoctrine()->getRepository('AppBundle:ShowPhoto');
        $was_ask = $repo->findBy(array(
            'owner' => $currentUser->getId(),
            'member' => $user->getId(),
        ));

        if (empty($was_ask)) {
            $showPhoto = new ShowPhoto();
            $showPhoto->setMember($user);
            $showPhoto->setOwner($currentUser);
            $em->persist($showPhoto);
            $em->flush();
            return array(
                'text' => $this->get('Translator')->trans('Request sent!'),
                'success' => true,
            );
        }
    }


    /**
     * @ApiDoc(
     *   resource = true,
     *   description = "set all notifications of this user as read",
     *
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     401 = "Returned when bad credentials were sent"
     *   }
     *
     * )
     * @return  array
     */
    public function getReadAllNotificationAction(Request $request)
    {
        $user = $this->getUser();
        $is_bingo = $request->query->get('bingo', false);
        $em = $this->getDoctrine()->getEntityManager();
        $allNotifs = $this->getDoctrine()->getRepository('AppBundle:UserNotifications')->findBy(array(
            'user' => $user->getId(),
            'isRead' => false,

        ));
        foreach ($allNotifs as $notif) {
            if (($notif->getNotification()->getId() == 2 && $is_bingo == 1)
                || ($notif->getNotification()->getId() == 1 && $is_bingo == 0)) {
                $notif->setIsRead(true);
                $em->persist($notif);
            }
        }
        $em->flush();
        return array();
    }

    /**
     * @ApiDoc(
     *   resource = true,
     *   description = "Set dislike for Arena to user",
     *
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     401 = "Returned when bad credentials were sent"
     *   }
     * )
     * @param User $contact the user that the current user dislike hin
     *
     * @return  void
     */

    public function getDislikeAction(User $contact)
    {
        $em = $this->getDoctrine()->getManager();
        $dislike = new ArenaDislike();
        $dislike->setUserFrom($this->getUser());
        $dislike->setUserTo($contact);

        $em->persist($dislike);
        $em->flush();
    }


    /**
     * @ApiDoc(
     *   resource = true,
     *   description = "Check if need update user information",
     *
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     401 = "Returned when bad credentials were sent"
     *   }
     * )
     * @param Request $request
     *
     * @return  array
     */

    public function getUpdateUserInformationAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $user->setLastloginAt(new \DateTime());
        $user->setLastActivityAt(new \DateTime());
        $em->persist($user);
        $em->flush();
        if (!count($user->getLookingFor())) {
            return array(
                'needPopup' => true,
                'texts' => array(
                    'header' => $this->get('Translator')->trans('Please update details'),
                    'message' => $this->get('Translator')->trans('Welcome back to PolyAmory! <br> To continue, please fill in the \'Here for\' on the profile page'),
                    'btns' => array(
                        'link' => $this->get('Translator')->trans('click here to be redirected to field')
                    )
                ),
            );
        }

        return array(
            'needPopup' => false,
        );
    }


    /**
     * @ApiDoc(
     *   resource = true,
     *   description = "update show users photos on home page",
     *
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     401 = "Returned when bad credentials were sent"
     *   }
     * )
     * @param $agree boolean if set true or false
     * @return  boolean
     */

    public function getUpdateOnHomepageAction($agree)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();

        $agree = $agree == 'false' ? false : true;

        $user->setIsOnHomepage($agree);

        $em->persist($user);
        $em->flush();

        return $user->getIsOnHomepage();
    }


    /**
     * @ApiDoc(
     *   resource = true,
     *   description = "Set verify to passed user",
     *
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     401 = "Returned when bad credentials were sent"
     *   }
     * )
     * @param User $user the user the verify set to him
     * @return  array | null - null if verify exists
     */

    public function postVerifyAction(User $user)
    {
        $currentUser = $this->getUser();
        $em = $this->getDoctrine()->getManager();
        $was_verify = $em->getRepository('AppBundle:Verify')->findOneBy(array(
            'userFrom' => $currentUser->getId(),
            'userTo' => $user->getId(),
        ));

        $verify = new Verify();
        $verify->setUserFrom($currentUser);
        $verify->setUserTo($user);
        $em->persist($verify);

        $user->addVerifyMe($verify);

        $notification = new UserMessengerNotifications();
        $notif = $em->getRepository('AppBundle:Notifications')
            ->find($user->getVerifyCount() < 3 ? 5 : 7);
        $notification->setFromUser($currentUser);
        $notification->setUser($user);
        $notification->setNotification($notif);
        $notification->setDate(new \DateTime());
        $notification->setLeftVerifies(3 - (int)$user->getVerifyCount());
        $em->persist($notification);

        $em->flush();

        return array(
            'success' => true,
            'message' => $this->get('Translator')->trans('Thank you for verifying the profile'),
        );
    }


    /**
     * @ApiDoc(
     *   resource = true,
     *   description = "Get User Subscriptions",
     *
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     401 = "Returned when bad credentials were sent"
     *   }
     * )
     */

    public function getUserSubscribeActions()
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $userId = $user->getId();
        $em = $this->getDoctrine()->getManager();
        $mobileDetector = $this->get('mobile_detect.mobile_detector');

        if ($user->isPaying()) {
            $before = new TextBeforePayment();
            $before->setText('<div class="ui olive tiny message">' . $this->get('Translator')->trans('Account successfully activated') . '</div>');
            $before->setIsActive(1);
            $textBefore = array($before);
            return $this->view(array(
                'url' => '',
                'textBefore' => $textBefore,
                'payments' => array(),
                'tableTexts' => array(),
                'textAfter' => array(),
            ), Response::HTTP_OK);
        }
        $textBefore = $em->getRepository('AppBundle:TextBeforePayment')->findBy(array('isActive' => true), array('order' => 'asc'));
        $paymentSubscriptions = $em->getRepository('AppBundle:PaymentSubscription')->findBy(array('isActive' => true), array('order' => 'asc'));
        $tableTexts = $em->getRepository('AppBundle:TableTextPayment')->findBy(array('isActive' => true), array('order' => 'asc'));

        if ($mobileDetector->isIOS()) {
            $textAfter = new TextAfterPayment();
            $textAfter->setText(
                $this->get('Translator')->trans('
			<ul>
				<li>After the purchase is approved, the payment will be handled by your itunes account</li>
			    <li>The payment is deducted 24 hours after the purchase</li>
			    <li>The subscription must be canceled at least 48 hours before it lapses, or it will renew itself</li>
			    <li>The subscription renews itself 24 hours before the previous one expires</li>
			    <li>Subscription details and renewals may be managed by the iTunes account settings.</li>


			</ul>'));
            $textAfter = array($textAfter);
            $productList = array('PolyAmory.oneMonth', 'PolyAmory.threeMonths');

        } else {
            $textAfter = $em->getRepository('AppBundle:TextAfterPayment')->findBy(array('isActive' => true), array('order' => 'asc'));

        }
        $payments = [];
        if (!$mobileDetector->isIOS()) {
            foreach ($paymentSubscriptions as $payment) {
                $text = $payment->getText();
                $payments[] = [
                    'period' => $payment->getPeriod(),
                    'amount' => $payment->getAmount(),
                    'id' => $payment->getId(),
                    'is_active' => $payment->getIsActive(),
                    'price' => $payment->getPrice(),
                    'text' => strip_tags($text),
                    'text_price' => $payment->getTextPrice(),
                    'title' => $payment->getTitle(),
                ];
            }
        }
        if ($mobileDetector->isIOS()) {
            $newBefore = array();
            foreach ($textBefore as $textBeforeText) {
                if ($textBeforeText->getId() != 13) {
                    $newBefore[] = $textBeforeText;
                }
            }
            $textBefore = $newBefore;
        }

        return $this->view(array(
            'url' => 'https://redirect.telepay.co.il?formId=e104ece0-b0b8-eb11-b890-ecebb8951f7e&userId=' . $userId,
            'textBefore' => $textBefore,
            'payments' => $payments,
            'tableTexts' => $tableTexts,
            'textAfter' => $textAfter,
            'productsList' => array('PolyAmory.oneMonth', 'PolyAmory.threeMonths')
        ), Response::HTTP_OK);
    }


    /**
     * @ApiDoc(
     *   resource = true,
     *   description = "post User Subscriptions",
     *
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     401 = "Returned when bad credentials were sent"
     *   }
     * )
     */

    public function postSubsAction(Request $request)
    {
        $monthsNumber = $request->request->get('month', false);
        $history = $request->request->get('history', false);
        $data = $request->request->get('data', false);
        $user = $this->get('security.token_storage')->getToken()->getUser();
        if ('new' === $monthsNumber) {
            switch ($history[0]['productId']) {
                case 'PolyAmory.oneWeek':
                    $monthsNumber = 7;
                    break;
                case 'PolyAmory.oneMonth':
                    $monthsNumber = 1;
                    break;
                case 'PolyAmory.threeMonths':
                    $monthsNumber = 3;
                    break;
                case 'PolyAmory.sixMonth':
                    $monthsNumber = 6;
                    break;
                case 'PolyAmory.oneYear':
                    $monthsNumber = 12;
                    break;
            }
        }

        $userRepo = $this->getDoctrine()->getManager()->getRepository('AppBundle:User');

        if ($user->getId()) {
            if ($monthsNumber) {
                $result = $userRepo->setUserSubscription($user, $monthsNumber, false, $data, true);
                return $result;

            } else if ($history) {
                $headers = "MIME-Version: 1.0" . "\r\n";
                $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
                $headers .= 'From: '.$this->getParameter('do_not_reply_email').' <'.$this->getParameter('do_not_reply_email').'>' . "\r\n";
                usort($history, function ($a, $b) {
                    return strtotime($a['date']) - strtotime($b['date']);
                });
                $subscribe = end($history);
                $dateLast = $subscribe['date'];

                $monthsNumber = 0;
                switch ($subscribe['productId']) {
                    case 'polyamory.oneWeek':
                        $monthsNumber = 7;
                        break;
                    case 'polyamory.oneMonth':
                        $monthsNumber = 1;
                        break;
                    case 'polyamory.threeMonths':
                        $monthsNumber = 3;
                        break;
                    case 'polyamory.sixMonth':
                        $monthsNumber = 6;
                        break;
                    case 'polyamory.oneYear':
                        $monthsNumber = 12;
                        break;
                }

                if ($monthsNumber > 0 and $monthsNumber <= 12) {
                    if ($monthsNumber == 7) {
                        $sub_end_at = strtotime($dateLast) + strtotime("+1 week"); //strtotime(date("Y-m-d H:i:s", strtotime($dateLast)) . " +1 week");
                    } else {
                        $sub_end_at = strtotime($dateLast) + strtotime(" +" . $monthsNumber . " month"); //strtotime(date("Y-m-d H:i:s", strtotime($dateLast)) . " +" . $monthsNumber . " month");
                    }

                    $dateNow = strtotime('now');

                    if ($dateNow < $sub_end_at && !$user->isPaying()) {
                        $result = $userRepo->setUserSubscription($user, $monthsNumber, $dateLast, $history);
                        return $result;
                    }
                }
            }
        }

    }

    /*
        Check from app every 5 seconds when open in app browser for buy subscription. if true
        the iab closed and redirect user to home page
     */
    public function getUserPayingAction()
    {
        $user = $this->getUser();
        return ['paying' => $user->isPaying()];
    }

    private function sendBingoPush($currentUser, $toUser)
    {
        $data = [
            'contact_id' => $toUser->getId(),
            'message' => $this->get('Translator')->trans('Bingo! with ' . $currentUser->getUsername()),
            'user_id' => $currentUser->getId(),
            'url' => '/bingo',
            'type' => 'linkIn',
            'android_channel_id' => 'polyArena',
            'bingoData' => [
                'texts' => [
                    'cancel' => $this->get('Translator')->trans('keep looking'),
                    'photo' => '/media/photos/3745/15161-face.jpeg?r=32',
                    'photoMessage' => $this->get('Translator')->trans('you must upload at least one photo'),
                    'send' => $this->get('Translator')->trans('chat with them'),
                    'text' => $this->get('Translator')->trans($currentUser->getUsername() . ' is also interested'),
                ],
                'user' => [
                    'username' => 'username',
                    'contact_id' => $currentUser->getId(),
                    'photo1' => $currentUser->getMainPhoto()->getFaceWebPath(),
                    'photo2' => $toUser->getMainPhoto()->getFaceWebPath(),
                ]
            ]
        ];
        $this->get('messenger')->pushNotification1($data, $this->getParameter('fcm_auth_key'), $this->getParameter('site_name'), $this->getParameter('base_url'));
    }


    /**
     * @ApiDoc(
     *   resource = true,
     *   description = "Apply coupon",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     401 = "Returned when bad credentials were sent"
     *   }
     * )
     */
    public function getCouponAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $coupon = $this->getDoctrine()->getRepository('AppBundle:Coupon')->findOneBy([
            'code' => $request->query->get('coupon', false),
            'isActive' => true,
        ]);

        if ($coupon) {
            $paymentSubscriptions = $em->getRepository('AppBundle:PaymentSubscription')->findBy(array('isActive' => true), array('order' => 'asc'));

            if ($coupon->getType() == 'nominal') {
                for ($i = 0; $i < count($paymentSubscriptions); $i++) {
                    if ($i >= 1) {
                        $paymentSubscriptions[$i]->setPrice(round(($paymentSubscriptions[$i]->getAmount() - $coupon->getValue()) / $paymentSubscriptions[$i]->getPeriod()));
                    } else {
                        $paymentSubscriptions[$i]->setPrice((int)$paymentSubscriptions[$i]->getAmount() - $coupon->getValue());
                    }
                    $amountWithDiscount = $paymentSubscriptions[$i]->getAmount() - $coupon->getValue();
                    $paymentSubscriptions[$i]->setText(str_replace($paymentSubscriptions[$i]->getAmount(), $amountWithDiscount, $paymentSubscriptions[$i]->getText()));
                    $paymentSubscriptions[$i]->setAmount((int)$amountWithDiscount);

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
            foreach ($paymentSubscriptions as $payment) {
                $text = $payment->getText();
                $payments[] = [
                    'period' => $payment->getPeriod(),
                    'amount' => $payment->getAmount(),
                    'id' => $payment->getId(),
                    'is_active' => $payment->getIsActive(),
                    'price' => $payment->getPrice(),
                    'text' => strip_tags($text),
                    'text_price' => $payment->getTextPrice(),
                    'title' => $payment->getTitle(),
                ];
            }
            $type = $coupon->getType() == 'percentage' ? $this->get('Translator')->trans('percent') : $this->get('Translator')->trans('$');
            $message = $this->get('Translator')->trans('Coupon {{coupon}} has been activated. You received a {{amount}} {{type}} discount', ['amount' => $coupon->getValue(), 'coupon' => $coupon->getCode(), 'type' => $type]);
        }

        return $this->view([
            'newPayments' => $payments ?? false,
            'errorMessage' => $this->get('Translator')->trans('No such coupon exists'),
            'successMessage' => $message ?? false,
        ]);
    }
}
