<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\BalanceBundle\Security;

use Cocorico\BalanceBundle\Entity\BalanceMovement;
use Cocorico\UserBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class BalanceMovementVoter implements VoterInterface
{
    const VIEW_CREDIT_CARD = 'view_credit_card';
    const VIEW_CREDIT_BANK_WIRE = 'view_credit_bank_wire';

    public function supportsAttribute($attribute)
    {
        return in_array(
            $attribute,
            array(
                self::VIEW_CREDIT_CARD,
                self::VIEW_CREDIT_BANK_WIRE,
            )
        );
    }

    public function supportsClass($class)
    {
        $supportedClass = 'Cocorico\BalanceBundle\Entity\BalanceMovement';

        return $supportedClass === $class || is_subclass_of($class, $supportedClass);
    }

    /**
     * @param TokenInterface $token
     * @param null|BalanceMovement $balanceMovement
     * @param array $attributes
     * @return int
     */
    public function vote(TokenInterface $token, $balanceMovement, array $attributes)
    {
        if (!$this->supportsClass(get_class($balanceMovement))) {
            return VoterInterface::ACCESS_ABSTAIN;
        }

        if (1 !== count($attributes)) {
            throw new \InvalidArgumentException(
                'Only one attribute is allowed for VIEW_CREDIT_CARD or VIEW_CREDIT_BANK_WIRE'
            );
        }

        $attribute = $attributes[0];

        if (!$this->supportsAttribute($attribute)) {
            return VoterInterface::ACCESS_ABSTAIN;
        }

        /** @var User $user */
        $user = $token->getUser();

        if (!$user instanceof UserInterface) {
            return VoterInterface::ACCESS_DENIED;
        }

        if ($user !== $balanceMovement->getUser()) {
            return VoterInterface::ACCESS_DENIED;
        }

        if ($attribute == self::VIEW_CREDIT_CARD) {
            if ($balanceMovement->getType() === BalanceMovement::TYPE_CREDIT_CARD) {
                return VoterInterface::ACCESS_GRANTED;
            }
        } elseif ($attribute == self::VIEW_CREDIT_BANK_WIRE) {
            if ($balanceMovement->getType() === BalanceMovement::TYPE_CREDIT_BANK_WIRE) {
                return VoterInterface::ACCESS_GRANTED;
            }
        }

        return VoterInterface::ACCESS_DENIED;
    }
}
