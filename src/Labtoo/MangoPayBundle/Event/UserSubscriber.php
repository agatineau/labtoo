<?php

/*
* This file is part of the Cocorico package.
*
* (c) Cocolabs SAS <contact@cocolabs.io>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Labtoo\MangoPayBundle\Event;

use Cocorico\MangoPayBundle\Model\Manager\BankAccountManager;
use Cocorico\MangoPayBundle\Model\Manager\WalletManager;
use Cocorico\UserBundle\Event\UserEvent;
use Cocorico\UserBundle\Event\UserEvents;
use Labtoo\MangoPayBundle\Model\Manager\UserManager;
use MangoPay\ResponseException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class UserSubscriber implements EventSubscriberInterface
{
    protected $mangopayUserManager;
    protected $mangopayWalletManager;
    protected $mangopayBankAccountManager;

    /**
     * @param UserManager $mangopayUserManager
     * @param WalletManager       $mangopayWalletManager
     * @param BankAccountManager  $mangopayBankAccountManager
     */
    public function __construct(
        UserManager $mangopayUserManager,
        WalletManager $mangopayWalletManager,
        BankAccountManager $mangopayBankAccountManager
    ) {
        $this->mangopayUserManager = $mangopayUserManager;
        $this->mangopayWalletManager = $mangopayWalletManager;
        $this->mangopayBankAccountManager = $mangopayBankAccountManager;
    }

    /**
     * Create Mangopay User and Wallet
     *
     * @param UserEvent $event
     */
    public function onRegister(UserEvent $event)
    {
        $this->setMangoPayData($event);
    }


    /**
     * Update Mangopay User info
     *
     * @param UserEvent $event
     */
    public function onProfileUpdate(UserEvent $event)
    {
        $this->setMangoPayData($event);
    }

    /**
     * Update or create Mangopay Bank Account
     *
     * @param UserEvent $event
     * @throws ResponseException
     * @throws \Exception
     */
    public function onBankAccountUpdate(UserEvent $event)
    {
        $user = $event->getUser();
        $createNewBankAccount = false;

        $existingBankAccount = $this->mangopayBankAccountManager->get(
            $user->getMangopayId(),
            $user->getMangopayBankAccountId()
        );

        $bankOwnerAddress = str_replace(array("\n", "\t", "\r"), ' ', $user->getBankOwnerAddress());

        if ($existingBankAccount && $existingBankAccount->Id) {
            //If Banks details have changed a new one is created
            if (trim($existingBankAccount->OwnerName) != trim($user->getBankOwnerName()) ||
                trim($existingBankAccount->OwnerAddress) != trim($bankOwnerAddress) ||
                trim($existingBankAccount->Details->IBAN) != str_replace(' ', '', $user->getIban()) ||
                trim($existingBankAccount->Details->BIC) != str_replace(' ', '', $user->getBic())
            ) {
                $createNewBankAccount = true;
            } else {
                //Abnormal situation: User have a bank account on mangopay but not here
                if (!$user->getMangopayBankAccountId()) {
                    $user->setMangopayBankAccountId($existingBankAccount->Id);
                }
            }
        } else {
            $createNewBankAccount = true;
        }

        if ($createNewBankAccount) {
            try {
                $bankAccount = $this->mangopayBankAccountManager->create(
                    'IBAN',
                    $user->getBankOwnerName(),
                    $bankOwnerAddress,
                    $user->getMangopayId(),
                    $user->getIban(),
                    $user->getBic()
                );

                if ($bankAccount->Id) {
                    $user->setMangopayBankAccountId($bankAccount->Id);
                }

            } catch (ResponseException $e) {
                throw $e;
            }
        }

        $this->setMangoPayData($event);
    }

    /**
     * Set User Mangopay data
     *
     * @param UserEvent $event
     */
    private function setMangoPayData(UserEvent $event)
    {
        $user = $event->getUser();

        //User creation
        if (!$user->getMangopayId()) {
            $mangopayUser = $this->mangopayUserManager->createOrUpdate($user);
            $user->setMangopayId($mangopayUser->Id);
        }

        //Wallet creation
        if (!$user->getMangopayWalletId()) {
            $mangopayWallet = $this->mangopayWalletManager->create(
                $mangopayUser->Id,
                "User: " . $user->getFullNameLegal()
            );
            $user->setMangopayWalletId($mangopayWallet->Id);
        }

        $event->setUser($user);

        $event->stopPropagation();
    }

    public static function getSubscribedEvents()
    {
        return array(
            UserEvents::USER_REGISTER => array('onRegister', 1),
            UserEvents::USER_PROFILE_UPDATE => array('onProfileUpdate', 1),
            UserEvents::USER_BANK_ACCOUNT_UPDATE => array('onBankAccountUpdate', 1),
        );
    }

}