<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\ExperimentBundle\Repository;

use Doctrine\ORM\EntityRepository;

class ExperimentRepository extends EntityRepository
{
    /**
     * @param $categoryId
     * @return array
     */
    public function findByCategoryId($categoryId)
    {
        $queryBuilder = $this->createQueryBuilder('e')
            ->join('e.category', 'ec')
            ->leftJoin('e.translations', 'et')
            ->where('e.publishedAt IS NOT NULL')
            ->andWhere('e.archivedAt IS NULL')
            ->andWhere('ec.id = :categoryId')
            ->setParameter('categoryId', $categoryId);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @param $experimentId
     * @return mixed
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findOneById($experimentId)
    {
        $queryBuilder = $this->createQueryBuilder('e')
            ->leftJoin('e.translations', 'et')
            ->leftJoin('e.questions', 'q')
            ->where('e.publishedAt IS NOT NULL')
            ->andWhere('e.archivedAt IS NULL')
            ->andWhere('e.id = :experimentId')
            ->setParameter('experimentId', $experimentId);

        return $queryBuilder->getQuery()->getSingleResult();
    }

    /**
     * @param $slug
     * @return mixed
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findOneBySlug($slug)
    {
        $qb = $this->createQueryBuilder('e')
            ->addSelect('t')
            ->leftJoin('e.translations', 't')
            ->where('t.slug = :slug')
            ->setParameter('slug', $slug);

        return $qb->getQuery()->getSingleResult();
    }

    /**
     * @return array
     */
    public function findAllPublishedAndUnarchived()
    {
        $queryBuilder = $this->createQueryBuilder('e')
            ->join('e.category', 'ec')
            ->leftJoin('e.translations', 'et')
            ->where('e.publishedAt IS NOT NULL')
            ->andWhere('e.archivedAt IS NULL');

        return $queryBuilder->getQuery()->getResult();
    }

}
