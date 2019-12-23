<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\ExperimentBundle\Form\Type\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class QuestionRangeType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'min',
                'number',
                array(
                    'label' => 'admin.experiment.question_range_min.label',
                    'required' => false
                )
            )
            ->add(
                'max',
                'number',
                array(
                    'label' => 'admin.experiment.question_range_max.label',
                    'required' => false
                )
            )
            ->add(
                'value',
                'checkbox',
                array(
                    'label' => 'admin.experiment.question_range_value.label',
                    'required' => false,
                    'attr' => array(
                        'class' => 'question-range-value'
                    )
                )
            );
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'Cocorico\ExperimentBundle\Entity\QuestionRange',
                'translation_domain' => 'SonataAdminBundle',
                'cascade_validation' => true,
                /** @Ignore */
                'label' => false
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
        return 'admin_question_range';
    }
}
