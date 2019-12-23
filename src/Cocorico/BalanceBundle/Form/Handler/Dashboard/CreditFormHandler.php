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

use Cocorico\BalanceBundle\Manager\CreditManager;
use Cocorico\BalanceBundle\Model\Credit;
use Cocorico\UserBundle\Entity\User;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class CreditFormHandler
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
     * @var CreditManager
     */
    private $creditManager;

    /**
     * @param RequestStack $requestStack
     * @param TokenStorage $tokenStorage
     * @param CreditManager $creditManager
     */
    public function __construct(
        RequestStack $requestStack,
        TokenStorage $tokenStorage,
        CreditManager $creditManager
    ) {
        $this->request = $requestStack->getCurrentRequest();
        $this->token = $tokenStorage->getToken();
        $this->creditManager = $creditManager;
    }

    /**
     * @param Form $form
     * @return \Cocorico\BalanceBundle\Entity\BalanceMovement|null
     */
    public function process(Form $form)
    {
        $form->handleRequest($this->request);

        if (!$form->isSubmitted() || !$this->request->isMethod('POST') || !$form->isValid()) {
            return null;
        }

        /** @var User $user */
        $user = $this->token->getUser();

        /** @var Credit $credit */
        $credit = $form->getData();

        return $this->creditManager->register($user, $credit);
    }
}
