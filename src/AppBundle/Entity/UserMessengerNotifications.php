<?php


namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UserMessengerNotifications
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class UserMessengerNotifications
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
     * @ORM\JoinColumn(name="notification_id", referencedColumnName="id")
     */
    private $notification;

    /**
     * @ORM\ManyToOne(targetEntity="Push", inversedBy="id")
     * @ORM\JoinColumn(name="push_id", referencedColumnName="id")
     */
    private $push;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="messengerNotification")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     **/
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="messengerNotification")
     * @ORM\JoinColumn(name="user_from_id", referencedColumnName="id")
     **/
    private $fromUser;



    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="datetime", nullable=true, options={"default": "CURRENT_TIMESTAMP"})
     */
    private $date;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_read", type="boolean")
     */
    private $isRead = false;

    /**
     * @var int | null
     *
     * @ORM\Column(name="left_verifies", type="integer", nullable=true)
     */
    private $leftVerifies;


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
     * @return UserMessengerNotifications
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
     * @return UserMessengerNotifications
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
     * @return UserMessengerNotifications
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
     * @return UserMessengerNotifications
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
     * @return mixed
     */
    public function getNotificationId()
    {
        return $this->notification;
    }

    /**
     * @return mixed
     */
    public function getPush()
    {
        return $this->push;
    }

    /**
     * @param mixed $push
     */
    public function setPush($push): void
    {
        $this->push = $push;
    }

    /**
     * @param mixed $notification
     */
    public function setNotificationId($notification): void
    {
        $this->notification = $notification;
    }

    /**
     * @param mixed $fromUser
     */
    public function setFromUser($fromUser): UserMessengerNotifications
    {
        $this->fromUser = $fromUser;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getFromUser()
    {
        return $this->fromUser;
    }

    /**
     * @param int|null $leftVerifies
     */
    public function setLeftVerifies(?int $leftVerifies): void
    {
        $this->leftVerifies = $leftVerifies;
    }

    /**
     * @return int|null
     */
    public function getLeftVerifies(): ?int
    {
        return (int)$this->leftVerifies;
    }
}
