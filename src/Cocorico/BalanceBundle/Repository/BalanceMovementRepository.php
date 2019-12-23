<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\BalanceBundle\Repository;

use Cocorico\BalanceBundle\Entity\BalanceMovement;
use Cocorico\UserBundle\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityRepository;

class BalanceMovementRepository extends EntityRepository
{
    /**
     * @param User $user
     * @return ArrayCollection|BalanceMovement[]
     */
    public function findToViewForUser(User $user)
    {
        $queryBuilder = $this->createQueryBuilder('bm');

        $queryBuilder
            ->where('bm.user = :user AND bm.type IN (:strict_type) AND bm.status IN (:strict_status)')
            ->setParameter(
                'strict_type',
                array(
                    BalanceMovement::TYPE_CREDIT,
                    BalanceMovement::TYPE_CREDIT_CARD,
                    BalanceMovement::TYPE_DEBIT,
                    BalanceMovement::TYPE_REFUND,
                )
            )
            ->setParameter(
                'strict_status',
                array(
                    BalanceMovement::STATUS_VALIDATE,
                )
            );

        $queryBuilder
            ->orWhere('bm.user = :user AND bm.type IN (:flexible_type) AND bm.status IN (:flexible_status)')
            ->setParameter(
                'flexible_type',
                array(
                    BalanceMovement::TYPE_CREDIT_BANK_WIRE,
                    BalanceMovement::TYPE_RECOVER
                )
            )
            ->setParameter(
                'flexible_status',
                array(
                    BalanceMovement::STATUS_WAITING,
                    BalanceMovement::STATUS_VALIDATE,
                    BalanceMovement::STATUS_INVALIDATE,
                )
            );

        $queryBuilder
            ->setParameter('user', $user)
            ->orderBy('bm.createdAt', 'DESC');

        return new ArrayCollection($queryBuilder->getQuery()->getResult());
    }

    /**
     * @return ArrayCollection|BalanceMovement[]
     */
    public function findCreditsBankWiresToCheck()
    {
        $queryBuilder = $this->createQueryBuilder('bm');
        $queryBuilder
            ->where('bm.status = :status')
            ->andWhere('bm.type = :type')
            ->andWhere('bm.createdAt > :date')
            ->setParameter('status', BalanceMovement::STATUS_WAITING)
            ->setParameter('type', BalanceMovement::TYPE_CREDIT_BANK_WIRE)
            ->setParameter('date', (new \DateTime())->modify('-1 months'));

        return new ArrayCollection($queryBuilder->getQuery()->getResult());
    }

    /**
     * @return ArrayCollection|BalanceMovement[]
     */
    public function findRecoversBankWiresToCheck()
    {
        $queryBuilder = $this->createQueryBuilder('bm');
        $queryBuilder
            ->where('bm.status IN (:status)')
            ->andWhere('bm.type = :type')
            ->andWhere('bm.createdAt > :date')
            ->setParameter(
                'status',
                array(
                    BalanceMovement::STATUS_WAITING,
                    BalanceMovement::STATUS_VALIDATE,
                )
            )
            ->setParameter('type', BalanceMovement::TYPE_RECOVER)
            ->setParameter('date', (new \DateTime())->modify('-1 months'));

        return new ArrayCollection($queryBuilder->getQuery()->getResult());
    }
}
