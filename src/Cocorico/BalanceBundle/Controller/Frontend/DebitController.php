<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\BalanceBundle\Controller\Frontend;

use Cocorico\BalanceBundle\Exception\DebitException;
use Cocorico\CoreBundle\Entity\Booking;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/booking/payment/debit")
 */
class DebitController extends Controller
{
    /**
     * @param Booking $booking
     * @return Response
     */
    public function formAction(Booking $booking)
    {
        $form = $this->createDebitForm($booking);

        return $this->render(
            'CocoricoBalanceBundle:Frontend/Debit:_new.html.twig',
            array(
                'form' => $form->createView(),
            )
        );
    }

    /**
     * @Route("/{id}/new", name="cocorico_balance_debit_new")
     * @Security("not has_role('ROLE_ADMIN') and has_role('ROLE_USER')")
     * @Method({"GET", "POST"})
     *
     * @param Booking $booking
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function newAction(Booking $booking)
    {
        $form = $this->createDebitForm($booking);

        $formHandler = $this->get('cocorico_balance.form.handler.debit');

        try {
            $formHandler->process($form, $booking);

        } catch (DebitException $e) {

            $this->addFlashMessage('error', $e->getMessage());

            return $this->redirectToRoute(
                'cocorico_booking_payment_new',
                array(
                    'booking_id' => $booking->getId(),
                )
            );
        }

        return $this->redirectToRoute('cocorico_dashboard_booking_asker');
    }

    /**
     * @param Booking $booking
     * @return \Symfony\Component\Form\Form|\Symfony\Component\Form\FormInterface
     */
    private function createDebitForm(Booking $booking)
    {
        return $this->get('form.factory')->createNamed(
            'debit',
            'debit',
            null,
            array(
                'action' => $this->generateUrl(
                    'cocorico_balance_debit_new',
                    array(
                        'id' => $booking->getId(),
                    )
                ),
                'method' => 'POST',
            )
        );
    }

    /**
     * @param string $type
     * @param string $message
     * @param string $domain
     * @param int|null $code
     */
    private function addFlashMessage(
        $type,
        $message,
        $domain = 'cocorico_balance',
        $code = null
    ) {
        /** @Ignore */
        $message = $this->get('translator')->trans($message, array(), $domain);
        if ($code) {
            $message = sprintf('%s (%d)', $message, $code);
        }
        $this->get('session')->getFlashBag()->add($type, $message);
    }
}
