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
use Cocorico\ExperimentBundle\Entity\Question;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Translation\TranslationContainerInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class ListingSearchAnswer implements TranslationContainerInterface
{
    /**
     * @var Question
     */
    private $question;

    /**
     * @ORM\Column(type="text", nullable=false)
     *
     * @var string
     */
    private $value;    

    /**
     * @var string
     */
    private $parentChoiceId;

    /**
     * @var ArrayCollection
     */
    private $answers;

    /**
     * @var ListingSearchAnswer
     */
    private $parent;    

    public function __construct()
    {
        $this->answers = new ArrayCollection();        
    }

    /**
     * @return Question
     */
    public function getQuestion()
    {
        return $this->question;
    }

    /**
     * @param Question $question
     */
    public function setQuestion(Question $question)
    {
        $this->question = $question;
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
        $this->value = $value;
    }    

    /**
     * @return string
     */
    public function getParentChoiceId()
    {
        return $this->parentChoiceId;
    }

    /**
     * @param string $parentChoiceId
     */
    public function setParentChoiceId($parentChoiceId)
    {
        $this->parentChoiceId = $parentChoiceId;
    }

    /**
     * @param  ListingSearchAnswer $answer
     */
    public function addAnswer(ListingSearchAnswer $answer)
    {
        $answer->setParent($this);
        $this->answers[] = $answer;
    }

    /**
     * @param ListingSearchAnswer $answer
     */
    public function removeAnswer(ListingSearchAnswer $answer)
    {
        $this->answers->removeElement($answer);
    }

    /**
     * @return ArrayCollection|ListingSearchAnswer[]
     */
    public function getAnswers()
    {
        if ($this->answers->isEmpty()) {
            $questionnaire = $this->question->getExperiment()->getQuestionnaire();
            foreach ($questionnaire->getAllEntries() as $entry) {
                if ($this->question->getUid() == $entry->getDependencyUid()) {
                    if (is_null($entry->getQuestion())) continue;
                    $listingSearchAnswer = new ListingSearchAnswer();
                    $listingSearchAnswer->setQuestion($entry->getQuestion());
                    $this->addAnswer($listingSearchAnswer);
                }
                if ($this->question->getType() == Question::TYPE_CHOICE) {                    
                    foreach ($this->question->getChoices() as $choice) {
                        if ($choice->getUid() == $entry->getDependencyUid()) {
                            if (is_null($entry->getQuestion())) continue;
                            $listingSearchAnswer = new ListingSearchAnswer();
                            $listingSearchAnswer->setQuestion($entry->getQuestion());
                            $listingSearchAnswer->setParentChoiceId($choice->getId());
                            $this->addAnswer($listingSearchAnswer);
                        }
                    }
                }
            }
        }
        return $this->answers;
    }

    /**
     * @param ArrayCollection|ListingSearchAnswer[] $answers
     */
    public function setAnswers($answers)
    {
        $this->answers = $answers;
    }

    /**
     * @return ListingSearchAnswer
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @param ListingSearchAnswer $parent
     */
    public function setParent($parent)
    {
        $this->parent = $parent;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string)$this->getQuestion()->translate()->getTitleAsker();
    }

    /**
     * @Assert\Callback
     * @param ExecutionContextInterface $context
     */
    public function validate(ExecutionContextInterface $context)
    {
        if ($this->getQuestion()->getType() == Question::TYPE_CHOICE) {
            foreach ($this->getQuestion()->getChoices() as $choice) {
                if (!$choice->isProhibitive()) continue;
                if ($this->getValue() == $choice->getId()) {
                    $context->buildViolation($choice->translate()->getExplanation())
                        ->atPath('value')->addViolation();
                    return;
                }
            }
        }

        if(!$this->getParent() && !$this->getValue()) {
            $context->buildViolation('listing_answer.error.must_answer_question')
                ->setTranslationDomain('cocorico_experiment')
                ->atPath('value')->addViolation();
            return;
        }

        if ($this->getParent()) {

            if ($this->getParentChoiceId()) {
                if ($this->getParent()->getValue() == $this->getParentChoiceId()) {
                    if (!$this->getValue()) {
                        $context->buildViolation('listing_answer.error.parent_must_answer_sub_question')
                            ->setTranslationDomain('cocorico_experiment')
                            ->atPath('value')->addViolation();
                        return;
                    }
                } elseif ($this->getValue()) {
                    $context->buildViolation('listing_answer.error.parent_must_not_answer_sub_question')
                        ->setTranslationDomain('cocorico_experiment')
                        ->atPath('value')->addViolation();
                    return;
                }
            } elseif ($this->getParent()->getValue() && !$this->getValue()) {
                $context->buildViolation('listing_answer.error.must_answer_sub_question')
                    ->setTranslationDomain('cocorico_experiment')
                    ->atPath('value')->addViolation();
                return;
            }
        }
    }

    /**
     * @param string $number
     * @return ArrayCollection|BookingAnswer[]
     */
    public function getBookingAnswers($number)
    {
        
        $bookingAnswers = new ArrayCollection();

        if (!$this->getValue()) return $bookingAnswers;

        $bookingAnswer = new BookingAnswer();
        $bookingAnswer->setQuestion($this->getQuestion());
        $bookingAnswer->setValue($this->getValue());
        $bookingAnswer->setNumber($number);
        if ($this->getQuestion()->getType() == Question::TYPE_CHOICE) {
            foreach ($this->getQuestion()->getChoices() as $key => $choice) {
                if($this->getValue() == $choice->getId()) {
                    $bookingAnswer->setChoice($choice);
                }
            }   
        }        
        $bookingAnswers->add($bookingAnswer);

        $number2 = 1;
        foreach ($this->getAnswers() as $answer) {
            $number3 = sprintf('%s.%d', $number, $number2);
            foreach ($answer->getBookingAnswers($number3) as $bookingAnswer) {
                $bookingAnswers->add($bookingAnswer);
            }
            $number2++;
        }
        return $bookingAnswers;
    }

    /**
     * @return array
     */
    public static function getTranslationMessages()
    {
        return array(
            new Message('listing_answer.error.must_answer_question', 'cocorico_experiment'),
            new Message('listing_answer.error.must_answer_sub_question', 'cocorico_experiment'),
            new Message('listing_answer.error.parent_must_answer_sub_question', 'cocorico_experiment'),
            new Message('listing_answer.error.parent_must_not_answer_sub_question', 'cocorico_experiment')
        );
    }


}
