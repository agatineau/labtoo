<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\UserBundle\Form\Type;

use Cocorico\UserBundle\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserKYCFormType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var User $user */
        $user = $builder->getData();

        $builder
            ->add(
                'birthday',
                'birthday',
                array(
                    'label' => 'form.user.birthday',
                    'widget' => 'choice',
                    'years' => range(date('Y') - 18, date('Y') - 100),
                    'required' => true,
                    'placeholder' => $user->getBirthday() ? null : '',
                )
            )
            ->add(
                'nationality',
                'country',
                array(
                    'label' => 'form.user.nationality',
                    'required' => true,
                    'preferred_choices' => array("GB", "FR", "ES", "DE", "IT", "CH", "US", "RU"),
                    'placeholder' => $user->getNationality() ? null : '',
                )
            )
            ->add(
                'countryOfResidence',
                'country',
                array(
                    'label' => 'form.user.country_of_residence',
                    'required' => true,
                    'preferred_choices' => array('GB', 'FR', 'ES', 'DE', 'IT', 'CH', 'US', 'RU'),
                    'placeholder' => $user->getCountryOfResidence() ? null : '',
                )
            )
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'Cocorico\UserBundle\Entity\User',
                'csrf_token_id' => 'user_kyc',
                'translation_domain' => 'cocorico_user',
                'validation_groups' => array('CocoricoKYC'),
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
        return 'user_kyc';
    }
}
