<?php

namespace AppBundle\Controller\Api\V1;

use AppBundle\Entity\Push;
use AppBundle\Entity\UserMessengerNotifications;
use AppBundle\Services\Messenger\Chat;
use AppBundle\Services\Messenger\Dialog;

use Doctrine\ORM\Query;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Entity\User;
use AppBundle\Entity\Photo;
use Symfony\Component\Validator\Constraints\DateTime;


session_write_close();

class MessengerController extends FOSRestController
{
    protected $headers = array(
        'Content-Type' => 'text/html;charset=UTF-8',
        'Access-Control-Allow-Origin' => '*',
        'Cache-Control' => 'public',
        'Access-Control-Allow-Methods' => 'GET, POST, PUT, DELETE, PATCH, OPTIONS',
        'Pragma' => 'public',
        'Access-Control-Expose-Headers' => 'Access-Control-*',
        'Access-Control-Allow-Headers' => 'Access-Control-*, Origin, X-Requested-With, Content-Type, Accept',
        'Allow' => 'GET, POST, PUT, DELETE, OPTIONS, HEAD'
    );

    /**
     * @ApiDoc(
     *   resource = true,
     *   description = "Set user device token",
     *   parameters = {
     *     {"name"="phone_id", "dataType"="string", "required"=true, "description"="Device Token Id. "}
     *   },
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     401 = "Returned when bad credentials were sent"
     *   }
     * )
     */

    public function postPhonesAction(Request $request)
    {

        $mobileDetector = $this->get('mobile_detect.mobile_detector');

        $mob_os = '';
        if ($mobileDetector->isAndroidOS()) {
            $mob_os = 'android';
        }
        if ($mobileDetector->isIOS()) {
            $mob_os = 'ios';
        }

        $token = $request->get('phone_id', false);
        $model = $request->get('model', '');
        $uuid = $request->get('uuid', '');
        $os = $request->get('os', '');

        $result = array('success' => false);
        if (($mob_os == 'ios' || $mob_os == 'android') && !empty($token)) {

            $user = $this->get('security.token_storage')->getToken()->getUser();

            $userId = $user->getId();
            $messenger = $this->get('messenger');
//            $userId = $messenger->getUserIdByDeviceToken($mob_os, $token);
            $result = array('success' => $messenger->setUserDevice($mob_os, $token, $userId, $model, $uuid, $os));
            //var_dump($result); die;
        }
        return $this->view($result, Response::HTTP_OK);

    }


    /**
     * @ApiDoc(
     *   resource = true,
     *   description = "Get User Inbox",
     *
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     401 = "Returned when bad credentials were sent"
     *   }
     * )
     * @param Request $request
     * @return View
     */

    public function getInboxAction(Request $request)
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $dialogs = $this->getDoctrine()->getRepository('AppBundle:User')->newGetDialogs($user);

        $notifications = $this->getNotifications();

