<?php
namespace Vidal\MainBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Form\FormMapper;

class AboutAdmin extends Admin
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
			->add('title', null, array('label' => 'Заголовок раздела'))
			->add('url', null, array('label' => 'Путь'))
			->add('body', null, array('label' => 'Содержимое раздела'))
			->add('priority', null, array('label' => 'Приоритет'))
			->add('enabled', null, array('label' => 'Активен'));
	}

	protected function configureFormFields(FormMapper $formMapper)
	{
		$formMapper
			->add('title', null, array('label' => 'Заголовок раздела'))
			->add('url', null, array('label' => 'Путь'))
			->add('body', null, array('label' => 'Содержимое раздела', 'attr' => array('class'=>'ckeditorfull')))
			->add('priority', null, array('label' => 'Приоритет', 'required' => false))
			->add('enabled', null, array('label' => 'Активен', 'required' => false));
	}

	protected function configureDatagridFilters(DatagridMapper $datagridMapper)
	{
		$datagridMapper
			->add('id')
			->add('title', null, array('label' => 'Заголовок раздела'))
			->add('url', null, array('label' => 'Путь'))
			->add('enabled', null, array('label' => 'Активен'));
	}

	protected function configureListFields(ListMapper $listMapper)
	{
		$listMapper
			->add('id')
			->add('title', null, array('label' => 'Заголовок раздела'))
			->add('url', null, array('label' => 'Путь'))
			->add('priority', null, array('label' => 'Приоритет'))
			->add('enabled', null, array('label' => 'Активен', 'template' => 'VidalDrugBundle:Sonata:swap_enabled_main.html.twig'))
			->add('_action', 'actions', array(
				'label'   => 'Действия',
				'actions' => array(
					'show' => array(),
					'edit' => array(),
					'delete' => array(),
				)
			));
	}
}