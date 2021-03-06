<?php

namespace AppBundle\Controller\Backend;

use AppBundle\AppBundle;
use AppBundle\Entity\Banner;
use AppBundle\Entity\Faq;
use AppBundle\Entity\FaqCategory;
use AppBundle\Entity\FooterHeader;
use AppBundle\Entity\HomePage;
use AppBundle\Entity\Page;
use AppBundle\Entity\PaymentSubscription;
use AppBundle\Entity\Photo;
use AppBundle\Entity\Slide;
use AppBundle\Entity\File;
use AppBundle\Entity\TableTextPayment;
use AppBundle\Entity\TextAfterPayment;
use AppBundle\Entity\TextBeforePayment;
use AppBundle\Form\Type\BannerType;
use AppBundle\Form\Type\FaqCategoryType;
use AppBundle\Form\Type\FaqType;
use AppBundle\Form\Type\PageType;
use AppBundle\Form\Type\SlideType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\File as UploadedFile;



class ContentController extends Controller
{
    /**
     * @Route("/admin/content", name="admin_content")
     */
    public function indexAction()
    {
        $manager = $this->getDoctrine()->getManager();


        return $this->render('backend/content/index.html.twig', array(
            'pages' => $manager->getRepository('AppBundle:Page')->findAll(),
            'slides' => $manager->getRepository('AppBundle:Slide')->findAll(),
            'homePageBlocks' => $manager->getRepository('AppBundle:HomePage')->findAll(),
            'homePageSeo' => $manager->getRepository('AppBundle:Seo')->findOneByPage('homepage'),
            'faqPageSeo' => $manager->getRepository('AppBundle:Seo')->findOneByPage('faq'),
            'footerHeaders' => $manager->getRepository('AppBundle:FooterHeader')->findAll(),
            'faqCategories' => $manager->getRepository('AppBundle:FaqCategory')->findAll(),
            'banners' => $manager->getRepository('AppBundle:Banner')->findAll(),
        ));
    }

    /**
     * @Route("/admin/content/page", name="admin_content_page")
     */
    public function pageAction(Request $request)
    {
        $page = new Page();
        $form = $this->createForm(PageType::class, $page);
        return $this->processPageForm($request, $form, $page);
    }

    /**
     * @Route("/admin/content/page/{id}", name="admin_content_page_edit")
     */
    public function editPageAction(Request $request, Page $page)
    {
        $form = $this->createForm(PageType::class, $page);
        return $this->processPageForm($request, $form, $page);
    }


    public function processPageForm($request, $form, $page)
    {
        if($request->isMethod('POST')) {
            $form->handleRequest($request);
            if($form->isSubmitted() && $form->isValid()){
                if(empty(trim($page->getUri()))){
                    $page->setUri(str_replace(" ", "_", $page->getName()));
                }

                $em = $this->getDoctrine()->getManager();
                $em->persist($page);
                $em->flush();
                return $this->redirect($this->generateUrl('admin_content'));
            }
        }

        return $this->render('backend/content/page.html.twig', array(
            'form' => $form->createView(),
            'page' => $page,
        ));
    }

    /**
     * @Route("/admin/content/payment", name="admin_content_payment_edit")
     */
    public function editPaymentPage(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        if ($request->isMethod('POST')) {
            $title = $request->request->get('title', false);
            if($title !== false){
                $titleEntity = $em->getRepository('AppBundle:TextBeforePayment')->find(1);
                $titleEntity->setTitle($title);
                $em->persist($titleEntity);
                $em->flush();
                return new Response();
            }
        }
        $textBefore = $em->getRepository('AppBundle:TextBeforePayment')->findBy(array(),array('order' => 'asc'));
        $paymentSubscriptions = $em->getRepository('AppBundle:PaymentSubscription')->findBy(array(),array('order' => 'asc'));
        $tableTexts = $em->getRepository('AppBundle:TableTextPayment')->findBy(array(),array('order' => 'asc'));
        $textAfter = $em->getRepository('AppBundle:TextAfterPayment')->findBy(array(),array('order' => 'asc'));
        $coupons = $em->getRepository('AppBundle:Coupon')->findAll();
        return $this->render('backend/content/payment.html.twig', array(
            'textBefore' => $textBefore,
            'payments' => $paymentSubscriptions,
            'tableTexts' => $tableTexts,
            'textAfter' => $textAfter,
            'coupons' => $coupons
        ));
    }

