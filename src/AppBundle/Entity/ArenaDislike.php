<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LikeMe
 *
 * @ORM\Table()
 * @ORM\Entity
 */

class ArenaDislike
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
     * @ORM\ManyToOne(targetEntity="User", inversedBy="dislikes")
     * @ORM\JoinColumn(name="from_id", referencedColumnName="id")
     */
    private $userFrom;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="dislikesMe")
     * @ORM\JoinColumn(name="to_id", referencedColumnName="id")
     */
    private $userTo;

    /**
     * Date/Time of the last activity
     *
     * @var \Datetime
     * @ORM\Column(name="date", type="datetime", options={"default": "CURRENT_TIMESTAMP"})
     */
    protected $date;


    public function __construct()
    {
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
     * @return ArenaDislike
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

    /**
     * @param \Datetime $date
     */
    public function setLastActivityAt($date)
    {
        $this->date = $date;
    }

    /**
     * @return \Datetime
     */
    public function getLastActivityAt()
    {
        return $this->date;
    }

}
