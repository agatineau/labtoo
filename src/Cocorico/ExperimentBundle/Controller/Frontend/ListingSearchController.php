<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\ExperimentBundle\Controller\Frontend;

use Cocorico\CoreBundle\Entity\ListingCategory;
use Cocorico\ExperimentBundle\Entity\Experiment;
use Cocorico\ExperimentBundle\Model\ListingSearchSession;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class ListingSearchController extends Controller
{
    /**
     * @Route("/listing/search", name="cocorico_listing_search_new")
     * @Method({"GET", "POST"})
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function newAction()
    {
        $empty_value = $this->get('session')->get('cocorico_experiment.experiment_search_query');
        if($empty_value != 'null' && !empty($empty_value)) {
            $this->get('cocorico_experiment.manager.experiment_search')->create(null, $this->getUser());
        }



        $this->get('cocorico_experiment.manager.experiment_search')->unsetQuery();

        $listingSearchHandler = $this->get('cocorico_experiment.form.handler.listing_search');
        $listingSearchSession = $listingSearchHandler->init();
        $form = $this->createCreateForm($listingSearchSession);
        $success = $listingSearchHandler->process($form);

        if ($success) {
            return $this->redirect($this->generateUrl('cocorico_listing_search_result'));
        }

        return $this->render(
            'CocoricoExperimentBundle:Frontend/ListingSearch:new.html.twig',
            array(
                'form' => $form->createView(),
            )
        );
    }

    /**
     * @Route("/listing/search/{category}", name="cocorico_listing_search_category", defaults={"category" = "category"}, requirements={
     *      "category" = "[a-z0-9-]+$"
     * })
     * @ParamConverter("category", class="Cocorico\CoreBundle\Entity\ListingCategory", options={"repository_method" = "findOneBySlug"})
     * @Method({"GET", "POST"})
     *
     * @param ListingCategory $category
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function categoryAction(ListingCategory $category)
    {
        $listingSearchHandler = $this->get('cocorico_experiment.form.handler.listing_search');
        $listingSearchSession = $listingSearchHandler->init($category);
        $form = $this->createCreateForm($listingSearchSession);
        $success = $listingSearchHandler->process($form);

        if ($success) {
            return $this->redirect($this->generateUrl('cocorico_listing_search_result'));
        }

        return $this->render(
            'CocoricoExperimentBundle:Frontend/ListingSearch:new.html.twig',
            array(
                'form' => $form->createView(),
                'search_fragment' => $category->getName(),
                'type' => 'category',
            )
        );
    }

    /**
     * @Route("/listing/search/{category}/{experiment}", name="cocorico_listing_search_experiment", defaults={"category" = "category", "experiment" = "experiment"}, requirements={
     *      "category" = "[a-z0-9-]+$",
     *      "experiment" = "[a-z0-9-]+$",
     * })
     * @ParamConverter("category", class="Cocorico\CoreBundle\Entity\ListingCategory", options={"repository_method" = "findOneBySlug"})
     * @ParamConverter("experiment", class="Cocorico\ExperimentBundle\Entity\Experiment", options={"repository_method" = "findOneBySlug"})
     * @Method({"GET", "POST"})
     *
     * @param ListingCategory $category
     * @param Experiment $experiment
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function experimentAction(ListingCategory $category, Experiment $experiment)
    {
        $this->get('cocorico_experiment.manager.experiment_search')->create($experiment, $this->getUser());

        $listingSearchHandler = $this->get('cocorico_experiment.form.handler.listing_search');
        $listingSearchSession = $listingSearchHandler->init($category, $experiment);
        $form = $this->createCreateForm($listingSearchSession);
        $success = $listingSearchHandler->process($form);

        if ($success) {
            return $this->redirect($this->generateUrl('cocorico_listing_search_result'));
        }

        return $this->render(
            'CocoricoExperimentBundle:Frontend/ListingSearch:new.html.twig',
            array(
                'form' => $form->createView(),
                'search_fragment' => $experiment->getTitle(),
                'type' => 'experiment',
            )
        );
    }

    /**
     * @param ListingSearchSession $listingSearchSession
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(ListingSearchSession $listingSearchSession)
    {
        $form = $this->get('form.factory')->createNamed(
            'listing',
            'listing_search_new',
            $listingSearchSession,
            array(
                'method' => 'POST',
                'action' => $this->generateUrl('cocorico_listing_search_new'),
            )
        );

        return $form;
    }
}
