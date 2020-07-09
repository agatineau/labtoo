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
use Cocorico\ExperimentBundle\Entity\Experiment;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;

class ExperimentSubscriber implements EventSubscriber
{
    /**
     * @param LifecycleEventArgs $args
     */
    public function postUpdate(LifecycleEventArgs $args)
    {
        $manager = $args->getObjectManager();
        $object = $args->getObject();
        if (!$object instanceof Experiment) return;
            /**
             * @var Listing $listing
             */
            foreach ($object->getListings() as $listing) {
                foreach ($object->getTranslations() as $i => $translation) {
                    $listing->translate($i)->setKeywords($translation->getKeywords());
                }
                $manager->persist($listing);
                $listing->mergeNewTranslations();
            }
        $manager->flush();

        }


    /**
     * @return array
     */
    public function getSubscribedEvents()
    {
        return array(
            'postUpdate'
        );
    }
}
