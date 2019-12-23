<?php

/*
* This file is part of the Cocorico package.
*
* (c) Cocolabs SAS <contact@cocolabs.io>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Cocorico\BalanceBundle\EventListener;

use Cocorico\BalanceBundle\Manager\TransactionManager;
use Cocorico\CoreBundle\Entity\Booking;
use Cocorico\CoreBundle\Event\BookingEvents;
use Cocorico\CoreBundle\Event\BookingValidateEvent;
use Cocorico\CoreBundle\Model\Manager\BookingBankWireManager;
use Cocorico\MangoPayBundle\Event\BookingValidateSubscriber as BaseBookingValidateSubscriber;
use Cocorico\MangoPayBundle\Model\Manager\TransferManager;

class BookingValidateSubscriber extends BaseBookingValidateSubscriber
{
    /**
     * @var TransactionManager
     */
    private $transactionManager;

    /**
     * BookingValidateSubscriber constructor.
     * @param BookingBankWireManager $bookingBankWireManager
     * @param TransferManager $mangopayTransferManager
     * @param array $bundles
     * @param TransactionManager $transactionManager
     */
    public function __construct(
        BookingBankWireManager $bookingBankWireManager,
        TransferManager $mangopayTransferManager,
        $bundles,
        TransactionManager $transactionManager
    ) {
        parent::__construct(
            $bookingBankWireManager,
            $mangopayTransferManager,
            $bundles
        );
        $this->transactionManager = $transactionManager;
    }

    /**
     * @param BookingValidateEvent $event
     * @return bool
     * @throws \Exception
     */
    public function onBookingValidate(BookingValidateEvent $event)
    {
        $booking = $event->getBooking();
        $validated = $this->transferFundFromAskerToOffererWallet($booking);
        $event->setValidated($validated);
        $event->setBooking($booking);
        $event->stopPropagation();
    }

    /**
     *  Transfer the funds from the asker's wallet to the offerer's wallet.
     *  The offerer can be payed.
     *  Platform fees are taken here.
     *  No funds have to be transferred to offerer wallet if cancellation refund is 100%
     *
     * @param Booking $booking
     * @return bool
     */
    protected function transferFundFromAskerToOffererWallet(Booking $booking)
    {
        $asker = $booking->getUser();
        $offerer = $booking->getListing()->getUser();
        $amountToTransfer = $booking->getAmountToPayByAsker();

        //If there is a deposit the deposit amount is not transferred on offerer wallet and stay in asker wallet.
        //This allow the deposit refunding to user (asker) through the Mangopay Payin Refund because the amount to be refunded
        //must be on the user (asker) wallet. No fees are taken on deposit.
        if (array_key_exists("CocoricoListingDepositBundle", $this->bundles)) {
            if ($booking->getAmountDeposit()) {
                $amountToTransfer -= $booking->getAmountDeposit();
            }
        }

        //Transfer the funds from the asker wallet to the offerer wallet.
        //All fees (offerer and asker) are collected here except for cancellation with refund of 100%.
        //In the case of refund of 100% then fees are collected while refunding
        $mangopayTransfer = $this->mangopayTransferManager->create(
            $asker->getMangopayId(),
            $asker->getMangopayWalletId(),
            $offerer->getMangopayId(),
            $offerer->getMangopayWalletId(),
            $amountToTransfer,//debited found
            $booking->getAmountTotalFee()
        );

        if ($mangopayTransfer->Status == 'SUCCEEDED' && $mangopayTransfer->ResultCode == '000000') {
            $this->transactionManager->credit($booking, $booking->getAmountToPayToOfferer(), $mangopayTransfer->Id);

            return true;
        } else {
            $message = "An error occurred while transferring amount from asker to offerer wallet:\n";
            $message .= "\n- Booking Id: " . $booking->getId();
            $message .= "\n- Mangopay message: " . $mangopayTransfer->ResultMessage;

            $this->bookingBankWireManager->getMailer()->sendMessageToAdmin("Error on wallet transfer", $message);

            return false;
        }
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            BookingEvents::BOOKING_VALIDATE => array('onBookingValidate', 3),
        );
    }
}
