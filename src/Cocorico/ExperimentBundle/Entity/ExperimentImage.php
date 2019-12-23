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

/**
 * @ORM\Entity
 * @ORM\Table(name="experiment_image")
 */
class ExperimentImage
{
    const IMAGE_DEFAULT = "default-experiment.png";
    const IMAGE_FOLDER = "/uploads/experiments/images/";

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     *
     * @var int
     */
    private $id;

    /**
     * @ORM\Column(type="string", nullable=true)
     *
     * @var string $name
     */
    private $name;

    /**
     * @ORM\OneToOne(targetEntity="Experiment", mappedBy="image")
     *
     * @var Experiment
     */
    private $experiment;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
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
     * @return string
     */
    public function getWebPath()
    {
        return sprintf(
            '%s%s',
            self::IMAGE_FOLDER,
            is_null($this->name) ? self::IMAGE_DEFAULT : $this->name
        );
    }

    public function __clone()
    {
        $this->id = null;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getWebPath();
    }
}
