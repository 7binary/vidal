<?php

namespace Vidal\MainBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;

class AstrazenecaNewAdmin extends Admin
{
	protected $datagridValues;

	public function __construct($code, $class, $baseControllerName)
	{
		parent::__construct($code, $class, $baseControllerName);

		if (!$this->hasRequest()) {
			$this->datagridValues = array(
				'_page'       => 1,
				'_per_page'   => 25,
				'_sort_order' => 'DESC',
				'_sort_by'    => 'created'
			);
		}
	}

	protected function configureFormFields(FormMapper $formMapper)
	{
		$formMapper
			->add('title', null, array('label' => 'Заголовок', 'required' => true))
			->add('photo', 'iphp_file', array('label' => 'Изображение', 'required' => false))
			->add('anons', null, array('label' => 'Анонс', 'required' => true, 'attr' => array('class' => 'ckeditorfull')))
			->add('body', null, array('label' => 'Тело новости', 'required' => true, 'attr' => array('class' => 'ckeditorfull')))
			->add('enabled', null, array('label' => 'Активен', 'required' => false))
			->add('created', null, array(
				'label'    => 'Дата создания',
				'data'     => new \DateTime('now'),
				'required' => true,
			));
	}

	protected function configureDatagridFilters(DatagridMapper $datagridMapper)
	{
		$datagridMapper
			->add('id')
			->add('title', null, array('label' => 'Заголовок'))
			->add('enabled', null, array('label' => 'Активнен'));
	}

	protected function configureListFields(ListMapper $listMapper)
	{
		$listMapper
			->add('id')
			->add('title', null, array('label' => 'Заголовок', 'required' => true))
			->add('enabled', null, array('label' => 'Активен', 'required' => false))
			->add('created', null, array(
				'label'    => 'Дата создания',
				'data'     => new \DateTime('now'),
				'required' => true,
			))
			->add('_action', 'actions', array(
				'label'   => 'Действия',
				'actions' => array(
					'edit'   => array(),
					'delete' => array(),
				)
			));
	}
}