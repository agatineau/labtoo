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

use Cocorico\ExperimentBundle\Entity\Experiment;
use Cocorico\ExperimentBundle\Entity\ExperimentSearch;
use Cocorico\UserBundle\Entity\User;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Session\Session;

class ExperimentSearchManager
{
    const QUERY = 'cocorico_experiment.experiment_search_query';

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @param EntityManager $entityManager
     * @param Session $session
     */
    public function __construct(
        EntityManager $entityManager,
        Session $session
    )
    {
        $this->entityManager = $entityManager;
        $this->session = $session;
    }

    /**
     * @param Experiment $experiment
     * @param User|null $user
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function create(Experiment $experiment= null, User $user = null)
    {
        if ($this->getQuery() === '') return;

        $search = new ExperimentSearch();
        $search->setQuery($this->getQuery());
        $search->setExperiment($experiment);
        $search->setUser($user);

        $this->entityManager->persist($search);
        $this->entityManager->flush();

        $this->unsetQuery();
    }

    /**
     * @return string
     */
    public function getQuery()
    {
        return $this->session->get($this::QUERY, '');
    }

    /**
     * @param string $query
     */
    public function setQuery($query)
    {
        $this->session->set($this::QUERY, $query);
    }

    public function unsetQuery()
    {
        $this->session->remove($this::QUERY);
    }
}
