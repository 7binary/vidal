<?php
namespace Vidal\MainBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Form\FormMapper;

class DrugInfoAdmin extends Admin
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
				'_sort_by'    => 'entityId'
			);
		}
	}

	protected function configureShowField(ShowMapper $showMapper)
	{
		$showMapper
			->add('id')
			->add('entityClass', null, array('label' => 'Класс'))
			->add('entityId', null, array('label' => 'Идентификатор'))
            ->add('uri', null, array('label' => 'URL-адрес (https://www.vidal.ru/drugs/***)'))
			->add('ga_pageviews', null, array('label' => 'GA открытий страницы'))
			->add('ga_from', null, array('label' => 'GA дата начала'))
			->add('ga_to', null, array('label' => 'GA дата окончания'));
	}

	protected function configureFormFields(FormMapper $formMapper)
	{
		$formMapper
			->add('entityClass', null, array('label' => 'Класс', 'read_only' => true))
			->add('entityId', null, array('label' => 'Идентификатор', 'read_only' => true))
			->add('uri', null, array('label' => 'URL-адрес (https://www.vidal.ru/drugs/***)', 'required' => false, 'read_only' => true))
			->add('ga_pageviews', null, array('label' => 'GA открытий страницы', 'required' => false, 'read_only' => true))
            ->add('ga_from', null, array('label' => 'GA дата начала', 'required' => false, 'read_only' => true))
            ->add('ga_to', null, array('label' => 'GA дата окончания', 'required' => false, 'read_only' => true))
		;
	}

	protected function configureDatagridFilters(DatagridMapper $datagridMapper)
	{
		$datagridMapper
            ->add('entityClass', null, array('label' => 'Класс'))
            ->add('entityId', null, array('label' => 'Идентификатор'))
            ->add('uri', null, array('label' => 'URL-адрес (https://www.vidal.ru/drugs/***)'))
            ->add('ga_pageviews', null, array('label' => 'GA открытий страницы'))
            ->add('ga_from', null, array('label' => 'GA дата начала'))
            ->add('ga_to', null, array('label' => 'GA дата окончания'))
            ;
	}

	protected function configureListFields(ListMapper $listMapper)
	{
		$listMapper
			->add('id')
            ->add('entityClass', null, array('label' => 'Класс'))
            ->add('entityId', null, array('label' => 'Идентификатор'))
            ->add('uri', null, array('label' => 'URL-адрес (https://www.vidal.ru/drugs/***)'))
            ->add('ga_pageviews', null, array('label' => 'GA открытий страницы'))
            ->add('ga_from', null, array('label' => 'GA дата начала'))
            ->add('ga_to', null, array('label' => 'GA дата окончания'))
			//->add('enabled', null, array('label' => 'Активен', 'template' => 'VidalDrugBundle:Sonata:swap_enabled_main.html.twig'))
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