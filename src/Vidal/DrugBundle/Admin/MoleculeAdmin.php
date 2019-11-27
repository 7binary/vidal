<?php

namespace Vidal\DrugBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;

class MoleculeAdmin extends Admin
{
    protected $datagridValues;

    public function __construct($code, $class, $baseControllerName)
    {
        parent::__construct($code, $class, $baseControllerName);

        if (!$this->hasRequest()) {
            $this->datagridValues = array(
                '_page'       => 1,
                '_per_page'   => 25,
                '_sort_order' => 'ASC',
                '_sort_by'    => 'RusName',
            );
        }
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('GNParent', null, array('label' => 'MoleculeBase', 'required' => false))
            ->add('RusName', null, array('label' => 'Название русское', 'required' => true))
            ->add('LatName', null, array('label' => 'Название латинское', 'required' => false));
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('GNParent', null, array('label' => 'MoleculeBase'))
            ->add('RusName', null, array('label' => 'Название русское'))
            ->add('LatName', null, array('label' => 'Название латинское'));
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('MoleculeID', null, array('label' => 'ID'))
            ->add('GNParent', null, array('label' => 'MoleculeBase'))
            ->add('RusName', null, array('label' => 'Название русское'))
            ->add('LatName', null, array('label' => 'Название латинское'))
            ->add('_action', 'actions', array(
                'label'   => 'Действия',
                'actions' => array(
                    'edit'   => array(),
                    'delete' => array(),
                )
            ));
    }
}