<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\CoreBundle\Controller\Frontend;

use Cocorico\UserBundle\Entity\UserImage;
use Cocorico\CoreBundle\Event\ListingSearchActionEvent;
use Cocorico\CoreBundle\Event\ListingSearchEvents;
use Cocorico\CoreBundle\Model\ListingSearchRequest;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class ListingSearchController extends Controller
{
    /**
     * Listings search result.
     *
     * @Route("/listing/search_result", name="cocorico_listing_search_result")
     * @Security("not has_role('ROLE_ADMIN') and has_role('ROLE_USER')")
     * @Method("GET")
     *
     * @param  Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function searchAction(Request $request)
    {
        //For drag map mode
        $isXmlHttpRequest = false;
        if ($request->isXmlHttpRequest()) {
            $isXmlHttpRequest = true;
        }

        /** @var ListingSearchRequest $listingSearchRequest */
        $listingSearchRequest = $this->get('cocorico.listing_search_request');
        $isXmlHttpRequest ? $listingSearchRequest->setSortBy('distance') : null;
        $form = $this->createSearchResultForm($listingSearchRequest);

        $listingSearchSession = $this->get('cocorico_experiment.model.listing_search_session');
        if (!$listingSearchSession->isValid()) {
            return $this->redirect($this->generateUrl('cocorico_listing_search_new'));
        }

        $form->handleRequest($request);
        $listingSearchRequest = $form->getData();

        $results = $this->get('cocorico_experiment.manager.listing_search')->search(
            $listingSearchRequest,
            $listingSearchSession,
            $request->getLocale()
        );

        $nbListings = $results->count();
        $listings = $results->getIterator();
        $markers = $this->getMarkers($request, $results, $listings);

        //Persist similar listings id
        $listingSearchRequest->setSimilarListings($markers['listingsIds']);

        //Persist listing search request in session
        !$isXmlHttpRequest ? $this->get('session')->set('listing_search_request', $listingSearchRequest) : null;

        if ($form->isSubmitted() && !$form->isValid()){
            foreach ($form->getErrors(true) as $error) {
                $this->get('session')->getFlashBag()->add(
                    'error',
                    /** @Ignore */
                    $this->get('translator')->trans($error->getMessage(), $error->getMessageParameters(), 'cocorico')
                );
            }
        }

        //Breadcrumbs
        $breadcrumbs = $this->get('cocorico.breadcrumbs_manager');
        $breadcrumbs->addListingResultItems($this->get('request_stack')->getCurrentRequest(), $listingSearchRequest);

        //Add params to view through event listener
        $event = new ListingSearchActionEvent($request);
        $this->get('event_dispatcher')->dispatch(ListingSearchEvents::LISTING_SEARCH_ACTION, $event);
        $extraViewParams = $event->getExtraViewParams();

        return $this->render(
            $isXmlHttpRequest ?
                '@CocoricoCore/Frontend/ListingResult/result_ajax.html.twig' :
                '@CocoricoCore/Frontend/ListingResult/result.html.twig',
            array_merge(
                array(
                    'form' => $form->createView(),
                    'listings' => $listings,
                    'nb_listings' => $nbListings,
                    'markers' => $markers['markers'],
                    'listing_search_request' => $listingSearchRequest,
                    'listing_search_session' => $listingSearchSession,
                    'pagination' => array(
                        'page' => $listingSearchRequest->getPage(),
                        'pages_count' => ceil($nbListings / $listingSearchRequest->getMaxPerPage()),
                        'route' => $request->get('_route'),
                        'route_params' => $request->query->all()
                    ),
                ),
                $extraViewParams
            )
        );

    }

    /**
     * @param  ListingSearchRequest $listingSearchRequest
     *
     * @return \Symfony\Component\Form\Form|\Symfony\Component\Form\FormInterface
     */
    protected function createSearchResultForm(ListingSearchRequest $listingSearchRequest)
    {
        $form = $this->get('form.factory')->createNamed(
            '',
            'listing_search_result',
            $listingSearchRequest,
            array(
                'method' => 'GET',
                'action' => $this->generateUrl('cocorico_listing_search_result'),
            )
        );

        return $form;
    }

    /**
     * Get Markers
     *
     * @param  Request        $request
     * @param  Paginator      $results
     * @param  \ArrayIterator $resultsIterator
     *
     * @return array
     *          array['markers'] markers data
     *          array['listingsIds'] listings ids
     */
    protected function getMarkers(Request $request, $results, $resultsIterator)
    {
        //We get listings id of current page to change their marker aspect on the map
        $resultsInPage = array();
        foreach ($resultsIterator as $i => $result) {
            $resultsInPage[] = $result[0]['id'];
        }

        //We need to display all listings (without pagination) of the current search on the map
        $results->getQuery()->setFirstResult(null);
        $results->getQuery()->setMaxResults(null);
        $nbResults = $results->count();

        $imagePath = UserImage::IMAGE_FOLDER;
        $currentCurrency = $this->get('session')->get('currency', $this->container->getParameter('cocorico.currency'));
        $locale = $request->getLocale();
        $liipCacheManager = $this->get('liip_imagine.cache.manager');
        $currencyExtension = $this->get('lexik_currency.currency_extension');
        $currencyExtension->getFormatter()->setLocale($locale);
        $markers = $listingsIds = array();

        foreach ($results->getIterator() as $i => $result) {
            $listing = $result[0];
            //$listingUser = $listing->getUser();
            $listingsIds[] = $listing['id'];

            $userFirstName = $listing['user']['firstName'] . " " . ucfirst(substr($listing['user']['lastName'], 0, 1)) . ".";

            $attachStrDesc = (strlen($listing['translations'][$locale]['informativeDescription']) > 50) ? '...' : '';
            $desc = substr($listing['translations'][$locale]['informativeDescription'],0,50);
            $informativeDescription = $desc . $attachStrDesc;

            $imageName = count($listing['user']['images']) ? $listing['user']['images'][0]['name'] :
                UserImage::IMAGE_DEFAULT;

            $image = $liipCacheManager->getBrowserPath($imagePath . $imageName, 'user_msmall', array());

            $price = $currencyExtension->convertAndFormat($listing['price'] / 100, $currentCurrency, false);

            $categories = count($listing['listingListingCategories']) ?
                $listing['listingListingCategories'][0]['category']['translations'][$locale]['name'] : '';

            $isInCurrentPage = in_array($listing['id'], $resultsInPage);

            $rating1 = $rating2 = $rating3 = $rating4 = $rating5 = 'hidden';
            if ($listing['averageRating']) {
                $rating1 = ($listing['averageRating'] >= 1) ? 'fa-star' : 'fa-star-o';
                $rating2 = ($listing['averageRating'] >= 2) ? 'fa-star' : 'fa-star-o';
                $rating3 = ($listing['averageRating'] >= 3) ? 'fa-star' : 'fa-star-o';
                $rating4 = ($listing['averageRating'] >= 4) ? 'fa-star' : 'fa-star-o';
                $rating5 = ($listing['averageRating'] >= 5) ? 'fa-star' : 'fa-star-o';
            }

            //Allow to group markers with same location
            $locIndex = $listing['location']['coordinate']['lat'] . "-" . $listing['location']['coordinate']['lng'];
            $markers[$locIndex][] = array(
                'id' => $listing['id'],
                'lat' => $listing['location']['coordinate']['lat'],
                'lng' => $listing['location']['coordinate']['lng'],
                'title' => $listing['translations'][$locale]['title'],
                'category' => $categories,
                'userFirstName' => $userFirstName,
                'informativeDescription' => $informativeDescription,
                'image' => $image,
                'rating1' => $rating1,
                'rating2' => $rating2,
                'rating3' => $rating3,
                'rating4' => $rating4,
                'rating5' => $rating5,
                'price' => $price,
                'certified' => $listing['certified'] ? 'sprite-ico-badge' : 'hidden',
                'url' => $this->generateUrl(
                    'cocorico_listing_show',
                    array('slug' => $listing['translations'][$locale]['slug'])
                ),
                'zindex' => $isInCurrentPage ? 2 * $nbResults - $i : $i,
                'opacity' => $isInCurrentPage ? 1 : 0.4,

            );
        }

        return array(
            'markers' => $markers,
            'listingsIds' => $listingsIds
        );
    }

    /**
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function searchHomeFormAction()
    {
        $listingSearchRequest = $this->getListingSearchRequest();
        $form = $this->createSearchHomeForm($listingSearchRequest);

        return $this->render(
            '@CocoricoCore/Frontend/Home/form_search.html.twig',
            array(
                'form' => $form->createView(),
            )
        );
    }

    /**
     * @param  ListingSearchRequest $listingSearchRequest
     *
     * @return \Symfony\Component\Form\Form|\Symfony\Component\Form\FormInterface
     */
    private function createSearchHomeForm(ListingSearchRequest $listingSearchRequest)
    {
        $form = $this->get('form.factory')->createNamed(
            '',
            'listing_search_home',
            $listingSearchRequest,
            array(
                'method' => 'GET',
                'action' => $this->generateUrl('cocorico_listing_search_result'),
            )
        );

        return $form;
    }

    /**
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function searchFormAction()
    {
        $listingSearchRequest = $this->getListingSearchRequest();
        $form = $this->createSearchForm($listingSearchRequest);

        return $this->render(
            '@CocoricoCore/Frontend/Common/form_search.html.twig',
            array(
                'form' => $form->createView(),
            )
        );
    }

    /**
     * @param  ListingSearchRequest $listingSearchRequest
     *
     * @return \Symfony\Component\Form\Form|\Symfony\Component\Form\FormInterface
     */
    protected function createSearchForm(ListingSearchRequest $listingSearchRequest)
    {
        $form = $this->get('form.factory')->createNamed(
            '',
            'listing_search',
            $listingSearchRequest,
            array(
                'method' => 'GET',
                'action' => $this->generateUrl('cocorico_listing_search_result'),
            )
        );

        return $form;
    }


    /**
     * similarListingAction will list out the listings which are almost similar to what has been
     * searched.
     *
     * @Route("/listing/similar_result/{id}", name="cocorico_listing_similar")
     * @Method("GET")
     *
     * @param  Request $request
     * @param int      $id
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function similarListingAction(Request $request, $id = null)
    {
        $results = new ArrayCollection();
        $listingSearchRequest = $this->getListingSearchRequest();
        $ids = ($listingSearchRequest) ? $listingSearchRequest->getSimilarListings() : array();
        if ($listingSearchRequest && count($ids) > 0) {
            $results = $this->get("cocorico.listing_search.manager")->getListingsByIds(
                $listingSearchRequest,
                $ids,
                null,
                $request->getLocale(),
                array($id)
            );
        }

        return $this->render(
            '@CocoricoCore/Frontend/Listing/similar_listing.html.twig',
            array(
                'results' => $results
            )
        );
    }

    /**
     * @return ListingSearchRequest
     */
    protected function getListingSearchRequest()
    {
        $session = $this->get('session');
        /** @var ListingSearchRequest $listingSearchRequest */
        $listingSearchRequest = $session->has('listing_search_request') ?
            $session->get('listing_search_request') :
            $this->get('cocorico.listing_search_request');

        return $listingSearchRequest;
    }
}
