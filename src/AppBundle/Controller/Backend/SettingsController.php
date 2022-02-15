<?php

namespace AppBundle\Controller\Backend;


use AppBundle\Entity\Settings;
use AppBundle\Form\Type\SettingsType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SettingsController extends Controller
{
    /**
     * @Route("/admin/settings", name="admin_settings")
     */
    public function indexAction(Request $request)
    {
        try {


            $settings = $this->getDoctrine()->getManager()->getRepository('AppBundle:Settings')->find(1);
            $form = $this->createForm(SettingsType::class, $settings);

            if ($request->isMethod('Post')) {
                $form->handleRequest($request);
                if ($form->isValid() && $form->isSubmitted()) {
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($settings);
                    $em->flush();
                }
            }
        }
        catch (\Exception $e){
            var_dump($e);
        }
        return $this->render('backend/settings/index.html.twig', array(
            'form' => $form->createView(),
        ));
    }




}
