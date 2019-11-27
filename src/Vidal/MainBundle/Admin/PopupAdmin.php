<?php
namespace Vidal\MainBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Form\FormMapper;

class PopupAdmin extends Admin
{
	protected $datagridValues;

	public function __construct($code, $class, $baseControllerName)
	{
		parent::__construct($code, $class, $baseControllerName);

		if (!$this->hasRequest()) {
			$this->datagridValues = array(
				'_page'       => 1,
				'_per_page'   => 25,
				'_sort_order' => 'DESC', // sort direction
				'_sort_by'    => 'created' // field name
			);
		}
	}

	protected function configureFormFields(FormMapper $formMapper)
	{
		$formMapper
            ->add('title', null, array('label' => 'Название', 'required' => true))
			->add('image', 'iphp_file', array('label' => 'Баннер'))
			->add('link', null, array('label' => 'Ссылка', 'required' => true))
			->add('frequency', null, array('label' => 'Приоритет', 'required' => true))
			->add('enabled', null, array('label' => 'Активен', 'required' => false));
	}

	protected function configureDatagridFilters(DatagridMapper $datagridMapper)
	{
		$datagridMapper
			->add('id')
            ->add('title', null, array('label' => 'Название', 'required' => true))
            ->add('frequency', null, array('label' => 'Приоритет', 'required' => true))
            ->add('counter', null, array('label' => 'Счетчик', 'required' => true))
            ->add('enabled', null, array('label' => 'Активен', 'required' => false));
	}

	protected function configureListFields(ListMapper $listMapper)
	{
		$listMapper
			->add('id')
            ->add('title', null, array('label' => 'Название', 'required' => true))
            ->add('frequency', null, array('label' => 'Приоритет', 'required' => true))
			->add('enabled', null, array('label' => 'Активен', 'template' => 'VidalDrugBundle:Sonata:swap_enabled_main.html.twig'))
			->add('_action', 'actions', array(
				'label'   => 'Действия',
				'actions' => array(
					'edit'   => array(),
					'delete' => array(),
				)
			));
	}
}