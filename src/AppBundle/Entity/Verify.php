<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Verify
 *
 * @ORM\Table()
 * @ORM\Entity
 */

class Verify
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
     * @ORM\ManyToOne(targetEntity="User", inversedBy="verify")
     * @ORM\JoinColumn(name="user_from", referencedColumnName="id")
     */
    private $userFrom;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="verify")
     * @ORM\JoinColumn(name="user_to", referencedColumnName="id")
     */
    private $userTo;


//    private $isVerify;

//    /**
//     * @ORM\OneToMany(targetEntity="UserNotifications", mappedBy="likeMe", cascade={"remove"}, orphanRemoval=true)
//     */
//    private $userNotifications;
//
//    public function __construct() {
//        $this->userNotifications = new \Doctrine\Common\Collections\ArrayCollection();
//    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

//    public function getIsVerify() {
//        return $this->verifyCount === 3;
//    }

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

//    /**
//     * Remove userNotification
//     *
//     * @param \AppBundle\Entity\UserNotifications $userNotification
//     */
//    public function removeUserNotification(\AppBundle\Entity\UserNotifications $userNotification)
//    {
//        $this->userNotifications->removeElement($userNotification);
//    }

//    /**
//     * Get userNotifications
//     *
//     * @return \Doctrine\Common\Collections\Collection
//     */
//    public function getUserNotifications()
//    {
//        return $this->userNotifications;
//    }
}
