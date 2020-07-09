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

trait BookingBalanceableTrait
{
    /**
     * @ORM\OneToMany(targetEntity="Cocorico\BalanceBundle\Entity\BalanceMovement", mappedBy="booking")
     *
     * @var ArrayCollection|BalanceMovement[]
     */
    private $balanceMovements;


    /**
     * @param BalanceMovement $balanceMovement
     */
    public function addBalanceMovement(BalanceMovement $balanceMovement)
    {
        $balanceMovement->setBooking($this);
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
            $balanceMovement->setBooking($this);
        }
        $this->balanceMovements = $balanceMovements;
    }

    /**
     * @return BalanceMovement|null
     */
    public function getBalanceMovementCredit()
    {
        foreach ($this->balanceMovements as $balanceMovement) {
            if ($balanceMovement->getType() == BalanceMovement::TYPE_CREDIT) {
                return $balanceMovement;
            }
        }

        return null;
    }

    /**
     * @return BalanceMovement|null
     */
    public function getBalanceMovementDebit()
    {
        foreach ($this->balanceMovements as $balanceMovement) {
            if ($balanceMovement->getType() == BalanceMovement::TYPE_DEBIT) {
                return $balanceMovement;
            }
        }

        return null;
    }

    /**
     * @return BalanceMovement|null
     */
    public function getBalanceMovementRefund()
    {
        foreach ($this->balanceMovements as $balanceMovement) {
            if ($balanceMovement->getType() == BalanceMovement::TYPE_REFUND) {
                return $balanceMovement;
            }
        }

        return null;
    }
}
