<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Favorite
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="AppBundle\Entity\FavoriteRepository")
 */
class Favorite
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
     * @ORM\ManyToOne(targetEntity="User", inversedBy="favorited")
     * @ORM\JoinColumn(name="owner_id", referencedColumnName="id")
     **/
    private $owner;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="favoritedMe")
     * @ORM\JoinColumn(name="member_id", referencedColumnName="id")
     **/
    private $member;

    /** @ORM\Column(type="datetime")
     **/
    private $date;

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
     * Set owner
     *
     * @param \AppBundle\Entity\User $owner
     * @return Favorite
     */
    public function setOwner(\AppBundle\Entity\User $owner = null)
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
     * @return Favorite
     */
    public function setMember(\AppBundle\Entity\User $member = null)
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

    /**
     * Set date
     * @return View
     */
    public function setDate($date = null): Favorite
    {
        if (!is_a($date, 'DateTime')){
            $date = null;
        }
        $this->date = $date ?? new \DateTime('now');

        return $this;
    }

    /**
     * Get date
     * @return \DateTime
     */
    public function getDate(): \DateTime
    {
        return $this->date;
    }
}
