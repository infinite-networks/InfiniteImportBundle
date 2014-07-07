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
use Symfony\Component\Security\Core\User\UserInterface;

class ImportRepository extends EntityRepository
{
    public function findStalledOrNewImports()
    {
        $qb = $this->queryAll();

        $heartbeatAge = new \DateTime;
        $heartbeatAge->modify('-2 minutes');

        $qb->andWhere('i.dateStarted IS NOT NULL');
        $qb->andWhere('i.dateFinished IS NULL');
        $qb->andWhere('i.running = 1');
        $qb->andWhere('i.heartbeat IS NULL or i.heartbeat < :heartbeat');

        $qb->setParameter('heartbeat', $heartbeatAge);

        return $qb->getQuery()->execute();
    }

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
    * @param UserInterface $user
    * @return int
    */
    public function getOutstandingCount(UserInterface $user)
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

    public function queryAllForUser(UserInterface $user)
    {
        $qb = $this->queryAll();

        $qb->andWhere('IDENTITY(i.user) = :userId');
        $qb->setParameter('userId', $user->getId());

        return $qb;
    }
}
