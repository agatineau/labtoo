<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\ExperimentBundle\Model;

use Cocorico\ExperimentBundle\Entity\BookingAnswer;
use Doctrine\Common\Collections\ArrayCollection;

trait BookingExperimentableTrait
{
    /**
     * @ORM\OneToMany(targetEntity="Cocorico\ExperimentBundle\Entity\BookingAnswer", mappedBy="booking", cascade={"persist", "remove"}, orphanRemoval=true)
     *
     * @var ArrayCollection|BookingAnswer[]
     */
    private $answers;

    /**
     * @param BookingAnswer $answer
     */
    public function addAnswer(BookingAnswer $answer)
    {
        $answer->setBooking($this);
        $this->answers[] = $answer;
    }

    /**
     * @param BookingAnswer $answer
     */
    public function removeAnswer(BookingAnswer $answer)
    {
        $this->answers->removeElement($answer);
        $answer->setBooking(null);
    }

    /**
     * @return ArrayCollection|BookingAnswer[]
     */
    public function getAnswers()
    {
        return $this->answers;
    }

    /**
     * @param ArrayCollection|BookingAnswer[] $answers
     */
    public function setAnswers($answers)
    {
        foreach ($answers as $answer) {
            $answer->setBooking($this);
        }
        $this->answers = $answers;
    }
}