    /**
     * @Route("/admin/content/payment/edit/{form}/{id}", name="admin_payment_content_edit")
     */
    public function editPaymentContentPage(Request $request, $form, $id = 0){
        $em = $this->getDoctrine()->getManager();
        $paymentContent = false;
        if((int)$id == 0) {
            if($form == 'TextBeforePayment') {
                $paymentContent = TextBeforePayment::class;
            }elseif ($form == 'PaymentSubscription'){
                $paymentContent = PaymentSubscription::class;
            }elseif ($form == 'TableTextPayment'){
                $paymentContent = TableTextPayment::class;
            }elseif ($form == 'TextAfterPayment'){
                $paymentContent = TextAfterPayment::class;
            }
        }else {
            $paymentContent = $em->getRepository('AppBundle:' . $form)->find($id);
        }

        if($paymentContent){
            $formEn = $this->createFormBuilder();
            $formEn->add('id', 'hidden', array(
                'data' => $paymentContent->getId()
            ))
            ->add('isActive', 'checkbox', array(
                'label' => $this->get('translator')->trans('active'),
                'required' => false,
                'data' => $paymentContent->getIsActive(),
            ))->add('order', 'text', array(
                'label' => 'Order',
                'data' => $paymentContent->getOrder(),
            ));

            if($form != 'TableTextPayment') {
                $formEn->add('text', 'textarea', array(
                    'label' => $this->get('translator')->trans('text'),
                    'data' => $paymentContent->getText(),
                    'required' => false,
                ));
            }
            if($form == 'TableTextPayment' or $form == 'PaymentSubscription') {
                $formEn->add('title', 'text', array(
                    'label' => $this->get('translator')->trans('Title'),
                    'data' => $paymentContent->getTitle(),
                    'required' => true,
                ));
            }
            if($form == 'TableTextPayment'){
                $formEn->add('pay', 'checkbox', array(
                    'label' => $this->get('translator')->trans('subscriber'),
                    'required' => false,
                    'data' => $paymentContent->getPay(),
                ))->add('notPay', 'checkbox', array(
                    'label' => $this->get('translator')->trans('member'),
                    'required' => false,
                    'data' => $paymentContent->getNotPay(),
                ));
            }
            if($form == 'PaymentSubscription'){
                $formEn->add('amount', 'text', array(
                    'label' => $this->get('translator')->trans('Amount'),
                    'data' => $paymentContent->getAmount(),
                    'required' => true,
                ))->add('price', 'text', array(
                    'label' =>$this->get('translator')->trans('Price in month'),
                    'data' => $paymentContent->getPrice(),
                    'required' => true,
                ))->add('textPrice', 'text', array(
                    'label' => $this->get('translator')->trans('Amount'),
                    'data' => (empty($paymentContent->getTextPrice())) ? $this->get('translator')->trans('per month') : $paymentContent->getTextPrice(),
                    'required' => true,
                ));
            }

            $formEn = $formEn->getForm();
            $formEn->handleRequest($request);
            if ($request->isMethod('POST')) {
                if ($formEn->isSubmitted() && $formEn->isValid()) {
                    $data = $formEn->getData();
                    if((int)$data['order'] > 0){
                        $conn = $this->getDoctrine()->getManager()->getConnection();
                        $index = 1;
                        if($form == 'TextAfterPayment'){
                            $index = 2;
                        }elseif ($form == 'PaymentSubscription'){
                            $index = 3;
                        }elseif ($form == 'TableTextPayment'){
                            $index = 4;
                        }
                        $res = $conn->query("SELECT id FROM content_payment WHERE `part` = " . $index . " AND `order_id` = " . (int)$data['order'])
                            ->fetch();
                        if($res and $res['id'] != $paymentContent->getId()){
                            $stmt = $conn->prepare("UPDATE content_payment SET order_id = order_id + 1 WHERE `part` = " . $index . " AND `order_id` >= " . (int)$data['order']);
                            $stmt->execute();
                            $result = $stmt->fetch();
                        }
                    }

                    foreach ($data as $key => $val){
                        if($key == 'text'){
                            $paymentContent->setText($val);
                        }
                        if($key == 'title'){
                            $paymentContent->setTitle($val);
                        }
                        if($key == 'isActive'){
                            $paymentContent->setIsActive((boolean)$val);
                        }
                        if($key == 'pay'){
                            $paymentContent->setPay((boolean)$val);
                        }
                        if($key == 'notPay'){
                            $paymentContent->setNotPay((boolean)$val);
                        }
                        if($key == 'amount'){
                            $paymentContent->setAmount($val);
                        }
                        if($key == 'price'){
                            $paymentContent->setPrice($val);
                        }
                        if($key == 'textPrice'){
                            $paymentContent->setTextPrice($val);
                        }
                        if($key == 'order'){
                            $val = (int)$val;
                            $paymentContent->setOrder($val);
                        }
                    }
                    $em->persist($paymentContent);
                    $em->flush();
                    return $this->redirect($this->generateUrl('admin_content_payment_edit'));
                }
            }
        }
        return $this->render('backend/content/edit_payment.html.twig', array(
            'payment' => $paymentContent,
            'form' => $formEn->createView(),
            'type' => $form,
        ));
    }

