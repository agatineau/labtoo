<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\BalanceBundle\Form\Handler\Frontend;

use Cocorico\BalanceBundle\Exception\DebitFormNotSubmitException;
use Cocorico\BalanceBundle\Exception\DebitInsufficientAmountException;
use Cocorico\BalanceBundle\Manager\TransactionManager;
use Cocorico\CoreBundle\Entity\Booking;
use Cocorico\CoreBundle\Event\BookingEvent;
use Cocorico\CoreBundle\Event\BookingEvents;
use Cocorico\CoreBundle\Model\Manager\BookingManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class DebitFormHandler
{
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var BookingManager
     */
    private $bookingManager;

    /**
     * @var TransactionManager
     */
    private $transactionManager;

    /**
     * @var Request
     */
    private $request;

    /**
     * DebitFormHandler constructor.
     * @param EventDispatcherInterface $eventDispatcher
     * @param BookingManager $bookingManager
     * @param TransactionManager $transactionManager
     * @param RequestStack $requestStack
     */
    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        BookingManager $bookingManager,
        TransactionManager $transactionManager,
        RequestStack $requestStack
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->bookingManager = $bookingManager;
        $this->transactionManager = $transactionManager;
        $this->request = $requestStack->getCurrentRequest();
    }

    /**
     * @param Form $form
     * @param Booking $booking
     * @throws DebitFormNotSubmitException
     * @throws DebitInsufficientAmountException
     */
    public function process(Form $form, Booking $booking)
    {
        $form->handleRequest($this->request);

        if (
            !$form->isSubmitted()
            || !$this->request->isMethod('POST')
            || !$form->isValid()
        ) {
            throw new DebitFormNotSubmitException();
        }

        if (!$this->transactionManager->isDebitable($booking)) {
            throw new DebitInsufficientAmountException();
        }

        $this->transactionManager->register($booking);

        $this->bookingManager->create($booking);
        $this->eventDispatcher->dispatch(
            BookingEvents::BOOKING_NEW_CREATED,
            new BookingEvent($booking)
        );
    }
}
