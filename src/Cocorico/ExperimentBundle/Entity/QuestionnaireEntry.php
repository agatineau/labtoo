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

use Doctrine\ORM\Mapping as ORM;
use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Translation\TranslationContainerInterface;

/**
 * @ORM\Entity
 * @ORM\Table(name="experiment_questionnaire_entry")
 */
class QuestionnaireEntry implements TranslationContainerInterface
{
    const DEPENDENCY_TYPE_QUESTION = 1;
    const DEPENDENCY_TYPE_ANSWER = 2;

    public static $dependencyTypeValues = array(
        self::DEPENDENCY_TYPE_QUESTION => 'admin.experiment.questionnaire_entry_dependency_type_question',
        self::DEPENDENCY_TYPE_ANSWER => 'admin.experiment.questionnaire_entry_dependency_type_answer'
    );

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     *
     * @var int
     */
    private $id;

    /**
     * @ORM\Column(name="question_uid", type="integer", nullable=true)
     *
     * @var int
     */
    private $questionUid;

    /**
     * @ORM\Column(name="dependency_type", type="string", nullable=true)
     *
     * @var string
     */
    private $dependencyType;

    /**
     * @ORM\Column(name="dependency_uid", type="integer", nullable=true)
     *
     * @var int
     */
    private $dependencyUid;

    /**
     * @ORM\ManyToOne(targetEntity="Questionnaire", inversedBy="entries")
     *
     * @var Questionnaire
     */
    private $questionnaire;

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
    public function getQuestionUid()
    {
        return $this->questionUid;
    }

    /**
     * @param int $questionUid
     */
    public function setQuestionUid($questionUid)
    {
        $this->questionUid = $questionUid;
    }

    /**
     * @return string
     */
    public function getDependencyType()
    {
        return $this->dependencyType;
    }

    /**
     * @param string $dependencyType
     */
    public function setDependencyType($dependencyType)
    {
        $this->dependencyType = $dependencyType;
    }

    /**
     * @return int
     */
    public function getDependencyUid()
    {
        return $this->dependencyUid;
    }

    /**
     * @param int $dependencyUid
     */
    public function setDependencyUid($dependencyUid)
    {
        $this->dependencyUid = $dependencyUid;
    }

    /**
     * @return Questionnaire
     */
    public function getQuestionnaire()
    {
        return $this->questionnaire;
    }

    /**
     * @param Questionnaire $questionnaire
     */
    public function setQuestionnaire($questionnaire)
    {
        $this->questionnaire = $questionnaire;
    }

    /**
     * @return null|Question
     */
    public function getQuestion()
    {
        foreach ($this->questionnaire->getExperiment()->getQuestions() as $question) {
            if ($question->getUid() !== $this->getQuestionUid()) continue;
            return $question;
        }
        return null;
    }

    public function __clone()
    {
        $this->id = null;
    }

    /**
     * JMS Translation messages
     *
     * @return array
     */
    public static function getTranslationMessages()
    {
        $messages = array();
        foreach (self::$dependencyTypeValues as $dependencyTypeValue) {
            $messages [] = new Message($dependencyTypeValue, 'SonataAdminBundle');
        }
        return $messages;
    }
}
