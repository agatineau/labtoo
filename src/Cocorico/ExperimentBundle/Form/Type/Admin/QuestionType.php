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

use Cocorico\ExperimentBundle\Entity\Question;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class QuestionType extends AbstractType
{
    /**
     * @var string
     */
    private $locales;

    /**
     * @param $locales
     */
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
                        'class' => 'question-uid'
                    )
                )
            )
            ->add(
                'type',
                'choice',
                array(
                    'label' => 'admin.experiment.question_type.label',
                    'choices' => Question::$typeValues,
                    'attr' => array(
                        'class' => 'question-type'
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
                        'titleAsker' => array(
                            'label' => 'admin.experiment.question_title_asker.label',
                            'required' => true,
                            'attr' => array(
                                'class' => 'question-title-asker'
                            )
                        ),
                        'titleOfferer' => array(
                            'label' => 'admin.experiment.question_title_offerer.label',
                            'required' => false
                        ),
                        'text' => array(
                            'label' => 'admin.experiment.question_text.label',
                            'required' => false,
                            'attr' => array(
                                'class' => 'question-text'
                            )
                        )
                    )
                )
            )
            ->add(
                'choices',
                'admin_question_choice_collection',
                array(
                    'label' => 'admin.experiment.question_choices.label',
                    'required' => false,
                    'attr' => array(
                        'class' => 'question-choice-collection'
                    )
                )
            )
            ->add(
                'range',
                'admin_question_range',
                array(
                    /** @Ignore */
                    'label' => false,
                    'attr' => array(
                        'class' => 'question-range'
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
                'data_class' => 'Cocorico\ExperimentBundle\Entity\Question',
                'translation_domain' => 'SonataAdminBundle',
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
        return 'admin_question';
    }
}
