<?php

namespace Vidal\BigMamaBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;

class CategoryAdmin extends Admin
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
				'_sort_by'    => 'id'
			);
		}
	}

	protected function configureShowField(ShowMapper $showMapper)
	{
		$showMapper
			->add('id')
			->add('title', null, array('label' => 'Раздел'))
            ->add('link', null, array('label' => 'URL-адрес'))
			->add('position', null, array('label' => 'Позиция'))
			->add('enabled', null, array('label' => 'Активен'));
	}

	protected function configureFormFields(FormMapper $formMapper)
	{
		$formMapper
			->add('title', null, array('label' => 'Заголовок', 'required' => true))
            ->add('linkManual', null, array('label' => 'URL-адрес вручную', 'required' => false))
            ->add('link', null, array('label' => 'URL-адрес авто', 'required' => false, 'disabled' => true))
			->add('position', null, array('label' => 'Позиция'))
			->add('enabled', null, array('label' => 'Активен', 'required' => false));
	}

	protected function configureDatagridFilters(DatagridMapper $datagridMapper)
	{
		$datagridMapper
			->add('id')
			->add('title', null, array('label' => 'Заголовок'))
			->add('link', null, array('label' => 'Адрес страницы'))
			->add('position', null, array('label' => 'Приоритет'))
			->add('enabled', null, array('label' => 'Активен'));
	}

	protected function configureListFields(ListMapper $listMapper)
	{
		$listMapper
			->add('id')
			->add('title', null, array('label' => 'Заголовок'))
			->add('link', null, array('label' => 'Адрес страницы'))
			->add('position', null, array('label' => 'Приоритет'))
			->add('enabled', null, array('label' => 'Активен', 'template' => 'VidalDrugBundle:Sonata:swap_enabled_big_mama.html.twig'))
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