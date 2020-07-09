<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\CoreBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Cocorico\CoreBundle\Entity\HeaderText;

class HeaderTextAdmin extends Admin
{
    protected $translationDomain = 'SonataAdminBundle';
    protected $baseRoutePattern = 'HeaderText';
    protected $locales;

    // setup the default sort column and order
    protected $datagridValues = array(
        '_sort_order' => 'DESC',
        '_sort_by' => 'id'
    );

    public function setLocales($locales)
    {
        $this->locales = $locales;
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        //Translations fields
        $urls = $descriptions = array();
        foreach ($this->locales as $i => $locale) {
            $descriptions[$locale] = array(
                'label' => 'Description',
                'required' => true,
            );
            $urls[$locale] = array(
                'label' => 'Url',
                'required' => true,
            );
        }

        $formMapper
            ->with('HeaderText')
            ->add(
                'translations',
                'a2lix_translations',
                array(
                    'locales' => $this->locales,
                    'required_locales' => $this->locales,
                    'fields' => array(
                        'description' => array(
                            'field_type' => 'text',
                            'locale_options' => $descriptions,
                            'required' => true,
                        ),
                        'url' => array(
                            'field_type' => 'text',
                            'locale_options' => $urls,
                        )
                    ),
//                    /** @Ignore */
                    'label' => 'Description'
                )
            )
            ->add(
                'sectionType',
                'choice',
                array(
                    'empty_data' => HeaderText::SECTION_TYPE_BANNER,
                    'required' => true,
                    'disabled' => false,
                    'multiple' => false,
                    'expanded' => true,
                    'choices' => array_flip(HeaderText::$sectionTypeValues),
                    'choices_as_values' => true,
                    'label' => 'admin.headertext.section_type.label'
                )
            )
            ->add(
                'published',
                null,
                array(
                    'label' => 'admin.headertext.published.label'
                )
            )
            ->end();
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add(
                'translations.description',
                null,
                array('label' => 'admin.headertext.description.label')
            )
            ->add(
                'translations.url',
                null,
                array('label' => 'admin.headertext.url.label')
            )
            ->add(
                'published',
                null,
                array('label' => 'admin.headertext.published.label')
            );
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('id')
            ->add(
                'url',
                null,
                array('label' => 'admin.headertext.url.label')
            )
            ->add(
                'description',
                'html',
                array(
                    'label' => 'admin.headertext.description.label',
                    'truncate' => array(
                        'length' => 100,
                        'preserve' => true
                    )
                )
            )
            ->add(
                'sectionTypeText',
                null,
                array(
                    'label' => 'admin.headertext.section_type.label',
                    'template' => 'CocoricoSonataAdminBundle::list_header_text_section_type_value_translated.html.twig',
                    'data_trans' => 'messages'
                )
            )
            ->add(
                'published',
                null,
                array(
                    'editable' => true,
                    'label' => 'admin.headertext.published.label'
                )
            );

        $listMapper->add(
            '_action',
            'actions',
            array(
                'actions' => array(
//                    'create' => array(),
                    'edit' => array(),
                    'delete' => array(),
                )
            )
        );
    }

    public function getExportFields()
    {
        return array(
            'Id' => 'id',
            'Description' => 'description',
            'Url' => 'description',
            'Published' => 'published'
        );
    }

    public function getDataSourceIterator()
    {
        $datagrid = $this->getDatagrid();
        $datagrid->buildPager();

        $dataSourceIt = $this->getModelManager()->getDataSourceIterator($datagrid, $this->getExportFields());
        $dataSourceIt->setDateTimeFormat('d M Y'); //change this to suit your needs
        return $dataSourceIt;
    }

    public function getBatchActions()
    {
        $actions = parent::getBatchActions();
        unset($actions["delete"]);

        return $actions;
    }

    protected function configureRoutes(RouteCollection $collection)
    {
//        $collection->remove('create');
       $collection->remove('delete');
    }


}
