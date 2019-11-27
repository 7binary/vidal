<?php
namespace Vidal\DrugBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Doctrine\ORM\EntityRepository;

class CompanyAdmin extends Admin
{
	protected function configureFormFields(FormMapper $formMapper)
	{
		$formMapper
			->add('LocalName', null, array('label' => 'Название', 'required' => true))
			->add('Property', null, array('label' => 'Приписка', 'required' => false))
			->add('CountryCode', null, array('label' => 'Страна'))
			->add('CompanyGroupID', null, array('label' => 'Группа компании'))
			->add('inactive', null, array('label' => 'Исключить', 'required' => false))
		;
	}

	protected function configureDatagridFilters(DatagridMapper $datagridMapper)
	{
		$datagridMapper
			->add('CompanyID', null, array('label' => 'ID'))
			->add('LocalName', null, array('label' => 'Название'))
			->add('Property', null, array('label' => 'Приписка'))
			->add('CountryCode', null, array('label' => 'Страна'))
			->add('CompanyGroupID', null, array('label' => 'Группа компании'))
			->add('inactive', null, array('label' => 'Исключена'))
		;
	}

	protected function configureListFields(ListMapper $listMapper)
	{
		$listMapper
			->addIdentifier('CompanyID', null, array('label' => 'ID'))
			->add('LocalName', null, array('label' => 'Название'))
			->add('Property', null, array('label' => 'Приписка'))
			->add('CountryCode', null, array('label' => 'Страна'))
			->add('CompanyGroupID', null, array('label' => 'Группа компании'))
			->add('inactive', null, array('label' => 'Исключена'))
			->add('_action', 'actions', array(
				'label'   => 'Действия',
				'actions' => array(
					'edit'   => array(),
					'delete' => array(),
				)
			));
	}
}