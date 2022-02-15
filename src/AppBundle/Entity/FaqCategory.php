<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FaqCategory
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class FaqCategory
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
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_active", type="boolean")
     */
    private $isActive;

    /**
     * @ORM\OneToMany(targetEntity="Faq", mappedBy="category", cascade={"remove"})
     */
    private $faq;


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
     * Set name
     *
     * @param string $name
     * @return FaqCategory
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
        return $this->name;
    }

    /**
     * Set isActive
     *
     * @param boolean $isActive
     * @return FaqCategory
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;

        return $this;
    }

    /**
     * Get isActive
     *
     * @return boolean 
     */
    public function getIsActive()
    {
        return $this->isActive;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->faq = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add faq
     *
     * @param \AppBundle\Entity\Faq $faq
     * @return FaqCategory
     */
    public function addFaq(\AppBundle\Entity\Faq $faq)
    {
        $this->faq[] = $faq;

        return $this;
    }

    /**
     * Remove faq
     *
     * @param \AppBundle\Entity\Faq $faq
     */
    public function removeFaq(\AppBundle\Entity\Faq $faq)
    {
        $this->faq->removeElement($faq);
    }

    /**
     * Get faq
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getFaq()
    {
        return $this->faq;
    }
}
