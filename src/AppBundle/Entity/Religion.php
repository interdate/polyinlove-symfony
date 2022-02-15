<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Religion
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Religion
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
     * @ORM\Column(name="name", type="string", length=15)
     */
    private $name;

//    /**
//     * @ORM\OneToMany(targetEntity="User", mappedBy="religion")
//     */
//    private $users;


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
     * @return Religion
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
     * Constructor
     */
    public function __construct()
    {
//        $this->users = new \Doctrine\Common\Collections\ArrayCollection();
    }

//    /**
//     * Add users
//     *
//     * @param \AppBundle\Entity\User $users
//     * @return Religion
//     */
//    public function addUser(\AppBundle\Entity\User $users)
//    {
//        $this->users[] = $users;
//
//        return $this;
//    }
//
//    /**
//     * Remove users
//     *
//     * @param \AppBundle\Entity\User $users
//     */
//    public function removeUser(\AppBundle\Entity\User $users)
//    {
//        $this->users->removeElement($users);
//    }
//
//    /**
//     * Get users
//     *
//     * @return \Doctrine\Common\Collections\Collection
//     */
//    public function getUsers()
//    {
//        return $this->users;
//    }
}
