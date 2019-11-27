<?php
namespace Vidal\MainBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Form\FormMapper;

class BannerAdmin extends Admin
{
	protected $datagridValues;

	public function __construct($code, $class, $baseControllerName)
	{
		parent::__construct($code, $class, $baseControllerName);

		if (!$this->hasRequest()) {
			$this->datagridValues = array(
				'_page'       => 1,
				'_per_page'   => 25,
				'_sort_order' => 'DESC', // sort direction
				'_sort_by'    => 'created' // field name
			);
		}
	}

	protected function configureFormFields(FormMapper $formMapper)
	{
        $displayChoices = array(
            'logged' => 'Только зарегистрированным',
            'guest'  => 'Только незарегистрированным',
        );

		$formMapper
            ->add('group', null, array('label' => 'Баннерное место', 'required' => true))
            ->add('expired', null, array('label' => 'Заканчивается', 'help' => 'Оставьте пустым, если срок не ограничен'))
            ->add('maxClicks', null, array('label' => 'Максимально переходов', 'help' => 'Заполняется когда баннер надо скрывать при таком-то количестве переходов'))
            ->add('rotateWithPosition', null, array('label' => 'Ротировать по позиции'))
            ->add('rotateWithId', null, array('label' => 'Ротировать с баннером номер'))
            ->add('mobileRotateOnly', null, array('label' => 'Ротация баннеров только в мобильной версии', 'required' => false))
            ->add('topPriority', null, array('label' => 'Наивысший приоритет в группе', 'required' => false, 'help' => 'При установке этой галочки вместо всех баннеров группы выводиться будет ТОЛЬКО этот, если попал в выборку'))
            ->add('title', null, array('label' => 'Название баннера (для Google Analitics)', 'required' => true))
            ->add('link', null, array('label' => 'Ссылка', 'required' => true))
            ->add('atc', null, array('label' => 'Код АТХ', 'required' => false, 'help' => 'Выводится лишь для препаратов с этим кодом АТХ (с подкодами), можно перечислением через ;'))
            ->add('atcCodes', 'textarea', array('label' => 'Все коды АТХ', 'required' => false, 'read_only' => true))
            ->add('productIds', 'textarea', array('label' => 'Препараты по АТХ', 'required' => false, 'read_only' => true))
            ->add('products', 'textarea', array('label' => 'Отображать для препаратов', 'required' => false, 'help' => 'Выводится лишь для препаратов с перечисленными идентификаторами, через ;'))
            ->add('nosology', null, array('label' => 'Код МКБ-10', 'required' => false, 'help' => 'Выводится лишь для препаратов с этим кодом нозологии МКБ-10 (с подкодами)'))
            ->add('nosologyCodes', 'textarea', array('label' => 'Все коды МКБ-10', 'required' => false, 'read_only' => true))
            ->add('nosologyProductIds', 'textarea', array('label' => 'Препараты по МКБ-10', 'required' => false, 'read_only' => true))
            ->add('trackImageUrl', null, array('label' => 'URL-адрес трек-пикселя', 'required' => false, 'help' => 'Заполняется по желанию в дополнение к аналитике, для отслеживания посещения страницы, можно перечислением через ;'))
            ->add('alt', null, array('label' => 'ALT-тег', 'required' => false))
            ->add('forPage', 'text', array('label' => 'Для страницы', 'required' => false, 'help' => 'Баннер будет отображаться лишь на этих страницах. Адрес указывается БЕЗ использования протокола и домена. Варианты перечисляются через ; Пример: /drugs/maalox__42761; /novosti/*'))
            ->add('notForPage', 'text', array('label' => 'Не для страницы', 'required' => false, 'help' => 'Баннер будет скрываться на этих страницах. Адрес указывается БЕЗ использования протокола и домена. Варианты перечисляются через ; Пример: /drugs/maalox__42761; /novosti/*'))
            ->add('displayTo', 'choice', array('label' => 'Кому отображать', 'required' => false, 'choices' => $displayChoices, 'empty_value' => 'ВСЕМ'))
            ->add('enabled', null, array('label' => 'Активен', 'required' => false))
            ->add('opened', null, array('label' => 'Доступен всем', 'required' => false))
            ->add('specOnly', null, array('label' => 'Спец да/нет', 'required' => false, 'help' => 'Отображать промежуточную страницу (специалистам)'))
            ->add('testMode', null, array('label' => 'Тестовый режим', 'required' => false, 'help'=> 'Виден лишь при добавлении в конец URL хвоста ?t=t'))
            ->add('indexPage', null, array('label' => 'Отображать только на главной странице', 'required' => false))
            ->add('mobile', null, array('label' => 'Отображать в мобильной версии', 'required' => false))
            ->add('mobileProduct', null, array('label' => 'Посреди описания препарата в мобильной версии', 'required' => false, 'help' => 'Если проставлена галочка, то в мобильной версии будет выводиться не СНИЗУ, а перед блоком Яндекс.Директа в описании препарата'))
            ->add('banner', 'iphp_file', array('label' => 'Баннер', 'required' => true))
            ->add('width', null, array('label' => 'Ширина', 'required' => false, 'help' => 'Ширина баннера (если не указано, то берется ширина группы)'))
            ->add('height', null, array('label' => 'Высота', 'required' => false, 'help' => 'Высота баннера (если не указано, то берется высота группы)'))
            ->add('showEvent', null, array('label' => 'Событие показа баннера', 'required' => false, 'help' => 'Заполняется по желанию в дополнение к ивенту по умолчанию'))
            ->add('clickEvent', null, array('label' => 'Событие перехода баннера', 'required' => false, 'help' => 'Заполняется по желанию в дополнение к ивенту по умолчанию'))
            ->add('mobileBanner', 'iphp_file', array('label' => '[МОБИЛЬНЫЙ] Баннер', 'required' => false))
            ->add('titleMobile', null, array('label' => '[МОБИЛЬНЫЙ] Название баннера (для Google Analitics)', 'required' => false, 'help' => 'Опционально, если требуется для мобильной версии свой счетчик вести'))
            ->add('mobileWidth', null, array('label' => '[МОБИЛЬНЫЙ] Ширина', 'required' => false, 'help' => 'Ширина моб. баннера (если не указано, то берется ширина группы)'))
            ->add('mobileHeight', null, array('label' => '[МОБИЛЬНЫЙ] Высота', 'required' => false, 'help' => 'Высота моб. баннера (если не указано, то берется высота группы)'))
            ->add('linkMobile', null, array('label' => '[МОБИЛЬНЫЙ] Ссылка', 'required' => false))
            ->add('trackImageUrlMobile', null, array('label' => '[МОБИЛЬНЫЙ] URL-адрес трек-пикселя', 'required' => false, 'help' => 'Заполняется по желанию в дополнение к аналитике, для отслеживания посещения страницы'))
            ->add('htmlBanner', null, array('label' => 'html5 баннер', 'required' => false, 'help' => 'Учесть что в коде могут быть зависимости от внешних скриптов которые будут заблокированы'))
            ->add('mobileHtmlBanner', null, array('label' => '[МОБИЛЬНЫЙ] html5 баннер', 'required' => false, 'help' => 'Учесть что в коде могут быть зависимости от внешних скриптов которые будут заблокированы'))
        ;
	}

