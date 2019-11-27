<?php

namespace Vidal\DrugBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;

class ClPhGroupsAdmin extends Admin
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
				'_sort_by'    => 'ATCCode',
			);
		}
	}

	protected function configureFormFields(FormMapper $formMapper)
	{//$ClPhGroupsID
		$formMapper
			->add('Name', null, array('label' => 'Название', 'required' => false))
            ->add('Code', null, array('label' => 'Код', 'required' => false))
        ;
	}

	protected function configureDatagridFilters(DatagridMapper $datagridMapper)
	{
		$datagridMapper
			->add('Name', null, array('label' => 'Название'))
            ->add('Code', null, array('label' => 'Код'))
        ;
	}

	protected function configureListFields(ListMapper $listMapper)
	{
		$listMapper
			->add('ClPhGroupsID', null, array('label' => 'ID'))
			->add('Name', null, array('label' => 'Название'))
            ->add('Code', null, array('label' => 'Код'))
			->add('_action', 'actions', array(
				'label'   => 'Действия',
				'actions' => array(
					'edit'   => array(),
					'delete' => array(),
				)
			));
	}
}