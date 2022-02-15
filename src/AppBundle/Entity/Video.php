<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use DoctrineExtensions\Query\Mysql\Date;
use Symfony\Component\Validator\Constraints\DateTime;

/**
 * Video
 *
 * @ORM\Table()
 * @ORM\Entity
 * mappedBy="Video"

 */

class Video
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
     * @ORM\ManyToOne(targetEntity="User", inversedBy="video")
     * @ORM\JoinColumn(name="user_from", referencedColumnName="id")
     */
    private $userFrom;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="video")
     * @ORM\JoinColumn(name="user_to", referencedColumnName="id")
     */
    private $userTo;

    /**
     * @var string
     * @ORM\Column(name="status", type="string")
     */
    private $status;


    /**
     * @var \DateTime
     *
     * @ORM\Column(name="start_date", type="datetime")
     */
    private $startDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="end_date", type="datetime", nullable=true)
     */
    private $endDate;





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

    /**
     * @return \DateTime
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * @param \DateTime $startDate
     */
    public function setStartDate($startDate)
    {
        $this->startDate = $startDate;
    }

    /**
     * @return \DateTime
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * @param \DateTime $endDate
     */
    public function setEndDate($endDate)
    {
        $this->endDate = $endDate;
    }


    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }


    /**
     * @param string $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

}
