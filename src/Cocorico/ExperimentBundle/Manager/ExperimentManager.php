<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\ExperimentBundle\Manager;

use Cocorico\ExperimentBundle\Entity\Experiment;
use Cocorico\ExperimentBundle\Entity\Question;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\ResultSetMapping;

class ExperimentManager
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param Experiment $experiment
     * @return Experiment
     */
    public function update(Experiment $experiment)
    {
        if ($experiment->getId()) {
            $experiment->setUpdatedAt(new \DateTime());
        }
        $oldStatus = false;
        if($experiment->getPublishedAt()){
            $oldStatus = true;
        }

        if($experiment->getListings()){
            foreach ($experiment->getListings() as $listing) {
                $listing->setOldStatus($oldStatus);
            }
        }
        foreach ($experiment->getQuestions() as $question) {
            if ($question->translate()->getTitleOfferer()) {
                $question->setMode(Question::MODE_OFFERER_ASKER);
            } else {
                $question->setMode(Question::MODE_ASKER_ONLY);
            }
        }
    }
}
