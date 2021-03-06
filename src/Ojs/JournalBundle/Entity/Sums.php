<?php

namespace Ojs\JournalBundle\Entity;

/**
 * Sums
 */
class Sums
{

    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $entity;

    /**
     * @var integer
     */
    private $sum;

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
     * Get entity
     *
     * @return string
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * Set entity
     *
     * @param  string $entity
     * @return Sums
     */
    public function setEntity($entity)
    {
        $this->entity = $entity;

        return $this;
    }

    /**
     * Get sum
     *
     * @return integer
     */
    public function getSum()
    {
        return $this->sum;
    }

    /**
     * Set sum
     *
     * @param  integer $sum
     * @return Sums
     */
    public function setSum($sum)
    {
        $this->sum = $sum;

        return $this;
    }
}
