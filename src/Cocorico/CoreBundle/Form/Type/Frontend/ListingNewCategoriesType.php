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

use Cocorico\CoreBundle\Event\ListingFormBuilderEvent;
use Cocorico\CoreBundle\Event\ListingFormEvents;
use Cocorico\CoreBundle\Form\DataTransformer\ListingListingCategoriesToListingCategoriesTransformer;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\HttpFoundation\RequestStack;
use Doctrine\ORM\EntityManager;

class ListingNewCategoriesType extends AbstractType
{
    protected $dispatcher;

    /**
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(RequestStack $requestStack, EventDispatcherInterface $dispatcher, EntityManager $entityManager)
    {
        $this->request = $requestStack->getCurrentRequest();
        $this->locale = $this->request->getLocale();
        $this->dispatcher = $dispatcher;
        $this->entityManager = $entityManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $listing = $builder->getData();
        $categoryRepository = $this->entityManager->getRepository("CocoricoCoreBundle:ListingCategory");

        $builder
            /*->add(
                'listingListingCategories',
                'listing_category'
            );*/
            ->add(
                'listingListingCategories',
                'entity',
                array(
                    'label' => 'listing_search.form.categories',
                    'choices' => $categoryRepository->getFindCategoryResult($this->locale, true),
                    'property' => 'translations[' . $this->locale . '].name',
                    'class' => 'Cocorico\CoreBundle\Entity\ListingCategory',
                    'multiple' => true
                )
            );

        $builder
            ->get('listingListingCategories')
            ->addModelTransformer(new ListingListingCategoriesToListingCategoriesTransformer($listing));


        //Dispatch LISTING_NEW_CATEGORIES_FORM_BUILD Event. Listener listening this event can add fields and validation
        //Used for example to add fields to categories
        $this->dispatcher->dispatch(
            ListingFormEvents::LISTING_NEW_CATEGORIES_FORM_BUILD,
            new ListingFormBuilderEvent($builder)
        );
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefaults(
            array(
                'data_class' => 'Cocorico\CoreBundle\Entity\Listing',
                'csrf_token_id' => 'listing_new_categories',
                'translation_domain' => 'cocorico_listing',
                'cascade_validation' => false,//To have error on collection item field,
                'validation_groups' => false,//To not have listing validation errors when categories are only edited
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
        return 'listing_new_categories';
    }

}
