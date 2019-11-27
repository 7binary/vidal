<?php

namespace Vidal\DrugBundle\Admin;

use Doctrine\ORM\EntityRepository;
use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;

class ClinicoPhPointersAdmin extends Admin
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
				'_sort_by'    => 'Code',
			);
		}
	}

	protected function configureFormFields(FormMapper $formMapper)
	{
		$formMapper
            ->add('Code', null, array('label' => 'Код', 'required' => true))
			->add('Name', null, array('label' => 'Название', 'required' => true))
            ->add('Level', null, array('label' => 'Уровень', 'required' => true))
            ->add('url', null, array('label' => 'URL-адрес', 'required' => true))
            ->add('countProducts', null, array('label' => 'Кол-во препаратов', 'required' => true))
            ->add('parent', null, array(
                'label'         => 'Родительский',
                'required'      => false,
                'attr'          => array('class' => 'kfu-parent', 'style' => 'width:70%'),
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('c')
                        ->orderBy('c.Code', 'ASC');
                },
            ))
        ;
	}

	protected function configureDatagridFilters(DatagridMapper $datagridMapper)
	{
		$datagridMapper
            ->add('Code', null, array('label' => 'Код'))
			->add('Name', null, array('label' => 'Название'))
            ->add('Level', null, array('label' => 'Уровень'))
            ->add('url', null, array('label' => 'URL-адрес'))
            ->add('countProducts', null, array('label' => 'Кол-во препаратов'))
        ;
	}

	protected function configureListFields(ListMapper $listMapper)
	{
		$listMapper
			->add('ClPhPointerID', null, array('label' => 'ID'))
			->add('Name', null, array('label' => 'Название'))
            ->add('Code', null, array('label' => 'Код'))
            ->add('Level', null, array('label' => 'Уровень'))
            ->add('url', null, array('label' => 'URL-адрес'))
			->add('_action', 'actions', array(
				'label'   => 'Действия',
				'actions' => array(
					'edit'   => array(),
					'delete' => array(),
				)
			));
	}
}