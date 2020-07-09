<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\ExperimentBundle\Form\Type\Frontend;

use Cocorico\ExperimentBundle\Entity\Question;
use Cocorico\ExperimentBundle\Model\ListingSearchAnswer;
use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Translation\TranslationContainerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ListingSearchAnswerType extends AbstractType implements TranslationContainerInterface
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'question',
                'entity_hidden',
                array(
                    'class' => 'Cocorico\ExperimentBundle\Entity\Question'
                )
            );

        $builder->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event) {

            /** @var ListingSearchAnswer $listingSearchAnswer */
            $listingSearchAnswer = $event->getData();

            $type = is_null($listingSearchAnswer) ? 'text' : $listingSearchAnswer->getQuestion()->getFormType();

            $valueOptions = array();

            if (!is_null($listingSearchAnswer)) {
                $valueOptions['label'] = $listingSearchAnswer->getQuestion()->getTitleAsker();
                if ($listingSearchAnswer->getQuestion()->getType() == Question::TYPE_CHOICE) {
                    $choices = array();
//                    $prohibitiveChoices = array();
                    foreach ($listingSearchAnswer->getQuestion()->getChoices() as $choice) {
                        $choices[$choice->getId()] = $choice->getText();
//                        if ($choice->isProhibitive()) {
//                            $prohibitiveChoices[$choice->getId()] = $choice->translate()->getExplanation();
//                        }
                    }
                    $valueOptions['choices'] = $choices;
//                    $valueOptions['attr'] = array('prohibitiveChoices' => $prohibitiveChoices);
                    // $valueOptions['placeholder'] = 'listing_search_answer.value.placeholder';
                } elseif ($listingSearchAnswer->getQuestion()->getType() == Question::TYPE_RANGE) {
                    $valueOptions['attr'] = array(
                        'min' => $listingSearchAnswer->getQuestion()->getRange()->getMin(),
                        'max' => $listingSearchAnswer->getQuestion()->getRange()->getMax()
                    );
                } elseif ($listingSearchAnswer->getQuestion()->getType() == Question::TYPE_TEXT) {
                    $valueOptions['attr'] = array(
                        'placeholder' => $listingSearchAnswer->getQuestion()->getText()
                    );
                }
            }
            $builder = $event->getForm();
            $builder->add(
                    'value',
                    $type,
                    $valueOptions
                )
                ->add(
                    'answers',
                    'listing_search_sub_answer_collection'
                );
        });
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'Cocorico\ExperimentBundle\Model\ListingSearchAnswer',
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
        return 'listing_search_answer';
    }

    public static function getTranslationMessages()
    {
        return array(
            new Message('listing_search_answer.value.placeholder', 'cocorico_booking')
        );
    }
}
