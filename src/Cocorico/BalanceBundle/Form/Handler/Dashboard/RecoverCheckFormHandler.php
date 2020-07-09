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

use Cocorico\BalanceBundle\Exception\RecoverCheckNotValidException;
use Cocorico\BalanceBundle\Manager\RecoverManager;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class RecoverCheckFormHandler
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
     * @throws \Exception
     */
    public function process(Form $form)
    {
        $form->handleRequest($this->request);

        if (
            !$form->isSubmitted()
            || !$this->request->isMethod('POST')
            || !$form->isValid()
        ) {
            throw new RecoverCheckNotValidException();
        }

        $this->recoverManager->check($this->token->getUser());
    }

    public function isRequired()
    {
        return !$this->recoverManager->isChecked($this->token->getUser());
    }
}
