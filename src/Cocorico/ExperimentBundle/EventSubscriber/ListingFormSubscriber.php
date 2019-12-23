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
use Cocorico\CoreBundle\Event\ListingFormBuilderEvent;
use Cocorico\CoreBundle\Event\ListingFormEvents;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ListingFormSubscriber implements EventSubscriberInterface
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->em = $entityManager;
    }

    /**
     * @param ListingFormBuilderEvent $event
     */
    public function onListingNewFormBuild(ListingFormBuilderEvent $event)
    {
        $builder = $event->getFormBuilder();

        /** @var Listing $listing */
        $listing = $builder->getData();

        $experiments = new ArrayCollection();
        if ($listing->getListingListingCategories()->count()) {
            $experiments = $this->em->getRepository('CocoricoExperimentBundle:Experiment')
                ->findByCategoryId($listing->getListingListingCategories()[0]->getCategory()->getId());
        }

        $builder
            ->add(
                'experiment',
                'experiment',
                array(
                    'choices' => $experiments
                )
            )
            ->add(
                'answers',
                'listing_answer_collection'
            );
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            ListingFormEvents::LISTING_NEW_FORM_BUILD => array('onListingNewFormBuild', 1),
        );
    }
}
