<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\BalanceBundle\Validator;

use Cocorico\BalanceBundle\Model\RecoverCheck;
use Cocorico\BalanceBundle\Validator\Constraints\RecoverCheckConstraint;
use Cocorico\UserBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Encoder\EncoderFactory;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class RecoverCheckValidator extends ConstraintValidator
{
    /**
     * @var EncoderFactory
     */
    private $encoderFactory;

    /**
     * @var TokenStorage
     */
    private $tokenStorage;

    /**
     * @param EncoderFactory $encoderFactory
     * @param TokenStorage $tokenStorage
     */
    public function __construct(
        EncoderFactory $encoderFactory,
        TokenStorage $tokenStorage
    ) {
        $this->encoderFactory = $encoderFactory;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @param RecoverCheck $recoverCheck
     * @param RecoverCheckConstraint|Constraint $constraint
     */
    public function validate($recoverCheck, Constraint $constraint)
    {
        /** @var User $user */
        $user = $this->tokenStorage->getToken()->getUser();
        $encoder = $this->encoderFactory->getEncoder($user);

        if (!$encoder->isPasswordValid(
            $user->getPassword(),
            $recoverCheck->getPassword(),
            $user->getSalt()
        )) {
            $this->context
                ->buildViolation($constraint::$messagePassword)
                ->setTranslationDomain('cocorico_balance')
                ->atPath('password')
                ->addViolation();
        }
    }
}
