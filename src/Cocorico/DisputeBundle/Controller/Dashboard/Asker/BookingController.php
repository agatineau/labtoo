<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\DisputeBundle\Controller\Dashboard\Asker;

use Cocorico\CoreBundle\Entity\Booking;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/asker/booking")
 */
class BookingController extends Controller
{
    /**
     * @param Booking $booking
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showButtonAction(Booking $booking)
    {
        $bookingDisputeManager = $this->get('cocorico_dispute.booking_dispute.manager');
        $type = $bookingDisputeManager->getEditableType($booking);

        return $this->render(
            '@CocoricoDispute/Dashboard/Booking/_booking_show_actions_dispute.html.twig',
            array(
                'booking' => $booking,
                'type' => $type
            )
        );
    }

    /**
     * @Route("/{id}/edit/dispute", name="cocorico_dispute_dashboard_booking_edit_asker", requirements={
     *      "id" = "\d+"
     * })
     * @Method({"GET", "POST"})
     * @Security("is_granted('edit_as_asker', booking)")
     * @ParamConverter("booking", class="Cocorico\CoreBundle\Entity\Booking")
     *
     * @param Request $request
     * @param Booking $booking
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editAction(Request $request, Booking $booking)
    {
        $bookingHandler = $this->get('cocorico_dispute.form.handler.booking.asker.dashboard');
        $bookingRefundManger = $this->get('cocorico.booking_payin_refund.manager');
        $form = $this->createEditForm($booking);

        $success = $bookingHandler->process($form);

        $translator = $this->container->get('translator');
        $session = $this->container->get('session');
        if ($success == 1) {
            $url = $this->generateUrl(
                'cocorico_dispute_dashboard_booking_edit_asker',
                array(
                    'id' => $booking->getId()
                )
            );

            $session->getFlashBag()->add(
                'success',
                $translator->trans('booking.edit.success', array(), 'cocorico_booking')
            );

            return $this->redirect($url);
        } elseif ($success < 0) {
            $errorMsg = $translator->trans('booking.new.unknown.error', array(), 'cocorico_booking');
            if ($success == -1 || $success == -2 || $success == -4) {
                $errorMsg = $translator->trans('booking.edit.error', array(), 'cocorico_booking');
            } elseif ($success == -3) {
                $errorMsg = $translator->trans('booking.edit.payin.error', array(), 'cocorico_booking');
            }
            $session->getFlashBag()->add('error', $errorMsg);
        }

        $bookingDisputeManager = $this->get('cocorico_dispute.booking_dispute.manager');
        $type = $bookingDisputeManager->getEditableType($booking);

        return $this->render(
            'CocoricoDisputeBundle:Dashboard/Booking:edit.html.twig',
            array(
                'booking' => $booking,
                'booking_can_be_edited' => true,
                'type' => $type,
                'form' => $form->createView(),
                'other_user' => $booking->getListing()->getUser(),
                'other_user_rating' => $booking->getListing()->getUser()->getAverageOffererRating(),
                'amount_total' => $bookingRefundManger->getAmountDecimalToRefundOrRefundedToAsker($booking),
                'vat_inclusion_text' => $this->get('cocorico.twig.core_extension')
                    ->vatInclusionText($request->getLocale(), true, true)
            )
        );
    }

    /**
     * Creates a form to edit a Booking entity.
     *
     * @param Booking $booking The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createEditForm(Booking $booking)
    {
        $form = $this->get('form.factory')->createNamed(
            'booking',
            'booking_edit',
            $booking,
            array(
                'method' => 'POST',
                'action' => $this->generateUrl(
                    'cocorico_dispute_dashboard_booking_edit_asker',
                    array(
                        'id' => $booking->getId()
                    )
                ),
            )
        );

        return $form;
    }
}
