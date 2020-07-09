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
use Cocorico\BalanceBundle\Exception\CreditCardNotValidException;
use Cocorico\BalanceBundle\Exception\CreditCardValidateException;
use Cocorico\BalanceBundle\Manager\CreditManager;
use Labtoo\MangoPayBundle\Model\Manager\PayInCardManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Translation\Translator;

class CreditCardValidationFormHandler
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
     * @var PayInCardManager
     */
    private $payInCardManager;

    /**
     * @param Session $session
     * @param RequestStack $requestStack
     * @param RouterInterface $router
     * @param Translator $translator
     * @param CreditManager $creditManager
     * @param PayInCardManager $payInCardManager
     */
    public function __construct(
        Session $session,
        RequestStack $requestStack,
        RouterInterface $router,
        Translator $translator,
        CreditManager $creditManager,
        PayInCardManager $payInCardManager
    ) {
        $this->session = $session;
        $this->request = $requestStack->getCurrentRequest();
        $this->router = $router;
        $this->translator = $translator;
        $this->creditManager = $creditManager;
        $this->payInCardManager = $payInCardManager;
    }

    /**
     * @param BalanceMovement $balanceMovement
     * @throws CreditCardNotValidException
     */
    public function process(BalanceMovement $balanceMovement)
    {
        $transactionId = $this->request->query->get('transactionId');

        if ($balanceMovement->getMangopayCardId()) {

            $payIn = $this->payInCardManager->get($transactionId);

            if (
                $payIn->Status === 'SUCCEEDED' &&
                $payIn->PaymentDetails->CardId == $balanceMovement->getMangopayCardId() &&
                $payIn->DebitedFunds->Amount == $balanceMovement->getAmount()
            ) {

                $this->creditManager->validate($balanceMovement);
            } else {

                throw new CreditCardNotValidException($payIn->ResultCode);
            }
        } else {

            throw new CreditCardNotValidException();
        }
    }
}
