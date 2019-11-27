<?php

namespace Vidal\VeterinarBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;

class DocumentAdmin extends Admin
{
	// Fields to be shown on create/edit forms
	protected function configureFormFields(FormMapper $formMapper)
	{
		$articleChoices = array(
			'5' => 'Инструкция по применению лекарственного препарата',
			'3' => 'Короткие описания под торговыми наименованиями',
			'1' => 'Описания активных веществ',
			'6' => 'Описания БАДов',
			'4' => 'Официальная типовая клинико-фармакологическая статья',
			'2' => 'Полные описания под торговыми наименованиями',
		);

		$usingChoices = array(
			'Can'  => 'Возможно применение',
			'Care' => 'C осторожностью применяется',
			'Not'  => 'Противопоказан',
		);

		# новому продукту можно проставить идентификатор
		if (!$this->getSubject()->getDocumentID()) {
			$formMapper->add('DocumentID', null, array('label' => 'ID документа', 'required' => true));
		}

		$formMapper
			->add('RusName', null, array('label' => 'Название', 'required' => true))
			->add('ArticleID', null, array('label' => 'Тип документа', 'help' => 'ArticleID'))
			->add('YearEdition', null, array('label' => 'Год выпуска', 'required' => true))
			->add('CompiledComposition', null, array('label' => 'Описание', 'required' => false, 'attr' => array('class' => 'ckeditorfull')))
			->add('CompaniesDescription', null, array('label' => 'Описание компаний', 'required' => false, 'attr' => array('class' => 'ckeditorfull')))
			->add('ClPhGrDescription', null, array('label' => 'Клинико-фарм. группа', 'required' => false, 'attr' => array('class' => 'ckeditorfull')))
			->add('PhInfluence', null, array('label' => 'Фарм. действие', 'required' => false, 'attr' => array('class' => 'ckeditorfull')))
			->add('PhKinetics', null, array('label' => 'Фармакокинетика', 'required' => false, 'attr' => array('class' => 'ckeditorfull')))
			->add('Dosage', null, array('label' => 'Режим дозирования', 'required' => false, 'attr' => array('class' => 'ckeditorfull')))
			->add('OverDosage', null, array('label' => 'Передозировка', 'required' => false, 'attr' => array('class' => 'ckeditorfull')))
			->add('Interaction', null, array('label' => 'Лекарственное взаимодействие', 'required' => false, 'attr' => array('class' => 'ckeditorfull')))
			->add('Lactation', null, array('label' => 'Применение при беременности и кормлении грудью', 'required' => false, 'attr' => array('class' => 'ckeditorfull')))
			->add('SideEffects', null, array('label' => 'Побочное действие', 'required' => false, 'attr' => array('class' => 'ckeditorfull')))
			->add('StorageCondition', null, array('label' => 'Условия и сроки хранения', 'required' => false, 'attr' => array('class' => 'ckeditorfull')))
			->add('Indication', null, array('label' => 'Показания', 'required' => false, 'attr' => array('class' => 'ckeditorfull')))
			->add('ContraIndication', null, array('label' => 'Противопоказания', 'required' => false, 'attr' => array('class' => 'ckeditorfull')))
			->add('PharmDelivery', null, array('label' => 'Условия отпуска из аптек', 'required' => false, 'attr' => array('class' => 'ckeditorfull')))
			->add('SpecialInstruction', null, array('label' => 'Особые указания', 'required' => false, 'attr' => array('class' => 'ckeditorfull')))
			->add('infoPages', null, array('label' => 'Представительства', 'required' => false))
			->add('molecules', null, array('label' => 'Активные вещества', 'required' => false));
	}

	// Fields to be shown on filter forms
	protected function configureDatagridFilters(DatagridMapper $datagridMapper)
	{
		$datagridMapper
			->add('RusName');
	}

	// Fields to be shown on lists
	protected function configureListFields(ListMapper $listMapper)
	{
		$listMapper
			->addIdentifier('DocumentID', null, array('label' => 'ID'))
			->add('RusName', null, array('label' => 'Название на русском', 'template' => 'VidalDrugBunde:Sonata:RusName.html.twig'))
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