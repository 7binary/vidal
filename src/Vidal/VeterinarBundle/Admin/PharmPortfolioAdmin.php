<?php

namespace Vidal\VeterinarBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Doctrine\ORM\EntityRepository;
use Vidal\VeterinarBundle\Transformer\DocumentToStringTransformer;

class PharmPortfolioAdmin extends Admin
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

	protected function configureFormFields(FormMapper $formMapper)
	{
		$subject     = $this->getSubject();
		$em          = $this->getModelManager()->getEntityManager($subject);
		$transformer = new DocumentToStringTransformer($em, $subject);

		$formMapper
			->add($formMapper->create('DocumentID', 'text', array(
				'label'        => 'ID документа',
				'required'     => true,
				'by_reference' => false,
			))->addModelTransformer($transformer))
			->add('url', null, array('label' => 'Короткий адрес', 'required' => true))
			->add('title', null, array('label' => 'Название', 'required' => true))
			->add('body', 'textarea', array('label' => 'Содержимое', 'required' => true, 'attr' => array('class' => 'ckeditorfull')))
			->add('created', null, array('label' => 'Дата создания', 'required' => false))
			->add('enabled', null, array('label' => 'Активен', 'required' => false));
	}

	protected function configureDatagridFilters(DatagridMapper $datagridMapper)
	{
		$datagridMapper
			->add('id')
			->add('enabled', null, array('label' => 'Активен'));
	}

	protected function configureListFields(ListMapper $listMapper)
	{
		$listMapper
			->add('id')
			->add('updated', null, array('label' => 'Представительство', 'template' => 'VidalDrugBundle:Sonata:portfolio_infoPage.html.twig'))
			->add('DocumentID', null, array('label' => 'Описание препарата (ID документа)'))
			->add('title', null, array('label' => 'Название', 'template' => 'VidalDrugBundle:Sonata:title.html.twig'))
			->add('url', null, array('label' => 'Короткий адрес'))
			->add('created', null, array('label' => 'Дата создания', 'widget' => 'single_text', 'format' => 'd.m.Y в H:i'))
			->add('enabled', null, array('label' => 'Активна', 'template' => 'VidalDrugBundle:Sonata:swap_enabled.html.twig'))
			->add('_action', 'actions', array(
				'label'   => 'Действия',
				'actions' => array(
					'edit'   => array(),
					'delete' => array(),
				)
			));
	}
}