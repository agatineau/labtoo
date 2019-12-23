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

class ExperimentAdmin extends Admin
{
    /**
     * @var string
     */
    protected $translationDomain = 'SonataAdminBundle';

    /**
     * @var string
     */
    protected $baseRoutePattern = 'experiment';

    /**
     * @var array
     */
    protected $datagridValues = array(
        '_sort_order' => 'DESC',
        '_sort_by' => 'createdAt'
    );

    /**
     * @var string
     */
    private $locale;

    /**
     * @var array
     */
    private $locales;

    /**
     * @var ExperimentManager
     */
    private $experimentManager;

    /**
     * @param $locale
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
    }

    /**
     * @param $locales
     */
    public function setLocales($locales)
    {
        $this->locales = $locales;
    }

    /**
     * @param ExperimentManager $experimentManager
     */
    public function setExperimentManager($experimentManager)
    {
        $this->experimentManager = $experimentManager;
    }

    /**
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper->tab('admin.experiment.description.tab');
        $this->configureFormDescriptionFields($formMapper);
        $formMapper->end();

        $formMapper->tab('admin.experiment.questions.tab');
        $this->configureFormQuestionsFields($formMapper);
        $formMapper->end();

        $formMapper->tab('admin.experiment.questionnaire.tab');
        $this->configureFormQuestionnaireFields($formMapper);
        $formMapper->end();

        $formMapper->tab('admin.experiment.formula.tab');
        $this->configureFormFormulaFields($formMapper);
        $formMapper->end();
    }

    /**
     * @param FormMapper $formMapper
     */
    private function configureFormDescriptionFields(FormMapper $formMapper)
    {
        $formMapper
            ->with('admin.experiment.textual_content.title')
            ->add(
                'translations',
                'a2lix_translations',
                array(
                    'locales' => $this->locales,
                    'translation_domain' => 'SonataAdminBundle',
                    'required_locales' => $this->locales,
                    /** @Ignore */
                    'label' => false,
                    'fields' => array(
                        'title' => array(
                            'label' => 'admin.experiment.title.label'
                        ),
                        'keywords' => array(
                            'label' => 'admin.experiment.keywords.label',
                            'required' => false
                        ),
                        'description' => array(
                            'label' => 'admin.experiment.description.label',
                            'required' => false
                        ),
                        'deliverable' => array(
                            'label' => 'admin.experiment.deliverable.label',
                            'required' => false
                        ),
                        'material' => array(
                            'label' => 'admin.experiment.materiel.label',
                            'required' => false
                        ),
                        'slug' => array(
                            'field_type' => 'hidden'
                        )
                    )
                )
            )
            ->end();

        $formMapper
            ->with(
                'admin.experiment.status.title',
                array(
                    'class' => 'col-md-4'
                )
            )
            ->add(
                'published',
                'checkbox',
                array(
                    'label' => 'admin.experiment.published.label',
                    'required' => false
                )
            )
            ->add(
                'createdAt',
                'date',
                array(
                    'disabled' => true,
                    'label' => 'admin.experiment.created_at.label',
                    'required' => false
                )
            )
            ->add(
                'updatedAt',
                'date',
                array(
                    'disabled' => true,
                    'label' => 'admin.experiment.updated_at.label',
                    'required' => false
                )
            )
            ->end();

        $formMapper
            ->with(
                'admin.experiment.image.title',
                array(
                    'class' => 'col-md-4'
                )
            )
            ->add(
                'image',
                'admin_experiment_image',
                array(
                    /** @Ignore */
                    'label' => false,
                    'required' => false
                )
            )
            ->end();

        $formMapper
            ->with(
                'admin.experiment.category.title',
                array(
                    'class' => 'col-md-4'
                )
            )
            ->add(
                'category',
                'admin_experiment_category',
                array(
                    /** @Ignore */
                    'label' => false,
                    'help' => 'admin.experiment.category.help'
                )
            )
            ->end();

    }

