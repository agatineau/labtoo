<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\ExperimentBundle\Form\Handler\Frontend;

use Cocorico\CoreBundle\Entity\ListingCategory;
use Cocorico\ExperimentBundle\Entity\Experiment;
use Cocorico\ExperimentBundle\Manager\ListingSearchManager;
use Cocorico\ExperimentBundle\Model\ListingSearchSession;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;

class ListingSearchFormHandler
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @var ListingSearchManager
     */
    private $listingSearchManager;

    /**
     * @var ListingSearchSession
     */
    private $listingSearchSession;

    /**
     * @param RequestStack $requestStack
     * @param ListingSearchManager $listingSearchManager
     * @param ListingSearchSession $listingSearchSession
     */
    public function __construct(
        RequestStack $requestStack,
        ListingSearchManager $listingSearchManager,
        ListingSearchSession $listingSearchSession
    )
    {
        $this->request = $requestStack->getCurrentRequest();
        $this->listingSearchManager = $listingSearchManager;
        $this->listingSearchSession = $listingSearchSession;
    }

    /**
     * @param ListingCategory|null $category
     * @param Experiment|null $experiment
     * @return ListingSearchSession
     */
    public function init(ListingCategory $category = null, Experiment $experiment = null)
    {
        $mode = $this->request->get('mode');
        if(!$mode){
            $this->listingSearchSession->reset();
        }
        if ($this->request->request->has('listing')) {
            $request = $this->request->request->get('listing');
            $this->listingSearchSession->reset();
            $this->listingSearchManager->fill($this->listingSearchSession, $request);
        }
        if (!is_null($category)) {
            $this->listingSearchSession->reset();
            $this->listingSearchManager->fillFromListingCategory($this->listingSearchSession, $category);
        }
        if (!is_null($experiment)) {
            $this->listingSearchSession->reset();
            $this->listingSearchManager->fillFromExperiment($this->listingSearchSession, $experiment);
        }
        return $this->listingSearchSession;
    }

    /**
     * @param Form $form
     * @return bool
     */
    public function process($form)
    {
        $form->handleRequest($this->request);
        if ($form->isSubmitted() && $this->request->isMethod('POST') && $form->isValid()) {
            return $this->onSuccess($form);
        }
        return false;
    }

    /**
     * @param Form $form
     * @return boolean
     */
    private function onSuccess(Form $form)
    {
        /** @var ListingSearchSession $listingSearchSession */
        $this->listingSearchSession = $form->getData();
        $this->listingSearchSession->save();
        return true;
    }
}
