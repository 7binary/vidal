<?php

namespace Vidal\BigMamaBundle\Admin;

use Doctrine\ORM\EntityRepository;
use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Vidal\DrugBundle\Entity\Publication;
use Vidal\DrugBundle\Transformer\TagTransformer;

class PublicationAdmin extends Admin
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
				'_sort_by'    => 'id'
			);
		}
	}

	protected function configureShowField(ShowMapper $showMapper)
	{
		$showMapper
			->add('id')
			->add('title', null, array('label' => 'Заголовок'))
            ->add('link', null, array('label' => 'URL-адрес'))
			->add('announce', null, array('label' => 'Анонс'))
			->add('body', null, array('label' => 'Основное содержимое'))
			->add('enabled', null, array('label' => 'Активна'))
			->add('date', null, array(
				'label'  => 'Дата создания',
				'widget' => 'single_text',
				'format' => 'd.m.Y в H:i'
			))
			->add('updated', null, array(
				'label'  => 'Дата последнего обновления',
				'widget' => 'single_text',
				'format' => 'd.m.Y в H:i'
			));
	}

	protected function configureFormFields(FormMapper $formMapper)
	{
		$formMapper
            ->add('category', null, array(
                'label'         => 'Раздел',
                'required'      => true,
                'attr'          => array('class' => 'art-rubrique'),
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('c')
                        ->orderBy('c.title', 'ASC');
                },
            ))
			->add('photo', 'iphp_file', array('label' => 'Фотография', 'required' => false))
			->add('title', 'textarea', array('label' => 'Заголовок', 'required' => true, 'attr' => array('class' => 'ckeditormizer')))
            ->add('linkManual', null, array('label' => 'URL-адрес вручную', 'required' => false))
            ->add('link', null, array('label' => 'URL-адрес авто', 'required' => false, 'disabled' => true))
            ->add('position', null, array('label' => 'Позиция', 'required' => false))
            ->add('announce', null, array('label' => 'Анонс', 'required' => false, 'attr' => array('class' => 'ckeditorfull')))
			->add('body', null, array('label' => 'Основное содержимое', 'required' => true, 'attr' => array('class' => 'ckeditorfull')))
			->add('date', null, array('label' => 'Дата создания', 'required' => true, 'years' => range(2000, date('Y'))))
            ->add('enabled', null, array('label' => 'Активна', 'required' => false))
        ;
	}

	protected function configureDatagridFilters(DatagridMapper $datagridMapper)
	{
		$datagridMapper
			->add('id')
            ->add('project',null, array('label' => 'Проект'), 'choice', array(
                'choices' => Publication::getProjectOptions()
            ))
            ->add('title', null, array('label' => 'Заголовок'))
			->add('position', null, array('label' => 'Позиция'))
			->add('enabled', null, array('label' => 'Активна'))
        ;
	}

	protected function configureListFields(ListMapper $listMapper)
	{
		$listMapper
			->add('id')
			->add('title', null, array('label' => 'Заголовок'))
			->add('date', null, array('label' => 'Дата создания', 'widget' => 'single_text', 'format' => 'd.m.Y в H:i'))
			->add('updated', null, array('label' => 'Дата изменения', 'widget' => 'single_text', 'format' => 'd.m.Y в H:i'))
			->add('position', null, array('label' => 'Позиция'))
            ->add('enabled', null, array('label' => 'Активна', 'template' => 'VidalDrugBundle:Sonata:swap_enabled_big_mama.html.twig'))
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