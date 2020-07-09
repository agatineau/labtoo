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
use Knp\DoctrineBehaviors\Model as ORMBehaviors;

/**
 * @ORM\Entity
 * @ORM\Table(name="experiment_question_translation")
 */
class QuestionTranslation
{
    use ORMBehaviors\Translatable\Translation;

    /**
     * @ORM\Column(name="title_offerer", type="string", nullable=true)
     *
     * @var string
     */
    private $titleOfferer;

    /**
     * @ORM\Column(name="title_asker", type="string", nullable=true)
     *
     * @var string
     */
    private $titleAsker;

    /**
     * @ORM\Column(type="text", nullable=true)
     *
     * @var string
     */
    private $text;

    /**
     * @return string
     */
    public function getTitleOfferer()
    {
        return $this->titleOfferer;
    }

    /**
     * @param string $titleOfferer
     */
    public function setTitleOfferer($titleOfferer)
    {
        $this->titleOfferer = $titleOfferer;
    }

    /**
     * @return string
     */
    public function getTitleAsker()
    {
        return $this->titleAsker;
    }

    /**
     * @param string $titleAsker
     */
    public function setTitleAsker($titleAsker)
    {
        $this->titleAsker = $titleAsker;
    }

    /**
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @param string $text
     */
    public function setText($text)
    {
        $this->text = $text;
    }

    public function __clone()
    {
        $this->id = null;
    }
}
