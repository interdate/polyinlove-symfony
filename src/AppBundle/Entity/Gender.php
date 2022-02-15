<?php

namespace AppBundle\Entity;

use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Translatable\Translatable;

/**
 * Gender
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Gender implements Translatable
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
     *
     * @ORM\Column(name="name", type="string", length=50)
     */
    private $name;


    /**
     * @ORM\OneToMany(targetEntity="User", mappedBy="gender")
     */
    private $users;

    /**
     * @ORM\OneToMany(targetEntity="User", mappedBy="lookingGender")
     */
    private $lookingGenderUsers;



    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->lookingGenderUsers = new ArrayCollection();
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
     * Set name
     *
     * @param string $name
     * @return Gender
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
     * Add users
     *
     * @param \AppBundle\Entity\User $users
     * @return Gender
     */
    public function addUser(\AppBundle\Entity\User $users)
    {
        $this->users[] = $users;

        return $this;
    }

    /**
     * Remove users
     *
     * @param \AppBundle\Entity\User $users
     */
    public function removeUser(\AppBundle\Entity\User $users)
    {
        $this->users->removeElement($users);
    }

    /**
     * Get users
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * Add lookingGenderUsers
     *
     * @param \AppBundle\Entity\User $lookingGenderUsers
     * @return Gender
     */
    public function addLookingGenderUser(\AppBundle\Entity\User $lookingGenderUsers)
    {
        $this->lookingGenderUsers[] = $lookingGenderUsers;

        return $this;
    }

    /**
     * Remove lookingGenderUsers
     *
     * @param \AppBundle\Entity\User $lookingGenderUsers
     */
    public function removeLookingGenderUser(\AppBundle\Entity\User $lookingGenderUsers)
    {
        $this->lookingGenderUsers->removeElement($lookingGenderUsers);
    }

    /**
     * Get lookingGenderUsers
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getLookingGenderUsers()
    {
        return $this->lookingGenderUsers;
    }
}
