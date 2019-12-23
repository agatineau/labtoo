<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\ExperimentBundle\Form\Type\Frontend;

class ListingSearchSubAnswerType extends ListingSearchAnswerType
{
    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'listing_search_sub_answer';
    }
}
