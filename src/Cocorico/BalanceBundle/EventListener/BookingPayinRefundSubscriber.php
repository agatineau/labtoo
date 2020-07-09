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
use Cocorico\CoreBundle\Entity\BookingPayinRefund;
use Cocorico\CoreBundle\Event\BookingEvents;
use Cocorico\CoreBundle\Event\BookingPayinRefundEvent;
use Cocorico\CoreBundle\Model\Manager\BookingPayinRefundManager;
use Cocorico\MangoPayBundle\Event\BookingPayinRefundSubscriber as BaseBookingPayinRefundSubscriber;
use Cocorico\MangoPayBundle\Model\Manager\PayinRefundManager;
use Cocorico\MangoPayBundle\Model\Manager\TransferManager;

class BookingPayinRefundSubscriber extends BaseBookingPayinRefundSubscriber
{
    /**
     * @var TransactionManager
     */
    private $transactionManager;

    /**
     * BookingPayinRefundSubscriber constructor.
     * @param BookingPayinRefundManager $bookingPayinRefundManager
     * @param PayinRefundManager $mangopayPayinRefundManager
     * @param TransferManager $mangopayTransferManager
     * @param TransactionManager $transactionManager
     */
    public function __construct(
        BookingPayinRefundManager $bookingPayinRefundManager,
        PayinRefundManager $mangopayPayinRefundManager,
        TransferManager $mangopayTransferManager,
        TransactionManager $transactionManager
    ) {
        parent::__construct(
            $bookingPayinRefundManager,
            $mangopayPayinRefundManager,
            $mangopayTransferManager
        );
        $this->transactionManager = $transactionManager;
    }

    /**
     * Refund booking amount to asker when it's canceled and transfer remaining funds from asker to offerer wallet.
     * If no refund is made to asker because of cancelation policy then the total amount of the booking will be withdraw
     * to the offerer and the total fees will be charged while transfer from asker to offerer wallet.
     * If refund to asker is 100% then no funds will be transferred from asker to offerer wallet and fees will be
     * charged while refunding
     *
     * @param BookingPayinRefundEvent $event
     */
    public function onBookingRefund(BookingPayinRefundEvent $event)
    {
        $booking = $event->getBooking();

        //Do asker funds have to be transferred to offerer wallet
        $transferAskerFundsToOfferer = false;

        if (in_array($booking->getStatus(), Booking::$refundableStatus)) {
            //Get fees and refund amount
            $feeAndAmountToRefund = $this->bookingPayinRefundManager->getFeeAndAmountToRefundToAsker($booking);

            //If there is something to refund to asker
            if ($feeAndAmountToRefund["refund_amount"]) { //$feeAndAmountToRefund["fee_to_collect_while_refund"] ||
                //Refund

//                if ($booking->getMangopayPayinPreAuthId()) {
//                    $mangopayPayinRefund = $this->mangopayPayinRefundManager->create(
//                        $booking->getMangopayPayinPreAuthId(),
//                        $booking->getUser()->getMangopayId(),
//                        $feeAndAmountToRefund["refund_amount"] + $feeAndAmountToRefund["fee_to_collect_while_refund"],
//                        $feeAndAmountToRefund["fee_to_collect_while_refund"]
//                    );
//
//                    if ($mangopayPayinRefund->Status == 'SUCCEEDED' && $mangopayPayinRefund->ResultCode == '000000') {
//                        $payinRefund = new BookingPayinRefund();
//                        $payinRefund->setBooking($booking);
//                        $payinRefund->setAmount($feeAndAmountToRefund["refund_amount"]);
//                        $payinRefund->setMangopayRefundId($mangopayPayinRefund->Id);
//                        $payinRefund->setUser($booking->getUser());
//                        $payinRefund->setPayedAt(new \DateTime());
//                        $this->bookingPayinRefundManager->save($payinRefund);
//                        $this->entityManager->refresh($booking);
//
//                        $transferAskerFundsToOfferer = true;
//                    } else {
//                        $message = "An error occurred while refunding amount to asker:\n";
//                        $message .= "\n- Booking Id: " . $booking->getId();
//                        $message .= "\n- Mangopay message: " . $mangopayPayinRefund->ResultMessage;
//
//                        $this->bookingPayinRefundManager->getMailer()->sendMessageToAdmin("Error on refund", $message);
//                    }
//                } else {
//                }

                $this->transactionManager->refund($booking, $feeAndAmountToRefund["refund_amount"]);

                $transferAskerFundsToOfferer = true;

            } elseif ($feeAndAmountToRefund["refund_percent"] == 0) {//nothing to refund to asker.
                $transferAskerFundsToOfferer = true;
            } else {
                //should not happen
            }

            if ($transferAskerFundsToOfferer) {
                $cancelable = $this->transferFundFromAskerToOffererWallet($booking, $feeAndAmountToRefund);
                $event->setCancelable($cancelable);
            }
        }

        $event->setBooking($booking);
        $event->stopPropagation();
    }

