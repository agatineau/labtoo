<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\CoreBundle\Repository;

use Cocorico\CoreBundle\Entity\ListingCategory;
use Doctrine\Common\Collections\ArrayCollection;
use Gedmo\Tree\Entity\Repository\NestedTreeRepository;

class ListingCategoryRepository extends NestedTreeRepository
{
    protected $rootAlias;

    /**
     * @param $locale
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getNodesHierarchyTranslatedQueryBuilder($locale)
    {
        $qb = $this->getNodesHierarchyQueryBuilder();

        $alias = $qb->getRootAliases();
        $this->rootAlias = $alias[0];

        $qb
            ->addSelect('t')
            ->leftJoin($this->rootAlias . ".translations", 't')
            ->andWhere('t.locale = :locale')
            ->setParameter('locale', $locale);

        return $qb;
    }

    /**
     * @param $locale
     * @param $experimentable
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getNodesHierarchyTranslatedQueryBuilderForListing($locale, $experimentable = false)
    {
        $qb = $this->getNodesHierarchyQueryBuilder();

        $alias = $qb->getRootAliases();
        $this->rootAlias = $alias[0];

        $qb
            ->addSelect('t')
            ->leftJoin($this->rootAlias . '.translations', 't')
            ->andWhere('t.locale = :locale')
            ->setParameter('locale', $locale);

        if ($experimentable) {
            $qb
                ->addSelect('e')
                ->leftJoin($this->rootAlias . '.experiments', 'e')
                ->andWhere($qb->expr()->orX(
                    $this->rootAlias . '.parent IS NULL',
                    $qb->expr()->andX('e.publishedAt IS NOT NULL', 'e.archivedAt IS NULL')
                ));
        }

        return $qb;
    }

    /**
     * @param  string $locale
     * @return ListingCategory[]|mixed
     */
    public function findCategories($locale)
    {
        $qb = $this->getNodesHierarchyTranslatedQueryBuilder($locale);

        $query = $qb->getQuery();
        $query->useResultCache(true, 43200, 'findCategories');

        return $query->execute();
    }

    /**
     * @param array   $ids
     * @param  string $locale
     * @return ListingCategory[]|mixed
     */
    public function findCategoriesByIds(array $ids, $locale)
    {
        $qb = $this->getNodesHierarchyTranslatedQueryBuilder($locale);
        $qb->andWhere($this->rootAlias . ".id IN (:ids)")
            ->setParameter('ids', $ids);


        $query = $qb->getQuery();
        $query->useResultCache(true, 43200, 'findCategoriesByIds');

        return $query->execute();
    }

    /**
     * @param $locale
     * @param $experimentable
     * @return ArrayCollection|array|null
     */
    public function getFindCategoryResult($locale, $experimentable = false)
    {
        $qb = $this->getNodesHierarchyTranslatedQueryBuilderForListing($locale, $experimentable);
        $query = $qb->getQuery();
        $query->useQueryCache(true);
        /** @var ListingCategory[] $categories */
        $categories = $query->getResult();
        // Sorting of categories
        $categoriesArr = $newCategories = $multiCategoriesArr = $multiCategoriesParentArr = array();

        foreach ($categories as $key => $category) {
            $categoriesArr[$category->getId()] = $category;
            $parentCategory = $category->getParent();
            if ($parentCategory == null) {
                $multiCategoriesArr[$category->getId()] = $category->getName();
                $multiCategoriesParentArr[$category->getId()] = $category->getName();
            } else {
                if (isset($multiCategoriesArr[$parentCategory->getId()]) && is_array(
                        $multiCategoriesArr[$parentCategory->getId()]
                    )
                ) {
                    $multiCategoriesArr[$parentCategory->getId()][$category->getId()] = $category->getName();
                } else {
                    $multiCategoriesArr[$parentCategory->getId()] = array($category->getId() => $category->getName());
                }
            }
        }

        foreach ($multiCategoriesArr as $k => $val) {
            if (!is_array($multiCategoriesArr[$k])) {
                unset($categoriesArr[$k]);
                unset($multiCategoriesArr[$k]);
                unset($multiCategoriesParentArr[$k]);
            }
        }

        asort($multiCategoriesParentArr);
        foreach ($multiCategoriesParentArr as $k => $val) {
            $newCategories[] = $categoriesArr[$k];
            if (is_array($multiCategoriesArr[$k])) {
                asort($multiCategoriesArr[$k]);
                foreach ($multiCategoriesArr[$k] as $child_key => $child_value) {
                    $newCategories[] = $categoriesArr[$child_key];
                }
            }
        }

        return $newCategories;
    }

    /**
     * @param $slug
     * @return mixed
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findOneBySlug($slug, $locale = null)
    {
        $qb = $this->createQueryBuilder('c')
            ->addSelect('t')
            ->leftJoin('c.translations', 't')
            ->where('t.slug = :slug')
            ->setParameter('slug', $slug);
        if($locale){
            $qb->andWhere('t.locale = :locale')
            ->setParameter('locale', $locale);
        }

        return $qb->getQuery()->getSingleResult();
    }

    /**
     * @param string $slug
     * @param string $locale
     *
     * @return mixed|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findTranslationsBySlug($slug, $locale)
    {
        $listingCategory = $this->findOneBySlug($slug, $locale);

        $queryBuilder = $this->getEntityManager()->createQueryBuilder()
            ->select('lct')
            ->from('CocoricoCoreBundle:ListingCategoryTranslation', 'lct')
            ->where('lct.translatable = :listingCategory')
            ->setParameter('listingCategory', $listingCategory);
        try {
            return $queryBuilder->getQuery()->getResult();
        } catch (NoResultException $e) {
            return null;
        }
    }
}
