<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\ExperimentBundle\EventSubscriber;

use Cocorico\CoreBundle\Entity\Listing;
use Cocorico\ExperimentBundle\Entity\ListingAnswer;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;

class ListingAnswerIndexerSubscriber implements EventSubscriber
{
    /**
     * @param LifecycleEventArgs $args
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        $object = $args->getObject();
        if (!$object instanceof ListingAnswer) return;

        $this->index($object->getListing());
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function preUpdate(LifecycleEventArgs $args)
    {
        $object = $args->getObject();
        if (!$object instanceof ListingAnswer) return;

        $this->index($object->getListing());
    }

    /**
     * @param Listing $listing
     */
    private function index(Listing $listing)
    {
        $searchableAnswerValues = array();
        foreach ($listing->getAnswers() as $answer) {
            $searchableAnswerValues = array_merge($searchableAnswerValues, $answer->getSearchableValues());
        }
        $listing->setSearchableAnswerValues($searchableAnswerValues);
    }

    /**
     * @return array
     */
    public function getSubscribedEvents()
    {
        return array(
            'prePersist',
            'preUpdate',
        );
    }
}
