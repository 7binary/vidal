<?php
namespace Vidal\VeterinarBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Vidal\VeterinarBundle\Transformer\DocumentToStringTransformer;

class ProductAdmin extends Admin
{
	protected function configureFormFields(FormMapper $formMapper)
	{
		$subject            = $this->getSubject();
		$em                 = $this->getModelManager()->getEntityManager($subject);
		$transformer        = new DocumentToStringTransformer($em, $subject);
		$pt = array(
			'ALRG' => 'Аллерген',
			'BAD'  => 'Биологически активная добавка',
			'GOME' => 'Гомеопатическое средство',
			'DIAG' => 'Диагностикум',
			'DRUG' => 'Лекарственный препарат',
			'MI'   => 'Мед. изделие',
			'SRED' => 'Питательная среда',
			'SUBS' => 'Субстанция',
		);

		# новому продукту можно проставить идентификатор
		if (!$this->getSubject()->getProductID()) {
			$formMapper
				->add('ProductID', null, array('label' => 'ID продукта', 'required' => true));
		}

		$formMapper
			->add('RusName', null, array('label' => 'Название', 'required' => true))
			->add('EngName', null, array('label' => 'Латинское', 'required' => true))
			->add('Name', 'text', array('label' => 'URL адрес', 'required' => true))
			->add($formMapper->create('document', 'text', array(
				'label'        => 'ID документа',
				'required'     => false,
				'by_reference' => true,
			))->addModelTransformer($transformer))
			->add('ProductTypeCode', 'choice', array('label' => 'Тип препарата', 'required' => true, 'choices' => $pt))
			->add('MarketStatusID', null, array('label' => 'Статус', 'required' => true))
			->add('ZipInfo', null, array('label' => 'Форма выпуска', 'required' => true))
			->add('photo', 'iphp_file', array('label' => 'Фотография временная', 'required' => false))
            ->add('photo2', 'iphp_file', array('label' => 'Фотография временная', 'required' => false))
            ->add('photo3', 'iphp_file', array('label' => 'Фотография временная', 'required' => false))
            ->add('photo4', 'iphp_file', array('label' => 'Фотография временная', 'required' => false))
            ->add('Composition', null, array('label' => 'Описание', 'required' => false, 'attr' => array('class' => 'ckeditorfull')))
			->add('RegistrationDate', null, array('label' => 'Дата регистрации', 'years' => range(2000, 2030)))
			->add('RegistrationNumber', null, array('label' => 'Номер регистрации'))
			->add('NonPrescriptionDrug', null, array('label' => 'Безрецептурный', 'required' => false))
			->add('StrongMeans', null, array('label' => 'Сильнодействующий', 'required' => false))
			->add('Poison', null, array('label' => 'Ядовитый', 'required' => false))
			->add('GNVLS', null, array('label' => 'ЖНВЛП', 'required' => false))
			->add('DLO', null, array('label' => 'ДЛО', 'required' => false))
			->add('ValidPeriod', null, array('label' => 'Срок действия', 'required' => false))
			->add('StrCond', null, array('label' => 'Условия хранения', 'required' => false))
			->add('moleculeNames', null, array('label' => 'Активные вещества', 'required' => false))
			->add('productCompany', 'sonata_type_collection',
				array(
					'label'              => 'Компании',
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
			->add('hidePhoto', null, array('label' => 'Скрывать фотографию', 'required' => false))
			->add('inactive', null, array('label' => 'Отключить', 'required' => false, 'help' => 'Исключить препарат из списков выдачи'));
	}

	// Fields to be shown on filter forms
	protected function configureDatagridFilters(DatagridMapper $datagridMapper)
	{
		$datagridMapper
			->add('ProductID', null, array('label' => 'ID'))
			->add('RusName', null, array('label' => 'Название', 'required' => true))
            ->add('RusName2', null, array('label' => 'Название без специальных символов', 'required' => true))
            ->add('EngName', null, array('label' => 'Латинское'))
			->add('MarketStatusID', null, array('label' => 'Статус'))
			->add('ZipInfo', null, array('label' => 'Форма выпуска'))
			->add('RegistrationDate', null, array('label' => 'Дата регистр.'))
			->add('inactive', null, array('label' => 'Отключен'));
	}

	// Fields to be shown on lists
	protected function configureListFields(ListMapper $listMapper)
	{
		$listMapper
			->addIdentifier('ProductID', null, array('label' => 'ID'))
			->add('RusName', null, array('label' => 'Название', 'template' => 'VidalDrugBundle:Sonata:RusName.html.twig'))
			->add('EngName', null, array('label' => 'Латинское', 'template' => 'VidalDrugBundle:Sonata:EngName.html.twig'))
			->add('MarketStatusID', null, array('label' => 'Статус'))
			->add('ZipInfo', null, array('label' => 'Форма выпуска'))
			->add('RegistrationDate', null, array('label' => 'Дата регистр.'))
			->add('_action', 'actions', array(
				'label'   => 'Действия',
				'actions' => array(
					'edit'   => array(),
					'delete' => array(),
				)
			));
	}
}