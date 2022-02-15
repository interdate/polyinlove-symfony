<?php
namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EmailBlocked
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class EmailBlocked extends Blocked
{
    public function setValue($value)
    {
        $this->value = strtolower($value);

        return $this;
    }
}
