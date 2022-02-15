<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\File as UploadedFile;
use JMS\Serializer\Annotation\Discriminator;


/**
 * Class Blocked
 *
 * @ORM\Entity()
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="field", type="integer", length=1)
 * @ORM\DiscriminatorMap({"1" = "EmailBlocked", "2" = "PhoneBlocked", "3" = "WordBlocked"})
 * @ORM\HasLifecycleCallbacks()
 * @Discriminator(disabled=true)
 */

abstract class Blocked
{

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=100)
     */
    protected $value;

    /**
     * File type
     * @var string
     */
    protected $field;


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
     * Set value
     *
     * @param string $value
     *
     * @return Blocked
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }
}
