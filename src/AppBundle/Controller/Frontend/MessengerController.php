<?php

namespace AppBundle\Controller\Frontend;

use AppBundle\Entity\User;
use AppBundle\Entity\UserRepository;
use AppBundle\Services\Messenger\Chat;
use AppBundle\Services\Messenger\Dialog;
use AppBundle\Services\Messenger\Message;
use FOS\RestBundle\Controller\Annotations as Rest;
use PhpParser\Node\Stmt\Return_;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

session_write_close();


class MessengerController extends Controller
{


    /**
     * @Route("/user/messenger/{page}/{ajax}", defaults={"page" = 1, "ajax" = 0}, name="messenger")
     */
    public function indexAction(Request $request, $page, $ajax)
    {

        $dialogs = $this->getDoctrine()->getRepository('AppBundle:User')->oldGetDialogsTest($this->getUser(), $page);
        $template = 'frontend/user/messenger/index.html.twig';
        if ($ajax == 1) {
            $template = 'frontend/user/messenger/index-ajax.html.twig';
        }

        return $this->render($template, array(
            'dialogs' => $dialogs,
            'mobile' => $this->detectIsMobile(),
            'page' => $page,
        ));
    }

    /**
     * @Route("/user/messenger/dialog/open/userId:{userId}/contactId:{contactId}", name="messenger_dialog_open")
     */
    public function openDialogAction($userId, $contactId)
    {
        if ($userId != $this->getUser()->getId()) {
            die;
        }

        $dialog = new Dialog(array(
            'userId' => $userId,
            'contactId' => $contactId,
        ));


        return $this->render('frontend/user/messenger/dialog.html.twig', array(
            'dialog' => $dialog,
            'history' => $dialog->oldGetHistory(),
            'contact' => $this->getDoctrine()->getRepository('AppBundle:User')->find($contactId),
            'messages' => $this->getDoctrine()->getManager()->getRepository('AppBundle:InlineMessages')->findAll(),
            'mobile' => $this->detectIsMobile(),
            'canReadMessages' => $this->canReadMessagesInChat($userId, $contactId),
            'points' => $this->getUser()->getPoints(),
            'hideMenu' => true,
            'savePageCookie' => true,
        ));
    }

    /**
     * @Route("/messenger/activeChats/newMessages/userId:{userId}/contactId:{contactId}/{checkForDialogAlso}", defaults={"checkForDialogAlso" = false}, name="user_messenger_active_chats_new_messages")
     */
    public function activeChatsNewMessagesAction(Request $request, $userId, $contactId, $checkForDialogAlso)
    {
        $options['userId'] = $userId;
        $messenger = $this->get('messenger');
        //$result = $messenger->checkActiveChatsNewMessages($options);
        $result['newMessages'] = array();

        if (!count($result['newMessages'])) {
            if ($checkForDialogAlso) {
                $options['contactId'] = $contactId;

                $result = $messenger->oldCheckDialogNewMessages($options);
            }
        }
        /*
        $post = $this->getRequest()->request->all();
        $result['readMessages'] = $messenger->checkMessagesIfRead($post['messages']);
        */
        $messages = $request->request->get('messages', '');
        $result['readMessages'] = $messenger->checkMessagesIfRead($messages);
        return $messenger->response($result);

    }

    /**
     * @Route("/messenger/newMessages/userId:{userId}", name="user_messenger_new_messages")
     */
    public function newMessagesAction($userId)
    {
        $messenger = $this->get('messenger');
        $options['userId'] = $userId;
        $options['lastLoginAt'] = $this->getDoctrine()
            ->getRepository('AppBundle:User')
            ->find($userId)
            ->getLastloginAt()
            ->format('Y-m-d H:i:s');

//        $this->setUpCloudinary();
        return $messenger->checkNewMessages($options);
    }

