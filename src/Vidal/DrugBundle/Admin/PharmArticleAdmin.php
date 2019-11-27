<?php

namespace Vidal\DrugBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Doctrine\ORM\EntityRepository;
use Vidal\DrugBundle\Transformer\DocumentsTransformer;
use Vidal\DrugBundle\Transformer\DocumentTransformer;
use Vidal\DrugBundle\Transformer\TagTransformer;

class PharmArticleAdmin extends Admin
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

	public function createQuery($context = 'list')
	{
		$qb = $this->getModelManager()->getEntityManager($this->getClass())->createQueryBuilder();
		$qb->select('a')->from($this->getClass(), 'a');

		if (!isset($_GET['filter']['_sort_by']) || $_GET['filter']['_sort_by'] == 'created') {
			$order = isset($_GET['filter']['_sort_order']) ? $_GET['filter']['_sort_order'] : 'DESC';
			$qb->orderBy('a.created', $order)->addOrderBy('a.id', 'ASC');
		}

		$proxyQuery = new \Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery($qb);

		return $proxyQuery;
	}

	protected function configureFormFields(FormMapper $formMapper)
	{
		$subject              = $this->getSubject();
		$em                   = $this->getModelManager()->getEntityManager($subject);
		$tagTransformer       = new TagTransformer($em, $subject);

		$formMapper
			->add('companies', null, array(
				'label'         => 'Фарм-компании',
				'required'      => true,
				'query_builder' => function (EntityRepository $er) {
						return $er->createQueryBuilder('c')->orderBy('c.title', 'ASC');
				},
			))
			->add('priority', null, array('label' => 'Приоритет', 'required' => false))
			->add('text', null, array('label' => 'Содержимое', 'required' => true, 'attr' => array('class' => 'ckeditorfull')))
			->add('tags', null, array('label' => 'Теги', 'required' => false, 'help' => 'Выберите существующие теги или добавьте новый ниже'))
			->add($formMapper->create('hidden', 'text', array(
					'label'        => 'Создать тег',
					'required'     => false,
					'by_reference' => false,
				))->addModelTransformer($tagTransformer)
			)
			->add('atcCodes-text', 'text', array('label' => 'Коды АТХ', 'required' => false, 'mapped' => false, 'attr' => array('class' => 'atcCodes-text', 'placeholder' => 'Начните вводить название или код')))
			->add('nozologies-text', 'text', array('label' => 'Заболевания МКБ-10', 'required' => false, 'mapped' => false, 'attr' => array('class' => 'nozologies-text', 'placeholder' => 'Начните вводить название или код')))
			->add('molecules-text', 'text', array('label' => 'Активные вещества', 'required' => false, 'mapped' => false, 'attr' => array('class' => 'molecules-text', 'placeholder' => 'Начните вводить название или код')))
			->add('infoPages-text', 'text', array('label' => 'Представительства', 'required' => false, 'mapped' => false, 'attr' => array('class' => 'infoPages-text', 'placeholder' => 'Начните вводить название')))
			->add('products-text', 'text', array('label' => 'Описания препаратов', 'required' => false, 'mapped'=>false, 'attr' => array('class' => 'doc')))
			->add('created', null, array('label' => 'Дата создания', 'required' => true, 'years' => range(2000, date('Y'))))
			->add('enabled', null, array('label' => 'Активна', 'required' => false));
	}

	protected function configureDatagridFilters(DatagridMapper $datagridMapper)
	{
		$datagridMapper
			->add('id')
			->add('companies', null, array(
				'label'         => 'Фарм-компании',
				'query_builder' => function (EntityRepository $er) {
						return $er->createQueryBuilder('c')
							->orderBy('c.title', 'ASC');
					},
			))
			->add('priority', null, array('label' => 'Приоритет'))
			->add('enabled', null, array('label' => 'Активна'));
	}

	protected function configureListFields(ListMapper $listMapper)
	{
		$listMapper
			->add('id')
			->add('companies', null, array('label' => 'Фарм-компании', 'template' => 'VidalDrugBundle:Sonata:pharm_companies.html.twig'))
			->add('priority', null, array('label' => 'Приоритет'))
			->add('created', null, array('label' => 'Дата создания', 'widget' => 'single_text', 'format' => 'd.m.Y в H:i'))
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