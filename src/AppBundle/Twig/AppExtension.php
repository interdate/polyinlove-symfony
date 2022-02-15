<?php

namespace AppBundle\Twig;

use AppBundle\Form\Type\AdminQuickSearchSidebarType;
use AppBundle\Form\Type\QuickSearchSidebarType;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class AppExtension extends \Twig_Extension
{
    protected $doctrine;
    protected $container;
    protected $requestStack;
    protected $messenger;

    public function __construct(RequestStack $requestStack, RegistryInterface $doctrine, $container, $messenger)
    {
        $this->requestStack = $requestStack;
        $this->doctrine = $doctrine;
        $this->container = $container;
        $this->messenger = $messenger;

    }

    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('json_decode', array($this, 'jsonDecodeFilter')),
            new \Twig_SimpleFilter('remove_main', array($this, 'removeMainPhoto')),
        );
    }

    public function jsonDecodeFilter($data){
        return json_decode($data, true);
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('getDownloadApp', array($this, 'getDownloadAppFunction')),
            new \Twig_SimpleFunction('getSettings', array($this, 'getSettingsFunction')),
            new \Twig_SimpleFunction('getUsersOnline', array($this, 'getUsersOnlineFunction')),
            new \Twig_SimpleFunction('getNewUsers', array($this, 'getNewUsersFunction')),
            new \Twig_SimpleFunction('getStatistics', array($this, 'getStatisticsFunction')),
            new \Twig_SimpleFunction('getQuickSearchSidebarForm', array($this, 'getQuickSearchSidebarFormFunction')),
            new \Twig_SimpleFunction('getAdminQuickSearchSidebarForm', array($this, 'getAdminQuickSearchSidebarFormFunction')),
            new \Twig_SimpleFunction('getFooterBlocks', array($this, 'getFooterBlocksFunction')),
            new \Twig_SimpleFunction('getZodiac', array($this, 'getZodiacFunction')),
            new \Twig_SimpleFunction('getDistance', array($this, 'getDistanceFunction')),
            new \Twig_SimpleFunction('jsonEncode', array($this, 'jsonEncodeFunction')),
        );
    }

    public function getDistanceFunction($user1,$user2){
        return $this->doctrine->getManager()->getRepository('AppBundle:User')->getDistance($user1,$user2);
    }

    public function getDownloadAppFunction($request)
    {
        //$request = $this->getRequest();
        $cookie = $request->cookies->all();
        if(isset($cookie['closeAppNotification']) and $cookie['closeAppNotification'] == 1){
            return false;
        }
        $mobileDetector = $this->container->get('mobile_detect.mobile_detector');
        $link = ($mobileDetector->isiOS()) ? 'https://itunes.apple.com/il/app/greendate-%D7%92%D7%A8%D7%99%D7%A0%D7%93%D7%99%D7%99%D7%98/id1282964919?mt=8' : 'https://play.google.com/store/apps/details?id=il.co.greendate';
        return $link;
    }

    public function getSettingsFunction()
    {
        return $this->doctrine->getManager()->getRepository('AppBundle:Settings')->find(1);
    }

    public function getUsersOnlineFunction()
    {
        $settings = $this->doctrine->getManager()->getRepository('AppBundle:Settings')->find(1);

        return $this->doctrine->getManager()->getRepository('AppBundle:User')->getOnline(
            array(
                'current_user' => $this->getUser() ? $this->getUser() : null,
                'data' => null,
                'paginator' => $this->container->get('knp_paginator'),
//                'allUsers' => true,
                'page' => 1,
                'per_page' => $settings->getUsersPerPage(),
                'considered_as_online_minutes_number' => $settings->getUserConsideredAsOnlineAfterLastActivityMinutesNumber(),
            )
        );
    }

    public function getNewUsersFunction($page = 1)
    {

        $settings = $this->doctrine->getManager()->getRepository('AppBundle:Settings')->find(1);

        return $this->doctrine->getManager()->getRepository('AppBundle:User')->getNew(
            array(
                'current_user' => $this->getUser() ? $this->getUser() : null,
                'data' => null,
                'paginator' => $this->container->get('knp_paginator'),
//                'allUsers' => true,
                'page' => $page,
                'per_page' => 44,
                'considered_as_online_minutes_number' => $settings->getUserConsideredAsOnlineAfterLastActivityMinutesNumber(),
            )
        );
    }

    public function getStatisticsFunction()
    {
        $settings = $this->doctrine->getManager()->getRepository('AppBundle:Settings')->find(1);
        $delay = new \DateTime();
        $delay->setTimestamp(strtotime(
                $settings->getUserConsideredAsOnlineAfterLastActivityMinutesNumber() . ' minutes ago')
        );

        $is_new_delay = new \DateTime();
        $is_new_delay->setTimestamp(strtotime(
                $settings->getUserConsideredAsNewAfterDaysNumber() . ' days ago')
        );

        $conn = $this->doctrine->getManager()->getConnection();
        return $conn->query("CALL get_statistics ('"
            . $delay->format('Y-m-d H:i:s.000000') . "', '"
            . $this->getUser()->getId() . "', '"
            . $this->getUser()->getGender()->getId() . "', '"
            . $is_new_delay->format('Y-m-d H:i:s.000000') . "')")
            ->fetch();
    }

    public function getQuickSearchSidebarFormFunction(){
        return $this->getSideBarForm(QuickSearchSidebarType::class);
    }

    public function getAdminQuickSearchSidebarFormFunction(){
        return $this->getSideBarForm(AdminQuickSearchSidebarType::class);
    }

    public function getSideBarForm($type){
        return $this->container->get('form.factory')
            ->create($type)
            ->handleRequest($this->requestStack->getCurrentRequest())
            ->createView();
    }

    public function getFooterBlocksFunction()
    {
        return $this->doctrine
            ->getManager()
            ->getRepository('AppBundle:FooterHeader')
            ->findAll()
            ;
    }

    public function getZodiacFunction($date = ""){

        $zodiac[355] = "Capricorn";
        $zodiac[325] = "Sagittarius";
        $zodiac[296] = "Scorpio";
        $zodiac[265] = "Libra";
        $zodiac[234] = "Virgo";
        $zodiac[203] = "Leo";
        $zodiac[172] = "Cancer";
        $zodiac[140] = "Gemini";
        $zodiac[110] = "Taurus";
        $zodiac[79]  = "Aries";
        $zodiac[49]  = "Pisces";
        $zodiac[21]  = "Aquarius";
        $zodiac[0]   = "Capricorn";


        if (!$date) $date = time();
        $dayOfTheYear = date("z",$date);
        $isLeapYear = date("L",$date);

        if ($isLeapYear && ($dayOfTheYear > 59)){
            $dayOfTheYear = $dayOfTheYear + 1;
        }
        foreach($zodiac as $day => $sign){
            if ($dayOfTheYear >= $day){
                break;
            }
        }
        return $sign;
    }

    public function getUser()
    {
        return $this->container->get('security.token_storage')->getToken()->getUser();
    }

    public function removeMainPhoto($array) {
        foreach($array as $index => $photo) {
            if ($photo->getIsMain()) {
                unset($array[$index]);
                break;
            }
        }
        return $array;
    }

    public function getName()
    {
        return 'app_extension';
    }

    public function jsonEncodeFunction($obj) {
        return json_encode($obj);
    }


}

?>
