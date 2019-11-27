<?php
namespace Vidal\DrugBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Doctrine\ORM\EntityRepository;

class InfoPageAdmin extends Admin
{
	protected function configureFormFields(FormMapper $formMapper)
	{
		$formMapper
			->add('InfoPageID', null, array('label' => 'ID', 'required' => true))
            ->add('logo', null, array('label' => 'Логотип обновленный (декабрь 2016)', 'required' => false))
			->add('tag', null, array('label' => 'Тег', 'required' => false))
			->add('RusName', null, array('label' => 'Название', 'required' => true))
			->add('EngName', null, array('label' => 'Латинское', 'required' => false))
			->add('RusAddress', null, array('label' => 'Информация', 'help' => 'RusAddress', 'attr' => array('class' => 'ckeditorfull')))
			->add('CountryCode', null, array('label' => 'Страна'))
			->add('photo', 'iphp_file', array('label' => 'Логотип временный', 'required' => false))
			->add('countProducts', null, array('label' => 'Продуктов у представительства', 'required' => false))
		;
	}

	protected function configureDatagridFilters(DatagridMapper $datagridMapper)
	{
		$datagridMapper
			->add('InfoPageID', null, array('label' => 'ID'))
			->add('CountryCode', null, array('label' => 'Страна'));
	}

	protected function configureListFields(ListMapper $listMapper)
	{
		$listMapper
			->addIdentifier('InfoPageID', null, array('label' => 'ID'))
			->add('tag', null, array('label' => 'Тег'))
			->add('RusName', null, array('label' => 'Название', 'template' => 'VidalDrugBundle:Sonata:RusName.html.twig'))
			->add('EngName', null, array('label' => 'Латинское', 'template' => 'VidalDrugBundle:Sonata:EngName.html.twig'))
			->add('CountryCode', null, array('label' => 'Страна'))
			->add('_action', 'actions', array(
				'label'   => 'Действия',
				'actions' => array(
					'edit'   => array(),
					'delete' => array(),
				)
			));
	}
}