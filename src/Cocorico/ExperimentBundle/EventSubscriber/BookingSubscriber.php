<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\ExperimentBundle\EventSubscriber;

use Cocorico\CoreBundle\Event\BookingEvent;
use Cocorico\CoreBundle\Event\BookingEvents;
use Cocorico\ExperimentBundle\Model\ListingSearchSession;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class BookingSubscriber implements EventSubscriberInterface
{
    /**
     * @var ListingSearchSession
     */
    private $listingSearchSession;

    /**
     * @param ListingSearchSession $listingSearchSession
     */
    public function __construct(ListingSearchSession $listingSearchSession)
    {
        $this->listingSearchSession = $listingSearchSession;
    }

    /**
     * @param BookingEvent $event
     */
    public function onBookingInit(BookingEvent $event)
    {
        $booking = $event->getBooking();
        $booking->setAmount($this->listingSearchSession->getBookingAmount());
        $event->setBooking($booking);
    }

    /**
     * @param BookingEvent $event
     */
    public function onBookingPay(BookingEvent $event)
    {
        $booking = $event->getBooking();

        $start = (new \DateTime());
        $start->setTime(0, 0);
        $booking->setStart($start);

        $end = clone $start;
        $end->modify(sprintf('+%d days', $booking->getListing()->getDuration()));
        $booking->setEnd($end);

        $event->setBooking($booking);
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            BookingEvents::BOOKING_INIT => array('onBookingInit', 1),
            BookingEvents::BOOKING_PAY => array('onBookingPay', 3),
        );
    }
}
