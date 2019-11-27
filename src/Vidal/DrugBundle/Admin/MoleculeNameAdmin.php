<?php

namespace Vidal\DrugBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;

class MoleculeNameAdmin extends Admin
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
            ->add('MoleculeID', null, array('label' => 'Molecule', 'required' => true))
            ->add('RusName', null, array('label' => 'Название русское', 'required' => true))
            ->add('EngName', null, array('label' => 'Название латинское', 'required' => false));
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('MoleculeID', null, array('label' => 'Molecule'))
            ->add('RusName', null, array('label' => 'Название русское'))
            ->add('EngName', null, array('label' => 'Название латинское'));
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('MoleculeNameID', null, array('label' => 'ID'))
            ->add('MoleculeID', null, array('label' => 'Molecule', 'required' => true))
            ->add('RusName', null, array('label' => 'Название русское'))
            ->add('EngName', null, array('label' => 'Название латинское'))
            ->add('_action', 'actions', array(
                'label'   => 'Действия',
                'actions' => array(
                    'edit'   => array(),
                    'delete' => array(),
                )
            ));
    }
}