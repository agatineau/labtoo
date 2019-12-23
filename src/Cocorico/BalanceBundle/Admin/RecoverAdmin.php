<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\BalanceBundle\Admin;

use Cocorico\BalanceBundle\Entity\BalanceMovement;
use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;

class RecoverAdmin extends Admin
{
    protected $translationDomain = 'SonataAdminBundle';
    protected $baseRoutePattern = 'recover';
    protected $currency;

    // setup the default sort column and order
    protected $datagridValues = array(
        '_sort_order' => 'DESC',
        '_sort_by' => 'createdAt'
    );

    public function setCurrency($currency)
    {
        $this->currency = $currency;
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        /** @var BalanceMovement $balanceMovement */
        $balanceMovement = $this->getSubject();

        if ($balanceMovement && $balanceMovement->getType() != BalanceMovement::TYPE_RECOVER) {
            return;
        }

        $statusDisabled = true;
        if ($balanceMovement && $balanceMovement->getStatus() == BalanceMovement::STATUS_WAITING) {
            $statusDisabled = false;
        }

        $formMapper
            ->tab('admin.recover_bank_wire.title')
            ->with('')
            ->add(
                'user',
                'sonata_type_model',
                array(
                    'disabled' => true,
                    'label' => 'admin.recover_bank_wire.user.label'
                )
            )
            ->add(
                'amount',
                'price',
                array(
                    'disabled' => true,
                    'label' => 'admin.recover_bank_wire.amount.label',
                    'include_vat' => true,
                    'scale' => 2,
                )
            )
            ->add(
                'status',
                'choice',
                array(
                    'choices' => array_flip(BalanceMovement::$statusTexts),
                    'label' => 'admin.recover_bank_wire.status.label',
                    'translation_domain' => 'cocorico_balance',
//                    'help' => 'admin.recover_bank_wire.status.help',
                    'choices_as_values' => true,
                    'disabled' => true//$statusDisabled
                )
            )
            ->add(
                'createdAt',
                null,
                array(
                    'disabled' => true,
                    'label' => 'admin.recover_bank_wire.created_at.label',
                )
            )
            ->end()
            ->end();

        $formMapper
            ->tab('Mangopay')
            ->with('')
            ->add(
                'id',
                null,
                array(
                    'disabled' => true,
                    'help' => 'Tag'
                )
            )
            ->add(
                'user.mangopayId',
                null,
                array(
                    'disabled' => true,
                    'help' => 'Author ID'
                )
            )
            ->add(
                'amountDecimal',
                'number',
                array(
                    'disabled' => true,
                    'label' => 'admin.recover_bank_wire.amount.label',
                    'help' => 'Debited funds (' . $this->currency . ')',
                    'scale' => 2,
                    'required' => false
                )
            )
            ->add(
                'fees',
                'number',
                array(
                    'disabled' => true,
                    'label' => 'admin.recover_bank_wire.fees.label',
                    'mapped' => false,
                    'data' => 0,
                    'help' => 'Fees',
                    'required' => false
                )
            )
            ->add(
                'user.mangopayWalletId',
                null,
                array(
                    'disabled' => true,
                    'help' => 'Debited Wallet ID'
                )
            )
            ->add(
                'wireReference',
                'text',
                array(
                    'disabled' => true,
                    'help' => 'Wire Reference',
                    'mapped' => false,
                    'data' => "CRId:" . (
                        (is_object($balanceMovement) && $balanceMovement)
                            ? $balanceMovement->getId()
                            : null
                        ),
                    'required' => false
                )
            )
            ->add(
                'user.mangopayBankAccountId',
                null,
                array(
                    'disabled' => true,
                    'help' => 'Bank account ID'
                )
            )
            ->end()
            ->end();
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('id')
            ->add(
                'status',
                'doctrine_orm_string',
                array(),
                'choice',
                array(
                    'choices' => array_flip(BalanceMovement::$statusTexts),
                    'label' => 'admin.recover_bank_wire.status.label',
                    'translation_domain' => 'cocorico_balance',
                    'choices_as_values' => true
                )
            )
            ->add(
                'user.email',
                null,
                array('label' => 'admin.recover_bank_wire.user_email.label')
            )
            ->add(
                'createdAt',
                'doctrine_orm_callback',
                array(
                    'label' => 'admin.recover_bank_wire.created_at.label',
                    'callback' => function ($queryBuilder, $alias, $field, $value) {
                        /** @var \DateTime $date */
                        $date = $value['value'];
                        if (!$date) {
                            return false;
                        }

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
                'recover',
                'doctrine_orm_callback',
                array(
                    'callback' => function ($queryBuilder, $alias, $field, $value) {
                        $queryBuilder
                            ->andWhere("$alias.type = :type")
                            ->setParameter('type', BalanceMovement::TYPE_RECOVER);
                        return true;
                    },
                    'field_type' => 'hidden'
                )
            );
    }


    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('id')
            ->add(
                'statusText',
                null,
                array(
                    'label' => 'admin.recover_bank_wire.status.label',
                    'template' => 'CocoricoSonataAdminBundle::list_field_value_translated.html.twig',
                    'data_trans' => 'cocorico_balance'
                )
            )
            ->add(
                'amountDecimal',
                null,
                array(
                    'label' => 'admin.recover_bank_wire.amount.label',
                )
            )
            ->add(
                'user.mangopayId',
                null
            )
            ->add(
                'user.mangopayBankAccountId',
                null
            );

        $listMapper->add(
            '_action',
            'actions',
            array(
                'actions' => array(
                    'edit' => array(),
                    'pay_out' => array(
                        'template' => 'CocoricoBalanceBundle:Admin:Recover/list_action_do_pay_out.html.twig'
                    )
                )
            )
        );
    }

    public function getBatchActions()
    {
        $actions = parent::getBatchActions();
        unset($actions["delete"]);

        return $actions;
    }

    public function getExportFields()
    {
        $fields = array(
            'Id' => 'id',
            'Status' => 'statusText',
            'User' => 'user',
            'Amount' => 'amountDecimal',
            'User Mangopay Id' => 'user.mangopayId',
            'User Mangopay Bank Account Id' => 'user.mangopayBankAccountId'
        );

        return $fields;
    }

    public function getDataSourceIterator()
    {
        $datagrid = $this->getDatagrid();
        $datagrid->buildPager();

        $dataSourceIt = $this->getModelManager()->getDataSourceIterator($datagrid, $this->getExportFields());
        $dataSourceIt->setDateTimeFormat('d M Y');

        return $dataSourceIt;
    }

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->remove('create');
        $collection->remove('delete');
        $collection->add('pay_out');
    }
}
