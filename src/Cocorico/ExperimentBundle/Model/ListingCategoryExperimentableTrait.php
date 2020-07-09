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
use Doctrine\Common\Collections\ArrayCollection;

trait ListingCategoryExperimentableTrait
{
    /**
     * @ORM\OneToMany(targetEntity="Cocorico\ExperimentBundle\Entity\Experiment", mappedBy="category")
     *
     * @var ArrayCollection|Experiment[]
     */
    private $experiments;

    /**
     * @return ArrayCollection|Experiment[]
     */
    public function getExperiments()
    {
        return $this->experiments;
    }
}
