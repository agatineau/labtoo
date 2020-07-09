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

use Cocorico\BalanceBundle\Exception\RecoverBankAccountException;
use Cocorico\BalanceBundle\Exception\RecoverFormNotSubmitException;
use Cocorico\BalanceBundle\Exception\RecoverInsufficientAmountException;
use Cocorico\BalanceBundle\Exception\RecoverNotCheckException;
use Cocorico\BalanceBundle\Manager\RecoverManager;
use Cocorico\UserBundle\Entity\User;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class RecoverFormHandler
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @var TokenInterface
     */
    private $token;

    /**
     * @var RecoverManager
     */
    private $recoverManager;

    /**
     * @param RequestStack $requestStack
     * @param TokenStorage $tokenStorage
     * @param RecoverManager $recoverManager
     */
    public function __construct(
        RequestStack $requestStack,
        TokenStorage $tokenStorage,
        RecoverManager $recoverManager
    ) {
        $this->request = $requestStack->getCurrentRequest();
        $this->token = $tokenStorage->getToken();
        $this->recoverManager = $recoverManager;
    }

    /**
     * @param Form $form
     * @throws RecoverBankAccountException
     * @throws RecoverInsufficientAmountException
     * @throws RecoverNotCheckException
     * @throws RecoverFormNotSubmitException
     */
    public function process(Form $form)
    {
        /** @var User $user */
        $user = $this->token->getUser();

        if (!$this->recoverManager->isRecoverable($user)) {
            throw new RecoverInsufficientAmountException();
        }

        if (!$this->recoverManager->isChecked($user)) {
            throw new RecoverNotCheckException();
        }

        if (!$this->recoverManager->isBankable($user)) {
            throw new RecoverBankAccountException();
        }

        $form->handleRequest($this->request);

        if (
            !$form->isSubmitted()
            || !$this->request->isMethod('POST')
            || !$form->isValid()
        ) {
            throw new RecoverFormNotSubmitException();
        }

        $this->recoverManager->create($user);
    }
}