    /**
     * @Route("/admin/content/page/{id}/{property}/{value}", name="admin_content_set_page_property")
     */
    function setPagePropertyAction(Page $page, $property, $value)
    {
        $em = $this->getDoctrine()->getManager();
        $setter = 'set' . ucfirst($property);
        $page->$setter($value);
        $em->persist($page);
        $em->flush();
        return new Response();
    }

    /**
     * @Route("/admin/content/page/{id}/delete", name="admin_content_page_delete")
     */
    function deletePageAction(Page $page)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($page);
        $em->flush();
        return new Response();
    }

    /**
     * @Route("/admin/content/slide/image/{id}", name="admin_content_slide_image_edit")
     */
    public function editSlideImageAction(Request $request, Slide $slide)
    {
        $file = $request->files->get('image');
        if($file instanceof UploadedFile){
            $slide->setFile($file);
            $em = $this->getDoctrine()->getManager();
            $slide->preUpload();
            $slide->upload();
            $em->persist($slide);
            $em->flush();
        }
        return new Response();
    }

    /**
     * @Route("/admin/content/slide/{id}", name="admin_content_slide_edit")
     */
    public function editSlideAction(Request $request, Slide $slide)
    {
        $form = $this->createForm(SlideType::class, $slide);
        if($request->isMethod('POST')) {
            $form->handleRequest($request);
            if($form->isSubmitted() && $form->isValid()){
                $em = $this->getDoctrine()->getManager();
                $em->persist($slide);
                $em->flush();
            }
        }

        return $this->render('backend/content/slide.html.twig', array(
            'form' => $form->createView(),
//            'renderedCloudForm' => $renderedCloudForm,
            'slide' => $slide,
        ));
    }

    /**
     * @Route("/admin/
     * content/homepage/block/{id}", name="admin_content_homepage_block_edit")
     */
    public function editHomePageBlockAction(Request $request, HomePage $homePageBlock)
    {
        $name = $request->request->get('name', null);
        $headerType = $request->request->get('headerType', null);
        $block1 = $request->request->get('block1', null);
        $block2 = $request->request->get('block2', null);
        $block3 = $request->request->get('block3', null);
        $block4 = $request->request->get('block4', null);
        $block5 = $request->request->get('block5', null);


        if(null !== $name){
            $homePageBlock->setName($name);
        }

        if(null !== $headerType){
            $homePageBlock->setHeaderType($headerType);
        }

        if(null !== $block1){
            $homePageBlock->setBlock1($block1);
        }

        if(null !== $block2){
            $homePageBlock->setBlock2($block2);
        }

        if(null !== $block3){
            $homePageBlock->setBlock3($block3);
        }

        if(null !== $block4){
            $homePageBlock->setBlock4($block4);
        }
        if(null !== $block5){
            $homePageBlock->setBlock5($block5);
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($homePageBlock);
        $em->flush();

        return new Response();
    }


    /**
     * @Route("/admin/content/pages/seo", name="admin_content_page_seo")
     */
    public function pageSeoAction(Request $request)
    {
        $page = $this->getDoctrine()->getManager()->getRepository('AppBundle:Seo')->findOneByPage(
            $request->request->get('page')
        );

        $page->setTitle($request->request->get('title'));
        $page->setDescription($request->request->get('description'));

        $em = $this->getDoctrine()->getManager();
        $em->persist($page);
        $em->flush();

        return new Response();
    }


    /**
     * @Route("/admin/content/footer/header/{id}", name="admin_content_footer_header_edit")
     */
    public function editFooterHeaderAction(Request $request, FooterHeader $footerHeader)
    {
        $name = $request->request->get('name', null);

        if(null !== $name){
            $footerHeader->setName($name);
        }
        $em = $this->getDoctrine()->getManager();
        $em->persist($footerHeader);
        $em->flush();

        return new Response();
    }

    /**
     * @Route("/admin/content/faq/category", name="admin_content_faq_category")
     */
    public function faqCategoryAction(Request $request)
    {
        $category = new FaqCategory();
        $form = $this->createForm(FaqCategoryType::class, $category);
        return $this->processFaqCategoryForm($request, $form, $category);
    }

    /**
     * @Route("/admin/content/faq/category/{id}", defaults={"id" = null}, name="admin_content_faq_category_edit")
     */
    public function editFaqCategoryAction(Request $request, FaqCategory $category)
    {
        $form = $this->createForm(FaqCategoryType::class, $category);
        return $this->processFaqCategoryForm($request, $form, $category);
    }

    public function processFaqCategoryForm($request, $form, $category)
    {
        if($request->isMethod('POST')) {
            $form->handleRequest($request);
            if($form->isSubmitted() && $form->isValid()){
                $em = $this->getDoctrine()->getManager();
                $em->persist($category);
                $em->flush();
                return $this->render('backend/content/faq_section.html.twig', array(
                    'active_tab' => $request->request->get('active_tab'),
                    'faqCategories' => $this->getDoctrine()
                        ->getManager()
                        ->getRepository('AppBundle:FaqCategory')
                        ->findAll(),
                    'faqPageSeo' => $this->getDoctrine()
                        ->getManager()
                        ->getRepository('AppBundle:Seo')
                        ->findOneByPage('faq'),
                ));
            }
        }

        return $this->render('backend/content/faq_category.html.twig', array(
            'form' => $form->createView(),
            'category' => $category,
        ));
    }

    /**
     * @Route("/admin/content/faq/category/{id}/{property}/{value}", defaults={"id" = null}, name="admin_content_faq_category_set_property")
     */
    public function setFaqCategoryPropertyAction(FaqCategory $category, $property, $value)
    {
        $em = $this->getDoctrine()->getManager();
        $setter = 'set' . ucfirst($property);
        $category->$setter($value);
        $em->persist($category);
        $em->flush();
        return new Response();
    }

    /**
     * @Route("/admin/content/faq/category/{id}/delete", name="admin_content_faq_category_delete")
     */
    function deleteFaqCategoryAction(FaqCategory $category)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($category);
        $em->flush();
        return new Response();
    }

    /**
     * @Route("/admin/content/banner/{id}/delete", name="admin_content_banner_delete")
     */
    function deleteBannerAction(Banner $banner)
    {
       // var_dump($banner->getId());die;
        $em = $this->getDoctrine()->getManager();
        $em->remove($banner);
        $em->flush();
        return new Response();
    }

    /**
     * @Route("/admin/content/faq", name="admin_content_faq")
     */
    public function faqAction(Request $request)
    {
        $faq = new Faq();
        $form = $this->createForm(FaqType::class, $faq);
        return $this->processFaqForm($request, $form, $faq);
    }

    /**
     * @Route("/admin/content/faq/{id}", requirements={"id": "\d+"}, name="admin_content_faq_edit")
     */
    public function editFaqAction(Request $request, Faq $faq)
    {
        $form = $this->createForm(FaqType::class, $faq);
        return $this->processFaqForm($request, $form, $faq);
    }


    /**
     * @Route("/admin/content/faq/{id}/delete", requirements={"id": "\d+"}, name="admin_content_faq_delete")
     */
    function deleteFaqAction(Faq $faq)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($faq);
        $em->flush();
        return new Response();
    }


    ///admin/banner/' + id + '/activate
    ///
    /**
     * @Route("/admin/banner/{id}/activate", name="admin_banner_active")
     */
    public function bannerActivation(Banner $banner) {
        $em = $this->getDoctrine()->getManager();
        $banner->setIsActive(!$banner->getIsActive());
        $em->persist($banner);
        $em->flush();
        return new Response();
    }


    /**
     * @Route("/admin/content/banner/{id}", name="admin_content_banner_edit")
     */
    public function editBannerAction(Request $request, Banner $banner)
    {

        $form = $this->createForm(BannerType::class, $banner);
        // $banner->setImg($this->getDoctrine()->getManager()->getRepository('AppBundle::Banner')->findBy('name' => ))
        //return $this->processPageForm($request, $form, $banner);
        return $this->render('backend/content/add_banner.html.twig', array(
            'title' => 'edit Banner',
            'form' => $form->createView(),
            //'id' => $banner->getId(),
            'banner' => $banner
        ));
    }

    /**
     * @Route("/admin/banner/add", requirements={"id": "\d+"}, name="admin_add_banner")
     */
    public function addBanner(Request $request) {
//        var_dump(123);die;
        if($id = $request->request->get('banner_id', false)) {
//            var_dump(432);die;
            $banner = $this->getDoctrine()->getManager()->getRepository('AppBundle:Banner')->find($id);
        } else {
            $banner = new Banner();
            $banner->setClickCount(0);
        }

        $img = $banner->getImg();
//        dump($img);die;

        $form = $this->createForm(BannerType::class, $banner);

        $em = $this->getDoctrine()->getManager();
        if ($request->getMethod() == 'POST') {
            $file = $request->files->get('banner')['img'];

            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {

              $banner = $form->getData();
                if (!$file) {
                  $banner->setImg($img);
                }

               // $banner->setIsActive(0);
                $em->persist($banner);
                $em->flush();


                if($file instanceof UploadedFile) {
                    $banner->preUpload($file);
                    $banner->upload($file);
                    $em->persist($banner);
                    $em->flush();
                }


            }
        }



        return $this->render('backend/content/add_banner.html.twig', array(
            'title' => $banner->getId() ? 'Edit Banner' : 'Add new Banner',
            'form' => $form->createView(),
            'banner' => $banner
        ));

    }


    public function processFaqForm($request, $form, $faq)
    {
        if($request->isMethod('POST')) {
            $form->handleRequest($request);
            if($form->isSubmitted() && $form->isValid()){
                $em = $this->getDoctrine()->getManager();
                $em->persist($faq);
                $em->flush();
                return $this->render('backend/content/faq_section.html.twig', array(
                    'active_tab' => $request->request->get('active_tab'),
                    'faqCategories' => $this->getDoctrine()
                        ->getManager()
                        ->getRepository('AppBundle:FaqCategory')
                        ->findAll(),
                    'faqPageSeo' => $this->getDoctrine()
                        ->getManager()
                        ->getRepository('AppBundle:Seo')
                        ->findOneByPage('faq'),
                ));
            }
        }

        return $this->render('backend/content/faq.html.twig', array(
            'form' => $form->createView(),
            'faq' => $faq,
        ));
    }

    public function setUpCloudinary()
    {
        \Cloudinary::config(array(
            "cloud_name" => "interdate",
            "api_key" => "771234826869846",
            "api_secret" => "-OWKuCgP1GtTjIgRhwfOUVu1jO8",
        ));
    }

}
