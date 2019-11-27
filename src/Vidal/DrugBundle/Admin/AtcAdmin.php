<?php

namespace Vidal\DrugBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;

class AtcAdmin extends Admin
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
	{
		$formMapper
			->add('ATCCode', null, array('label' => 'Код АТХ', 'required' => true))
			->add('RusName', null, array('label' => 'Название русское', 'required' => true))
			->add('EngName', null, array('label' => 'Название латинское', 'required' => false))
			->add('ParentATCCode', null, array('label' => 'Родительский код АТХ'));
	}

	protected function configureDatagridFilters(DatagridMapper $datagridMapper)
	{
		$datagridMapper
			->add('ATCCode', null, array('label' => 'Код АТХ'))
			->add('RusName', null, array('label' => 'Название русское'))
			->add('EngName', null, array('label' => 'Название латинское'))
			->add('ParentATCCode', null, array('label' => 'Родительский код АТХ'));
	}

	protected function configureListFields(ListMapper $listMapper)
	{
		$listMapper
			->add('ATCCode', null, array('label' => 'Код АТХ'))
			->add('RusName', null, array('label' => 'Название русское'))
			->add('EngName', null, array('label' => 'Название латинское'))
			->add('ParentATCCode', null, array('label' => 'Родительский код АТХ'))
			->add('_action', 'actions', array(
				'label'   => 'Действия',
				'actions' => array(
					'edit'   => array(),
					'delete' => array(),
				)
			));
	}
}