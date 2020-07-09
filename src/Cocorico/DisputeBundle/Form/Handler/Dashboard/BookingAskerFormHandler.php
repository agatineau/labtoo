<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Cocorico\DisputeBundle\Form\Handler\Dashboard;

use Cocorico\CoreBundle\Entity\Booking;
use Cocorico\CoreBundle\Form\Handler\Dashboard\BookingAskerFormHandler as BaseBookingAskerFormHandler;
use Cocorico\DisputeBundle\Manager\BookingDisputeManager;
use Symfony\Component\Form\Form;

class BookingAskerFormHandler extends BaseBookingAskerFormHandler
{
    /**
     * @var BookingDisputeManager
     */
    private $bookingDisputeManager;

    /**
     * @param BookingDisputeManager $bookingDisputeManager
     */
    public function setBookingDisputeManager($bookingDisputeManager)
    {
        $this->bookingDisputeManager = $bookingDisputeManager;
    }

    /**
     * @param Form $form
     *
     * @return int equal to :
     * 1: Success
     * -3: Refund error
     */
    protected function onSuccess(Form $form)
    {
        /** @var Booking $booking */
        $booking = $form->getData();
        $message = $form->get("message")->getData();
        $this->threadManager->addReplyThread($booking, $message, $booking->getUser());
        if ($this->bookingDisputeManager->dispute($booking)) {
            $result = 1;
        } else {
            $result = -3;
        }

        return $result;
    }
}
