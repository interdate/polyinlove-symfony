<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Banner
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Banner // id, position , href, img
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;


    /**
     * @var string
     *
     * @ORM\Column(name="href", type="string", length=255)
     */
    private $href;
    
    /**
     * @var string
     *
     * @ORM\Column(name="img", type="string", length=255)
     */
    private $img;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var integer
     *
     * @ORM\Column(name="click_count", type="integer", length=255)
     */
    private $clickCount;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_active", type="boolean", length=1)
     */
    private $isActive;


    /**
     * @var boolean
     *
     * @ORM\Column(name="before_login", type="boolean", length=1)
     */
    private $beforeLogin;

    /**
     * @var boolean
     *
     * @ORM\Column(name="after_login", type="boolean", length=1)
     */
    private $afterLogin;

    /**
     * @var boolean
     *
     * @ORM\Column(name="subscription_page", type="boolean", length=1)
     */
    private $subscriptionPage;

    /**
     * @var boolean
     *
     * @ORM\Column(name="profile_bottom", type="boolean", length=1)
     */
    private $profileBottom;

    /**
     * @var boolean
     *
     * @ORM\Column(name="profile_top", type="boolean", length=1)
     */
    private $profileTop;

    /**
     * @var boolean
     *
     * @ORM\Column(name="mobile", type="boolean", length=1)
     */
    private $mobile;


    /**
     * @var boolean
     *
     * @ORM\Column(name="mans", type="boolean", length=1)
     */
    private $mans = false;

    /**
     * @var boolean
     *
     * @ORM\Column(name="womans", type="boolean", length=1)
     */
    private $womans = false;

    /**
     * @var boolean
     *
     * @ORM\Column(name="abinary", type="boolean", length=1)
     */
    private $abinary = false;

    /**
     * @var boolean
     *
     * @ORM\Column(name="pay", type="boolean", length=1)
     */
    private $pay = false;

    /**
     * @var boolean
     *
     * @ORM\Column(name="not_pay", type="boolean", length=1)
     */
    private $not_pay = false;





    public $ext;

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set contactEmail
     *
     * @param string $href
     * @return Banner
     */
    public function setHref($href)
    {
        $this->href = $href;

        return $this;
    }
    
    /**
     * Get href
     *
     * @return string
     */
    public function getHref()
    {
    	return $this->href;
    }
    
    /**
     * Set img
     *
     * @param string $img
     * @return Banner
     */
    public function setImg($img)
    {
    	$this->img = $img;
    
    	return $this;
    }

    /**
     * Get img
     *
     * @return string 
     */
    public function getImg()
    {
        return $this->img;
    }


    /**
     * Set name
     *
     * @param string $name
     * @return Banner
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->img;
    }

    /**
     * Set name
     *
     * @param string $clickCount
     * @return Banner
     */
    public function setClickCount($clickCount)
    {
        $this->clickCount = $clickCount;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getClickCount()
    {
        return $this->clickCount;
    }

    /**
     * Set isActive
     *
     * @param string $isActive
     * @return Banner
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;

        return $this;
    }

    /**
     * Get isActive
     *
     * @return string
     */
    public function getIsActive()
    {
        return $this->isActive;
    }


    /**
     * Set isActive
     *
     * @param string $beforeLogin
     * @return Banner
     */
    public function setBeforeLogin($beforeLogin)
    {
        $this->beforeLogin = $beforeLogin;

        return $this;
    }

    /**
     * Get beforeLogin
     *
     * @return string
     */
    public function getBeforeLogin()
    {
        return $this->beforeLogin;
    }

    /**
     * Set afterLogin
     *
     * @param string $afterLogin
     * @return Banner
     */
    public function setAfterLogin($afterLogin)
    {
        $this->afterLogin = $afterLogin;

        return $this;
    }

    /**
     * Get afterLogin
     *
     * @return string
     */
    public function getAfterLogin()
    {
        return $this->afterLogin;

     }

    /**
     * Get subscriptionPage
     *
     * @return string
     */
    public function getSubscriptionPage()
    {
        return $this->subscriptionPage;
    }

    /**
     * Set subscriptionPage
     *
     * @param string $subscriptionPage
     * @return Banner
     */
    public function setSubscriptionPage($subscriptionPage)
    {
        $this->subscriptionPage = $subscriptionPage;

        return $this;
    }

    /**
     * Get profileBottom
     *
     * @return string
     */
    public function getProfileBottom()
    {
        return $this->profileBottom;
    }

    /**
     * Set profileBottom
     *
     * @param string $profileBottom
     * @return Banner
     */
    public function setProfileBottom($profileBottom)
    {
        $this->profileBottom = $profileBottom;

        return $this;
    }

    /**
     * Get profileTop
     *
     * @return string
     */
    public function getProfileTop()
    {
        return $this->profileTop;
    }

    /**
     * Set profileTop
     *
     * @param string $profileTop
     * @return Banner
     */
    public function setProfileTop($profileTop)
    {
        $this->profileTop = $profileTop;

        return $this;
    }


    /**
     * Get mobile
     *
     * @return string
     */
    public function getMobile()
    {
        return $this->mobile;
    }

    /**
     * Set mobile
     *
     * @param string $mobile
     * @return Banner
     */
    public function setMobile($mobile)
    {
        $this->mobile = $mobile;

        return $this;
    }

    /**
     * @param bool $womans
     */
    public function setWomans(bool $womans): void
    {
        $this->womans = $womans;
    }

    /**
     * @param bool $pay
     */
    public function setPay(bool $pay): void
    {
        $this->pay = $pay;
    }

    /**
     * @param bool $not_pay
     */
    public function setNotPay(bool $not_pay): void
    {
        $this->not_pay = $not_pay;
    }

    /**
     * @param bool $mans
     */
    public function setMans(bool $mans): void
    {
        $this->mans = $mans;
    }

    /**
     * @param bool $abinary
     */
    public function setAbinary(bool $abinary): void
    {
        $this->abinary = $abinary;
    }

    /**
     * @return bool
     */
    public function isAbinary(): bool
    {
        return $this->abinary;
    }

    /**
     * @return bool
     */
    public function isPay(): bool
    {
        return $this->pay;
    }

    /**
     * @return bool
     */
    public function isWomans(): bool
    {
        return $this->womans;
    }

    /**
     * @return bool
     */
    public function isMans(): bool
    {
        return $this->mans;
    }

    /**
     * @return bool
     */
    public function isNotPay(): bool
    {
        return $this->not_pay;
    }

    /**
     * Called before saving the entity
     *
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function preUpload($file)
    {
        if (null !== $file) {
            if(!is_dir($this->getUploadRootDir())){
                mkdir($this->getUploadRootDir(), 0777, true);
            }
            $this->ext = $file->guessExtension();

        }
    }

    /**
     * @ORM\PostPersist()
     * @ORM\PostUpdate()
     */
public function upload($file)
{
    // the file property can be empty if the field is not required
    if (null === $file) {
        return;
    }

    $file->move(
        $this->getUploadRootDir(),
        $this->id . '.' .$file->guessExtension()
    );
    $this->img = '/images/banners/' .  $this->id . '.' .$this->ext;
//    $this->file = null;
}

    public function getUploadRootDir()
    {
        return $_SERVER['DOCUMENT_ROOT'] . '/images/banners/';
    }



   // public function getUploadRootDir()
}
