<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\ExperimentBundle\Manager;

use Cocorico\CoreBundle\Entity\Listing;
use Cocorico\CoreBundle\Entity\ListingCategory;
use Cocorico\CoreBundle\Model\ListingSearchRequest;
use Cocorico\ExperimentBundle\Entity\Experiment;
use Cocorico\ExperimentBundle\Entity\Question;
use Cocorico\ExperimentBundle\Model\ListingSearchAnswer;
use Cocorico\ExperimentBundle\Model\ListingSearchSession;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query;
use Doctrine\ORM\Tools\Pagination\Paginator;

class ListingSearchManager
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->em = $entityManager;
    }

    /**
     * @param ListingSearchSession $listingSearchSession
     * @param $request
     */
    public function fill(ListingSearchSession $listingSearchSession, $request)
    {
        if (isset($request['listingCategory'])) {
            /** @var ListingCategory $listingCategory */
            $listingCategory = $this->em->getRepository('CocoricoCoreBundle:ListingCategory')
                ->find($request['listingCategory']);
            if ($listingCategory) {
                $listingSearchSession->setListingCategory($listingCategory);
                if (isset($request['experiment'])) {
                    /** @var Experiment $experiment */
                    $experiment = $this->em->getRepository('CocoricoExperimentBundle:Experiment')
                        ->find($request['experiment']);
                    if ($experiment) {
                        foreach ($experiment->getAskerQuestions() as $question) {
                            $answer = new ListingSearchAnswer();
                            $answer->setQuestion($question);
                            $listingSearchSession->addAnswer($answer);
                        }
                    }
                }
            }
        }
    }

    /**
     * @param ListingSearchSession $listingSearchSession
     * @param ListingCategory $listingCategory
     */
    public function fillFromListingCategory(ListingSearchSession $listingSearchSession, ListingCategory $listingCategory)
    {
        $listingSearchSession->setListingCategory($listingCategory);
    }

    /**
     * @param ListingSearchSession $listingSearchSession
     * @param Experiment $experiment
     */
    public function fillFromExperiment(ListingSearchSession $listingSearchSession, Experiment $experiment)
    {
        $listingSearchSession->setListingCategory($experiment->getCategory());
        $listingSearchSession->setExperiment($experiment);
    }

    /**
     * @param ListingSearchRequest $listingSearchRequest
     * @param ListingSearchSession $listingSearchSession
     * @param string $locale
     * @return Paginator
     */
    public function search(
        ListingSearchRequest $listingSearchRequest,
        ListingSearchSession $listingSearchSession,
        $locale
    )
    {
        $queryBuilder = $this->em->getRepository('CocoricoCoreBundle:Listing')->getFindQueryBuilder();
        $queryBuilder
            ->where('t.locale = :locale')
            ->andWhere('l.status = :listingStatus')
            ->setParameter('locale', $locale)
            ->setParameter('listingStatus', Listing::STATUS_PUBLISHED);

        //Experiment
        $queryBuilder
            ->innerJoin('l.experiment', 'e')
            ->andWhere('e.id = :experiment')
            ->setParameter('experiment', $listingSearchSession->getExperiment()->getId());

        //Answers
        foreach ($listingSearchSession->getBookingAnswers() as $i => $bookingAnswer) {
            if ($bookingAnswer->getQuestion()->getMode() != Question::MODE_OFFERER_ASKER) continue;
            $queryBuilder
                ->andWhere(sprintf('l.searchableAnswerValues LIKE :searchableAnswerValue_%d', $i))
                ->setParameter(
                    sprintf('searchableAnswerValue_%d', $i),
                    '%' . $bookingAnswer->getSearchableValue() . '%'
                );
        }

        //Location
        $searchLocation = $listingSearchRequest->getLocation();
        $queryBuilder
            ->addSelect('GEO_DISTANCE(co.lat = :lat, co.lng = :lng) AS distance')
            ->setParameter('lat', $searchLocation->getLat())
            ->setParameter('lng', $searchLocation->getLng());
        $viewport = $searchLocation->getBound();
        if ($viewport) {
            $queryBuilder
                ->andWhere('co.lat < :neLat')
                ->andWhere('co.lat > :swLat')
                ->andWhere('co.lng < :neLng')
                ->andWhere('co.lng > :swLng')
                ->setParameter('neLat', $viewport["ne"]["lat"])
                ->setParameter('swLat', $viewport["sw"]["lat"])
                ->setParameter('neLng', $viewport["ne"]["lng"])
                ->setParameter('swLng', $viewport["sw"]["lng"]);

        }

        //Duration
        $durationRange = $listingSearchRequest->getDurationRange();
        if ($durationRange->getMin() && $durationRange->getMax()) {
            $queryBuilder
                ->andWhere('l.duration BETWEEN :minDuration AND :maxDuration')
                ->setParameter('minDuration', $durationRange->getMin())
                ->setParameter('maxDuration', $durationRange->getMax());
        }

        //Order
        switch ($listingSearchRequest->getSortBy()) {
            case 'recommended':
                $queryBuilder->orderBy('l.averageRating', 'DESC');
                $queryBuilder->addOrderBy('l.certified', 'DESC');
                $queryBuilder->addOrderBy('l.adminNotation', 'DESC');
                $queryBuilder->addOrderBy('distance', 'ASC');
                $queryBuilder->orderBy('l.duration', 'ASC');
                break;
            case 'duration':
                $queryBuilder->orderBy('l.duration', 'ASC');
                $queryBuilder->addOrderBy('l.certified', 'DESC');
                $queryBuilder->addOrderBy('l.averageRating', 'DESC');
                $queryBuilder->addOrderBy('l.adminNotation', 'DESC');
                $queryBuilder->addOrderBy('distance', 'ASC');
                break;
            default:
                $queryBuilder->orderBy('distance', 'ASC');
                $queryBuilder->addOrderBy('l.certified', 'DESC');
                $queryBuilder->addOrderBy('l.averageRating', 'DESC');
                $queryBuilder->addOrderBy('l.adminNotation', 'DESC');
                $queryBuilder->addOrderBy('l.duration', 'ASC');
                break;
        }


        //Pagination
        if ($listingSearchRequest->getMaxPerPage()) {
            $queryBuilder
                ->setFirstResult(($listingSearchRequest->getPage() - 1) * $listingSearchRequest->getMaxPerPage())
                ->setMaxResults($listingSearchRequest->getMaxPerPage());
        }

        //Query
        $query = $queryBuilder->getQuery();
        $query->setHydrationMode(Query::HYDRATE_ARRAY);

        return new Paginator($query);
    }
}
