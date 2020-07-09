<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\BalanceBundle\Entity\Traits;

use Cocorico\BalanceBundle\Entity\BalanceMovement;
use Doctrine\Common\Collections\ArrayCollection;

trait UserBalanceableTrait
{
    /**
     * @ORM\Column(name="amount_balance", type="decimal", precision=8, scale=0, nullable=true)
     *
     * @var int
     */
    private $amountBalance;

    /**
     * @ORM\Column(name="last_balance_recover_checked_at", type="datetime", nullable=true)
     *
     * @var \DateTime
     */
    private $lastBalanceRecoverCheckedAt;

    /**
     * @ORM\OneToMany(targetEntity="Cocorico\BalanceBundle\Entity\BalanceMovement", mappedBy="user", cascade={"persist"})
     *
     * @var ArrayCollection|BalanceMovement[]
     */
    private $balanceMovements;

    /**
     * @return int
     */
    public function getAmountBalance()
    {
        return is_null($this->amountBalance) ? 0 : $this->amountBalance;
    }

    /**
     * @return int
     */
    public function getAmountBalanceDecimal()
    {
        return $this->amountBalance / 100;
    }

    /**
     * @param int $amountBalance
     */
    public function setAmountBalance($amountBalance)
    {
        if ($amountBalance < 0) {
            throw new \InvalidArgumentException(
                sprintf('Invalid value for user.amount_balance: %s', $amountBalance)
            );
        }
        $this->amountBalance = $amountBalance;
    }

    /**
     * @return \DateTime
     */
    public function getLastBalanceRecoverCheckedAt()
    {
        return $this->lastBalanceRecoverCheckedAt;
    }

    /**
     * @param \DateTime $lastBalanceRecoverCheckedAt
     */
    public function setLastBalanceRecoverCheckedAt($lastBalanceRecoverCheckedAt)
    {
        $this->lastBalanceRecoverCheckedAt = $lastBalanceRecoverCheckedAt;
    }

    /**
     * @param BalanceMovement $balanceMovement
     */
    public function addBalanceMovement(BalanceMovement $balanceMovement)
    {
        $balanceMovement->setUser($this);
        $this->balanceMovements[] = $balanceMovement;
    }

    /**
     * @param BalanceMovement $balanceMovement
     */
    public function removeBalanceMovement(BalanceMovement $balanceMovement)
    {
        $this->balanceMovements->removeElement($balanceMovement);
    }

    /**
     * @return ArrayCollection|BalanceMovement[]
     */
    public function getBalanceMovements()
    {
        return $this->balanceMovements;
    }

    /**
     * @param ArrayCollection|BalanceMovement[] $balanceMovements
     */
    public function setBalanceMovements($balanceMovements)
    {
        foreach ($balanceMovements as $balanceMovement) {
            $balanceMovement->setUser($this);
        }
        $this->balanceMovements = $balanceMovements;
    }
}
