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

use Cocorico\CoreBundle\Entity\Listing;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="experiment_listing_answer")
 */
class ListingAnswer
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
     * @ORM\ManyToOne(targetEntity="Cocorico\CoreBundle\Entity\Listing", inversedBy="answers")
     * @ORM\JoinColumn(nullable=false)
     *
     * @var Listing
     */
    private $listing;


    /**
     * @ORM\ManyToOne(targetEntity="Cocorico\ExperimentBundle\Entity\Question")
     * @ORM\JoinColumn(nullable=false)
     *
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
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Listing
     */
    public function getListing()
    {
        return $this->listing;
    }

    /**
     * @param Listing $listing
     */
    public function setListing($listing)
    {
        $this->listing = $listing;
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
    public function setQuestion($question)
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
     * @return array
     */
    public function getSearchableValues()
    {
        $searchableValues = array();
        $values = explode('|', $this->getValue());
        foreach ($values as $value) {
            if ($this->getQuestion()->getType() == Question::TYPE_CHOICE) {
                foreach ($this->getQuestion()->getChoices() as $choice) {
                    if (intval($value) == $choice->getId()) {
                        if (is_null($choice->getVariable())) {
                            $searchableValues[] = sprintf('%d:%s', $this->getQuestion()->getId(), $choice->getId());
                        } else {
                            $searchableValues[] = sprintf('%d:%s', $this->getQuestion()->getId(), $choice->getVariable());
                        }
                    }
                }
            } else {
                $searchableValues[] = sprintf('%d:%s', $this->getQuestion()->getId(), $value);
            }
        }
        return array_unique($searchableValues);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string)$this->getQuestion()->translate()->getTitleOfferer();
    }
}
