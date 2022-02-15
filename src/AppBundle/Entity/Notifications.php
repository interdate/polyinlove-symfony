<?php

namespace AppBundle\Entity;

use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;

/**
 * Notifications
 *
 * @ORM\Table()
 * @ORM\Entity
 */

class Notifications
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
     * @ORM\Column(name="template", type="string", length=1000)
     */
    private $template;

    /**
     * @ORM\OneToMany(targetEntity="UserNotifications", mappedBy="notifications", cascade={"remove"}, orphanRemoval=true)
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
     * Set template
     *
     * @param string $template
     *
     * @return Notifications
     */
    public function setTemplate($template)
    {
        $this->template = $template;

        return $this;
    }

    /**
     * Get template
     *
     * @return string
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * Add userNotification
     *
     * @param \AppBundle\Entity\UserNotifications $userNotification
     *
     * @return Notifications
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