    /**
     * If refund is not 100% then the remaining funds are transferred from the asker's wallet to the offerer's wallet.
     * Platform fees are taken here.
     *
     * @param Booking $booking
     * @param array   $feeAndAmountRefund
     * @return bool
     */
    protected function transferFundFromAskerToOffererWallet(Booking $booking, array $feeAndAmountRefund)
    {
        $asker = $booking->getUser();
        $offerer = $booking->getListing()->getUser();

        //Refund to asker is not 100%. A transfer of remaining fund have to be made to the offerer wallet
        if ($feeAndAmountRefund["refund_percent"] != 1) {
            //Transfer amount is equal to total to pay by asker minus refund amount
            $debitedFunds = $booking->getAmountToPayByAsker() - $feeAndAmountRefund["refund_amount"];

            //Amount to pay to offerer
            //Deposit amount does not impact amount to pay to offerer
            if ($this->bookingPayinRefundManager->depositIsEnabled()) {
                if ($booking->getAmountDeposit()) {
                    $feeAndAmountRefund["refund_amount"] -= $booking->getAmountDeposit();
                }
            }

            $amountToPayToOfferer = $booking->getAmountToPayToOfferer() - $feeAndAmountRefund["refund_amount"];
            if ($this->bookingPayinRefundManager->voucherIsEnabled()) {
                if ($booking->getAmountDiscountVoucher() && $feeAndAmountRefund["refund_amount"]) {
                    $amountToPayToOfferer -= $booking->getAmountDiscountVoucher();//Discount amount is not refunded
                }
            }
            //Transfer the funds from the asker wallet to the offerer wallet.
            //In the case of refund of 100% fees are collected while refunding
            //DebitedFunds – Fees = CreditedFunds (amount received on wallet)
            $mangopayTransfer = $this->mangopayTransferManager->create(
                $asker->getMangopayId(),
                $asker->getMangopayWalletId(),
                $offerer->getMangopayId(),
                $offerer->getMangopayWalletId(),
                $debitedFunds,
                $booking->getAmountTotalFee()
            );

            if ($mangopayTransfer->Status == 'SUCCEEDED' && $mangopayTransfer->ResultCode == '000000') {
                $this->transactionManager->credit($booking, $amountToPayToOfferer, $mangopayTransfer->Id);
                return true;
            } else {
                $message = "An error occurred while transferring amount from asker to offerer wallet while refunding:\n";
                $message .= "\n- Booking Id: " . $booking->getId();
                $message .= "\n- Mangopay message: " . $mangopayTransfer->ResultMessage;

                $this->bookingPayinRefundManager->getMailer()->sendMessageToAdmin("Error on wallet transfer", $message);

                return false;
            }
        } else {//Refund is 100%. So no transfer have to be made
            return true;
        }

    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            BookingEvents::BOOKING_REFUND => array('onBookingRefund', 3),
        );
    }
}
