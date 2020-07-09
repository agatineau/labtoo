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
use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Translation\TranslationContainerInterface;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;

/**
 * @ORM\Entity
 * @ORM\Table(name="experiment_question")
 */
class Question implements TranslationContainerInterface
{
    const TYPE_CHOICE = 1;
    const TYPE_RANGE = 2;
    const TYPE_TEXT = 3;

    public static $typeValues = array(
        self::TYPE_CHOICE => 'admin.experiment.question_type_choice',
        self::TYPE_RANGE => 'admin.experiment.question_type_range',
        self::TYPE_TEXT => 'admin.experiment.question_type_text',
    );

    const MODE_ASKER_ONLY = 1;
    const MODE_OFFERER_ASKER = 2;

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
     * @ORM\Column(type="integer")
     *
     * @var int
     */
    private $uid;

    /**
     * @ORM\Column(type="smallint")
     *
     * @var int
     */
    private $mode;

    /**
     * @ORM\Column(type="smallint")
     *
     * @var int
     */
    private $type;

    /**
     * @ORM\ManyToOne(targetEntity="Experiment", inversedBy="questions")
     *
     * @var Experiment
     */
    private $experiment;

    /**
     * @ORM\OneToMany(targetEntity="QuestionChoice", mappedBy="question", cascade={"persist", "remove"}, orphanRemoval=true)
     *
     * @var ArrayCollection
     */
    private $choices;

    /**
     * @ORM\OneToOne(targetEntity="QuestionRange", inversedBy="question", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\JoinColumn(onDelete="CASCADE")
     *
     * @var QuestionRange
     **/
    private $range;

    public function __construct()
    {
        $this->choices = new ArrayCollection();
        $this->range = new QuestionRange();
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
    public function getUid()
    {
        return $this->uid;
    }

    /**
     * @param int $uid
     */
    public function setUid($uid)
    {
        $this->uid = $uid;
    }

    /**
     * @return int
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * @param int $mode
     */
    public function setMode($mode)
    {
        $this->mode = $mode;
    }

    /**
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getTypeText()
    {
        return self::$typeValues[$this->getType()];
    }

    /**
     * @param int $type
     */
    public function setType($type)
    {
        if (!in_array($type, array_keys(self::$typeValues))) {
            throw new \InvalidArgumentException(
                sprintf('Invalid value for question.type : %s.', $type)
            );
        }
        $this->type = $type;
    }

    /**
     * @return Experiment
     */
    public function getExperiment()
    {
        return $this->experiment;
    }

    /**
     * @param Experiment|null $experiment
     */
    public function setExperiment($experiment)
    {
        $this->experiment = $experiment;
    }

    /**
     * @param QuestionChoice $choice
     * @return $this
     */
    public function addChoice(QuestionChoice $choice)
    {
        $choice->setQuestion($this);
        $this->choices[] = $choice;

        return $this;
    }

    /**
     * @param QuestionChoice $choice
     */
    public function removeChoice(QuestionChoice $choice)
    {
        $this->choices->removeElement($choice);
    }

    /**
     * @return ArrayCollection|QuestionChoice[]
     */
    public function getChoices()
    {
        return $this->choices;
    }

    /**
     * @param ArrayCollection|QuestionChoice[] $choices
     */
    public function setChoices(ArrayCollection $choices)
    {
        foreach ($choices as $choice) {
            $choice->setQuestion($this);
        }
        $this->choices = $choices;
    }

    /**
     * @return QuestionRange
     */
    public function getRange()
    {
        return $this->range;
    }

    /**
     * @param QuestionRange $range
     */
    public function setRange(QuestionRange $range)
    {
        $this->range = $range;
    }

    /**
     * @return string
     */
    public function getTitleOfferer()
    {
        return (string)$this->__call('titleOfferer');
    }

    /**
     * @return string
     */
    public function getTitleAsker()
    {
        return (string)$this->__call('titleAsker');
    }

    /**
     * @return string
     */
    public function getText()
    {
        return (string)$this->__call('text');
    }

    /**
     * @return string
     */
    public function getFormType()
    {
        return str_replace('admin.experiment.question_type_', 'question_', $this->getTypeText());
    }

    /**
     * @param $method
     * @param array $arguments
     * @return mixed
     */
    public function __call($method, array $arguments = array())
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

        $choices = $this->getChoices();
        $this->choices = new ArrayCollection();
        foreach ($choices as $choice) {
            $this->addChoice(clone $choice);
        }

        $range = $this->getRange();
        $this->setRange(clone $range);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return strval($this->getId());
    }

    /**
     * JMS Translation messages
     *
     * @return array
     */
    public static function getTranslationMessages()
    {
        $messages = array();
        foreach (self::$typeValues as $typeValue) {
            $messages [] = new Message($typeValue, 'SonataAdminBundle');
        }
        return $messages;
    }
}
