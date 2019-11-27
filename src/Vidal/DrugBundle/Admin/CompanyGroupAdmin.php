<?php
namespace Vidal\DrugBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Doctrine\ORM\EntityRepository;

class CompanyGroupAdmin extends Admin
{
	protected function configureFormFields(FormMapper $formMapper)
	{
		$formMapper
			->add('RusName', null, array('label' => 'Общеизвестна как', 'required' => true))
			->add('companies', null, array('label' => 'Компании группы', 'required' => false))
		;
	}

	protected function configureDatagridFilters(DatagridMapper $datagridMapper)
	{
		$datagridMapper
			->add('CompanyGroupID', null, array('label' => 'ID'))
			->add('RusName', null, array('label' => 'Общеизвестна как'))
		;
	}

	protected function configureListFields(ListMapper $listMapper)
	{
		$listMapper
			->addIdentifier('CompanyGroupID', null, array('label' => 'ID'))
			->add('RusName', null, array('label' => 'Общеизвестна как'))
			->add('_action', 'actions', array(
				'label'   => 'Действия',
				'actions' => array(
					'edit'   => array(),
					'delete' => array(),
				)
			));
	}
}