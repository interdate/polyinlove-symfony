<?php

namespace AppBundle\Controller;

use AppBundle\Entity\City;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Cookie;


class AjaxController extends Controller
{
    /**
     * @Route("/ajax/cities", )
     */
    public function getCities(Request $request)
    {
        $word = $request->query->get('word');

        $repository = $this->getDoctrine()->getRepository(City::class);
        $query = $repository->createQueryBuilder('p')
            ->select('p.id,p.name')
            ->where('p.name LIKE :name')
            ->setParameter('name', $word.'%')
            ->getQuery();

        $result = $query->getResult();

        $cities = $result;
        
        return new Response(json_encode($cities));
     //   return new Response(json_encode($request->query->get('word')));
    }
    
    /**
     * @Route("/close_app_notification", name="close_app_notification")
     */
    public function closeAppNotificationAction()
    {
    	$response = new Response();
    	$cookie = new Cookie('closeAppNotification', '1', strtotime('now + 7 days'));
    	$response->headers->setCookie($cookie);
    	return $response;
    }
 
}
