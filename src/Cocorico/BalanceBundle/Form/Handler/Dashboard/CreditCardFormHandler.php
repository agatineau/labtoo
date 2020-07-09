<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\BalanceBundle\Form\Handler\Dashboard;

use Cocorico\BalanceBundle\Entity\BalanceMovement;
use Cocorico\BalanceBundle\Exception\CreditCard3DSecureException;
use Cocorico\BalanceBundle\Exception\CreditCardNotValidException;
use Cocorico\BalanceBundle\Exception\CreditCardValidateException;
use Cocorico\BalanceBundle\Manager\CreditManager;
use Cocorico\MangoPayBundle\Model\Manager\CardRegistrationManager;
use Labtoo\MangoPayBundle\Model\Manager\PayInCardManager;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\RouterInterface;

class CreditCardFormHandler
{
    /**
     * @var Session
     */
    private $session;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var Translator
     */
    private $translator;

    /**
     * @var CreditManager
     */
    private $creditManager;

    /**
     * @var CardRegistrationManager
     */
    private $cardRegistrationManager;

    /**
     * @var PayInCardManager
     */
    private $payInCardManager;

    /**
     * @param Session $session
     * @param RequestStack $requestStack
     * @param RouterInterface $router
     * @param Translator $translator
     * @param CreditManager $creditManager
     * @param CardRegistrationManager $cardRegistrationManager
     * @param PayInCardManager $payInCardManager
     */
    public function __construct(
        Session $session,
        RequestStack $requestStack,
        RouterInterface $router,
        Translator $translator,
        CreditManager $creditManager,
        CardRegistrationManager $cardRegistrationManager,
        PayInCardManager $payInCardManager
    ) {
        $this->session = $session;
        $this->request = $requestStack->getCurrentRequest();
        $this->router = $router;
        $this->translator = $translator;
        $this->creditManager = $creditManager;
        $this->cardRegistrationManager = $cardRegistrationManager;
        $this->payInCardManager = $payInCardManager;
    }

    /**
     * @param $cardRegistrationId
     * @param BalanceMovement $balanceMovement
     * @return bool|RedirectResponse
     */
    public function process($cardRegistrationId, BalanceMovement $balanceMovement)
    {
        $data = $this->request->query->get('data');
        $errorCode = $this->request->query->get('errorCode');

        $this->createCard($balanceMovement, $cardRegistrationId, $data, $errorCode);

        $this->createPayInCard($balanceMovement);
    }


    /**
     * @param BalanceMovement $balanceMovement
     * @param $cardRegistrationId
     * @param $data
     * @param $errorCode
     * @return mixed
     * @throws CreditCardNotValidException
     */
    private function createCard(BalanceMovement $balanceMovement, $cardRegistrationId, $data, $errorCode)
    {
        if ($data && !$errorCode) {

            if ($cardId = $this->cardRegistrationManager->update(
                $cardRegistrationId,
                $data,
                $errorCode
            )) {
                $this->creditManager->registerCard($balanceMovement, $cardId);
            } else {
                throw new CreditCardNotValidException();
            }
        } else {
            throw new CreditCardNotValidException();
        }
    }

    /**
     * @param BalanceMovement $balanceMovement
     * @throws CreditCard3DSecureException
     * @throws CreditCardNotValidException
     */
    private function createPayInCard(BalanceMovement $balanceMovement)
    {
        if ($balanceMovement->getMangopayCardId()) {

            $payIn = $this->payInCardManager->create(
                $balanceMovement->getUser()->getMangopayId(),
                $balanceMovement->getUser()->getMangopayWalletId(),
                $balanceMovement->getAmount(),
                $balanceMovement->getMangopayCardId(),
                $this->router->generate(
                    'cocorico_balance_dashboard_balance_credit_card_validation',
                    array(
                        'balanceMovementId' => $balanceMovement->getId(),
                    ),
                    true
                ),
                sprintf('balance_credit_card_%d', $balanceMovement->getId())
            );

            if (
                $payIn->Status === 'SUCCEEDED' &&
                $payIn->PaymentDetails->CardId == $balanceMovement->getMangopayCardId() &&
                $payIn->DebitedFunds->Amount == $balanceMovement->getAmount()
            ) {

                $this->creditManager->validate($balanceMovement);

            } elseif ($payIn->Status === 'CREATED') {

                throw new CreditCard3DSecureException($payIn->ExecutionDetails->SecureModeRedirectURL);
            } else {

                throw new CreditCardNotValidException($payIn->ResultCode);
            }
        } else {

            throw new CreditCardNotValidException();
        }
    }
}
