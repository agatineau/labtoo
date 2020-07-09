<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\BalanceBundle\Form\Type\Dashboard;

use Cocorico\BalanceBundle\Validator\Constraints\RecoverCheckConstraint;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;

class RecoverCheckFormType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'password',
                'password',
                array(
                    'label' => 'recover_check.password.label',
                )
            )
            ->add(
                'tac',
                'checkbox',
                array(
                    'label' => 'recover_check.tac.label',
                    'mapped' => false,
                    'constraints' => new IsTrue(
                        array(
                            'message' => RecoverCheckConstraint::$messageTac,
                        )
                    ),
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
                'data_class' => 'Cocorico\BalanceBundle\Model\RecoverCheck',
                'translation_domain' => 'cocorico_balance',
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
        return 'recover_check';
    }
}
