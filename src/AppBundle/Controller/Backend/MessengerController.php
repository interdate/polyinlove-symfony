<?php

namespace AppBundle\Controller\Backend;

use AppBundle\Entity\Article;
use AppBundle\Form\Type\ArticleType;
use AppBundle\Services\Messenger\Chat;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MessengerController extends Controller
{
    /**
     * @Route("/admin/messenger/list/{page}", defaults={"page" = 1, "userId" = null}, name="admin_messenger")
     * @Route("/admin/messenger/user/{userId}/{page}", defaults={"page" = 1, "userId" = null}, name="admin_messenger_user")
     */
    public function indexAction(Request $request, $page, $userId)
    {

        $perPage = $userId === null ? 100 : 50;
        $messages = $this->get('messenger')->getUsersMessages($page, $perPage, $userId, $this->getDoctrine()->getConnection());
        $messagesNumber = $this->get('messenger')->getUsersMessagesNumber($userId);

        $route = $request->get('_route');
        if($route == 'admin_messenger'){
            $title = "All messages";
            $icon = 'list';
        }
        else{
            $title = "messages from"
                . $this->getDoctrine()->getRepository('AppBundle:User')->find($userId)->getUsername();
            $icon = 'wechat';
        }
       // dump($messages);die;
        return $this->render('backend/messenger/index.html.twig', array(
            'messages' => $messages,
            'title' => $title,
            'icon' => $icon,
        	'userId' => $userId,
            'pagination' => array(
                'page' => $page,
                'route' => $route,
                'pages_count' => ceil($messagesNumber / $perPage),
            )
        ));
    }

    /**
     * @Route("/admin/messenger/messages/delete", name="admin_messenger_messages_delete")
     */
    public  function removeMessagesAction(Request $request){
        $messagesIds = $request->request->get('messagesIds');
        $this->get('messenger')->removeMessages($messagesIds);
        return new Response();
    }
    
    /**
     * @Route("/admin/messenger/send", name="admin_messenger_send")
     */
    public function sendMessagesAction(Request $request)
    {
        $messenger = $this->get('messenger');
    	$reportsRepo = $this->getDoctrine()->getManager()->getRepository('AppBundle:Report');
    	$reports = $reportsRepo->findAll();
    	$message = $request->request->get('message', '');
    	$message = str_replace("../../",$this->getParameter('base_url'),$message);
    	$message = urlencode($message);
    	$toUsers = $request->request->get('reportId', false);
    	$title = "Sent Message to Users";
    	$icon = 'send';
    	$send = false;
        if($toUsers != false and !empty($message)){
            $usersRepo = $this->getDoctrine()->getManager()->getRepository('AppBundle:User');
            $report = $reportsRepo->find($request->request->get('reportId'));
            $data = json_decode($report->getParams(), true);
            $data['filter'] = '';

            $users = $usersRepo->setAdminMode()->search(
                array(
                    'current_user' => $this->getUser(),
                    'data' => $data,
                    'allResults' => true
                )
            );

            $options['userId'] = $this->getUser()->getId();

            $options['message'] = $message;

            $usersId = '';
            $usersIdArray = [];
            foreach((array)$users as $user){
                $contactId = $user->getId();
                $usersIdArray[] = $contactId;

                if ($usersId == '') {
                    $usersId .= $contactId;
                } else {
                    $usersId .= ', ' . $contactId;
                }
            }
            $messenger->sendMessageFromAdmin($usersIdArray, $message);
            $data = array(
                'users' => $usersId,
                'message' => ' קיבלת הודעה חדשה מ admin',
                'user_id' => 111,
                'onlyInBackgroundMode' => false,
            );

            $messenger = $this->get('messenger');

            $messenger->getPushToken($data, 'android');
            $messenger->getPushToken($data, 'browser');


            $messenger->adminAndroidPush($data);
            $messenger->adminWebPush($data);

            $send = true;
        }

    	return $this->render('backend/messenger/send.html.twig', array(
    			'messages' => $message,
    			'title' => $title,
    			'icon' => $icon,
    			'reports' => $reports,
    			'send' => $send
    	));
    }

    public function setUpCloudinary()
    {
        \Cloudinary::config(array(
            "cloud_name" => "interdate",
            "api_key" => "771234826869846",
            "api_secret" => "-OWKuCgP1GtTjIgRhwfOUVu1jO8"
        ));
    }


}