    /**
     * @Route("/messenger/newMessagesMobile/{userId}/{contactId}", name="user_messenger_new_messages_mobile")
     */
    public function newMessagesMobileAction($userId, $contactId)
    {
        $messenger = $this->get('messenger');
        $options['userId'] = $userId;
        $options['contactId'] = $contactId;
        if ($options['userId'] == 4214) {
            return $messenger->testCheckNewMessagesMobile($options);
        }

        return $messenger->checkNewMessagesMobile($options);
    }


    /**
     * @Route("/messenger/message/send/push/userId:{userId}/contactId:{contactId}", name="user_messenger_message_send_push")
     * @Route("/test/send/push/userId:{userId}/contactId:{contactId}", name="user_test_send_push")
     */
    public function sendPushAction(Request $request, $userId, $contactId)
    {
        $contact = $this->getDoctrine()->getRepository('AppBundle:User')->find($contactId);
        $user = $this->getDoctrine()->getRepository('AppBundle:User')->find($userId);
        if ($contact->getIsSentPush()) {

            $messenger = $this->get('messenger');
            $image = ($user->getMainPhoto() === null) ? 'https://' . $this->getParameter('base_url') . $user->getNoPhoto()
                : 'https://' . $this->getParameter('base_url') . $user->getMainPhoto()->getFaceWebPath();
            $data = array(
                'message' => 'you got a new message from  ' . $user->getUsername(),
                'contact_id' => $contact->getId(),
                'user_id' => $userId,
                'image' => $image,
                'isVideo' => false,
            );
            $messenger->pushNotification1($data, $this->getParameter('fcm_auth_key'), $this->getParameter('site_name'), $this->getParameter('base_url'));

        }
        return $messenger->response(array('success' => false));
    }

