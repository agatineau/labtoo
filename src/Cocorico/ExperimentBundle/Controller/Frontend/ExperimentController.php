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

use Cocorico\CoreBundle\Entity\Listing;
use Cocorico\ExperimentBundle\Entity\Experiment;
use Cocorico\ExperimentBundle\Entity\ListingAnswer;
use Cocorico\ExperimentBundle\Model\ListingSearchAnswer;
use Doctrine\Common\Collections\ArrayCollection;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * Experiment controller.
 *
 * @Route("/experiment")
 */
class ExperimentController extends Controller
{
    /**
     * @Route("/selector", name="cocorico_experiment_selector")
     * @Method({"GET"})
     *
     * @return Response
     */
    public function selectorAction()
    {
        return $this->render(
            '@CocoricoExperiment/Frontend/Experiment/selector.html.twig',
            array(
                'form' => $this->get('form.factory')->createNamed(
                    'listing',
                    'experiment_selector'
                )->createView()
            )
        );
    }

    /**
     * @Route("/details-{type}", name="cocorico_experiment_details",
     *      requirements={"type" = "asker|offerer"})
     * @Method({"GET"})
     *
     * @param $type
     * @return Response
     */
    public function detailsAction($type)
    {
        $request = $this->get('request_stack')->getCurrentRequest();

        /** @var Experiment $experiment */
        $experiment = $this->get('doctrine')->getRepository('CocoricoExperimentBundle:Experiment')
            ->find($request->query->get('experiment_id'));

        if ($type == 'offerer') {
            $model = new Listing();
            foreach ($experiment->getOffererQuestions() as $question) {
                $answer = new ListingAnswer();
                $answer->setQuestion($question);
                $model->addAnswer($answer);
            }
        } else {
            $model = $this->get('cocorico_experiment.model.listing_search_session');
            $model->setAnswers(new ArrayCollection());
            foreach ($experiment->getAskerQuestions() as $question) {
                $answer = new ListingSearchAnswer();
                $answer->setQuestion($question);
                $model->addAnswer($answer);
            }
        }

        return $this->render(
            sprintf('@CocoricoExperiment/Frontend/Experiment/details_%s.html.twig', $type),
            array(
                'experiment' => $experiment,
                'form' => $this->get('form.factory')->createNamed(
                    'listing',
                    sprintf('experiment_details_%s', $type),
                    $model
                )->createView()
            )
        );
    }
}
