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

use Cocorico\CoreBundle\Entity\ListingCategory;
use Cocorico\ExperimentBundle\Validator\Constraints as CocoricoExperimentAssert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;


/**
 * @CocoricoExperimentAssert\Experiment()
 *
 * @ORM\Entity(repositoryClass="Cocorico\ExperimentBundle\Repository\ExperimentRepository")
 * @ORM\Table(name="experiment")
 */
class Experiment
{
    use ORMBehaviors\Translatable\Translatable;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     *
     * @var int
     */
    private $id;

    /**
     * @ORM\Column(name="created_at", type="datetime", nullable=true)
     *
     * @var \DateTime
     */
    private $createdAt;

    /**
     * @ORM\Column(name="updated_at", type="datetime", nullable=true)
     *
     * @var \DateTime
     */
    private $updatedAt;

    /**
     * @ORM\Column(name="archived_at", type="datetime", nullable=true)
     *
     * @var \DateTime
     */
    private $archivedAt;

    /**
     * @ORM\Column(name="published_at", type="datetime", nullable=true)
     *
     * @var \DateTime
     */
    private $publishedAt;

    /**
     * @ORM\Column(name="next_uid", type="integer")
     *
     * @var int
     */
    private $nextUid;

    /**
     * @ORM\Column(type="string", nullable=true)
     *
     * @var string
     */
    private $formula;

    /**
     * @ORM\OneToOne(targetEntity="ExperimentImage", inversedBy="experiment", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\JoinColumn(onDelete="CASCADE")
     *
     * @var ExperimentImage
     **/
    private $image;

    /**
     * @ORM\OneToOne(targetEntity="Questionnaire", inversedBy="experiment", cascade={"persist", "remove"})
     *
     * @var Questionnaire
     **/
    private $questionnaire;

    /**
     * @ORM\ManyToOne(targetEntity="Cocorico\CoreBundle\Entity\ListingCategory", inversedBy="experiments")
     * @ORM\JoinColumn(nullable=false)
     *
     * @var ListingCategory
     */
    private $category;

    /**
     * @ORM\OneToMany(targetEntity="Question", mappedBy="experiment", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\JoinColumn(onDelete="CASCADE")
     *
     * @var ArrayCollection|Question[]
     */
    private $questions;

    /**
     * @ORM\OneToMany(targetEntity="Cocorico\CoreBundle\Entity\Listing", mappedBy="experiment")
     *
     * @var ArrayCollection|listing[]
     */
    private $listings;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->nextUid = 1;
        $this->image = new ExperimentImage();
        $this->questionnaire = new Questionnaire();
        $this->questions = new ArrayCollection();
        $this->listings = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $createdAt
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param \DateTime $updatedAt
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * @return \DateTime
     */
    public function getArchivedAt()
    {
        return $this->archivedAt;
    }

    /**
     * @param \DateTime $archivedAt
     */
    public function setArchivedAt(\DateTime $archivedAt)
    {
        $this->archivedAt = $archivedAt;
    }

    /**
     * @return \DateTime
     */
    public function getPublishedAt()
    {
        return $this->publishedAt;
    }

    /**
     * @param \DateTime $publishedAt
     */
    public function setPublishedAt($publishedAt)
    {
        $this->publishedAt = $publishedAt;
    }

    /**
     * @return bool
     */
    public function isPublished()
    {
        return !is_null($this->publishedAt) && is_null($this->archivedAt);
    }

    /**
     * @param bool $published
     */
    public function setPublished($published)
    {
        $this->publishedAt = ($published ? new \DateTime() : null);
    }

    /**
     * @return int
     */
    public function getNextUid()
    {
        return $this->nextUid;
    }

    /**
     * @param int $nextUid
     */
    public function setNextUid($nextUid)
    {
        $this->nextUid = $nextUid;
    }

    /**
     * @return string
     */
    public function getFormula()
    {
        return $this->formula;
    }

    /**
     * @param string $formula
     */
    public function setFormula($formula)
    {
        $this->formula = $formula;
    }

    /**
     * @return ExperimentImage
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * @param ExperimentImage $image
     */
    public function setImage(ExperimentImage $image)
    {
        $image->setExperiment($this);
        $this->image = $image;
    }

