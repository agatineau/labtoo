<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\BalanceBundle\Manager;

use Cocorico\BalanceBundle\Entity\BalanceMovement;
use Cocorico\CoreBundle\Entity\Booking;
use Cocorico\MangoPayBundle\Model\Manager\TransferManager;
use Doctrine\ORM\EntityManager;

class TransactionManager
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var TransferManager
     */
    private $transferManager;

    /**
     * TransactionManager constructor.
     * @param EntityManager $entityManager
     * @param TransferManager $transferManager
     */
    public function __construct(
        EntityManager $entityManager,
        TransferManager $transferManager
    ) {
        $this->entityManager = $entityManager;
        $this->transferManager = $transferManager;
    }

    /**
     * @param Booking $booking
     */
    public function register(Booking $booking)
    {
        $balanceMovementDebit = new BalanceMovement();
        $balanceMovementDebit->setAmount($booking->getAmountToPayByAsker());
        $balanceMovementDebit->setStatus(BalanceMovement::STATUS_WAITING);
        $balanceMovementDebit->setType(BalanceMovement::TYPE_DEBIT);
        $balanceMovementDebit->setUser($booking->getUser());
        $booking->addBalanceMovement($balanceMovementDebit);
        $this->entityManager->persist($balanceMovementDebit);
        $this->entityManager->flush($balanceMovementDebit);
    }

    /**
     * @param Booking $booking
     * @return bool
     */
    public function isDebitable(Booking $booking)
    {
        return (
            $booking->getUser()->getAmountBalance() >= $booking->getAmountToPayByAsker()
        );
    }

    /**
     * @param Booking $booking
     */
    public function debit(Booking $booking)
    {
        $balanceMovementDebit = $booking->getBalanceMovementDebit();
        if (is_null($balanceMovementDebit)) return;

        $balanceMovementDebit->setStatus(BalanceMovement::STATUS_VALIDATE);
        $balanceMovementDebit->setValidatedAt(new \DateTime());
        $this->entityManager->flush($balanceMovementDebit);
        $user = $balanceMovementDebit->getUser();
        $user->setAmountBalance($user->getAmountBalance() - $balanceMovementDebit->getAmount());
        $this->entityManager->flush($user);
    }

    /**
     * @param Booking $booking
     * @param int $amountToRefund
     */
    public function refund(Booking $booking, $amountToRefund)
    {
        if (!is_null($booking->getBalanceMovementRefund())) return;

        $balanceMovementRefund = new BalanceMovement();
        $balanceMovementRefund->setAmount($amountToRefund);
        $balanceMovementRefund->setStatus(BalanceMovement::STATUS_VALIDATE);
        $balanceMovementRefund->setValidatedAt(new \DateTime());
        $balanceMovementRefund->setType(BalanceMovement::TYPE_REFUND);
        $balanceMovementRefund->setUser($booking->getUser());
        $booking->addBalanceMovement($balanceMovementRefund);
        $this->entityManager->persist($balanceMovementRefund);
        $this->entityManager->flush($balanceMovementRefund);

        $user = $balanceMovementRefund->getUser();
        $user->setAmountBalance($user->getAmountBalance() + $balanceMovementRefund->getAmount());
        $this->entityManager->flush($user);
    }

    /**
     * @param Booking $booking
     * @param int $amountToCredit
     * @param int $mangopayId
     */
    public function credit(Booking $booking, $amountToCredit = null, $mangopayId = null)
    {
        $balanceMovementCredit = new BalanceMovement();
        $balanceMovementCredit->setAmount($amountToCredit);
        $balanceMovementCredit->setStatus(BalanceMovement::STATUS_VALIDATE);
        $balanceMovementCredit->setValidatedAt(new \DateTime());
        $balanceMovementCredit->setType(BalanceMovement::TYPE_CREDIT);
        $balanceMovementCredit->setUser($booking->getListing()->getUser());
        $balanceMovementCredit->setMangopayId($mangopayId);
        $booking->addBalanceMovement($balanceMovementCredit);
        $this->entityManager->persist($balanceMovementCredit);
        $this->entityManager->flush($balanceMovementCredit);

        $user = $balanceMovementCredit->getUser();
        $user->setAmountBalance($user->getAmountBalance() + $balanceMovementCredit->getAmount());
        $this->entityManager->flush($user);
    }
}
