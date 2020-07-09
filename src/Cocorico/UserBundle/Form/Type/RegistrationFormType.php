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
use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Translation\TranslationContainerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;

/**
 * Class RegistrationFormType.
 */
class RegistrationFormType extends AbstractType implements TranslationContainerInterface
{
    public static $tacError = 'form.user.tac.error';

    private $class;

    /**
     * @param string $class
     */
    public function __construct($class)
    {
        $this->class = $class;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'lastName',
                'hidden'
            )
            ->add(
                'firstName',
                'hidden'
            )
            ->add(
                'companyName',
                null,
                array(
                    'label' => 'form.company_name',
                    'required' => true,
                )
            )
            ->add(
                'lastNameLegal',
                null,
                array(
                    'label' => 'form.last_name_legal',
                    'required' => true,
                )
            )
            ->add(
                'firstNameLegal',
                null,
                array(
                    'label' => 'form.first_name_legal',
                    'required' => true,
                )
            )
            ->add(
                'email',
                'email',
                array('label' => 'form.email')
            )
            ->add(
                'emailLegal',
                'hidden'
            )
            ->add(
                'plainPassword',
                'repeated',
                array(
                    'type' => 'password',
                    'options' => array('translation_domain' => 'cocorico_user'),
                    'first_options' => array(
                        'label' => 'form.password',
                        'required' => true,
                    ),
                    'second_options' => array(
                        'label' => 'form.password_confirmation',
                        'required' => true,
                    ),
                    'invalid_message' => 'fos_user.password.mismatch',
                    'required' => true,
                )
            )
            ->add(
                'tac',
                'checkbox',
                array(
                    'label' => 'form.user.tac',
                    'mapped' => false,
                    'constraints' => new IsTrue(
                        array(
                            'message' => self::$tacError,
                        )
                    ),
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
                'data_class' => $this->class,
                'csrf_token_id' => 'user_registration',
                'translation_domain' => 'cocorico_user',
                'validation_groups' => array('CocoricoRegistration'),
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
        return 'user_registration';
    }

    /**
     * JMS Translation messages.
     *
     * @return array
     */
    public static function getTranslationMessages()
    {
        $messages = array();
        $messages[] = new Message(self::$tacError, 'cocorico');

        return $messages;
    }
}