	protected function configureDatagridFilters(DatagridMapper $datagridMapper)
	{
		$datagridMapper
			->add('id')
            ->add('title', null, array('label' => 'Название баннера GA'))
			->add('link', null, array('label' => 'Ссылка'))
			->add('group', null, array('label' => 'Баннерное место'))
            ->add('mobile', null, array('label' => 'Отображать в мобильной версии'))
			->add('enabled', null, array('label' => 'Активен'));
	}

	protected function configureListFields(ListMapper $listMapper)
	{
		$listMapper
            ->add('title', null, array('label' => 'Название баннера GA'))
			->add('link', null, array('label' => 'Ссылка'))
			->add('group', null, array('label' => 'Баннерное место'))
			->add('clicks', null, array('label' => 'Переходов'))
			->add('enabled', null, array('label' => 'Активен', 'template' => 'VidalDrugBundle:Sonata:swap_enabled_main.html.twig'))
            ->add('mobile', null, array('label' => 'Отображать в мобильной версии', 'template' => 'VidalDrugBundle:Sonata:swap_mobile_main.html.twig'))
            ->add('created', null, array('label' => 'Ссылки', 'template' => 'VidalDrugBundle:Sonata:banner_stats.html.twig'))
            ->add('mobileProduct', null, array('label' => 'Отображать в описании препарата в мобильной версии', 'template' => 'VidalDrugBundle:Sonata:swap_mobileProduct_main.html.twig'))
            ->add('rotateWithId', null, array('label' => 'Ротируется с баннером номер'))
            ->add('rotateWithPosition', null, array('label' => 'Ротируется по позиции'))
            ->add('_action', 'actions', array(
				'label'   => 'Действия',
				'actions' => array(
					'edit'   => array(),
					'delete' => array(),
				)
			));
	}
}