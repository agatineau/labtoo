<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\ExperimentBundle\ApiController;

use Cocorico\CoreBundle\Entity\Listing;
use Cocorico\ExperimentBundle\DTO\ExperimentDTO;
use Cocorico\ExperimentBundle\Entity\Experiment;
use Cocorico\ExperimentBundle\Entity\ListingAnswer;
use Cocorico\ExperimentBundle\Model\ListingSearchAnswer;
use Doctrine\Common\Collections\ArrayCollection;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @Route("/experiments")
 */
class ExperimentController extends Controller
{
    /**
     * @Route("/", name="cocorico_experiment_api_search_experiment")
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function searchAction(Request $request)
    {
        if ($request->getMethod() !== 'GET' || !$request->query->has('q')) {
            throw new NotFoundHttpException();
        }

        $query = urldecode($request->query->get('q'));

        $this->get('cocorico_experiment.manager.experiment_search')->setQuery($query);

        $keywords = array_filter(explode(
            $this->getParameter('cocorico_elasticsearch.keyword_delimiter'),
            $query
        ));

        $listingRepository = $this->get('cocorico_elasticsearch.listing_repository');
        $categoryRepository = $this->get("doctrine.orm.entity_manager")
            ->getRepository('CocoricoCoreBundle:ListingCategory');

        $listings = $listingRepository->findByKeywords($keywords);
        $experiments = [];
        /** @var Listing $listing */
        foreach ($listings as $listing) {
            if (isset($experiments[$listing->getExperiment()->getId()])) {
                continue;
            }
            if (!$listing->getExperiment()->isPublished()) {
                continue;
            }
            $categories = $categoryRepository->getPath($listing->getExperiment()->getCategory());
            $categoryStr = "";
            foreach ($categories as $category) {
                $categoryStr .= $category->getTranslations()[$this->container->get('request')->getLocale()]. ' > ';
            }
            $experiments[$listing->getExperiment()->getId()] = new ExperimentDTO(
                $listing->getExperiment()->getId(),
                $categoryStr . $listing->getExperiment()->getTitle(),
                $this->generateUrl(
                    'cocorico_listing_search_experiment',
                    [
                        'category' => $listing->getExperiment()->getCategory()->getSlug(),
                        'experiment' => $listing->getExperiment()->getSlug()
                    ]
                )
            );
        }

        if (empty($experiments)) {
            $translator = $this->get('translator');
            $experiments[] = new ExperimentDTO(
                0,
                $translator->trans('form.search.empty_result', [], 'cocorico_experiment'),
                $this->generateUrl(
                    'cocorico_listing_search_new'
                )
            );
        }

        return new JsonResponse(array_values($experiments));
    }
}
