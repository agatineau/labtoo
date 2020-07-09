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

use Cocorico\CoreBundle\Entity\ListingCategory;
use Cocorico\CoreBundle\Repository\ListingCategoryRepository;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ExperimentCategoryType extends AbstractType
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var string
     */
    private $locale;

    /**
     * @param EntityManager $entityManager
     * @param string $locale
     * @param RequestStack $requestStack
     */
    public function __construct(
        EntityManager $entityManager,
        $locale,
        RequestStack $requestStack
    )
    {
        $this->entityManager = $entityManager;
        $this->locale = $locale;
        if (!is_null($requestStack->getCurrentRequest())) {
            $this->locale = $requestStack->getCurrentRequest()->getLocale();
        }
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        /** @var ListingCategoryRepository $categoryRepository */
        $categoryRepository = $this->entityManager->getRepository('CocoricoCoreBundle:ListingCategory');

        $categories = $categoryRepository->getFindCategoryResult($this->locale);

        $choices = array();
        /** @var ListingCategory $category */
        foreach ($categories as $category) {
            if (!$category->isLeaf() || !$category->getParent()) continue;
            if (!isset($choices[$category->getParent()->getName()])) {
                $choices[$category->getParent()->getName()] = array();
            }
            $choices[$category->getParent()->getName()][$category->getId()] = $category;
        }

        $resolver->setDefaults(array(
            'class' => 'Cocorico\CoreBundle\Entity\ListingCategory',
            'attr' => array(
                'size' => true,
                'data-sonata-select2' => 'false',
                'data-jcf' => true
            ),
            'choices' => $choices,
            'property' => 'translations[' . $this->locale . '].name',
        ));
    }

    /**
     * @return string
     */
    public function getParent()
    {
        return 'entity';
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
        return 'admin_experiment_category';
    }
}
