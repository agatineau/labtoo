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
use Cocorico\BalanceBundle\Model\Credit;
use Cocorico\UserBundle\Entity\User;
use Doctrine\ORM\EntityManager;
use Labtoo\MangoPayBundle\Model\Manager\PayInBankWireManager;
use Labtoo\MangoPayBundle\Model\Manager\PayInCardManager;

class CreditManager
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var PayInCardManager
     */
    private $payInCardManager;

    /**
     * @var PayInBankWireManager
     */
    private $payInBankWireManager;

    /**
     * @param EntityManager $entityManager
     * @param PayInCardManager $payInCardManager
     * @param PayInBankWireManager $payInBankWireManager
     */
    public function __construct(
        EntityManager $entityManager,
        PayInCardManager $payInCardManager,
        PayInBankWireManager $payInBankWireManager
    ) {
        $this->entityManager = $entityManager;
        $this->payInCardManager = $payInCardManager;
        $this->payInBankWireManager = $payInBankWireManager;
    }

    /**
     * @param User $user
     * @param Credit $credit
     * @return BalanceMovement
     */
    public function register(User $user, Credit $credit)
    {
        $balanceMovement = $credit->getBalanceMovement();
        $balanceMovement->setStatus(BalanceMovement::STATUS_WAITING);

        if ($balanceMovement->getType() === BalanceMovement::TYPE_CREDIT_BANK_WIRE) {

            $payInBankWire = $this->payInBankWireManager->create(
                $user->getMangopayId(),
                $user->getMangopayWalletId(),
                $balanceMovement->getAmount(),
                $balanceMovement->getId()
            );
            $balanceMovement->setMangopayId($payInBankWire->Id);
        }

        $user->addBalanceMovement($balanceMovement);
        $this->entityManager->persist($balanceMovement);
        $this->entityManager->flush($balanceMovement);

        return $balanceMovement;
    }

    /**
     * @param BalanceMovement $balanceMovement
     * @param int $cardId
     * @return BalanceMovement|null|object
     */
    public function registerCard(BalanceMovement $balanceMovement, $cardId)
    {
        /** @var BalanceMovement $balanceMovement */
        $balanceMovement = $this->entityManager
            ->getRepository('CocoricoBalanceBundle:BalanceMovement')
            ->findOneBy(
                array(
                    'id' => $balanceMovement->getId(),
                    'status' => BalanceMovement::STATUS_WAITING,
                )
            );

        $balanceMovement->setMangopayCardId($cardId);
        $this->entityManager->flush($balanceMovement);
    }

    /**
     * @param BalanceMovement $balanceMovement
     */
    public function validate(BalanceMovement $balanceMovement)
    {
        /** @var BalanceMovement $balanceMovement */
        $balanceMovement = $this->entityManager
            ->getRepository('CocoricoBalanceBundle:BalanceMovement')
            ->findOneBy(
                array(
                    'id' => $balanceMovement->getId(),
                    'status' => BalanceMovement::STATUS_WAITING,
                )
            );

        $balanceMovement->setStatus(BalanceMovement::STATUS_VALIDATE);
        $balanceMovement->setValidatedAt(new \DateTime());
        $user = $balanceMovement->getUser();
        $user->setAmountBalance($user->getAmountBalance() + $balanceMovement->getAmount());
        $this->entityManager->flush($balanceMovement);
        $this->entityManager->flush($user);
    }

    /**
     * @param BalanceMovement $balanceMovement
     */
    public function invalidate(BalanceMovement $balanceMovement)
    {
        if ($balanceMovement->getStatus() == BalanceMovement::STATUS_INVALIDATE) {
            return;
        }

        $balanceMovement->setStatus(BalanceMovement::STATUS_INVALIDATE);
        $this->entityManager->flush($balanceMovement);
    }

    /**
     * @return int
     */
    public function checkBankWires()
    {
        $balanceMovements = $this->entityManager
            ->getRepository('CocoricoBalanceBundle:BalanceMovement')
            ->findCreditsBankWiresToCheck();

        foreach ($balanceMovements as $balanceMovement) {

            $payIn = $this->payInBankWireManager->get($balanceMovement->getMangopayId());

            if ($payIn->Status === 'SUCCEEDED') $this->validate($balanceMovement);
            elseif ($payIn->Status === 'FAILED') $this->invalidate($balanceMovement);
        }

        return count($balanceMovements);
    }
}