        return $this->view(array(
            'title' => 'Messages',
            'dialogs' => $dialogs,
            'notifications' => $notifications,
            'texts' => array('no_results' => ' No messages '),
        ), Response::HTTP_OK, $this->headers);
    }

    /**
     * @ApiDoc(
     *   resource = true,
     *   description = "Get dialog with contact",
     *
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     401 = "Returned when bad credentials were sent"
     *   }
     * )
     */

    public function getDialogAction(User $contact, Request $request)
    {
        $version = $request->headers->get('version');
        $page = $request->get('page', 1);
        $per_page = $request->get('per_page', 30);

        $user = $this->get('security.token_storage')->getToken()->getUser();
        $userId = $user->getId();
        $contactId = $contact->getId();

        $dialog = new Dialog(array(
            'userId' => $userId,
            'contactId' => $contactId,
        ));


        $photo = new Photo();

        $repository = $this->getDoctrine()->getRepository(Photo::class);
        $photo = $repository->findOneBy(array(
            'user' => $contact->getId(),
            'isMain' => 1,
            'isValid' => 1,
        ));


        $repository = $this->getDoctrine()->getRepository(User::class);
        $user = $repository->findOneBy(array(
            'id' => $contact->getId(),
        ));

        if (!$photo) {
            $photo = "";
        }
        return $this->view(array(
            'dialog' => $dialog,
            'history' => $dialog->oldGetHistory($page, $per_page),
            'texts' => array(
                'name' => $user->getUsername(),
                'title' => $this->get('translator')->trans('Messages'),
                'a_conversation_with' => $this->get('translator')->trans('Chat with'),
                'send' => $this->get('translator')->trans('Send'),
            ),
            'fullPhoto' => $this->getParameter('base_url') . ($contact->getMainPhoto() ? $contact->getMainPhoto()->getWebPath() : $user->getNoPhoto()),
            'contactImage' => $contact->getMainPhoto() ? $contact->getMainPhoto()->getFaceWebPath() : $contact->getNoPhoto(),
            'quickMessages' => $this->getDoctrine()->getManager()->getRepository('AppBundle:InlineMessages')->findAll(),
            'allowedToReadMessage' => $this->canReadMessagesInChat($contactId),
            'payment' => array(
                'paymentText' => $this->get('translator')->trans('You may not read this message. Please click here to purchase a subscription.'),
                'payLinkText' => $this->get('translator')->trans('Purchase subscription here'),
                'hasPoints' => '',
                'or' => $this->get('translator')->trans(' or '),
                'pointsText' => $this->get('translator')->trans(' user a point'),
            ),


        ), Response::HTTP_OK, $this->headers);
    }


    /**
     * @ApiDoc(
     *   resource = true,
     *   description = "Get new messages in active chat",
     *     parameters = {
     *     {"name"="messages", "dataType"="string", "required"= false, "description"="Array of Messages Id's."}
     *   },
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     401 = "Returned when bad credentials were sent"
     *   }
     * )
     */

    public function getChatsNewMessagesAction(User $contact, Request $request)
    {

        $user = $this->get('security.token_storage')->getToken()->getUser();
        $userId = $user->getId();
        $contactId = $contact->getId();
        $options['userId'] = $userId;
        $messenger = $this->get('messenger', 'messages');
        $messages = rtrim($request->get('messages', false), ',');
        $options['messages'] = ($messages) ? implode(',', $messages) : '';
        $options['notReadMess'] = $request->get('notReadMess');
        $options['contactId'] = $contactId;
        $options['lastMess'] = $request->get('lastMess');
        $result = $messenger->checkDialogNewMessages($options);

        return $messenger->response($result);


    }

    /**
     * @ApiDoc(
     *   resource = true,
     *   description = "Check messages",
     *   parameters = {
     *     {"name"="messages", "dataType"="string", "required"=true, "description"="Array of Messages Id's."}
     *   },
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     401 = "Returned when bad credentials were sent"
     *   }
     * )
     */

    public function postCheckMessagesAction(Request $request)
    {

        $messages = $request->get('messages');
        $messages = implode(',', $messages);
        $messenger = $this->get('messenger');
        $result['readMessages'] = $messenger->checkMessagesIfReadApi($messages);
        return $this->view($result, Response::HTTP_OK);
    }


    /**
     * @ApiDoc(
     *   resource = true,
     *   description = "Get count of new messages, count of new notifications and new Messages not notified",
     *
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     401 = "Returned when bad credentials were sent"
     *   }
     * )
     */

    public function getNewMessagesAction(Request $request)
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $conn = $this->getDoctrine()->getManager()->getConnection();
        $res = $conn->query("CALL get_new_messages ('"
            . $user->getId() . "', '"
            . $user->getGender()->getId() . "')")
            ->fetch();
        if ($res['newMessagesNumber'] > 0) {
            $res['messages'] = $conn->query("CALL get_new_messages_not_notified ('"
                . $user->getId() . "')")
                ->fetchAll();
            $i = 0;

            foreach ($res['messages'] as $key => $message) {

                $options['userId'] = $res['messages'][$i]['userId'];
                $options['contactId'] = $user->getId();
                $chat = new Chat($options);
                $res['messages'][$i]['is_not_sent_today'] = $options['contactId'] == 5548 ? true : $chat->isLastFromContact();
                $res['messages'][$i]['mainPhoto'] = $res['messages'][$i]['ext'] ?
                    '/media/photos/' . $res['messages'][$i]['userId'] . '/' . $res['messages'][$i]['photo'] . '.' . $res['messages'][$i]['ext']
                    : $res['messages'][$i]['noPhoto'];
                $text = nl2br(urldecode($message['text']));
                $res['messages'][$i]['text'] = $text;
                $res['messages'][$i]['newMessagesText'] = $this->get('translator')->trans('new message!');
                $i++;

            }

        } else {
            $res['messages'] = array();
        }

        $res['timeout'] = 30 * 1000;
        return $this->view($res, Response::HTTP_OK);
    }


    /**
     * @ApiDoc(
     *   resource = true,
     *   description = "Send message",
     *   parameters = {
     *     {"name"="message", "dataType"="string", "required"=true, "description"="Message text."}
     *   },
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     401 = "Returned when bad credentials were sent"
     *   }
     * )
     */

    public function postSendMessageAction(Request $request, User $contact)
    {

        $user = $this->get('security.token_storage')->getToken()->getUser();
        $userId = $user->getId();
        $contactId = $contact->getId();
        $message = trim($request->get('message'));

        $mess = $options['message'] = trim(strip_tags($message));
        $options['message'] = urlencode($message);
        $options['userId'] = $userId;
        $options['contactId'] = $contactId;
        $options['messageId'] = $request->request->get('quickMessage', false);
        $options['isInline'] = $options['messageId'] ? 1 : 0;

        if (strlen($message) > 0 || $options['isInline'] == 1) {
            if ($options['isInline']) {
                $message2 = $this->getDoctrine()->getManager()->getRepository('AppBundle:InlineMessages')->findOneBy(array(
                    'id' => $options['messageId']
                ));
                $text = $message2->getText();
                $options['message'] = trim(strip_tags($text));
                $options['message'] = urlencode($text);
            }

            $contact = $this->getDoctrine()->getManager()->getRepository('AppBundle:User')->find($contactId);
            $contact->getAgeFrom();
            $contact->getAgeTo();
            $user = $this->getUser();

            $userAge = $user->age();
            $userGender = $user->getGender()->getId();

            $contactCanContact = array();
            foreach ($contact->getContactGender() as $contact2) {
                $contactCanContact[] = $contact2->getId();
            }

            $chat = new Chat($options);

            $messenger = $this->get('messenger');

            //check if the user agree to your gender and age to write him
            if (!$messenger->CheckIfCanWriteTo($user, $contact)) {
                return $chat->response(array(
                    'success' => false,
                    'cantContact' => true,
                    'errorMessage' => $this->get('Translator')->trans('Due to this users settings, you may not contact them '),
                ));
            }

            $chatIsNotActive = $chat->getUsersBlockStatus();

            if ($chatIsNotActive != '0') {
                $message =
                    ($chatIsNotActive == '1')
                        ?
                        $this->get('translator')->trans('This user has been blocked by the site admins, and can not be sent messages')//This message was not sent because this user has been blocked by the administrator.
                        :
                        $this->get('translator')->trans('You have ben blocked by the site admins, and may not send messages.');

                return $this->view(array('success' => false, 'chatIsNotActive' => $chatIsNotActive, 'errorMessage' => $message), Response::HTTP_OK);
            }

            if ($chat->isForbidden()) {
                if ($chat->isForbidden() == 1) {
                    $errMess = $this->get('translator')->trans('You have blocked this user, and, may not message him.');
                } else {
                    $errMess = $this->get('translator')->trans('This user has blocked you, and you may not message them');
                }
                return $this->view(array('success' => false, 'chatIsForbidden' => true, 'errorMessage' => $errMess), Response::HTTP_OK);
            }

            if ($chat->contact()->isFrozen()) {
                return $this->view(array('success' => false, 'contactIsFrozen' => true, 'errorMessage' => $this->get('translator')->trans('this message was not sent, because the users account was frozen')), Response::HTTP_OK); //This message was not sent because it user froze the account.
            }

            $settings = $this->getDoctrine()->getRepository('AppBundle:Settings')->find(1);

            if ($chat->isLimit($settings->getSendMessageUsersNumber())) {
                return $this->view(array('success' => false, 'isLimit' => true, 'errorMessage' => $this->get('translator')->trans('You have reached the limit of new contacts allowed per day')), Response::HTTP_OK);//You have reached the maximum amount of messaging.
            }


            $messageObj = $chat->sendMessage($mess);

            if ($messageObj) {

                return $this->view(array(
                    'success' => true,
                    'message' => $messageObj,
                    'allowedToReadMessage' => $this->canReadMessagesInChat($contactId),
                ), Response::HTTP_OK);


            }
        }
        if (!isset($errorMessage))
            return $this->view(array('success' => false,
                'errorMessage' => $this->get('translator')->trans('Message not sent, please try again')), Response::HTTP_OK);
    }

    /**
     * @ApiDoc(
     *   resource = true,
     *   description = "Send push",
     *
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     401 = "Returned when bad credentials were sent"
     *   }
     * )
     */
    public function postSendPushAction(Request $request, User $contact)
    {


        $user = $this->get('security.token_storage')->getToken()->getUser();
        $userId = $user->getId();
        $contactId = $contact->getId();


        $options['userId'] = $userId;
        $options['contactId'] = $contactId;

        $chat = new Chat($options);
        $settings = $this->getDoctrine()->getRepository('AppBundle:Settings')->find(1);
        if ($chat->isLastFromContact() || $contactId == 9118 || $contactId == 9119) {
            if ($contact->getIsSentEmail() && $chat->isNotSentToday()) {
                $contact = $this->getDoctrine()->getRepository('AppBundle:User')->find($chat->contact()->getId());
                $user = $this->getDoctrine()->getRepository('AppBundle:User')->find($chat->user()->getId());

                $subject = $this->getParameter('site_name') . '| ' . $this->get('translator')->trans('new message')
                    . ' | ' . date("d/m/Y");

                $image = ($user->getMainPhoto() === null) ? $user->getNoPhoto() : $user->getMainPhoto()->getFaceWebPath();

                $body = '<div dir="ltr">';
                $body .= $this->get('translator')->trans('A new message is waiting for you at ') . $this->getParameter('site_name') . '<br>';
                $body .= $this->get('translator')->trans(' from ') . '<strong>' . $user->getUsername() . '</strong><br>';
                $body .= $this->get('translator')->trans('age: ') . $user->age() . '<br>';
                $body .= $this->get('translator')->trans('city: ') . $user->getCity()->getName() . '<br>';
                $body .= '</div>';
                $body .= '<p dir="rtl">'
                    . $this->get('translator')->trans('regards,')
                    . '<br>'
                    . $this->get('translator')->trans('from the ') . $this->getParameter('site_name') . ' team.'
                    . '<br><br><a href="https://' . $this->getParameter('base_url') . '" target="_blank">'
                    . $this->get('translator')->trans('')
                    . '</a></p>';

                $headers = "MIME-Version: 1.0" . "\r\n";
                $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
                $headers .= 'From: Admin <' . $settings->getContactEmail() . '>' . "\r\n";
                mail($contact->getEmail(), $subject, $body, $headers);
            }
            if ($contact->getIsSentPush() || $userId == 9118 || $contactId == 9118) {
                $messenger = $this->get('messenger');
                $image = $user->getMainPhoto() ? $user->getMainPhoto()->getFaceWebPath() : $user->getNoPhoto();
                $image = 'https://' . $this->getParameter('base_url') . $image;
                $data = array(
                    'message' => $this->get('translator')->trans(' you got a new message from') . $user->getUsername(),
                    'image' => $image,
                    'contact_id' => $contactId,
                    'user_id' => $userId,
                    'version' => $request->headers->get('version', 0),
                );
                return $messenger->pushNotification1($data, $this->getParameter('fcm_auth_key'), $this->getParameter('site_name'), $this->getParameter('base_url'));

            }

        }
    }


    /**
     * @ApiDoc(
     *   resource = true,
     *   description = "Set message as read",
     *   parameters = {
     *     {"name"="message_id", "dataType"="integer", "required"=true, "description"="Message id."}
     *   },
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     401 = "Returned when bad credentials were sent"
     *   }
     * )
     */

    public function postReadMessageAction(Request $request, User $contact)
    {
        $messagesId = $request->get('messages_id', false);
        $result = false;
        if ($messagesId) {

            $user = $this->get('security.token_storage')->getToken()->getUser();
            $userId = $user->getId();
            $contactId = $contact->getId();

            $options['userId'] = $userId;
            $options['contactId'] = $contactId;

            $chat = new Chat($options);
            return $result = $chat->setMessageAsRead($messagesId);
        }
        return $this->view(array('success' => $result), Response::HTTP_OK);
    }


    /**
     * @ApiDoc(
     *   resource = true,
     *   description = "Set message as notified",
     *   parameters = {
     *     {"name"="message_id", "dataType"="integer", "required"=true, "description"="Message id."}
     *   },
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     401 = "Returned when bad credentials were sent"
     *   }
     * )
     */

    public function notifyMessageAction(Request $request)
    {
        $messageId = $request->get('message_id', false);
        $result = false;
        if ($messageId) {
            $messenger = $this->get('messenger');
            $result = $messenger->setMessageAsNotified($messageId);
        }
        return $this->view(array('success' => $result), Response::HTTP_OK);
    }


    /**
     * @ApiDoc(
     *   resource = true,
     *   description = "Use free point for read message",
     *   parameters = {
     *     {"name"="message_id", "dataType"="integer", "required"=true, "description"="Message id."}
     *   },
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     401 = "Returned when bad credentials were sent"
     *   }
     * )
     */

    public function useFreePointToReadMessageAction(Request $request)
    {
        $messageId = $request->get('message_id', false);
        $result = array('success' => false);
        if ($messageId) {
            $user = $this->get('security.token_storage')->getToken()->getUser();
            $userId = $user->getId();
            $messenger = $this->get('messenger');
            $result = $messenger->useFreePointToReadMessage($messageId, $userId, false);
        }
        return $this->view($result, Response::HTTP_OK);
    }

    /**
     * @ApiDoc(
     *   resource = true,
     *   description = "Set message as deleted",
     *   parameters = {
     *     {"name"="message_id", "dataType"="integer", "required"=true, "description"="Message id."},
     *     {"name"="delete_from", "dataType"="boolean", "required"=true, "description"="delete from or to"}
     *   },
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     401 = "Returned when bad credentials were sent"
     *   }
     * )
     */
    public function postDeleteMessageAction(Request $request)
    {
        $message_id = $request->get('messageId', false);
        $delete_from = $request->get('deleteFrom', null);
        $contact_id = $request->get('contactId', false);
        $user_id = $request->get('userId', false);
        if ($message_id && $delete_from !== null && $user_id) {
            $messenger = $this->get('messenger');
            $result = $messenger->deleteMessage($message_id, $delete_from, $user_id, $contact_id);
            return $result;
        }
    }


    /**
     * @ApiDoc(
     *   resource = true,
     *   description = "Delete dialog",
     *   parameters = {
     *     {"name"="contact_id", "dataType"="integer", "required"=true, "description"="contact id"},
     *     {"name"="user_id", "dataType"="integer", "required"=true, "description"="user id"}
     *   },
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     401 = "Returned when bad credentials were sent"
     *   }
     * )
     */
    public function postDeleteInboxAction(Request $request)
    {
        $user = $this->getUser()->getId(); // $request->get('user_id');
        $contact = $request->get('contact_id');

        if ($user && $contact) {
            return $this->getDoctrine()->getRepository('AppBundle:User')->deleteDialog($user, $contact);
        } else {
            return array(
                'deleted' => false
            );
        }
    }


    public function postQuickMessageAction(Request $request)
    {
        $messageId = $request->request->get('message');
        $message = $this->getDoctrine()->getRepository('AppBundle:InlineMessage')->find($messageId);
        if ($message) {

        }
    }


    /**
     * @ApiDoc(
     *   resource = true,
     *   description = "Check if can write to user",
     *   parameters = {
     *     {"name"="$contact", "dataType"="integer", "required"=true, "description"="contact id"},
     *   },
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     401 = "Returned when bad credentials were sent"
     *   }
     * )
     */
    public function getWriteAction(User $contact)
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();

        if (!$user->getMainPhoto(true) && in_array($user->getGender()->getId(), [1, 4])) {
            $settings = $this->getDoctrine()->getRepository('AppBundle:Settings')->find(1);
            if (!$this->get('messenger')->checkIfCanCreateChatToday($user->getId(), $contact->getId(), $settings->getSendMessageUsersNumberWithoutPhoto())) {
                return $this->view(array(
                    'canContact' => false,
                    'message' => array(
                        'messageText' =>
                            $this->get('translator')->trans('You have reached the maximum number of messages that can be sent by an account with no picture. Would you like to send more messages? Upload a picture and the number of messages to be sent will be increased. '),
                        'btns' => array(
                            'ok' => 'OK'
                        ),
                    ),

                ), Response::HTTP_OK);
            }

        }

        return $this->view(array(
            'canContact' => $this->get('messenger')->CheckIfCanWriteTo($user, $contact),
            'message' => array(
                'messageText' => $this->get('translator')->trans('You may not message this user due to their settings'),
                'messageHeader' => $this->get('translator')->trans('You may not message this user'),
                'btns' => array(
                    'ok' => 'OK'
                ),
            ),

        ), Response::HTTP_OK);

    }

    /**
     * @ApiDoc(
     *   resource = true,
     *   description = "Set users messengerNotifications as read"
     * )
     */

    public function postMessengerNotificationsReadsAction()
    {
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();

        $notifications = $user->getMessengerNotifications();

        foreach ($notifications as $notification) {
            $notification->setIsRead(true);
            $em->persist($notification);
            $em->flush();
        }
    }


    public function canReadMessagesInChat($contactId)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $contact = $em->getRepository('AppBundle:User')->find($contactId);

        $settings = $this->getDoctrine()->getRepository('AppBundle:Settings')->find(1);

        if (!$settings->getIsCharge()
//            && strpos($user->getEmail(), 'interdate') === false
//            && strpos($contact->getEmail(), 'interdate') === false
        ) {
            return true;
        }

        if (in_array($user->getGender()->getId(), [2, 3])) {
            return true;
        }

        if (!in_array($user->getGender()->getId(), [1, 4]) && !in_array($contact->getGender()->getId(), [1, 4])) {
            return true;
        }

        return $user->isPaying() || $contact->isPaying();

    }

    private function getNotifications()
    {
        $user = $this->getUser();
        $notifs = $user->getMessengerNotifications();

        $new_notifs = [];
        $not_read = 0;
        $lastText = '';

        foreach ($notifs as $notif) {

            $new_array = [
                'id' => $notif->getId(),
                'date' => $notif->getDate()->format('Y/m/d'),
                'isRead' => $notif->getIsRead(),
            ];

            if (!$notif->getIsRead()) {
                $not_read++;
            }

            if ($push = $notif->getPush()) {
                $new_array['push'] = [
                    'title' => $push->getTitle(),
                    'message' => $push->getMessage(),
                    'webLink' => $push->getWebLink(),
                    'appLink' => $push->getAppLink(),
                    'webAppLink' => $push->getAppWebLink(),
                    'type' => $push->getType(),
                ];

                if ($notif->getFromUser()) {
                    $new_array['push']['userFrom'] = [
                        'id' => $notif->getFromUser()->getId(),
                        'username' => $notif->getFromUser()->getUsername(),
                    ];
                }

                $lastText = $new_array['push']['message'];
            } else {
                $new_array['notification'] = [
                    'template' => str_replace('[USERNAME]',
                        $notif->getFromUser()->getUsername(), str_replace('[NUM]',
                            $notif->getLeftVerifies(), $notif->getNotification()->getTemplate())),
                    'fromUser' => $notif->getFromUser()->getId(),
                ];
                $lastText = $new_array['notification']['template'];
            }

            $new_notifs[] = $new_array;


        }

        return [
            'notifications' => $new_notifs,
            'notReadCount' => $not_read,
            'from' => $this->get('translator')->trans('Admin announcement'),
            'image' => 'https://' . $this->getParameter('base_url') . '/images/icon.png',
            'date' => !empty($notif) ? $notif->getDate()->format('d-m-Y H:i') : false,
            'lastText' => $lastText,
        ];
    }
}
