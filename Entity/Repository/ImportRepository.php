<?php

/**
 * This file is part of the ImportBundle project.
 *
 * (c) Infinite Networks Pty Ltd <http://www.infinite.net.au>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Infinite\ImportBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Entity\User;

class ImportRepository extends EntityRepository
{
    /**
     * @return \Infinite\ImportBundle\Entity\Import[]
     */
    public function findAllForProcessing()
    {
        $qb = $this->queryAll();

        $qb->andWhere('i.processorKey IS NULL');

        return $qb->getQuery()->execute();
    }

    /**
    * Returns the number of outstanding imports that need to be configured, processed or
    * imported by the user.
    *
    * @param User $user
    * @return int
    */
    public function getOutstandingCount(User $user)
    {
        $qb = $this->queryAllForUser($user);
        $e = $qb->expr();

        $qb->select('COUNT(i)');
        $qb->andWhere($e->isNull('i.dateFinished'));

        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function queryAll()
    {
        $qb = $this->createQueryBuilder('i');

        return $qb;
    }

    public function queryAllForUser(User $user)
    {
        $qb = $this->queryAll();

        $qb->andWhere('IDENTITY(i.user) = :userId');
        $qb->setParameter('userId', $user->getId());

        return $qb;
    }
}