    /**
     * @Route("/messenger/message/send/userId:{userId}/contactId:{contactId}", name="user_messenger_message_send")
     */
    public function sendMessageAction(Request $request, $userId, $contactId)
    {
        $message = preg_replace('/<br(\s+)?\/?>/i', "\n", $request->request->get('message'));

        $isInline = $request->request->get('tag', false) == 'LI' ? 1 : 0;
        $fromAdmin = $request->request->get('fromAdmin', false);

        $options['message'] = trim(strip_tags($message));
        $options['message'] = urlencode($message);

        $options['userId'] = $userId;
        $options['contactId'] = $contactId;
        $options['isInline'] = $isInline;
        $options['messageId'] = $request->request->get('val') ?? false;

        if ($options['message'] == 'QUICKMESSALLOW') {
            $options['isInline'] = 1;
            $options['message'] = 'I permit you to view my private photos';
            $options['messageId'] = 9999;

        }

        if ($options['isInline'] && $options['messageId'] != 9999) {
            $message2 = $this->getDoctrine()->getManager()->getRepository('AppBundle:InlineMessages')->findOneBy(array(
                'id' => $options['messageId']
            ));
            $text = $message2->getText();
            $options['message'] = trim(strip_tags($text));
            $options['message'] = urlencode($text);
        }


//        var_dump($request->request->all());die;
//        var_dump( $options['isInline']);die;
        $contact = $this->getDoctrine()->getManager()->getRepository('AppBundle:User')->find($contactId);

//        $contact->getAgeFrom();
//        $contact->getAgeTo();
        $user = $this->getUser();
        // TODO - user provider is not working so we are using this workaround
        $user = $this->getDoctrine()->getManager()->getRepository(User::class)->findOneBy(array('username' => $user->getUsername()));

        $userAge = $user->age();

        $userGender = $user->getGender()->getId();


        $contactCanContact = array();

        foreach ($contact->getContactGender() as $contact2) {
            $contactCanContact[] = $contact2->getId();
        }

        $chat = new Chat($options);
// if genders selected and if not has chat and not from admin and not likes
        $messenger = $this->get('messenger');
//        var_dump($this->isLiked($contact, $user));die;
        if ($contactCanContact && !$messenger->hasChat($contactId, $userId) && !$fromAdmin && $userId != 111 && !$messenger->isLiked($contact, $user)) {
//            var_dump(123);
            if (!$fromAdmin) {
                if ($options['messageId'] != 9999) {
                    if (!in_array($userGender, $contactCanContact)) {
                        return $chat->response(array('success' => false, 'cantContact' => true));
                    }

                    if (!($contact->getAgeFrom() <= $userAge &&
                        $contact->getAgeTo() >= $userAge)) {
                        return $chat->response(array('success' => false, 'cantContact' => true));
                    }
                }
            }
        }

        if ($chat->isForbidden()) {
//            var_dump($chat->isForbidden());
            if ($chat->isForbidden() == 1) {
                $errMess = $this->get('translator')->trans('You have blocked this user, and may not send hom a message.');
            } else {
                $errMess = $this->get('translator')->trans('This user has blocked you, and you may not send them a message');
            }
            return $chat->response(array('success' => false, 'chatIsForbidden' => true, 'errMess' => $errMess));
        }
//
        if ($chat->contact()->isFrozen()) {
            return $chat->response(array('success' => false, 'contactIsFrozen' => true));
        }

        $settings = $this->getDoctrine()->getRepository('AppBundle:Settings')->find(1);

        if ($chat->isLimit($settings->getSendMessageUsersNumber())) {
            return $chat->response(array('success' => false, 'isLimit' => true));
        }


        $messageObj = $chat->sendMessage();

        if ($messageObj) {

            if ($chat->isLastFromContact() || $fromAdmin) { //$chat->isNotSentToday()

                $contact = $this->getDoctrine()->getRepository('AppBundle:User')->find($chat->contact()->getId());
                $user = $this->getDoctrine()->getRepository('AppBundle:User')->find($chat->user()->getId());
                //$settings = $this->getDoctrine()->getRepository('AppBundle:Settings')->find(1);
                if ($contact->getIsSentEmail() && $chat->isNotSentToday()) {
                    $subject = "{$this->getParameter('site_name')} | New Message";

//	                $this->setUpCloudinary();
                    $imageName = $user->getMainPhoto() === null ? '' : $user->getMainPhoto()->getFaceWebPath();
                    $image = /*(!empty($imageName))
	                    ?
	                        cl_image_tag($imageName, array(
	                            "width" => 150, "height" => 150, "crop" => "thumb", "gravity" => "face", "radius" => 20
	                        ))
	                    :*/
                        '';

                    $body = '<div dir="rtl">';
                    $body .= 'A new message is waiting for you' . '<br>';
                    $body .= ' from ' . '<strong>' . $user->getUsername() . '</strong><br>';
                    $body .= 'age: ' . $user->age() . '<br>';
//	                $body .= 'עיר: ' . $user->getCity()->getName() . '<br>';
                    ///$body .= $image;
                    $body .= '</div>';
                    $body .= '<p dir="ltr">
 regards,
 <br>
The ' . $this->getParameter('site_name') . ' Team
<br>
<br>
    <a href="https://' . $this->getParameter('base_url') . '" target="_blank"> click here to be redirected to the site</a>
 </p>';

                    $headers = "MIME-Version: 1.0" . "\r\n";
                    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
                    //$headers .= 'From: ' . $settings->getContactEmail() . ' <' . $settings->getContactEmail() .'>' . "\r\n";
                    $headers .= 'From:  '.$this->getParameter('contact_email').' <info@polydate.co.il>' . "\r\n";
                    mail($contact->getEmail(), $subject, $body, $headers);
                }


            }

            if ($options['messageId'] == 9999) {
                $em = $this->getDoctrine()->getEntityManager();
                $request = $this->getDoctrine()->getRepository('AppBundle:ShowPhoto')->findOneBy(array(
                    'member' => $userId,
                    'owner' => $contactId,
                ));

                $request->setIsNotificated(true);
                $em->persist($request);
                $em->flush();
            }

            return $chat->response(array(
                'success' => true,
                'message' => $messageObj,
                //   'notified' => $options['message'] == 'QUICKMESSALLOW' ? 1 : 0,
            ));
        }

        return $chat->response(array('success' => false));
    }

