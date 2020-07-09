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

use Cocorico\CoreBundle\Event\BookingAmountEvent;
use Cocorico\CoreBundle\Event\BookingAmountEvents;
use Cocorico\ExperimentBundle\Model\ListingSearchSession;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class BookingAmountSubscriber implements EventSubscriberInterface
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
     * @param BookingAmountEvent $event
     */
    public function onBookingPreAmountsSetting(BookingAmountEvent $event)
    {
        $booking = $event->getBooking();
        $booking->setAmount($this->listingSearchSession->getBookingAmount());
        $booking->setAnswers($this->listingSearchSession->getBookingAnswers());
        $event->setBooking($booking);
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            BookingAmountEvents::BOOKING_PRE_AMOUNTS_SETTING => array('onBookingPreAmountsSetting', 1),
        );
    }
}