    /**
     * @return Questionnaire
     */
    public function getQuestionnaire()
    {
        return $this->questionnaire;
    }

    /**
     * @return Questionnaire
     */
    public function getQuestionnaireCopy()
    {
        return clone $this->questionnaire;
    }

    /**
     * @param Questionnaire $questionnaire
     */
    public function setQuestionnaire(Questionnaire $questionnaire)
    {
        $questionnaire->setExperiment($this);
        $this->questionnaire = $questionnaire;
    }

    /**
     * @param Questionnaire $questionnaire
     */
    public function setQuestionnaireCopy(Questionnaire $questionnaire)
    {
        $this->setQuestionnaire($questionnaire);
    }

    /**
     * @return ListingCategory
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param ListingCategory $category
     */
    public function setCategory(ListingCategory $category)
    {
        $this->category = $category;
    }

    /**
     * @param Question $question
     * @return $this
     */
    public function addQuestion(Question $question)
    {
        $question->setExperiment($this);
        $this->questions[] = $question;

        return $this;
    }

    /**
     * @param Question $question
     */
    public function removeQuestion(Question $question)
    {
        $this->questions->removeElement($question);
    }

    /**
     * @return ArrayCollection|Question[]
     */
    public function getQuestions()
    {
        return $this->questions;
    }


    /**
     * @return ArrayCollection|listing[]
     */
    public function getListings()
    {
        return $this->listings;
    }

    /**
     * @return ArrayCollection|Question[]
     */
    public function getOffererQuestions()
    {
        $questions = new ArrayCollection();
        foreach ($this->getQuestions() as $question) {
            if ($question->getMode() == Question::MODE_OFFERER_ASKER) {
                $questions->add($question);
            }
        }
        return $questions;
    }

    /**
     * @return ArrayCollection|Question[]
     */
    public function getAskerQuestions()
    {
        $questions = new ArrayCollection();
        foreach ($this->getQuestionnaire()->getMainEntries() as $entry) {
            foreach ($this->getQuestions() as $question) {
                if ($question->getUid() == $entry->getQuestionUid()) {
                    $questions->add($question);
                }
            }
        }
        return $questions;
    }

    /**
     * @param ArrayCollection|Question[] $questions
     */
    public function setQuestions(ArrayCollection $questions)
    {
        /** @var  $question */
        foreach ($questions as $question) {
            $question->setExperiment($this);
        }
        $this->questions = $questions;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return (string)$this->translate()->getTitle();
    }

    /**
     * @return string
     */
    public function getKeywords()
    {
        return (string)$this->translate()->getKeywords();
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return (string)$this->translate()->getDescription();
    }

    /**
     * @return string
     */
    public function getSlug()
    {
        return (string)$this->translate()->getSlug();
    }

    /**
     * @return array
     */
    public function getDeliverables()
    {
        $deliverables = explode(chr(10), $this->translate()->getDeliverable());
        $deliverables = array_map('trim', $deliverables);
        return array_filter($deliverables, function ($value) {
            return $value !== '';
        });
    }

    /**
     * @return array
     */
    public function getMaterials()
    {
        $materials = explode(chr(10), $this->translate()->getMaterial());
        $materials = array_map('trim', $materials);
        return array_filter($materials, function ($value) {
            return $value !== '';
        });
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getTitle();
    }

    /**
     * @param $method
     * @param $arguments
     * @return mixed
     */
    public function __call($method, $arguments)
    {
        return $this->proxyCurrentLocaleTranslation('get' . ucfirst($method), $arguments);
    }

    public function __clone()
    {
        $this->id = null;

        $translations = $this->getTranslations();
        $this->translations = new ArrayCollection();
        foreach ($translations as $translation) {
            $this->addTranslation(clone $translation);
        }

        $image = $this->getImage();
        $this->setImage(clone $image);

        $questionnaire = $this->getQuestionnaire();
        $this->setQuestionnaire(clone $questionnaire);

        $questions = $this->getQuestions();
        $this->questions = new ArrayCollection();
        foreach ($questions as $question) {
            $this->addQuestion(clone $question);
        }
    }
}
