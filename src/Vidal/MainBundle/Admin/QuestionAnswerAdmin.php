<?php

namespace Vidal\MainBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Vidal\MainBundle\Form\DataTransformer\CityToStringTransformer;

class QuestionAnswerAdmin extends Admin
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
		$em = $this->modelManager->getEntityManager('Vidal\MainBundle\Entity\City');
		$cityToStringTransformer = new CityToStringTransformer($em);

		$formMapper
            ->add('authorFirstName', null, array('label' => 'Автор вопроса', 'required' => true))
			->add(
				$formMapper->create('city', 'text', array('label' => 'Город'))->addModelTransformer($cityToStringTransformer)
			)
            ->add('authorEmail', null, array('label' => 'Email автора', 'required' => true))
			->add('question', null, array('label' => 'Вопрос', 'required' => true, 'attr' => array('class' => 'ckeditorfull')))
			->add('answer', null, array('label' => 'Ответ', 'required' => true, 'attr' => array('class' => 'ckeditorfull')))

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
			->add('question', null, array('label' => 'Вопрос'))
			->add('enabled', null, array('label' => 'Активнен'));
	}

	protected function configureListFields(ListMapper $listMapper)
	{
		$listMapper
			->add('id')
            ->add('city', null, array('label' => 'Город'))
			->add('question', null, array('label' => 'Вопрос'))
			->add('answer', null, array('label' => 'Ответ', 'template' => 'VidalDrugBundle:Sonata:qa_answer.html.twig'))
			->add('created', null, array(
				'label'  => 'Дата создания',
				'widget' => 'single_text',
				'format' => 'd.m.Y в H:i'
			))
			->add('enabled', null, array('label' => 'Активен', 'template' => 'VidalDrugBundle:Sonata:swap_enabled_main.html.twig'))
			->add('emailSent', null, array('label' => 'Письмо отправлено', 'template' => 'VidalDrugBundle:Sonata:swap_emailSent.html.twig'))
			->add('_action', 'actions', array(
				'label'   => 'Действия',
				'actions' => array(
					'edit'   => array(),
					'delete' => array(),
				)
			));
	}
}