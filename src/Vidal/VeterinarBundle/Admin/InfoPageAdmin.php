<?php
namespace Vidal\VeterinarBundle\Admin;

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
			->add('RusName', null, array('label' => 'Название', 'required' => true))
			->add('EngName', null, array('label' => 'Латинское', 'required' => false))
			->add('RusAddress', null, array('label' => 'Информация', 'help' => 'RusAddress', 'attr' => array('class' => 'ckeditorfull')))
			->add('CountryCode', null, array('label' => 'Страна'))
			->add('photo', 'iphp_file', array('label' => 'Логотип временный', 'required' => false))
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