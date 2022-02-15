<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;


/**
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class LocRegions
{
    /**
     * @var integer
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="LocCountries", inversedBy="regions")
     * @ORM\JoinColumn(nullable=false)
     */
    private $country;

    /**
     * @ORM\Column(type="string", length=5, nullable= true)
     */
    private $code;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

//    /**
//     * @ORM\OneToMany(targetEntity="LocCities", mappedBy="region")
//     */
//    private $cities;

    public function __construct()
    {
//        $this->cities = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getCountry()
    {
        return $this->country;
    }

    public function setCountry(LocCountries $country)
    {
        $this->country = $country;

        return $this;
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
//    public function getCities()
//    {
//        return $this->cities;
//    }

//    public function addCity(LocCities $city)
//    {
//        if (!$this->cities->contains($city)) {
//            $this->cities[] = $city;
//            $city->setRegion($this);
//        }
//
//        return $this;
//    }

//    public function removeCity(LocCities $city)
//    {
//        if ($this->cities->removeElement($city)) {
//            // set the owning side to null (unless already changed)
//            if ($city->getRegion() === $this) {
//                $city->setRegion(null);
//            }
//        }
//
//        return $this;
//    }
}
