<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\ExperimentBundle\Entity;

use Cocorico\CoreBundle\Entity\Booking;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="experiment_booking_answer")
 */
class BookingAnswer
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     *
     * @var int
     */
    private $id;

    /**
     * @ORM\Column(type="text")
     *
     * @var string
     */
    private $number;

    /**
     * @ORM\Column(type="text")
     *
     * @var string
     */
    private $value;

    /**
     * @ORM\ManyToOne(targetEntity="Cocorico\ExperimentBundle\Entity\QuestionChoice")
     *
     * @var QuestionChoice
     */
    private $choice;

    /**
     * @ORM\ManyToOne(targetEntity="Cocorico\CoreBundle\Entity\Booking", inversedBy="answers")
     * @ORM\JoinColumn(nullable=false)
     *
     * @var Booking
     */
    private $booking;

    /**
     * @ORM\ManyToOne(targetEntity="Cocorico\ExperimentBundle\Entity\Question")
     * @ORM\JoinColumn(nullable=false)
     *
     * @var Question
     */
    private $question;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * @param string $number
     */
    public function setNumber($number)
    {
        $this->number = strval($number);
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param string $value
     */
    public function setValue($value)
    {
        $this->value = strval($value);
    }

    /**
     * @return QuestionChoice
     */
    public function getChoice()
    {
        return $this->choice;
    }

    /**
     * @param QuestionChoice $choice
     */
    public function setChoice($choice)
    {
        $this->choice = $choice;
    }

    /**
     * @return Booking
     */
    public function getBooking()
    {
        return $this->booking;
    }

    /**
     * @param Booking $booking
     */
    public function setBooking($booking)
    {
        $this->booking = $booking;
    }

    /**
     * @return Question
     */
    public function getQuestion()
    {
        return $this->question;
    }

    /**
     * @param Question|object $question
     */
    public function setQuestion(Question $question)
    {
        $this->question = $question;
    }

    /**
     * @return string
     */
    public function getSearchableValue()
    {
        if ($this->getQuestion()->getType() == Question::TYPE_CHOICE) {
            foreach ($this->getQuestion()->getChoices() as $choice) {
                if (intval($this->getValue()) == $choice->getId()) {
                    if (is_null($choice->getVariable())) {
                        return sprintf('%d:%s', $this->getQuestion()->getId(), $choice->getId());
                    } else {
                        return sprintf('%d:%s', $this->getQuestion()->getId(), $choice->getVariable());
                    }
                }
            }
        }
        return sprintf('%d:%s', $this->getQuestion()->getId(), $this->getValue());
    }

    /**
     * @return int
     */
    public function getLevel()
    {
        return substr_count($this->number, '.');
    }
}
