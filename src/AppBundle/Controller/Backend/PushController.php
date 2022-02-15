<?php
namespace AppBundle\Controller\Backend;


use AppBundle\Entity\Push;
use AppBundle\Entity\UserMessengerNotifications;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use AppBundle\Services\PushService\pushService;

class PushController  extends Controller
{


    /**
     * @Route("/admin/push", name="admin_push")
     */
    public function indexAction(Request $request) {
//        var_dump(123);
        $em = $this->getDoctrine()->getManager();
        $reportsRepo = $em->getRepository('AppBundle:Report');

        $errors = [];

        if ($request->getMethod() == 'POST') {

            $title_mess = $request->request->get('titleMess', false);
            $text_message = $request->request->get('message', false);
            $url = $request->request->get('url', false);
            $app_url = $request->request->get('app_url', false);
            $web_url = $request->request->get('web_url', false);
            $type = $request->request->get('type', false);

            if (empty($request->request->get('reportId', false))) {
                $errors['reportId'] = 'שדה זה הוא שדה חובה';
            }

            if (empty($title_mess)) {
                $errors['titleMess'] = 'שדה זה הוא שדה חובה';
            }

            if (empty($text_message)) {
                $errors['textMessage'] = 'שדה זה הוא שדה חובה';
            }

            if (empty($web_url)) {
                $errors['web_url'] = 'שדה זה הוא שדה חובה';
            }

            if ($type == 'linkOut' && empty($url)) {
                $errors['url'] = 'שדה זה הוא שדה חובה';
            }

            if ($type == 'app' && empty($app_url)) {
                $errors['app_url'] = 'שדה זה הוא שדה חובה';
            }

            if (empty($errors)) {
                $usersRepo = $em->getRepository('AppBundle:User');
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

//                var_dump(123);
                $push = new Push();
                $push->setTitle($title_mess);
                $push->setMessage($text_message);
                $push->setWebLink($web_url);
                $push->setAppLink($app_url);
                $push->setAppWebLink($url);
                $push->setType($type);
                $em->persist($push);



//                var_dump(423);

                $notif = $em->getRepository('AppBundle:Notifications')->find(6);

                $usersId = '';
                foreach ($users as $user) {

                    $notification = new UserMessengerNotifications();
                    $notification->setNotification($notif);
                    $notification->setPush($push);
                    $notification->setDate(new \DateTime());

                    if ($usersId == '') {
                        $usersId .= $user->getId();
                    } else {
                        $usersId .= ', ' . $user->getId();
                    }

                    $notification->setUser($user);
                    $em->persist($notification);
                }
                $em->flush();

//                var_dump(count($users));

//                $em->flush();
//                die;

                $data = array(
                    'users' => $usersId,
                    'url' => $app_url ? $app_url : $url,
                    'type' => $type,
                    'message' => $text_message,
                    'titleMess' => $title_mess,
                    'webUrl' => $web_url
                );

                $messenger = $this->get('messenger');
                $data['onlyInBackgroundMode'] = false;

                $messenger->getPushToken($data, 'android');
                $messenger->getPushToken($data, 'browser');

                $messenger->adminAndroidPush($data);
                $messenger->adminWebPush($data);

                $send = true;
            }
        }
//        die;

        return $this->render('backend/push/index.html.twig', array(
            'reports' =>  $reportsRepo->findAll(),
            'send' => $send ?? false,
            'errors' => $errors,
        ));
    }


}
