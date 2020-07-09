<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\ExperimentBundle\Admin;

use Cocorico\ExperimentBundle\Entity\Experiment;
use Cocorico\ExperimentBundle\Manager\ExperimentManager;
use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;

class ExperimentSearchAdmin extends Admin
{
    /**
     * @var string
     */
    protected $translationDomain = 'SonataAdminBundle';

    /**
     * @var string
     */
    protected $baseRoutePattern = 'experiment-search';

    /**
     * @var array
     */
    protected $datagridValues = array(
        '_sort_order' => 'DESC',
        '_sort_by' => 'createdAt'
    );

    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add(
                'createdAt',
                'doctrine_orm_callback',
                array(
                    'callback' => function ($queryBuilder, $alias, $field, $value) {
                        /** @var \DateTime $date */
                        $date = $value['value'];
                        if (!$date) return false;
                        $queryBuilder
                            ->andWhere("DATE_FORMAT($alias.createdAt,'%Y-%m-%d') = :createdAt")
                            ->setParameter('createdAt', $date->format('Y-m-d'));
                        return true;
                    },
                    'field_type' => 'sonata_type_datetime_picker',
                    'field_options' => array('format' => 'dd/MM/yyyy'),
                ),
                null
            )
            ->add(
                'experiment'
            )
            ->add(
                'user'
            )
        ;
    }

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add(
                'createdAt',
                null,
                array('label' => 'admin.experiment_search.created_at.list')
            )
            ->add(
                'experiment',
                null,
                array('label' => 'admin.experiment_search.experiment.list')
            )
            ->add(
                'query',
                null,
                array('label' => 'admin.experiment_search.query.list')
            )
            ->add(
                'user',
                null,
                array('label' => 'admin.experiment_search.user.list')
            )
        ;
    }

    /**
     * @return array
     */
    public function getBatchActions()
    {
        $actions = parent::getBatchActions();
        unset($actions["delete"]);
        return $actions;
    }

    /**
     * @param RouteCollection $collection
     */
    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->remove('create');
        $collection->remove('edit');
        $collection->remove('delete');
        $collection->remove('export');
    }
}
