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

use Cocorico\CoreBundle\Entity\Listing;
use Cocorico\CoreBundle\Entity\ListingCategory;
use Cocorico\ExperimentBundle\Entity\BookingAnswer;
use Cocorico\ExperimentBundle\Entity\Experiment;
use Cocorico\ExperimentBundle\Entity\Question;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Validator\Constraints as Assert;

class ListingSearchSession
{
    /**
     * @Assert\NotBlank()
     *
     * @var ListingCategory
     */
    private $listingCategory;

    /**
     * @Assert\NotBlank()
     *
     * @var Experiment
     */
    private $experiment;

    /**
     * @var ArrayCollection|ListingSearchAnswer[]
     */
    private $answers;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var EntityManager
     */
    private $entityManager;

    protected $feeAsAsker;

    /**
     * @param Session $session
     * @param EntityManager $entityManager
     * @param float $feeAsAsker
     */
    public function __construct(Session $session, EntityManager $entityManager,$feeAsAsker)
    {
        $this->session = $session;
        $this->entityManager = $entityManager;
        $this->feeAsAsker = $feeAsAsker;

        if ($this->session->has('listing_search_session')) {
            /** @var ListingSearchSession $listingSearchSession */
            $listingSearchSession = $session->get('listing_search_session');

            $this->setListingCategory($this->entityManager->find(
                'CocoricoCoreBundle:ListingCategory',
                $listingSearchSession->getListingCategory()->getId()
            ));

            $this->setExperiment($this->entityManager->find(
                'CocoricoExperimentBundle:Experiment',
                $listingSearchSession->getExperiment()->getId()
            ));

            $this->setAnswers($listingSearchSession->getAnswers());
            return;
        }

        $this->reset();
    }

    public function reset()
    {
        $this->listingCategory = null;
        $this->experiment = null;
        $this->answers = new ArrayCollection();
        $this->session->remove('listing_search_session');
    }

    public function save()
    {
        $this->session->set('listing_search_session', $this);
    }

    /**
     * @param Listing|null $listing
     * @return bool
     */
    public function isValid(Listing $listing = null)
    {
        if (!$this->session->has('listing_search_session')) {
            return false;
        }
        if (!is_null($listing) && $listing->getExperiment()->getId() !== $this->getExperiment()->getId()) {
            return false;
        }
        return true;
    }

    /**
     * @return ListingCategory
     */
    public function getListingCategory()
    {
        return $this->listingCategory;
    }

    /**
     * @param ListingCategory $listingCategory
     */
    public function setListingCategory($listingCategory)
    {
        $this->listingCategory = $listingCategory;
    }

    /**
     * @return Experiment
     */
    public function getExperiment()
    {
        return $this->experiment;
    }

    /**
     * @param Experiment $experiment
     */
    public function setExperiment($experiment)
    {
        $this->experiment = $experiment;
    }

    /**
     * @param  ListingSearchAnswer $answer
     */
    public function addAnswer(ListingSearchAnswer $answer)
    {
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
     * @return ArrayCollection|BookingAnswer[]
     */
    public function getBookingAnswers()
    {
        $bookingAnswers = new ArrayCollection();
        $number = 1;
        foreach ($this->getAnswers() as $listingSearchAnswer) {
            foreach ($listingSearchAnswer->getBookingAnswers($number) as $bookingAnswer) {
                if ($bookingAnswer->getChoice()) {
                    $bookingAnswer->setChoice($this->entityManager->find(
                        'CocoricoExperimentBundle:QuestionChoice',
                        $bookingAnswer->getChoice()->getId()
                    ));
                }
                $bookingAnswer->setQuestion($this->entityManager->find(
                    'CocoricoExperimentBundle:Question',
                    $bookingAnswer->getQuestion()->getId()
                ));
                $bookingAnswers->add($bookingAnswer);
            }
            $number++;
        }
        return $bookingAnswers;
    }

    /**
     * @return int
     */
    public function getBookingAmount()
    {
        $formula = $this->getExperiment()->getFormula();
        $matches = array();
        if(preg_match_all('/\[(.*?)\]/', $formula, $matches)) {
            foreach ($matches[1] as $i => $uid) {
                $value = 1;
                foreach ($this->getBookingAnswers() as $answer) {
                    $question = $answer->getQuestion();
                    if ($question->getUid() == $uid) {
                        if ($question->getType() == Question::TYPE_CHOICE) {
                            foreach ($question->getChoices() as $choice) {
                                if (intval($answer->getValue()) == $choice->getId()) {
                                    if (is_null($choice->getVariable())) {
                                        $value = is_numeric($choice->getText()) ? $choice->getText() : 1;
                                    } else {
                                        $value = is_numeric($choice->getVariable()) ? $choice->getVariable() : 1;
                                    }
                                }
                            }
                        } else {
                            $value = is_numeric($answer->getValue()) ? $answer->getValue() : 1;
                        }
                    }
                }
                $formula = str_replace($matches[0][$i], $value, $formula);
            }
        }
        $amount = 0;
        if ($formula) eval(sprintf("\$amount=%s;", $formula));
        return $amount;
    }

    /**
     * @return float
     */
    public function getBookingAmountDecimal()
    {
        return $this->getBookingAmount() / 100;
    }


    /**
     * Remove some Object properties while serialisation
     *
     * @return array
     */
    public function __sleep()
    {
        return array_diff(array_keys(get_object_vars($this)), array('session', 'entityManager'));
    }

    /**
     * Amount excluding VAT
     *
     * @param float $vatRate
     *
     * @return int
     */
    public function getAmountExcludingVAT($vatRate,$user)
    {
        return ($this->getAmountFeeAsAsker($user) + $this->getBookingAmount()) / (1 + $vatRate);
    }

    /**
     * Amount Decimal excluding VAT
     *
     * @param float $vatRate
     *
     * @return int
     */
    public function getAmountExcludingVATDecimal($vatRate,$user)
    {
        return $this->getAmountExcludingVAT($vatRate,$user) / 100;
    }

    /**
     * Get amount fee  decimal
     *
     * @return float
     */
    public function getAmountFeeAsAskerDecimal($user)
    {
        return $this->getAmountFeeAsAsker($user) / 100;
    }

    /**
     * @return int
     */
    public function getAmountFeeAsAsker($user)
    {
        //Fees computation Asker
        $amountFeeAsAsker = $this->feeAsAsker * 100;
        //If user has a custom fee defined we use it
        if ($user) {
            $feeAsAsker = $user->getFeeAsAsker();
            if ($feeAsAsker || $feeAsAsker === 0) {
                $amountFeeAsAsker = ($feeAsAsker);
            }
        }
        $amountFeeAsAsker = ($amountFeeAsAsker / 100) * $this->getBookingAmount();
        return $amountFeeAsAsker;
    }
}
