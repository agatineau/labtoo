<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\BalanceBundle\Controller\Dashboard;

use Cocorico\BalanceBundle\Entity\BalanceMovement;
use Cocorico\BalanceBundle\Exception\CreditCard3DSecureException;
use Cocorico\BalanceBundle\Exception\CreditCardNotValidException;
use Cocorico\BalanceBundle\Model\Credit;
use Cocorico\UserBundle\Entity\User;
use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Translation\TranslationContainerInterface;
use MangoPay\CardRegistration;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * @Route("/balance")
 */
class CreditController extends Controller implements TranslationContainerInterface
{
    /**
     * @Route("/credit", name="cocorico_balance_dashboard_balance_credit")
     * @Method({"GET", "POST"})
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function creditAction()
    {
        $creditForm = $this->createNewCreditForm(new Credit());

        $creditFormHandler = $this->get('cocorico_balance.form.handler.credit');

        $balanceMovement = $creditFormHandler->process($creditForm);

        if ($balanceMovement instanceof BalanceMovement) {

            if ($balanceMovement->getType() == BalanceMovement::TYPE_CREDIT_CARD) {

                return $this->redirectToRoute(
                    'cocorico_balance_dashboard_balance_credit_card',
                    array(
                        'id' => $balanceMovement->getId(),
                    )
                );

            } elseif ($balanceMovement->getType() == BalanceMovement::TYPE_CREDIT_BANK_WIRE) {

                return $this->redirectToRoute(
                    'cocorico_balance_dashboard_balance_credit_bank_wire',
                    array(
                        'id' => $balanceMovement->getId(),
                    )
                );
            }
        }

        return $this->render(
            'CocoricoBalanceBundle:Dashboard/Balance:credit.html.twig',
            array(
                'form' => $creditForm->createView(),
            )
        );
    }

    /**
     * @param Credit $credit
     * @return \Symfony\Component\Form\Form|\Symfony\Component\Form\FormInterface
     */
    private function createNewCreditForm(Credit $credit)
    {
        return $this->get('form.factory')->createNamed(
            'credit',
            'credit',
            $credit,
            array(
                'action' => $this->generateUrl('cocorico_balance_dashboard_balance_credit'),
                'method' => 'POST',
            )
        );
    }

    /**
     * @Route("/credit/{id}/card", name="cocorico_balance_dashboard_balance_credit_card")
     * @Method({"GET", "POST"})
     * @Security("is_granted('view_credit_card', balanceMovement)")
     *
     * @param BalanceMovement $balanceMovement
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function creditCardAction(BalanceMovement $balanceMovement)
    {
        /** @var User $user */
        $user = $this->getUser();

        $cardRegistration = $this->get('cocorico_mangopay.card_registration_manager')
            ->create($user->getMangopayId());

        $cardForm = $this->createNewCreditCardForm($cardRegistration, $balanceMovement);

        return $this->render(
            'CocoricoBalanceBundle:Dashboard/Balance:credit_card.html.twig',
            array(
                'balanceMovement' => $balanceMovement,
                'form' => $cardForm->createView(),
            )
        );
    }

    /**
     * @param CardRegistration $cardRegistration
     * @param BalanceMovement $balanceMovement
     * @return \Symfony\Component\Form\Form|\Symfony\Component\Form\FormInterface
     */
    private function createNewCreditCardForm(CardRegistration $cardRegistration, BalanceMovement $balanceMovement)
    {
        return $this->get('form.factory')->createNamed(
            '',
            'card',
            $cardRegistration,
            array(
                'method' => 'POST',
                'action' => $cardRegistration->CardRegistrationURL,
                'return_url' => $this->generateUrl(
                    'cocorico_balance_dashboard_balance_credit_card_registration',
                    array(
                        'balanceMovementId' => $balanceMovement->getId(),
                        'cardRegistrationId' => $cardRegistration->Id,
                    ),
                    true
                ),
            )
        );
    }

    /**
     * @Route("/credit/{balanceMovementId}/card/{cardRegistrationId}/registration", name="cocorico_balance_dashboard_balance_credit_card_registration")
     * @Method({"GET"})
     * @ParamConverter("balanceMovement", class="CocoricoBalanceBundle:BalanceMovement", options={"id" = "balanceMovementId"})
     *
     * @param BalanceMovement $balanceMovement
     * @param int $cardRegistrationId
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function creditCardRegistrationAction(BalanceMovement $balanceMovement, $cardRegistrationId)
    {
        $formHandler = $this->get('cocorico_mangopay.form.handler.credit_card');

        try {
            $formHandler->process($cardRegistrationId, $balanceMovement);

        } catch (CreditCardNotValidException $e) {

            $this->addFlashMessage('error', $e->getMessage(), 'cocorico_mangopay', $e->getCode());

            return $this->redirectToRoute(
                'cocorico_balance_dashboard_balance_credit_card',
                array(
                    'id' => $balanceMovement->getId(),
                )
            );

        } catch (CreditCard3DSecureException $e) {

            return new RedirectResponse($e->getRedirectUrl());

        }

        $this->addFlashMessage('success', 'credit_card_success');

        return $this->redirectToRoute('cocorico_balance_dashboard_balance_view');
    }

    /**
     * @Route("/credit/{balanceMovementId}/card/validation", name="cocorico_balance_dashboard_balance_credit_card_validation")
     * @Method({"GET"})
     * @ParamConverter("balanceMovement", class="CocoricoBalanceBundle:BalanceMovement", options={"id" = "balanceMovementId"})
     *
     * @param BalanceMovement $balanceMovement
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function creditCardValidationAction(BalanceMovement $balanceMovement)
    {
        $formHandler = $this->get('cocorico_mangopay.form.handler.credit_card_validation');

        try {
            $formHandler->process($balanceMovement);

        } catch (CreditCardNotValidException $e) {

            $this->addFlashMessage('error', $e->getMessage(), 'cocorico_mangopay', $e->getCode());

            return $this->redirectToRoute(
                'cocorico_balance_dashboard_balance_credit_card',
                array(
                    'id' => $balanceMovement->getId(),
                )
            );

        }

        $this->addFlashMessage('success', 'credit_card_success');

        return $this->redirectToRoute('cocorico_balance_dashboard_balance_view');
    }

    /**
     * @Route("/credit/{id}/bank-wire", name="cocorico_balance_dashboard_balance_credit_bank_wire")
     * @Method({"GET", "POST"})
     * @Security("is_granted('view_credit_bank_wire', balanceMovement)")
     *
     * @param BalanceMovement $balanceMovement
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function creditBankWireAction(BalanceMovement $balanceMovement)
    {
        $payIn = $this->get('labtoo_mangopay.pay_in_bank_wire_manager')
            ->get($balanceMovement->getMangopayId());

        return $this->render(
            'CocoricoBalanceBundle:Dashboard/Balance:credit_bank_wire.html.twig',
            array(
                'balanceMovement' => $balanceMovement,
                'iban' => $payIn->PaymentDetails->BankAccount->Details->IBAN,
                'wire_reference' => $payIn->PaymentDetails->WireReference,
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

    /**
     * @return array
     */
    public static function getTranslationMessages()
    {
        return array(
            new Message('recover_success', 'cocorico_balance'),
            new Message('credit_card_success', 'cocorico_balance'),
        );
    }
}
