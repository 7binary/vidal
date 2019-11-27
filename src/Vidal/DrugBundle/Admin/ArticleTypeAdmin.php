<?php

namespace Vidal\DrugBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;

class ArticleTypeAdmin extends Admin
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
				'_sort_by'    => 'title'
			);
		}
	}

	protected function configureShowField(ShowMapper $showMapper)
	{
		$showMapper
			->add('id')
			->add('title', null, array('label' => 'Название'));
	}

	protected function configureFormFields(FormMapper $formMapper)
	{
		$formMapper
			->add('title', null, array('label' => 'Название', 'required' => true));
	}

	protected function configureDatagridFilters(DatagridMapper $datagridMapper)
	{
		$datagridMapper
			->add('title', null, array('label' => 'Название'));
	}

	protected function configureListFields(ListMapper $listMapper)
	{
		$listMapper
			->add('title', null, array('label' => 'Название'))
			->add('_action', 'actions', array(
				'label'   => 'Действия',
				'actions' => array(
					'show'   => array(),
					'edit'   => array(),
					'delete' => array(),
				)
			));
	}
}