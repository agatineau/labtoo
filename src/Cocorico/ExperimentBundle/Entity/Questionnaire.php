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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="experiment_questionnaire")
 */
class Questionnaire
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
     * @ORM\Column(name="question_root_uid", type="integer", nullable=true)
     *
     * @var int
     */
    private $questionRootUid;

    /**
     * @ORM\OneToOne(targetEntity="Experiment", mappedBy="questionnaire")
     *
     * @var Experiment
     */
    private $experiment;

    /**
     * @ORM\OneToMany(targetEntity="QuestionnaireEntry", mappedBy="questionnaire", cascade={"persist", "refresh", "remove"}, orphanRemoval=true)
     *
     * @var ArrayCollection|QuestionnaireEntry[]
     */
    private $entries;

    public function __construct()
    {
        $this->entries = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getQuestionRootUid()
    {
        return $this->questionRootUid;
    }

    /**
     * @param int $questionRootUid
     */
    public function setQuestionRootUid($questionRootUid)
    {
        $this->questionRootUid = $questionRootUid;
    }

    /**
     * @return Question|null
     */
    public function getQuestionRoot()
    {
        foreach ($this->experiment->getQuestions() as $question) {
            if ($question->getUid() == $this->questionRootUid) {
                return $question;
            }
        }
        return null;
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
    public function setExperiment(Experiment $experiment)
    {
        $this->experiment = $experiment;
    }

    /**
     * @param QuestionnaireEntry $entry
     * @return $this
     */
    public function addEntry(QuestionnaireEntry $entry)
    {
        $entry->setQuestionnaire($this);
        $this->entries[] = $entry;

        return $this;
    }

    /**
     * @param QuestionnaireEntry $entry
     */
    public function removeEntry(QuestionnaireEntry $entry)
    {
        $this->entries->removeElement($entry);
    }

    /**
     * @return ArrayCollection|QuestionnaireEntry[]
     */
    public function getEntries()
    {
        return $this->entries;
    }

    /**
     * @return ArrayCollection|QuestionnaireEntry[]
     */
    public function getMainEntries()
    {
        $entries = new ArrayCollection();
        if ($this->getQuestionRootUid()) {
            $rootEntry = new QuestionnaireEntry();
            $rootEntry->setQuestionUid($this->getQuestionRootUid());
            $entries->add($rootEntry);
        }
        if ($this->getEntries()->count()) {
            foreach ($this->getEntries() as $entry) {
                if (is_null($entry->getDependencyUid())) {
                    $entries->add($entry);
                }
            }
        }
        return $entries;
    }

    /**
     * @return ArrayCollection|QuestionnaireEntry[]
     */
    public function getAllEntries()
    {
        $entries = new ArrayCollection();
        if ($this->getQuestionRootUid()) {
            $rootEntry = new QuestionnaireEntry();
            $rootEntry->setQuestionUid($this->getQuestionRootUid());
            $rootEntry->setQuestionnaire($this);
            $entries->add($rootEntry);
        }
        if ($this->getEntries()->count()) {
            foreach ($this->getEntries() as $entry) {
                $entries->add($entry);
            }
        }
        return $entries;
    }

    /**
     * @param ArrayCollection|QuestionnaireEntry[] $entries
     */
    public function setEntries(ArrayCollection $entries)
    {
        foreach ($entries as $entry) {
            $entry->setQuestionnaire($this);
        }
        $this->entries = $entries;
    }

    public function __clone()
    {
        $this->id = null;

        $entries = $this->getEntries();
        $this->entries = new ArrayCollection();
        foreach ($entries as $entry) {
            $this->addEntry(clone $entry);
        }
    }
}
