<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\CoreBundle\Controller\Admin;

use Cocorico\CoreBundle\Entity\Booking;
use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Translation\TranslationContainerInterface;
use Sonata\AdminBundle\Controller\CRUDController as Controller;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class BookingController extends Controller implements TranslationContainerInterface
{
    static $cancelSuccess = 'admin.booking.cancel.success';
    static $cancelError = 'admin.booking.cancel.error';
    static $validateSuccess = 'admin.booking.validate.success';
    static $validateError = 'admin.booking.validate.error';

    /**
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function cancelAction()
    {
        /** @var Booking $booking */
        $booking = $this->admin->getSubject();

        if (!$booking) throw new NotFoundHttpException();

        if ($this->get('cocorico_dispute.booking_dispute.manager')->cancelByAdmin($booking)) {
            $this->addFlash('sonata_flash_success', self::$cancelSuccess);
        } else {
            $this->addFlash('sonata_flash_error', self::$cancelError);
        }
        return $this->redirect($this->admin->generateUrl('edit', array(
            'id' => $booking->getId()
        )));
    }

    /**
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function validateAction()
    {
        /** @var Booking $booking */
        $booking = $this->admin->getSubject();

        if (!$booking) throw new NotFoundHttpException();

        if ($this->get('cocorico.booking.manager')->validate($booking)) {
            $booking->setStatus(Booking::STATUS_CANCELED_ADMIN);
            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('sonata_flash_success', self::$validateSuccess);
        } else {
            $this->addFlash('sonata_flash_error', self::$validateError);
        }
        return $this->redirect($this->admin->generateUrl('edit', array(
            'id' => $booking->getId()
        )));
    }

    /**
     * @return array
     */
    public static function getTranslationMessages()
    {
        return array(
            new Message(self::$cancelSuccess, 'SonataAdminBundle'),
            new Message(self::$cancelError, 'SonataAdminBundle'),
            new Message(self::$validateSuccess, 'SonataAdminBundle'),
            new Message(self::$validateError, 'SonataAdminBundle'),
        );
    }
}
