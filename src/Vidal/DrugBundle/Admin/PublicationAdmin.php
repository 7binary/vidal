<?php

namespace Vidal\DrugBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Doctrine\ORM\EntityRepository;
use Vidal\DrugBundle\Entity\Publication;
use Vidal\DrugBundle\Transformer\DocumentTransformer;
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
				'_sort_by'    => 'date'
			);
		}
	}

	public function createQuery($context = 'list')
	{
		$qb = $this->getModelManager()->getEntityManager($this->getClass())->createQueryBuilder();
		$qb->select('a')->from($this->getClass(), 'a');

		if (!isset($_GET['filter']['_sort_by']) || $_GET['filter']['_sort_by'] == 'created') {
			$order = isset($_GET['filter']['_sort_order']) ? $_GET['filter']['_sort_order'] : 'DESC';
			$qb->orderBy('a.date', $order)->addOrderBy('a.id', 'ASC');
		}

		$proxyQuery = new \Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery($qb);

		return $proxyQuery;
	}

	protected function configureShowField(ShowMapper $showMapper)
	{
		$showMapper
			->add('id')
			->add('title', null, array('label' => 'Заголовок'))
            ->add('link', null, array('label' => 'URL-адрес'))
			->add('announce', null, array('label' => 'Анонс'))
			->add('body', null, array('label' => 'Основное содержимое'))
			->add('bodyMobile', null, array('label' => 'Cодержимое для мобильного приложения'))
			->add('enabled', null, array('label' => 'Активна'))
			->add('mobile', null, array('label' => 'Для мобильного приложения'))
            ->add('push', null, array('label' => 'Для пуш-уведомления'))
            ->add('invisible', null, array('label' => 'Скрыта (доступна тестовому пользователю)'))
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
		$em                  = $this->getModelManager()->getEntityManager($this->getSubject());
		$tagTransformer      = new TagTransformer($em, $this->getSubject());

		$formMapper
			->add('photo', 'iphp_file', array('label' => 'Фотография', 'required' => false))
			->add('title', 'textarea', array('label' => 'Заголовок', 'required' => true, 'attr' => array('class' => 'ckeditormizer')))
            ->add('linkManual', null, array('label' => 'URL-адрес вручную', 'required' => false))
            ->add('link', null, array('label' => 'URL-адрес авто', 'required' => false, 'disabled' => true))
            ->add('priority', null, array('label' => 'Приоритет', 'required' => false, 'help' => 'Закреплено на главной по приоритету. Оставьте пустым, чтоб снять приоритет'))
            ->add('project', 'choice', array('label' => 'Раздел на сайте', 'required' => false, 'choices' => Publication::getProjectOptions()))
            ->add('categories', 'sonata_type_model', array('label' => 'Проекты', 'required' => false, 'multiple' => true, 'by_reference' => false))
            ->add('announce', null, array('label' => 'Анонс', 'required' => false, 'attr' => array('class' => 'ckeditorfull')))
			->add('body', null, array('label' => 'Основное содержимое', 'required' => true, 'attr' => array('class' => 'ckeditorfull')))
			->add('bodyMobile', null, array('label' => 'Cодержимое для мобильного приложения', 'required' => false, 'attr' => array('class' => 'ckeditorfull',  'help' => 'Оставьте пустым, если содержимое совпадает с основым.')))
			->add('tags', null, array('label' => 'Теги', 'required' => false, 'help' => 'Выберите существующие теги или добавьте новый ниже'))
			->add($formMapper->create('hidden', 'text', array(
				'label'        => 'Создать теги через ;',
				'required'     => false,
				'by_reference' => false,
			))->addModelTransformer($tagTransformer)
			)
			->add('atcCodes-text', 'text', array('label' => 'Коды АТХ', 'required' => false, 'mapped' => false, 'attr' => array('class' => 'atcCodes-text', 'placeholder' => 'Начните вводить название или код')))
			->add('nozologies-text', 'text', array('label' => 'Заболевания МКБ-10', 'required' => false, 'mapped' => false, 'attr' => array('class' => 'nozologies-text', 'placeholder' => 'Начните вводить название или код')))
			->add('molecules-text', 'text', array('label' => 'Активные вещества', 'required' => false, 'mapped' => false, 'attr' => array('class' => 'molecules-text', 'placeholder' => 'Начните вводить название или код')))
			->add('infoPages-text', 'text', array('label' => 'Представительства', 'required' => false, 'mapped' => false, 'attr' => array('class' => 'infoPages-text', 'placeholder' => 'Начните вводить название')))
			->add('products-text', 'text', array('label' => 'Описания препаратов', 'required' => false, 'mapped'=>false, 'attr' => array('class' => 'doc')))
			->add('date', null, array('label' => 'Дата создания', 'required' => true, 'years' => range(2000, date('Y'))))
			->add('videos', 'sonata_type_collection',
				array(
					'label'              => 'Видео файлы',
					'by_reference'       => false,
					'cascade_validation' => true,
					'required'           => false,
				),
				array(
					'edit'         => 'inline',
					'inline'       => 'table',
					'allow_delete' => true
				)
			)
			->add('code', null, array('label' => 'Дополнительный код', 'required' => false))
			->add('testMode', null, array('label' => 'В режиме тестирования', 'required' => false, 'help' => 'видно только если в конец url-адреса дописать ?test или для тестового аккаунта'))
			->add('sticked', null, array('label' => 'Закреплена', 'required' => false))
            ->add('disableLinks', null, array('label' => 'Отключить перелинковку', 'required' => false))
            ->add('enabled', null, array('label' => 'Активна', 'required' => false))
			->add('mobile', null, array('label' => 'Для мобильного приложения', 'required' => false))
            ->add('invisible', null, array('label' => 'Скрыта (доступна тестовому пользователю)', 'required' => false))
            ->add('push', null, array('label' => 'Для пуш-уведомления КАРДИО', 'required' => false, 'disabled' => true))
            ->add('pushNeuro', null, array('label' => 'Для пуш-уведомления НЕЙРО', 'required' => false, 'disabled' => true))
            ->add('pushVeterinary', null, array('label' => 'Для пуш-уведомления ВЕТЕРИНАРИИ', 'required' => false, 'disabled' => true))
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
			->add('priority', null, array('label' => 'Приоритет'))
			->add('testMode', null, array('label' => 'В режиме тестирования'))
			->add('sticked', null, array('label' => 'Закреплена'))
			->add('enabled', null, array('label' => 'Активна'))
			->add('mobile', null, array('label' => 'Для мобильного приложения'))
            ->add('push', null, array('label' => 'Для пуш-уведомления'));
	}

	protected function configureListFields(ListMapper $listMapper)
	{
		$listMapper
			->add('id')
			->add('title', null, array('label' => 'Заголовок'))
			->add('tags', null, array('label' => 'Теги', 'template' => 'VidalDrugBundle:Sonata:tags.html.twig'))
			->add('date', null, array('label' => 'Дата создания', 'widget' => 'single_text', 'format' => 'd.m.Y в H:i'))
			->add('updated', null, array('label' => 'Дата изменения', 'widget' => 'single_text', 'format' => 'd.m.Y в H:i'))
			->add('priority', null, array('label' => 'Приоритет'))
            ->add('project', null, array('label' => 'Проект'))
			->add('sticked', null, array('label' => 'Закреплена', 'template' => 'VidalDrugBundle:Sonata:swap_sticked.html.twig'))
			->add('mobile', null, array('label' => 'Для мобильного приложения', 'template' => 'VidalDrugBundle:Sonata:swap_mobile.html.twig'))
            ->add('push', null, array('label' => 'Для пуш-уведомления КАРДИО', 'template' => 'VidalDrugBundle:Sonata:swap_push.html.twig'))
            ->add('pushNeuro', null, array('label' => 'Для пуш-уведомления НЕЙРО', 'template' => 'VidalDrugBundle:Sonata:swap_pushNeuro.html.twig'))
            ->add('pushVeterinary', null, array('label' => 'Для пуш-уведомления ВЕТЕРИНАРИИ', 'template' => 'VidalDrugBundle:Sonata:swap_pushVeterinary.html.twig'))
            ->add('invisible', null, array('label' => 'Скрыта (доступна тестовому пользователю)', 'template' => 'VidalDrugBundle:Sonata:swap_invisible.html.twig'))
            ->add('disableLinks', null, array('label' => 'Отключить перелиновку', 'template' => 'VidalDrugBundle:Sonata:swap_disableLinks.html.twig'))
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