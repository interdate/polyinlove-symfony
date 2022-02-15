<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UserNotifications
 *
 * @ORM\Table()
 * @ORM\Entity
 */

class UserNotifications
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
     * @ORM\ManyToOne(targetEntity="Notifications", inversedBy="userNotification")
     * @ORM\JoinColumn(name="notifications_id", referencedColumnName="id")
     */
    private $notification;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="notifications")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     **/
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="LikeMe", inversedBy="userNotifications")
     * @ORM\JoinColumn(name="like_me_id", referencedColumnName="id")
     */
    private $likeMe;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="date")
     */
    private $date;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_read", type="boolean")
     */
    private $isRead = false;


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
     * Set date
     *
     * @param \DateTime $date
     *
     * @return UserNotifications
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set isRead
     *
     * @param boolean $isRead
     *
     * @return UserNotifications
     */
    public function setIsRead($isRead)
    {
        $this->isRead = $isRead;

        return $this;
    }

    /**
     * Get isRead
     *
     * @return boolean
     */
    public function getIsRead()
    {
        return $this->isRead;
    }

    /**
     * Set notification
     *
     * @param \AppBundle\Entity\Notifications $notification
     *
     * @return UserNotifications
     */
    public function setNotification(\AppBundle\Entity\Notifications $notification = null)
    {
        $this->notification = $notification;

        return $this;
    }

    /**
     * Get notification
     *
     * @return \AppBundle\Entity\Notifications
     */
    public function getNotification()
    {
        return $this->notification;
    }

    /**
     * Set user
     *
     * @param \AppBundle\Entity\User $user
     *
     * @return UserNotifications
     */
    public function setUser(\AppBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \AppBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set likeMe
     *
     * @param \AppBundle\Entity\LikeMe $likeMe
     *
     * @return UserNotifications
     */
    public function setLikeMe(\AppBundle\Entity\LikeMe $likeMe = null)
    {
        $this->likeMe = $likeMe;

        return $this;
    }

    /**
     * Get likeMe
     *
     * @return \AppBundle\Entity\LikeMe
     */
    public function getLikeMe()
    {
        return $this->likeMe;
    }
}