    /**
     * @Route("/messenger/checkMessagesIfRead/userId:{userId}", name="user_messenger_check_messages_if_read")
     */
    public function checkMessagesIfReadAction(Request $request, $userId)
    {
        $options['userId'] = $userId;
        $messenger = $this->get('messenger');
        $post = $request->request->all();
        $result['readMessages'] = $messenger->checkMessagesIfRead($post['messages']);
        return $messenger->response($result);
    }

    /**
     * @Route("/messenger/message/read/messageId:{messageId}/userId:{userId}/contactId:{contactId}", name="user_messenger_message_read")
     */
    public function readMessageAction($messageId, $userId, $contactId)
    {
        $options['userId'] = $userId;
        $options['contactId'] = $contactId;

        $chat = new Chat($options);

        if ($this->canReadMessagesInChat($userId, $contactId)) {
            $result = $chat->setMessageAsRead($messageId);
        }

        return $chat->response(array('success' => $result ?? false));
    }

    /**
     * @Route("/messenger/message/notify/messageId:{messageId}/userId:{userId}", name="user_messenger_message_notify")
     */
    public function notifyMessageAction($messageId, $userId)
    {
        $messenger = $this->get('messenger');
        $result = $messenger->setMessageAsNotified($messageId);
        return $messenger->response(array('success' => $result));
    }

    /**
     * @Route("/messenger/message/delete/userId:{userId}/{message_id}/{contact_id}/{delete_from}", name="user_messenger_message_ttt")
     */
    public function postDeleteMessageAction($message_id, $contact_id, $delete_from, $userId)
    {
        if ($message_id && $delete_from !== null && $userId) {
            $messenger = $this->get('messenger');
            $result = $messenger->deleteMessage($message_id, $delete_from, $userId, $contact_id);
            echo $result;die;
        }
    }

    /**
     * @Route("/messenger/message/messageId:{messageId}/userId:{userId}/useFreePointToRead", name="user_use_free_point_to_read")
     */
    public function useFreePointToReadMessageAction($messageId, $userId)
    {
        $messenger = $this->get('messenger');
        return $messenger->useFreePointToReadMessage($messageId, $userId);
    }

    /**
     * @param UserRepository $userRepository object
     * @param $contact_id integer The id of contact user
     *
     *
     *  set the dialog between current user and contact user as deleted for current user
     *
     * @return JsonResponse
     *
     * @Route("/messenger/delete/dialog/{contact_id}", name="messenger_delete_dialog")
     */
    public function deleteDialog($contact_id)
    {
        $res = $this->getDoctrine()->getManager()->getRepository('AppBundle:User')->deleteDialog($this->getUser()->getId(), $contact_id);
        return new JsonResponse(array(
            'success' => $res['deleted'],
        ));
    }


    private function detectIsMobile()
    { // 'mobile' => $this->detectIsMobile(),
        $mobileDetector = $this->get('mobile_detect.mobile_detector');
        return $mobileDetector->isMobile();
    }

    private function canReadMessagesInChat($userId, $contactId)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('AppBundle:User')->find($userId);
        $contact = $em->getRepository('AppBundle:User')->find($contactId);

        $settings = $this->getDoctrine()->getRepository('AppBundle:Settings')->find(1);

        if (!$settings->getIsCharge()
            && strpos($contact->getEmail(), 'interdate') === false
            && strpos($user->getEmail(), 'interdate') === false) {
            return true;
        }

        if (!in_array($user->getGender()->getId(), [1, 4]) && in_array($contact->getGender()->getId(), [1, 4])) {
            return true;
        }

        if ($user->isPaying() || $contact->isPaying()) {
            return true;
        }

        return false;
    }


}
