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

use Cocorico\ExperimentBundle\Model\ListingSearchSession;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ListingSearchNewType extends AbstractType
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
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var ListingSearchSession $listingSearchSession */
        $listingSearchSession = $builder->getData();

        $experiments = new ArrayCollection();
        if (!is_null($listingSearchSession->getListingCategory())) {
            $experiments = $this->em->getRepository('CocoricoExperimentBundle:Experiment')
                ->findByCategoryId($listingSearchSession->getListingCategory()->getId());
        }

        $builder
            ->add(
                'listingCategory',
                'listing_category',
                array(
                    'multiple' => false
                )
            )
            ->add(
                'experiment',
                'experiment',
                array(
                    'choices' => $experiments
                )
            )
            ->add(
                'answers',
                'listing_search_answer_collection'
            );
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'Cocorico\ExperimentBundle\Model\ListingSearchSession',
                'cascade_validation' => true
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
        return 'listing_search_new';
    }
}
