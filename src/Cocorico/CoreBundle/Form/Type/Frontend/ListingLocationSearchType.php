<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\CoreBundle\Form\Type\Frontend;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ListingLocationSearchType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('address','hidden')
            ->add('lat', 'hidden')
            ->add('lng', 'hidden')
            ->add('viewport', 'hidden')
            ->add('country', 'hidden')
            ->add('area', 'hidden')
            ->add('department', 'hidden')
            ->add('city', 'hidden')
            ->add('zip', 'hidden')
            ->add('route', 'hidden')
            ->add('streetNumber', 'hidden')
            ->add('addressType', 'hidden');
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefaults(
            array(
                'csrf_protection' => false,
                'data_class' => 'Cocorico\CoreBundle\Model\ListingLocationSearchRequest',
                'translation_domain' => 'cocorico_listing',
            )
        );
    }

    /**
     * BC
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->getBlockPrefix();
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'listing_location_search';
    }
}
