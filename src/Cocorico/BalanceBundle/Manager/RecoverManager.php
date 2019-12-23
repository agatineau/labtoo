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
use Cocorico\CoreBundle\Mailer\TwigSwiftMailer;
use Cocorico\MangoPayBundle\Model\Manager\BankAccountManager;
use Cocorico\MangoPayBundle\Model\Manager\UserManager;
use Cocorico\UserBundle\Entity\User;
use Doctrine\ORM\EntityManager;
use MangoPay\BankAccount;
use Monolog\Logger;

class RecoverManager
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var BankAccountManager
     */
    private $bankAccountManager;

    /**
     * @var UserManager
     */
    private $mangopayUserManager;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var TwigSwiftMailer
     */
    private $mailer;

    /**
     * @var array
     */
    private $parameters;

    /**
     * @param EntityManager $entityManager
     * @param BankAccountManager $bankAccountManager
     * @param UserManager $mangopayUserManager
     * @param Logger $logger
     * @param TwigSwiftMailer $mailer
     * @param array $vars
     */
    public function __construct(
        EntityManager $entityManager,
        BankAccountManager $bankAccountManager,
        UserManager $mangopayUserManager,
        Logger $logger,
        TwigSwiftMailer $mailer,
        array $vars
    ) {
        $this->entityManager = $entityManager;
        $this->bankAccountManager = $bankAccountManager;
        $this->mangopayUserManager = $mangopayUserManager;
        $this->logger = $logger;
        $this->mailer = $mailer;
        $this->parameters = $vars['parameters'];
    }

    /**
     * @param User $user
     * @return bool
     */
    public function isChecked(User $user)
    {
        $lastCheckedAt = $user->getLastBalanceRecoverCheckedAt();
        if (!$lastCheckedAt) {
            return false;
        }
        $limitCheckedAt = clone $lastCheckedAt;
        $limitCheckedAt->modify(sprintf('+%d minutes', $this->parameters['recover_delay']));
        if ($limitCheckedAt < new \DateTime()) {
            return false;
        }

        return true;
    }

    /**
     * @param User $user
     */
    public function check(User $user)
    {
        $user->setLastBalanceRecoverCheckedAt(new \DateTime());
        $this->entityManager->flush();
    }

    /**
     * @param User $user
     * @return bool
     */
    public function isRecoverable(User $user)
    {
        return $user->getAmountBalance() > 0;
    }

    /**
     * @param User $user
     * @return bool
     */
    public function isBankable(User $user)
    {
        $bankAccount = $this->bankAccountManager->get(
            $user->getMangopayId(),
            $user->getMangopayBankAccountId()
        );

        return $bankAccount instanceof BankAccount;
    }

    /**
     * @param User $user
     */
    public function create(User $user)
    {
        $balanceMovement = new BalanceMovement();
        $balanceMovement->setAmount($user->getAmountBalance());
        $balanceMovement->setType(BalanceMovement::TYPE_RECOVER);
        $balanceMovement->setStatus(BalanceMovement::STATUS_WAITING);
        $user->addBalanceMovement($balanceMovement);
        $user->setAmountBalance(0);
        $this->entityManager->persist($balanceMovement);
        $this->entityManager->flush($balanceMovement);
        $this->entityManager->flush($user);
    }

    /**
     * @param BalanceMovement $balanceMovement
     * @param int $mangopayId
     */
    public function validate(BalanceMovement $balanceMovement, $mangopayId)
    {
        $balanceMovement->setStatus(BalanceMovement::STATUS_VALIDATE);
        $balanceMovement->setValidatedAt(new \DateTime());
        $balanceMovement->setMangopayId($mangopayId);
        $this->entityManager->flush($balanceMovement);
    }

    /**
     * @param BalanceMovement $balanceMovement
     * @param int $mangopayId
     */
    public function invalidate(BalanceMovement $balanceMovement, $mangopayId)
    {
        if ($balanceMovement->getStatus() == BalanceMovement::STATUS_INVALIDATE) {
            return;
        }

        $balanceMovement->setStatus(BalanceMovement::STATUS_INVALIDATE);
        $balanceMovement->setMangopayId($mangopayId);
        $user = $balanceMovement->getUser();
        $user->setAmountBalance($user->getAmountBalance() + $balanceMovement->getAmount());
        $this->entityManager->flush($balanceMovement);
        $this->entityManager->flush($user);
    }

    /**
     * @return int
     */
    public function checkBankWires()
    {

        $balanceMovements = $this->entityManager
            ->getRepository('CocoricoBalanceBundle:BalanceMovement')
            ->findRecoversBankWiresToCheck();

        $count = 0;

        $status = 'SUCCEEDED';
//        Now in the sandbox environment BankWire PayOuts is processed automatically
//        To avoid to ask to mangopay to manually validate a bank wire
//        if ($this->parameters['bankwire_checking_simulation']) {
//            $status = 'CREATED';
//        }

        foreach ($balanceMovements as $balanceMovement) {

            $transactions = $this->mangopayUserManager->getTransactions(
                $balanceMovement->getUser()->getMangopayId(),
                array(
                    'Status' => $status,
                    'Type' => 'PAYOUT',
                    'Nature' => 'REGULAR',
                )
            );

            foreach ($transactions as $transaction) {
                if ($transaction->Tag == $balanceMovement->getId()) {
                    $this->logger->debug(
                        'RecoverManager Transaction found:'.
                        '|-Id:'.$transaction->Id.
                        '|-Type:'.$transaction->Type.
                        '|-Nature:'.$transaction->Nature.
                        '|-AuthorId:'.$transaction->AuthorId.
                        '|-DebitedFunds:'.$transaction->DebitedFunds->Amount.
                        '|-Status:'.$transaction->Status.
                        '|-ResultCode:'.$transaction->ResultCode
                    );

                    if ((
                        $balanceMovement->getAmount() == $transaction->DebitedFunds->Amount &&
                        $balanceMovement->getUser()->getMangopayId() == $transaction->AuthorId
                    )) {
                        if (
                            ($transaction->Status == 'SUCCEEDED' && $transaction->ResultCode == '00000' && $transaction->Id)
//                            || $this->parameters['bankwire_checking_simulation']
                        ) {
                            $this->logger->debug(
                                'RecoverManager Transaction Payed:'.
                                '|-Balance Movement Id:'.$balanceMovement->getId().
                                '|-Transaction Id:'.$transaction->Id
                            );
                            $this->validate($balanceMovement, $transaction->Id);
                        } else {
                            $errors = array(
                                '121999' => 'Generic withdrawal error',
                                '121001' => 'The bank wire has been refused',
                                '121002' => 'The author is not the wallet owner',
                                '121003' => 'Insufficient wallet balance',
                                '121004' => 'Specific case: please contact Mangopay Support Team or Other case',
                                '121005' => 'Refused due to the Fraud Policy',
                            );

                            if ($transaction->Status == 'FAILED') {
                                $this->logger->debug(
                                    'RecoverManager Transaction Failed:'.
                                    '|-Balance Movement Id:'.$balanceMovement->getId().
                                    '|-Transaction Id:'.$transaction->Id
                                );

                                $this->invalidate($balanceMovement, $transaction->Id);

                                $this->mailer->sendMessageToAdmin(
                                    "Error while bank wire transfer for recover ".$balanceMovement->getId(),
                                    'Hello, there was en error while bank wire request operation with the following error code :'.
                                    $transaction->ResultCode.
                                    (isset($errors[$transaction->ResultCode]) ? ":".$errors[$transaction->ResultCode] : '')
                                );
                            }
                        }
                        $count++;
                    }
                }
            }
        }

        return $count;
    }
}
