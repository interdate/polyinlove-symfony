<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LikeMe
 *
 * @ORM\Table()
 * @ORM\Entity
 */

class LikeMe
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
     * @ORM\ManyToOne(targetEntity="User", inversedBy="likes")
     * @ORM\JoinColumn(name="from_id", referencedColumnName="id")
     */
    private $userFrom;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="likesMe")
     * @ORM\JoinColumn(name="to_id", referencedColumnName="id")
     */
    private $userTo;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_bingo", type="boolean")
     */
    private $isBingo = false;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_show_splash_from", type="boolean")
     */
    private $isShowSplashFrom = false;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_show_splash_to", type="boolean")
     */
    private $isShowSplashTo = false;

    /**
     * @ORM\OneToMany(targetEntity="UserNotifications", mappedBy="likeMe", cascade={"remove"}, orphanRemoval=true)
     */
    private $userNotifications;

    public function __construct() {
        $this->userNotifications = new \Doctrine\Common\Collections\ArrayCollection();
    }

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
     * Set isBingo
     *
     * @param boolean $isBingo
     *
     * @return LikeMe
     */
    public function setIsBingo($isBingo)
    {
        $this->isBingo = $isBingo;

        return $this;
    }

    /**
     * Get isBingo
     *
     * @return boolean
     */
    public function getIsBingo()
    {
        return $this->isBingo;
    }

    /**
     * Set isShowSplashFrom
     *
     * @param boolean $isShowSplashFrom
     *
     * @return LikeMe
     */
    public function setIsShowSplashFrom($isShowSplashFrom)
    {
        $this->isShowSplashFrom = $isShowSplashFrom;

        return $this;
    }

    /**
     * Get isShowSplashFrom
     *
     * @return boolean
     */
    public function getIsShowSplashFrom()
    {
        return $this->isShowSplashFrom;
    }

    /**
     * Set isShowSplashTo
     *
     * @param boolean $isShowSplashTo
     *
     * @return LikeMe
     */
    public function setIsShowSplashTo($isShowSplashTo)
    {
        $this->isShowSplashTo = $isShowSplashTo;

        return $this;
    }

    /**
     * Get isShowSplashTo
     *
     * @return boolean
     */
    public function getIsShowSplashTo()
    {
        return $this->isShowSplashTo;
    }

    /**
     * Set userFrom
     *
     * @param \AppBundle\Entity\User $userFrom
     *
     * @return LikeMe
     */
    public function setUserFrom(\AppBundle\Entity\User $userFrom = null)
    {
        $this->userFrom = $userFrom;

        return $this;
    }

    /**
     * Get userFrom
     *
     * @return \AppBundle\Entity\User
     */
    public function getUserFrom()
    {
        return $this->userFrom;
    }

    /**
     * Set userTo
     *
     * @param \AppBundle\Entity\User $userTo
     *
     * @return LikeMe
     */
    public function setUserTo(\AppBundle\Entity\User $userTo = null)
    {
        $this->userTo = $userTo;

        return $this;
    }

    /**
     * Get userTo
     *
     * @return \AppBundle\Entity\User
     */
    public function getUserTo()
    {
        return $this->userTo;
    }

    /**
     * Add userNotification
     *
     * @param \AppBundle\Entity\UserNotifications $userNotification
     *
     * @return User
     */
    public function addUserNotification(\AppBundle\Entity\UserNotifications $userNotification)
    {
        $this->userNotifications[] = $userNotification;

        return $this;
    }

    /**
     * Remove userNotification
     *
     * @param \AppBundle\Entity\UserNotifications $userNotification
     */
    public function removeUserNotification(\AppBundle\Entity\UserNotifications $userNotification)
    {
        $this->userNotifications->removeElement($userNotification);
    }

    /**
     * Get userNotifications
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getUserNotifications()
    {
        return $this->userNotifications;
    }
}
