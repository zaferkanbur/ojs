<?php

namespace Ojs\JournalBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * IssueRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class IssueRepository extends EntityRepository
{
    /**
     * Return issue count by condition
     * @param $field
     * @param $value
     * @return mixed
     */
    public function getCountBy($field, $value)
    {
        $qb = $this->createQueryBuilder("u");
        $qb->select("count(u.id)")
            ->where(
                $qb->expr()->eq("u.$field", ':value')
            )
            ->setParameter("value", $value);
        return $qb->getQuery()->getSingleScalarResult();
    }
}
