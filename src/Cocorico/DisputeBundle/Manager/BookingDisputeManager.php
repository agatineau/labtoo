<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\DisputeBundle\Manager;

use Cocorico\CoreBundle\Entity\Booking;
use Cocorico\CoreBundle\Event\BookingEvents;
use Cocorico\CoreBundle\Event\BookingPayinRefundEvent;
use Cocorico\DisputeBundle\Entity\BookingDefer;
use Cocorico\DisputeBundle\Mailer\TwigSwiftMailer;
use Doctrine\ORM\EntityManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class BookingDisputeManager
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var TwigSwiftMailer
     */
    private $mailer;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var array
     */
    private $parameters;

    /**
     * BookingDisputeManager constructor.
     * @param EntityManager $entityManager
     * @param TwigSwiftMailer $mailer
     * @param EventDispatcherInterface $eventDispatcher
     * @param array $vars
     */
    public function __construct(
        EntityManager $entityManager,
        TwigSwiftMailer $mailer,
        EventDispatcherInterface $eventDispatcher,
        array $vars
    )
    {
        $this->entityManager = $entityManager;
        $this->mailer = $mailer;
        $this->eventDispatcher = $eventDispatcher;
        $this->parameters = $vars['parameters'];
    }

    /**
     * @param Booking $booking
     * @return bool
     */
    private function isDisputableByAsker(Booking $booking)
    {
        return (
            in_array($booking->getStatus(), Booking::$disputableStatus) &&
            !$booking->isValidated() &&
            is_null($booking->getDisputedAskerBookingAt()) &&
            $booking->getEnd() < new \DateTime()
        );
    }

    /**
     * @param Booking $booking
     * @return bool
     */
    public function canBeDeferredByAsker(Booking $booking)
    {
        if ($booking->getDefers()->count() < $this->parameters['defer_limit']) {
            return $this->isDisputableByAsker($booking);
        }
        return false;
    }

    /**
     * @param Booking $booking
     * @return bool
     */
    public function canBeDisputedByAsker(Booking $booking)
    {
        if ($booking->getDefers()->count() >= $this->parameters['defer_limit']) {
            return $this->isDisputableByAsker($booking);
        }
        return false;
    }

    /**
     * @param Booking $booking
     * @return string
     */
    public function getEditableType(Booking $booking)
    {
        if ($this->canBeDeferredByAsker($booking)) {
            return 'defer';
        }
        if ($this->canBeDisputedByAsker($booking)) {
            return 'dispute';
        }
        return '';
    }

    /**
     * @param Booking $booking
     * @return bool
     */
    public function dispute(Booking $booking)
    {
        if ($this->canBeDeferredByAsker($booking)) {
            $defer = new BookingDefer();
            $defer->setDuration($this->parameters['defer_duration']);
            $booking->addDefer($defer);
            $newEndDate = clone $booking->getEnd();
            $newEndDate->modify(sprintf('+%d minutes', $this->parameters['defer_duration']));
            $booking->setEnd($newEndDate);
            $this->entityManager->persist($defer);
            $this->entityManager->flush();
            $this->mailer->sendBookingDeferredByAskerMessageToAsker($booking);
            $this->mailer->sendBookingDeferredByAskerMessageToOfferer($booking);
            return true;
        }
        if ($this->canBeDisputedByAsker($booking)) {
            $booking->setStatus(Booking::STATUS_DISPUTED_ASKER);
            $booking->setDisputedAskerBookingAt(new \DateTime());
            $booking->setEnd(null);
            $this->entityManager->flush();
            $this->mailer->sendBookingDisputedByAskerMessageToAsker($booking);
            $this->mailer->sendBookingDisputedByAskerMessageToOfferer($booking);
            $this->mailer->sendBookingDisputedByAskerMessageToAdmin($booking);
            return true;
        }
        return false;
    }

    /**
     * @param Booking $booking
     * @return bool
     */
    public function cancelByAdmin(Booking $booking)
    {
        if ($booking->getStatus() != Booking::STATUS_DISPUTED_ASKER) return false;

        $event = new BookingPayinRefundEvent($booking);

        $this->eventDispatcher->dispatch(BookingEvents::BOOKING_REFUND, $event);

        if ($event->getCancelable()) {
            $booking->setStatus(Booking::STATUS_CANCELED_ADMIN);
            $booking->setCanceledAdminBookingAt(new \DateTime());
            $this->entityManager->flush($booking);

            return true;
        }
        return false;
    }
}
