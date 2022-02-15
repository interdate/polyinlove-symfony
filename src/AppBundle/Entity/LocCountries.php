<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * LocCountries
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class LocCountries
{
    /**
     * @var integer
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=5, nullable=true)
     */
    private $code;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

//    /**
//     * @ORM\OneToMany(targetEntity="LocRegions", mappedBy="country" )
//     */
//    private $regions;

    public function __construct()
    {
//        $this->regions = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getCode()
    {
        return $this->code;
    }

    public function setCode(string $code)
    {
        $this->code = $code;

        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName(string $name)
    {
        $this->name = $name;

        return $this;
    }

//    /**
//     * @return Collection|LocRegions[]
//     */
//    public function getRegions()
//    {
//        return $this->regions;
//    }

//    public function addRegion(LocRegions $region)
//    {
//        if (!$this->regions->contains($region)) {
//            $this->regions[] = $region;
//            $region->setCountry($this);
//        }
//
//        return $this;
//    }

//    public function removeRegion(LocRegions $region)
//    {
//        if ($this->regions->removeElement($region)) {
//            // set the owning side to null (unless already changed)
//            if ($region->getCountry() === $this) {
//                $region->setCountry(null);
//            }
//        }
//
//        return $this;
//    }
}
