<?php

namespace Vidal\DrugBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;

class ArticleRubriqueAdmin extends Admin
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
			->add('title', null, array('label' => 'Название'))
			->add('rubrique', null, array('label' => 'Страница рубрики', 'help' => 'Принимаются латинские буквы и тире'))
			->add('priority', null, array('label' => 'Приоритет'))
			->add('redirect', null, array('label' => 'Переход сюда'))
            ->add('testMode', null, array('label' => 'Тестовый режим'))
            ->add('enabled', null, array('label' => 'Активна'));
	}

	protected function configureFormFields(FormMapper $formMapper)
	{
		$formMapper
			->add('title', null, array('label' => 'Название', 'required' => true))
			->add('rubrique', null, array(
				'label'    => 'Страница рубрики',
				'help'     => 'Принимаются латинские буквы и тире',
				'required' => true
			))
			->add('priority', null, array('label' => 'Приоритет'))
			->add('redirect', null, array('label' => 'Переход сюда', 'required' => false, 'help' => 'Оставьте пустым, чтоб оставить поведение по умолчанию'))
            ->add('testMode', null, array('label' => 'Тестовый режим', 'required' => false, 'help' => 'Видно лишь если в конце URL дописать ?test'))
            ->add('enabled', null, array('label' => 'Активна', 'required' => false));
	}

	protected function configureDatagridFilters(DatagridMapper $datagridMapper)
	{
		$datagridMapper
			->add('title', null, array('label' => 'Название'))
			->add('priority', null, array('label' => 'Приоритет'))
			->add('enabled', null, array('label' => 'Активна'));
	}

	protected function configureListFields(ListMapper $listMapper)
	{
		$listMapper
			->add('title', null, array('label' => 'Название'))
			->add('rubrique', null, array('label' => 'Страница рубрики'))
			->add('priority', null, array('label' => 'Приоритет'))
			->add('redirect', null, array('label' => 'Переход сюда'))
			->add('testMode', null, array('label' => 'Тестовый режим'))
            ->add('enabled', null, array('label' => 'Активна', 'template' => 'VidalDrugBundle:Sonata:swap_enabled.html.twig'))
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