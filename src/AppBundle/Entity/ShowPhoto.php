<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ShowPhoto
 *
 * @ORM\Table()
 * @ORM\Entity
 */

class ShowPhoto
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
     * @ORM\ManyToOne(targetEntity="User", inversedBy="user")
     * @ORM\JoinColumn(name="owner", referencedColumnName="id")
     */
    private $owner;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="user")
     * @ORM\JoinColumn(name="member", referencedColumnName="id")
     */
    private $member;

    /**
     * @var boolean
     *
     * @ORM\Column(name="allow", type="boolean")
     */
    private $isAllow = false;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_cancel", type="boolean")
     */
    private $isCancel = false;

    /**
     * Date/Time of the last activity
     *
     * @var \Datetime
     * @ORM\Column(name="date", type="datetime")
     */
    protected $date;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_view", type="boolean")
     */
    private $isView = 0;

    /**
    * If request is allowed and the user taht ask get a notification about the allow
    * @ORM\Column(name="is_notificated", type="boolean")
    */
    private $isNotificated = 0;

    private $userNotifications = [];

    public function __construct() {
//        $this->userNotifications = new \Doctrine\Common\Collections\ArrayCollection();
        $this->date = new \DateTime();
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
     * Set isAllow
     *
     * @param boolean $isAllow
     *
     * @return isAllow
     */
    public function setIsAllow($isAllow)
    {
        $this->isAllow = $isAllow;

        return $this;
    }

    /**
     * Get isAllow
     *
     * @return boolean
     */
    public function getIsAllow()
    {
        return $this->isAllow;
    }

    /**
     * Set isCancel
     *
     * @param boolean $isCancel
     *
     * @return LikeMe
     */
    public function setIsCancel($isCancel)
    {
        $this->isCancel = $isCancel;

        return $this;
    }

    /**
     * Get isShowSplashFrom
     *
     * @return boolean
     */
    public function getIsCancel()
    {
        return $this->isCancel;
    }


    /**
     * Set owner
     *
     * @param \AppBundle\Entity\User $owner
     *
     * @return LikeMe
     */
    public function setOwner(\AppBundle\Entity\User $owner)
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * Get owner
     *
     * @return \AppBundle\Entity\User
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * Set member
     *
     * @param \AppBundle\Entity\User $member
     *
     * @return LikeMe
     */
    public function setMember(\AppBundle\Entity\User $member)
    {
        $this->member = $member;

        return $this;
    }

    /**
     * Get member
     *
     * @return \AppBundle\Entity\User
     */
    public function getMember()
    {
        return $this->member;
    }

//    /**
//     * Add userNotification
//     *
//     * @param \AppBundle\Entity\UserNotifications $userNotification
//     *
//     * @return User
//     */
//    public function addUserNotification(\AppBundle\Entity\UserNotifications $userNotification)
//    {
//        $this->userNotifications[] = $userNotification;
//
//        return $this;
//    }
//
//    /**
//     * Remove userNotification
//     *
//     * @param \AppBundle\Entity\UserNotifications $userNotification
//     */
//    public function removeUserNotification(\AppBundle\Entity\UserNotifications $userNotification)
//    {
//        $this->userNotifications->removeElement($userNotification);
//    }
//
//    /**
//     * Get userNotifications
//     *
//     * @return \Doctrine\Common\Collections\Collection
//     */
//    public function getUserNotifications()
//    {
//        return $this->userNotifications;
//    }

    /**
     * @param \Datetime $date
     */
    public function setDate($date)
    {
        $this->date = $date;
    }

    /**
     * @return \Datetime
     */
    public function getDate()
    {
        return $this->date;
    }


    /**
     * Set isView
     *
     * @param boolean $isView
     *
     * @return ShowPhoto
     */
    public function setIsView($isView)
    {
        $this->isView = $isView;

        return $this;
    }

    /**
     * Get isView
     *
     * @return boolean
     */
    public function getIsView()
    {
        return $this->isView;
    }

    /**
     * Set isNotificated
     *
     * @param boolean $isNotificated
     *
     * @return ShowPhoto
     */
    public function setIsNotificated($isNotificated)
    {
        $this->isNotificated = $isNotificated;

        return $this;
    }

    /**
     * Get isNotificated
     *
     * @return boolean
     */
    public function getIsNotificated()
    {
        return $this->isNotificated;
    }
}
