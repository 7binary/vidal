<?php
namespace Vidal\MainBundle\Admin;

use Doctrine\ORM\EntityManager;
use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Vidal\MainBundle\Form\DataTransformer\YearToNumberTransformer;
use Vidal\MainBundle\Form\DataTransformer\CityToStringTransformer;
use Doctrine\ORM\EntityRepository;

class UserAdmin extends Admin
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
		$em                      = $this->modelManager->getEntityManager('Vidal\MainBundle\Entity\User');
		$yearToNumberTransformer = new YearToNumberTransformer($em);
		$cityToStringTransformer = new CityToStringTransformer($em);

		$formMapper
			->add('username', null, array('label' => 'E-mail', 'required' => true))
			->add('oldLogin', null, array('label' => 'Логин', 'required' => false))
			->add('avatar', 'iphp_file', array('label' => 'Аватарка', 'required' => false))
			->add('firstName', null, array('label' => 'Имя', 'required' => true))
			->add('lastName', null, array('label' => 'Фамилия', 'required' => true))
			->add('surName', null, array('label' => 'Отчество', 'required' => false))
			->add('confirmationScan', 'iphp_file', array('label' => 'Файл подтверждения', 'required' => false))
			->add('confirmation', null, array('label' => 'Подтвержденный', 'required' => false))

			->add($formMapper->create('city', 'text', array(
				'label' => 'Город',
			))->addModelTransformer($cityToStringTransformer))
			->add('emailConfirmed', null, array('label' => 'e-mail подтвержден', 'required' => false))
			->add('specialization', null, array('label' => 'Специализация', 'required' => false))
			->add('primarySpecialty', null, array('label' => 'Основная специальность', 'required' => false))
			->add('university', null, array('label' => 'ВУЗ', 'required' => false))
			->add('school', null, array('label' => 'Учебное заведение'))
			->add($formMapper->create('graduateYear', 'text', array(
				'label'    => 'Год выпуска',
				'required' => false,
			))->addModelTransformer($yearToNumberTransformer))
			->add('educationType', null, array('label' => 'Форма обучения', 'required' => false))
			->add('academicDegree', null, array('label' => 'Ученая степень', 'required' => false))
			->add('birthdate', null, array('label' => 'Дата рождения', 'required' => false, 'widget' => 'single_text'))
			->add('icq', null, array('label' => 'ICQ', 'required' => false))
			->add('dissertation', null, array('label' => 'Тема диссертации', 'required' => false))
			->add('professionalInterests', null, array('label' => 'Профессиональные интересы', 'required' => false))
			->add('jobPlace', null, array('label' => 'Место работы', 'required' => false))
			->add('jobSite', null, array('label' => 'Сайт', 'required' => false))
			->add('jobPosition', null, array('label' => 'Должность', 'required' => false))
			->add('jobStage', null, array('label' => 'Стаж работы по специальности', 'required' => false))
			->add('about', null, array('label' => 'О себе', 'required' => false))
			->add('jobPublications', null, array('label' => 'Публикации', 'required' => false))
			->add('oldUser', null, array('label' => 'Со старого сайта', 'required' => false))
			->add('digestSubscribed', null, array('label' => 'Подписан на рассылку', 'required' => false))
			->add('created', null, array('label' => 'Зарегистрировался', 'required' => false))
            ->add('androidId', null, array('label' => 'Идентификатор устройства Android', 'required' => false));
	}

	protected function configureDatagridFilters(DatagridMapper $datagridMapper)
	{
        /** @var EntityManager $em */
		$em             = $this->modelManager->getEntityManager('Vidal\MainBundle\Entity\User');
		$cityChoices    = $em->getRepository('VidalMainBundle:City')->getChoices();
		$regionChoices  = $em->getRepository('VidalMainBundle:Region')->getChoices();
		$countryChoices = $em->getRepository('VidalMainBundle:Country')->getChoices();

		$datagridMapper
			->add('id')
			->add('username', null, array('label' => 'E-mail'))
			->add('lastName', null, array('label' => 'Фамилия'))
			->add('primarySpecialty', null, array('label' => 'Основная специальность'))
			->add('city', 'doctrine_orm_choice', array('label' => 'Город'), 'choice', array('choices' => $cityChoices))
			->add('region', 'doctrine_orm_choice', array('label' => 'Область'), 'choice', array('choices' => $regionChoices))
			->add('country', 'doctrine_orm_choice', array('label' => 'Страна'), 'choice', array('choices' => $countryChoices))
			->add('emailConfirmed', null, array('label' => 'E-mail подтвержден'))
            ->add('mail_delete', null, array('label' => 'E-mail недоступен'))
			->add('digestSubscribed', null, array('label' => 'Подписан на рассылку'))
			->add('oldUser', null, array('label' => 'Со старого сайта'))
			->add('confirmationHas', null, array('label' => 'Со сканами'));
	}

	protected function configureListFields(ListMapper $listMapper)
	{
		$listMapper
			->add('id')
			->add('username', null, array('label' => 'E-mail'))
			->add('emailConfirmed', null, array('label' => 'Подтвердил', 'template' => 'VidalDrugBundle:Sonata:swap_emailConfirmed.html.twig'))
			->add('lastName', null, array('label' => 'Фамилия И.О.', 'template' => 'VidalDrugBundle:Sonata:user_fio.html.twig'))
			->add('primarySpecialty', null, array('label' => 'Основная специальность'))
			->add('birthdate', null, array('label' => 'Дата рождения', 'widget' => 'single_text', 'format' => 'd.m.Y'))
			->add('city', null, array('label' => 'Город'))
			->add('region', null, array('label' => 'Область'))
			->add('created', null, array('label' => 'Зарегистрировался', 'widget' => 'single_text', 'format' => 'd.m.Y в H:i'))
			->add('oldUser', null, array('label' => 'Со старого сайта'))
			->add('mail_delete', null, array('label' => 'Была ошибка недоставки'))
            ->add('mail_delete_counter', null, array('label' => 'Всего ошибок недоставки'))
			->add('_action', 'actions', array(
				'label'   => 'Действия',
				'actions' => array(
					'edit' => array(),
                    'delete' => array(),
				)
			));
	}
}