<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\BalanceBundle\Model;

use Cocorico\BalanceBundle\Entity\BalanceMovement;
use Cocorico\BalanceBundle\Validator\Constraints as CocoricoBalanceAssert;

/**
 * @CocoricoBalanceAssert\CreditConstraint()
 */
class Credit
{
    /**
     * @var BalanceMovement
     */
    private $balanceMovement;

    public function __construct()
    {
        $this->balanceMovement = new BalanceMovement();
    }

    /**
     * @return BalanceMovement
     */
    public function getBalanceMovement()
    {
        return $this->balanceMovement;
    }

    /**
     * @param BalanceMovement $balanceMovement
     */
    public function setBalanceMovement($balanceMovement)
    {
        $this->balanceMovement = $balanceMovement;
    }
}
