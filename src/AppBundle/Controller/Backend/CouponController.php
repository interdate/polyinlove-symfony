<?php

namespace AppBundle\Controller\Backend;

use AppBundle\AppBundle;
use AppBundle\Entity\Coupon;
use AppBundle\Form\Type\CouponType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\File as UploadedFile;


class CouponController extends Controller
{

    /**
     * @Route("/admin/coupon/page/{id}", name="admin_coupon_edit")
     */
    public function editCouponAction(Request $request, Coupon $page)
    {
        $form = $this->createForm(CouponType::class, $page);
        return $this->processPageForm($request, $form, $page);
    }

    /**
     * @Route("/admin/coupon/add/new", name="admin_coupon_add")
     */
    public function addCouponAction(Request $request)
    {
        $page = new Coupon();
        $form = $this->createForm(CouponType::class, $page);
        return $this->processPageForm($request, $form, $page);
    }


    public function processPageForm($request, $form, $page)
    {

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {

                $em = $this->getDoctrine()->getManager();
                $em->persist($page);
                $em->flush();
                return $this->redirect($this->generateUrl('admin_settings'));
            }
        }

        return $this->render('backend/coupon/coupon.html.twig', array(
            'form' => $form->createView(),
            'page' => $page,
        ));
    }


    /**
     * @Route("/admin/coupon/page/{id}/delete", name="admin_coupon_delete")
     */
    function deleteCouponAction(Page $page)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($page);
        $em->flush();
        return new Response();
    }


}