    /**
     * @param FormMapper $formMapper
     */
    private function configureFormQuestionsFields(FormMapper $formMapper)
    {
        $formMapper
            ->with('admin.experiment.questions.title')
            ->add(
                'nextUid',
                'hidden',
                array(
                    /** @Ignore */
                    'label' => false,
                    'attr' => array(
                        'class' => 'next-uid'
                    )
                )
            )
            ->add(
                'questions',
                'admin_question_collection',
                array(
                    /** @Ignore */
                    'label' => false,
                )
            )
            ->end();
    }

    /**
     * @param FormMapper $formMapper
     */
    private function configureFormQuestionnaireFields(FormMapper $formMapper)
    {
        $formMapper
            ->with('admin.experiment.questionnaire.title')
            ->add(
                'questionnaireCopy',
                'admin_questionnaire',
                array(
                    /** @Ignore */
                    'label' => false,
                )
            )
            ->end();
    }

    /**
     * @param FormMapper $formMapper
     */
    private function configureFormFormulaFields(FormMapper $formMapper)
    {
        $formMapper
            ->with('admin.experiment.formula.title')
            ->add(
                'formula',
                'admin_experiment_formula',
                array(
                    'label' => 'admin.experiment.formula.label'
                )
            )
            ->end();
    }

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
                    'field_type' => 'sonata_type_date_picker',
                    'field_options' => array('format' => 'dd/MM/yyyy'),
                ),
                null
            )
            ->add(
                'updatedAt',
                'doctrine_orm_callback',
                array(
                    'callback' => function ($queryBuilder, $alias, $field, $value) {
                        /** @var \DateTime $date */
                        $date = $value['value'];
                        if (!$date) return false;
                        $queryBuilder
                            ->andWhere("DATE_FORMAT($alias.updatedAt,'%Y-%m-%d') = :updatedAt")
                            ->setParameter('updatedAt', $date->format('Y-m-d'));
                        return true;
                    },
                    'field_type' => 'sonata_type_date_picker',
                    'field_options' => array('format' => 'dd/MM/yyyy'),
                ),
                null
            )
            ->add(
                'archived',
                'doctrine_orm_callback',
                array(
                    'callback' => function ($queryBuilder, $alias, $field, $value) {
                        $date = $value['value'];
                        if ($date) {
                            $queryBuilder->andWhere("$alias.archivedAt IS NOT NULL");
                        } else {
                            $queryBuilder->andWhere("$alias.archivedAt IS NULL");
                        }
                        return true;
                    },
                    'field_type' => 'checkbox'
                )
            );
    }

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add(
                'id',
                null,
                array('label' => 'admin.experiment.id.list')
            )
            ->addIdentifier(
                'title',
                null,
                array('label' => 'admin.experiment.title.list')
            )
            ->add(
                'description',
                null,
                array('label' => 'admin.experiment.description.list')
            )
            ->add(
                'keywords',
                null,
                array('label' => 'admin.experiment.keywords.list')
            )
            ->add(
                'published',
                'boolean',
                array('label' => 'admin.experiment.published.list','editable' => true)
            )
            ->add(
                'createdAt',
                'date',
                array('label' => 'admin.experiment.created_at.list')
            );

        $listMapper->add(
            '_action',
            'actions',
            array(
                'actions' => array(
                    'edit' => array(),
                    'delete' => array(),
                )
            )
        );
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
     * @return array
     */
    public function getFormTheme()
    {
        return array_merge(
            parent::getFormTheme(),
            array('CocoricoExperimentBundle:Admin/Experiment:fields.html.twig')
        );
    }

    /**
     * @param RouteCollection $collection
     */
    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->remove('export');
    }

    /**
     * {@inheritdoc}
     */
    public function prePersist($object)
    {
        $this->experimentManager->update($object);
    }

    /**
     * {@inheritdoc}
     */
    public function preUpdate($object)
    {
        $this->experimentManager->update($object);
    }
}
