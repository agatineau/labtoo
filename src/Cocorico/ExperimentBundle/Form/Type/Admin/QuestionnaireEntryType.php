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

use Cocorico\ExperimentBundle\Entity\QuestionnaireEntry;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class QuestionnaireEntryType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'questionUid',
                'text',
                array(
                    'label' => 'admin.experiment.questionnaire_entry_question_uid.label',
                    'required' => false,
                    'attr' => array(
                        'class' => 'question-uid-selector'
                    )
                )
            )
            ->add(
                'dependencyType',
                'choice',
                array(
                    'label' => 'admin.experiment.questionnaire_entry_dependency_type.label',
                    'choices' => QuestionnaireEntry::$dependencyTypeValues,
                )
            )
            ->add(
                'dependencyUid',
                'text',
                array(
                    'label' => 'admin.experiment.questionnaire_entry_dependency_uid.label',
                    'required' => false,
                    'attr' => array(
                        'class' => 'dependency-uid-selector'
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
                'data_class' => 'Cocorico\ExperimentBundle\Entity\QuestionnaireEntry',
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
        return 'admin_questionnaire_entry';
    }
}
