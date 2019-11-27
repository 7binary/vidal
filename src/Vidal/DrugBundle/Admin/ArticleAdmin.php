<?php

namespace Vidal\DrugBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Doctrine\ORM\EntityRepository;
use Vidal\DrugBundle\Transformer\DocumentTransformer;
use Vidal\DrugBundle\Transformer\TagTransformer;

class ArticleAdmin extends Admin
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

	protected function configureFormFields(FormMapper $formMapper)
	{
		$em                  = $this->getModelManager()->getEntityManager($this->getSubject());
		$subject             = $this->getSubject();
		$tagTransformer      = new TagTransformer($em, $subject);

		$formMapper
			->add('title', 'textarea', array('label' => 'Заголовок', 'required' => true, 'attr' => array('class' => 'ckeditormizer')))
			->add('link', null, array('label' => 'Адрес страницы', 'required' => false, 'help' => 'латинские буквы и цифры, слова через тире. Оставьте пустым для автогенерации'))
			->add('rubrique', null, array(
				'label'         => 'Рубрика',
				'required'      => true,
				'empty_value'   => 'выберите',
				'query_builder' => function (EntityRepository $er) {
					return $er->createQueryBuilder('r')->orderBy('r.title', 'ASC');
				},
			))
            ->add('category', null, array('label' => 'Подраздел', 'required' => false))
			->add('type', null, array('label' => 'Категория', 'required' => false, 'empty_value' => 'не указано'))
			->add('priority', null, array('label' => 'Приоритет на главной', 'required' => false, 'help' => 'Закреплено на главной по приоритету. Оставьте пустым, чтоб снять приоритет'))
            ->add('listPriority', null, array('label' => 'Приоритет в списке', 'required' => false, 'help' => 'Приоритет в списке. Оставьте пустым, чтоб снять приоритет'))
			->add('authors', null, array('label' => 'Авторы', 'required' => false, 'attr' => array('class' => 'ckeditorfull')))
            ->add('announce', null, array('label' => 'Анонс', 'required' => false, 'attr' => array('class' => 'ckeditorfull')))
			->add('body', null, array('label' => 'Основное содержимое', 'required' => true, 'attr' => array('class' => 'ckeditorfull')))
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
			->add('synonym', null, array('label' => 'Синонимы', 'required' => false, 'help' => 'Через ;'))
			->add('metaTitle', null, array('label' => 'Мета заголовок', 'required' => false))
			->add('metaDescription', null, array('label' => 'Мета описание', 'required' => false))
			->add('metaKeywords', null, array('label' => 'Мета ключевые слова', 'required' => false))
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
			->add('links', 'sonata_type_collection',
				array(
					'label'        => 'Ссылки Купить в интернет-аптеке',
					'required'     => false,
					'by_reference' => false,
					'type_options' => array('btn_delete' => true, 'btn_add' => true),
				),
				array(
					'edit'   => 'inline',
					'inline' => 'table'
				)
			)
			->add('anons', null, array('label' => 'Отображать в анонсе', 'required' => false))
			->add('anonsPriority', null, array('label' => 'Приоритет в анонсе'))
			->add('code', null, array('label' => 'Дополнительный код', 'required' => false))
			->add('testMode', null, array('label' => 'В режиме тестирования', 'required' => false, 'help' => 'видно только если в конец url-адреса дописать ?test'))
            ->add('invisible', null, array('label' => 'Скрыта (доступна тестовому пользователю)', 'required' => false))
            ->add('disableExport', null, array('label' => 'Не для экпорта', 'required' => false))
            ->add('disableLinks', null, array('label' => 'Отключить перелинковку', 'required' => false))
            ->add('enabled', null, array('label' => 'Активна', 'required' => false));
	}

	protected function configureDatagridFilters(DatagridMapper $datagridMapper)
	{
		$datagridMapper
			->add('id')
			->add('title', null, array('label' => 'Заголовок'))
			->add('link', null, array('label' => 'Адрес страницы'))
			->add('rubrique', null, array(
				'label'         => 'Рубрика',
				'query_builder' => function (EntityRepository $er) {
					return $er->createQueryBuilder('r')
						->orderBy('r.title', 'ASC');
				},
			))
            ->add('category', null, array('label' => 'Подраздел'))
			->add('type', null, array('label' => 'Категория'))
			->add('priority', null, array('label' => 'Приоритет'))
            ->add('anons', null, array('label' => 'Отображать в анонсе', 'help' => 'В разделе специалистам'))
			->add('anonsPriority', null, array('label' => 'Приоритет в анонсе'))
			->add('testMode', null, array('label' => 'В режиме тестирования'))
            ->add('disableExport', null, array('label' => 'Не для экпорта'))
            ->add('disableLinks', null, array('label' => 'Отключить перелиновку', 'template' => 'VidalDrugBundle:Sonata:swap_disableLinks.html.twig'))
            ->add('enabled', null, array('label' => 'Активна'));
	}

	protected function configureListFields(ListMapper $listMapper)
	{
		$listMapper
			->add('id')
			->add('title', null, array('label' => 'Заголовок'))
			->add('link', null, array('label' => 'Адрес страницы', 'help' => 'латинские буквы и цифры, слова через тире'))
			->add('rubrique', null, array('label' => 'Рубрика'))
            ->add('category', null, array('label' => 'Подраздел'))
			->add('type', null, array('label' => 'Категория'))
			->add('tags', null, array('label' => 'Теги', 'template' => 'VidalDrugBundle:Sonata:tags.html.twig'))
			->add('anons', null, array('label' => 'в анонсе', 'template' => 'VidalDrugBundle:Sonata:swap_anons.html.twig'))
			->add('anonsPriority', null, array('label' => 'Приоритет в анонсе'))
			->add('date', null, array('label' => 'Дата создания', 'widget' => 'single_text', 'format' => 'd.m.Y в H:i'))
			->add('updated', null, array('label' => 'Дата изменения', 'widget' => 'single_text', 'format' => 'd.m.Y в H:i'))
            ->add('invisible', null, array('label' => 'Скрыта (доступна тестовому пользователю)', 'template' => 'VidalDrugBundle:Sonata:swap_invisible.html.twig'))
            ->add('disableExport', null, array('label' => 'Не для экпорта', 'template' => 'VidalDrugBundle:Sonata:swap_disableExport.html.twig'))
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
