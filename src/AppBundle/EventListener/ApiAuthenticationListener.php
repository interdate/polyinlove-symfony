<?php


namespace AppBundle\EventListener;

use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;

class ApiAuthenticationListener
{

    /**
     * Handles security related exceptions.
     *
     * @param GetResponseForExceptionEvent $event An GetResponseForExceptionEvent instance
     */
    public function onCoreException(GetResponseForExceptionEvent $event)
    {

        $exception = $event->getException();
        $request = $event->getRequest();
            //var_dump($request->get('_route'));die;
        if (strpos($request->get('_route'), 'app_api_v2') !== false) {
            if ($exception instanceof AccessDeniedException || $exception instanceof AuthenticationCredentialsNotFoundException) {
                $username = $request->headers->get('php-auth-user');
                $password = $request->headers->get('php-auth-pw');
//                $user = $this->getDoctrine()->getRepository('AppBundle:User')->loadUserByUsername($username);
//                if($user->getFacebook() == $password){
//                    $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
//                    $this->get('security.token_storage')->setToken($token);
//                }
//                $responseData = array('status' => 403, 'msg' => 'User Not Authenticated');
//                $response = new JsonResponse();
//                $response->setData($responseData);
//                $response->setStatusCode($responseData['status']);
//                //$response = new Response('Forbiddens');
//                //$response->headers->set('X-Status-Code', 403);
//                $event->setResponse($response);

            }
        }
    }
}


