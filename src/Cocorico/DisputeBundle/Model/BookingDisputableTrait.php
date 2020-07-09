<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\DisputeBundle\Model;

use Cocorico\DisputeBundle\Entity\BookingDefer;
use Doctrine\Common\Collections\ArrayCollection;

trait BookingDisputableTrait
{
    /**
     * @ORM\Column(name="disputed_asker_booking_at", type="datetime", nullable=true)
     *
     * @var \DateTime
     */
    private $disputedAskerBookingAt;

    /**
     * @ORM\OneToMany(targetEntity="Cocorico\DisputeBundle\Entity\BookingDefer", mappedBy="booking", cascade={"persist", "remove"}, orphanRemoval=true)
     *
     * @var ArrayCollection|BookingDefer[]
     */
    private $defers;


    /**
     * @return \DateTime
     */
    public function getDisputedAskerBookingAt()
    {
        return $this->disputedAskerBookingAt;
    }

    /**
     * @param \DateTime $disputedAskerBookingAt
     */
    public function setDisputedAskerBookingAt($disputedAskerBookingAt)
    {
        $this->disputedAskerBookingAt = $disputedAskerBookingAt;
    }

    /**
     * @param BookingDefer $defer
     */
    public function addDefer(BookingDefer $defer)
    {
        $defer->setBooking($this);
        $this->defers[] = $defer;
    }

    /**
     * @param BookingDefer $defer
     */
    public function removeDefer(BookingDefer $defer)
    {
        $this->defers->removeElement($defer);
    }

    /**
     * @return ArrayCollection|BookingDefer[]
     */
    public function getDefers()
    {
        return $this->defers;
    }

    /**
     * @param ArrayCollection|BookingDefer[] $defers
     */
    public function setDefers($defers)
    {
        foreach ($defers as $defer) {
            $defer->setBooking($this);
        }
        $this->defers = $defers;
    }
}
