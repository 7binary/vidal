<?php

namespace Vidal\DrugBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;

class MoleculeBaseAdmin extends Admin
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
            ->add('GNParent', null, array('label' => 'Название', 'required' => true))
            ->add('description', null, array('label' => 'описание', 'required' => false));
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('GNParent', null, array('label' => 'Название'))
            ->add('description', null, array('label' => 'описание'));
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('GNParent', null, array('label' => 'Название'))
            ->add('description', null, array('label' => 'описание'))
            ->add('_action', 'actions', array(
                'label'   => 'Действия',
                'actions' => array(
                    'edit'   => array(),
                    'delete' => array(),
                )
            ));
    }
}