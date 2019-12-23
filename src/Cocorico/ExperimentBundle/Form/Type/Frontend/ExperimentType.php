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

use Cocorico\ExperimentBundle\Entity\Experiment;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Router;

class ExperimentType extends AbstractType
{
    /**
     * @var Router
     */
    protected $router;

    /**
     * @param Router $router
     */
    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'class' => 'Cocorico\ExperimentBundle\Entity\Experiment',
                'expanded' => true,
                'choice_attr' => function ($choice) {
                    /** @var Experiment $choice */
                    return [
                        'title' => $choice->getTitle(),
                        'description' => $choice->getDescription(),
                        'data-url' => $this->router->generate('cocorico_listing_search_experiment', [
                            'category' => $choice->getCategory()->getSlug(),
                            'experiment' => $choice->getSlug(),
                        ])
                    ];
                }
            )
        );
    }

    public function getParent()
    {
        return 'entity';
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
        return 'experiment';
    }
}
