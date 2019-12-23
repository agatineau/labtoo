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

use Cocorico\BalanceBundle\Validator\Constraints\CreditConstraint;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;

class CreditFormType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'balanceMovement',
                'credit_balance_movement'
            )
            ->add(
                'tac',
                'checkbox',
                array(
                    'label' => 'credit.tac.label',
                    'translation_domain' => 'cocorico_balance',
                    'required' => false,
                    'mapped' => false,
                    'constraints' => new IsTrue(
                        array(
                            'message' => CreditConstraint::$messageTac,
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
                'data_class' => 'Cocorico\BalanceBundle\Model\Credit',
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
        return 'credit';
    }
}
