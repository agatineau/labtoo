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

use Cocorico\CoreBundle\Event\ListingSearchFormBuilderEvent;
use Cocorico\CoreBundle\Event\ListingSearchFormEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ListingSearchFormSubscriber implements EventSubscriberInterface
{
    /**
     * @param ListingSearchFormBuilderEvent $event
     */
    public function onListingSearchResultFormBuild(ListingSearchFormBuilderEvent $event)
    {
        $event->stopPropagation();
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            ListingSearchFormEvents::LISTING_SEARCH_RESULT_FORM_BUILD => array('onListingSearchResultFormBuild', 2)
        );
    }
}
