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

class QuestionChoiceType extends AbstractType
{
    private $locales;

    public function __construct($locales)
    {
        $this->locales = $locales;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'uid',
                'hidden',
                array(
                    'attr' => array(
                        'class' => 'question-choice-uid'
                    )
                )
            )
            ->add(
                'prohibitive',
                'checkbox',
                array(
                    'label' => 'admin.experiment.question_choice_prohibitive.label',
                    'required' => false,
                    'attr' => array(
                        'class' => 'question-choice-prohibitive'
                    )
                )
            )
            ->add(
                'translations',
                'a2lix_translations',
                array(
                    'locales' => $this->locales,
                    'required_locales' => $this->locales,
                    /** @Ignore */
                    'label' => false,
                    'fields' => array(
                        'name' => array(
                            'label' => 'admin.experiment.question_choice_name.label',
                            'required' => false,
                            'attr' => array(
                                'class' => 'question-choice-name'
                            )
                        ),
                        'explanation' => array(
                            'label' => 'admin.experiment.question_choice_explanation.label',
                            'required' => false,
                            'attr' => array(
                                'class' => 'question-choice-explanation'
                            )
                        )
                    )
                )
            )
            ->add(
                'variable',
                'number',
                array(
                    'label' => 'admin.experiment.question_choice_variable.label',
                    'required' => false,
                    'attr' => array(
                        'class' => 'question-choice-variable'
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
                'data_class' => 'Cocorico\ExperimentBundle\Entity\QuestionChoice',
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
        return 'admin_question_choice';
    }
}
