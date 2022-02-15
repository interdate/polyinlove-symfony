<?php

namespace AppBundle\Entity;

use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;

/**
 * Push
 *
 * @ORM\Table()
 * @ORM\Entity
 */

class Push
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
     * @ORM\Column(name="message", type="string", length=1000)
     */
    private $message;
    
    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=1000)
     */
    private $title;

    /**
     * @ORM\OneToMany(targetEntity="UserMessengerNotifications", mappedBy="pushes", cascade={"remove"}, orphanRemoval=true)
     */
    private $userPush;



    /** @var string
     *
     *  @ORM\Column(name="web_link", type="string", length=1000)
     */
    private $web_link;

    /** @var string
     *
     *  @ORM\Column(name="app_link", type="string", length=1000)
     */
    private $app_link;

    /** @var string
     *
     *  @ORM\Column(name="app_web_link", type="string", length=1000)
     */
    private $appWebLink;

    /** @var string
     *
     *  @ORM\Column(name="type", type="string", length=1000)
     */
    private $type;



    public function __construct() {
        $this->userPush = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set message
     *
     * @param string $message
     *
     * @return Push
     */
    public function setMessage(string $message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Get message
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Set title
     *
     * @param string $title
     *
     * @return Push
     */
    public function setTitle(string $title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Add userPush
     *
     * @param Push $userPush
     *
     * @return Push
     */
    public function addUserPush(Push $userPush)
    {
        $this->userPush[] = $userPush;

        return $this;
    }

    /**
     * Remove userPush
     *
     * @param Push $push
     */
    public function removeUserPush(Push $push)
    {
        $this->userPush->removeElement($push);
    }

    /**
     * Get userPush
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getUserPush()
    {
        return $this->userPush;
    }

    /**
     * @return string
     */
    public function getAppLink(): string
    {
        return $this->app_link;
    }

    /**
     * @param string $app_link
     */
    public function setAppLink(string $app_link): void
    {
        $this->app_link = $app_link;
    }

    /**
     * @return string
     */
    public function getWebLink(): string
    {
        return $this->web_link;
    }

    /**
     * @param string $web_link
     */
    public function setWebLink(string $web_link): void
    {
        $this->web_link = $web_link;
    }

    /**
     * @param \Doctrine\Common\Collections\ArrayCollection $userPush
     */
    public function setUserPush(\Doctrine\Common\Collections\ArrayCollection $userPush): void
    {
        $this->userPush = $userPush;
    }

    /**
     * @return string
     */
    public function getAppWebLink(): string
    {
        return $this->appWebLink;
    }

    /**
     * @param string $appWebLink
     */
    public function setAppWebLink(string $appWebLink): void
    {
        $this->appWebLink = $appWebLink;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType(string $type): void
    {
        $this->type = $type;
    }

}
