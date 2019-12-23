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

use Cocorico\BalanceBundle\Exception\DebitInsufficientAmountException;
use Cocorico\BalanceBundle\Manager\TransactionManager;
use Cocorico\CoreBundle\Event\BookingEvent;
use Cocorico\CoreBundle\Event\BookingEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class BookingSubscriber implements EventSubscriberInterface
{
    /**
     * @var TransactionManager
     */
    private $transactionManager;

    /**
     * @param TransactionManager $transactionManager
     */
    public function __construct(TransactionManager $transactionManager)
    {
        $this->transactionManager = $transactionManager;
    }

    /**
     * @param BookingEvent $event
     * @throws DebitInsufficientAmountException
     */
    public function onBookingPay(BookingEvent $event)
    {
        $booking = $event->getBooking();
        if ($booking->getBalanceMovements()->isEmpty()) return;
        if (!$this->transactionManager->isDebitable($booking)) {
            throw new DebitInsufficientAmountException();
        }
        $this->transactionManager->debit($booking);
        $event->stopPropagation();
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            BookingEvents::BOOKING_PAY => array('onBookingPay', 2),
        );
    }
}
