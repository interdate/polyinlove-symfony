<?php
namespace AppBundle\Services;

//use PhpParser\Node\Expr\Cast\Object_;
use AppBundle\Entity\User;

class BannerService
{
    public $orm;

    public function __construct($orm)
    {
        $this->orm = $orm;
    }

    /**
     * @string $page can be 'beforeLogin', 'afterLogin', 'subscriptionPage', 'profileBottom', 'profileTop' - the pages that can contain banners
     *
     */

    public function getBanners($page, User $user = null)
    {
        $allBanners = $this->orm->getRepository('AppBundle:Banner')->findBy(array(
            $page => 1,
            'isActive' => 1
        ));

        if (is_object($user)) {

            $banners = [];
            $gender = '';
            $payment = $user->isPaying() ? 'isPay' : 'isNotPay';

            if (in_array($user->getGender()->getId(), [1, 4])) {
                $gender = 'isMans';
            }

            if (in_array($user->getGender()->getId(), [2, 3])) {
                $gender = 'isWomans';
            }

            if ($gender == '') {
                $gender = 'isAbinary';
            }

            foreach ($allBanners as $banner) {

                if ($banner->$gender() && $banner->$payment()) {
                    $banners[] = $banner;
                }
            }
        } else {
            $banners = $allBanners;
        }


        return $banners;
    }
}
