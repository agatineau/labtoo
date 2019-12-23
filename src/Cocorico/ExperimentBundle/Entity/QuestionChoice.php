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
use Knp\DoctrineBehaviors\Model as ORMBehaviors;

/**
 * @ORM\Entity
 * @ORM\Table(name="experiment_question_choice")
 */
class QuestionChoice
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
     * @ORM\Column(type="integer")
     *
     * @var int
     */
    private $uid;

    /**
     * @ORM\Column(type="boolean")
     *
     * @var bool
     */
    private $prohibitive;

    /**
     * @ORM\Column(type="integer", nullable=true)
     *
     * @var int
     */
    private $variable;

    /**
     * @ORM\ManyToOne(targetEntity="Question", inversedBy="choices")
     *
     * @var Question
     */
    private $question;

    public function __construct()
    {
        $this->prohibitive = false;
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
     * @return bool
     */
    public function isProhibitive()
    {
        return $this->prohibitive;
    }

    /**
     * @param bool $prohibitive
     */
    public function setProhibitive($prohibitive)
    {
        $this->prohibitive = $prohibitive;
    }

    /**
     * @return int
     */
    public function getVariable()
    {
        return $this->variable;
    }

    /**
     * @param int $variable
     */
    public function setVariable($variable)
    {
        $this->variable = $variable;
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
    public function getText()
    {
        return (string)$this->__call('name');
    }

    public function getValue()
    {
        return $this->getVariable() ?: $this->getText();
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
    }
}
