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

use Cocorico\ExperimentBundle\Entity\Experiment;
use Cocorico\ExperimentBundle\Entity\ListingAnswer;
use Doctrine\Common\Collections\ArrayCollection;

trait ListingExperimentableTrait
{

    /**
     * @ORM\ManyToOne(targetEntity="Cocorico\ExperimentBundle\Entity\Experiment", inversedBy="listings")
     *
     * @var Experiment
     */
    private $experiment;

    /**
     * @ORM\OneToMany(targetEntity="Cocorico\ExperimentBundle\Entity\ListingAnswer", mappedBy="listing", cascade={"persist", "remove"}, orphanRemoval=true)
     *
     * @var ArrayCollection|ListingAnswer[]
     */
    private $answers;

    /**
     * @ORM\Column(name="searchable_answer_values", type="simple_array", nullable=true)
     *
     * @var array
     */
    private $searchableAnswerValues;

    /**
     *
     * @ORM\Column(name="old_status", type="boolean", nullable=true)
     *
     * @var boolean
     */
    protected $oldStatus = true;

    /**
     * @param Experiment $experiment
     */
    public function setExperiment($experiment)
    {
        $this->experiment = $experiment;
    }

    /**
     * @return Experiment
     */
    public function getExperiment()
    {
        return $this->experiment;
    }

    /**
     * @param ListingAnswer $answer
     */
    public function addAnswer(ListingAnswer $answer)
    {
        $answer->setListing($this);
        $this->answers[] = $answer;
    }

    /**
     * @param ListingAnswer $answer
     */
    public function removeAnswer(ListingAnswer $answer)
    {
        $this->answers->removeElement($answer);
        $answer->setListing(null);
    }

    /**
     * @return ArrayCollection|ListingAnswer[]
     */
    public function getAnswers()
    {
        return $this->answers;
    }

    /**
     * @param ArrayCollection|ListingAnswer[] $answers
     */
    public function setAnswers($answers)
    {
        foreach ($answers as $answer) {
            $answer->setListing($this);
        }
        $this->answers = $answers;
    }

    /**
     * @return array
     */
    public function getSearchableAnswerValues()
    {
        return $this->searchableAnswerValues;
    }

    /**
     * @param array $searchableAnswerValues
     */
    public function setSearchableAnswerValues($searchableAnswerValues)
    {
        $this->searchableAnswerValues = $searchableAnswerValues;
    }

    /**
     * Set oldStatus
     *
     * @param  integer $oldStatus
     * @return Listing
     */
    public function setOldStatus($oldStatus)
    {

        $this->oldStatus = $oldStatus;

        return $this;
    }

    /**
     * Get oldStatus
     *
     * @return integer
     */
    public function getOldStatus()
    {
        return $this->oldStatus;
    }

    /**
     * Get Status Text
     *
     * @return string
     */
    public function getOldStatusText()
    {
        if($this->oldStatus === false){
            return 'entity.listing.experiment_deactived_status';
        }else{
            return 'entity.listing.experiment_actived_status';
        }
    }
}
