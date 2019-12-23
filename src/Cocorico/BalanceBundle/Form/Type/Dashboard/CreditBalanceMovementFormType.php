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

use Cocorico\BalanceBundle\Entity\BalanceMovement;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CreditBalanceMovementFormType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'type',
                'choice',
                array(
                    'label' => 'credit.type.label',
                    'translation_domain' => 'cocorico_balance',
                    'expanded' => true,
                    'required' => false,
                    'empty_value' => false,
                    'choices' => BalanceMovement::$typeCreditableTexts,
                )
            )
            ->add(
                'amount',
                'price',
                array(
                    'label' => 'credit.amount.label',
                    'translation_domain' => 'cocorico_balance',
                    'required' => false,
                    'attr' => array(
                        'nosign' => true,
                        'signonly' => true,
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
                'data_class' => 'Cocorico\BalanceBundle\Entity\BalanceMovement',
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
        return 'credit_balance_movement';
    }
}
