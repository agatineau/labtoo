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

use Cocorico\ExperimentBundle\Entity\ListingAnswer;
use Cocorico\ExperimentBundle\Entity\Question;
use Cocorico\ExperimentBundle\Form\DataTransformer\QuestionChoiceValueTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ListingAnswerType extends AbstractType
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

            /** @var ListingAnswer $listingAnswer */
            $listingAnswer = $event->getData();

            $type = is_null($listingAnswer) ? 'text' : $listingAnswer->getQuestion()->getFormType();

            $valueOptions = array();
            if (!is_null($listingAnswer)) {
                $valueOptions['label'] = $listingAnswer->getQuestion()->getTitleOfferer();
                if ($listingAnswer->getQuestion()->getType() == Question::TYPE_CHOICE) {
                    $choices = array();
                    foreach ($listingAnswer->getQuestion()->getChoices() as $choice) {
                        $choices[$choice->getId()] = $choice->getText();
                    }
                    $valueOptions['choices'] = $choices;
                    $valueOptions['multiple'] = true;
                    $valueOptions['model_transformer'] = new QuestionChoiceValueTransformer();
                } elseif ($listingAnswer->getQuestion()->getType() == Question::TYPE_RANGE) {
                    $valueOptions['attr'] = array(
                        'min' => $listingAnswer->getQuestion()->getRange()->getMin(),
                        'max' => $listingAnswer->getQuestion()->getRange()->getMax()
                    );
                } elseif ($listingAnswer->getQuestion()->getType() == Question::TYPE_TEXT) {
                    $valueOptions['attr'] = array(
                        'placeholder' => $listingAnswer->getQuestion()->getText()
                    );
                }
            }

            $builder = $event->getForm();

            $builder->add('value', $type, $valueOptions);
        });
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'Cocorico\ExperimentBundle\Entity\ListingAnswer'
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
        return 'listing_answer';
    }
}
