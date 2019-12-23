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

class ProfileBankAccountFormType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var User $user */
        $user = $builder->getData();

        $builder
            ->add(
                'companyName',
                'text',
                array(
                    'label' => 'form.company_name',
                    'required' => true,
                )
            )
            ->add(
                'lastNameLegal',
                'text',
                array(
                    'label' => 'form.last_name_legal',
                    'required' => true,
                )
            )
            ->add(
                'firstNameLegal',
                'text',
                array(
                    'label' => 'form.first_name_legal',
                    'required' => true,
                )
            )
            ->add(
                'birthday',
                'birthday',
                array(
                    'label' => 'form.user.birthday',
                    'widget' => "choice",
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
                    'preferred_choices' => array("GB", "FR", "ES", "DE", "IT", "CH", "US", "RU"),
                    'placeholder' => $user->getCountryOfResidence() ? null : '',
                )
            )
            ->add(
                'bankOwnerName',
                null,
                array(
                    'label' => 'form.bank_owner_name',
                    'required' => true,
                )
            )
            ->add(
                'bankOwnerAddress',
                'textarea',
                array(
                    'label' => 'form.user.bank_owner_address',
                    'required' => true,
                )
            )
            ->add(
                'iban',
                'text',
                array(
                    'label' => 'IBAN',
                    'required' => true
                )
            )
            ->add(
                'bic',
                'text',
                array(
                    'label' => 'BIC',
                    'required' => true
                )
            );


    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'Cocorico\UserBundle\Entity\User',
                'csrf_token_id' => 'CocoricoProfileBankAccount',
                'translation_domain' => 'cocorico_user',
                'cascade_validation' => true,
                'validation_groups' => array('CocoricoProfileBankAccount'),
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
        return 'user_profile_bank_account';
    }
}
