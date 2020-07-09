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

use Cocorico\BalanceBundle\Model\Credit;
use Cocorico\BalanceBundle\Validator\Constraints\RecoverCheckConstraint;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class CreditValidator extends ConstraintValidator
{
    /**
     * @param Credit $credit
     * @param RecoverCheckConstraint|Constraint $constraint
     */
    public function validate($credit, Constraint $constraint)
    {
        if (!($credit->getBalanceMovement()->getType())) {
            $this->context
                ->buildViolation($constraint::$messageType)
                ->setTranslationDomain('cocorico_balance')
                ->atPath('balanceMovement.type')
                ->addViolation();
        }

        if (!($credit->getBalanceMovement()->getAmount() > 0)) {
            $this->context
                ->buildViolation($constraint::$messageAmount)
                ->setTranslationDomain('cocorico_balance')
                ->atPath('balanceMovement.amount')
                ->addViolation();
        }
    }
}
