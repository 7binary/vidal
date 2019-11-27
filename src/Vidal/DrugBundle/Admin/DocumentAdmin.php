<?php
// src/Vidal/DrugBundle/Admin/PostAdmin.php

namespace Vidal\DrugBundle\Admin;

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
            '7' => 'Медицинские изделия',
            '9' => 'Лечебное питание',
            '10' => 'Лечебная косметика',
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
            ->add('inactive', null, array('label' => 'Отключить', 'required' => false))
            ->add('IsApproved', null, array('label' => 'Одобренный', 'required' => false))
            ->add('IsShortened', null, array('label' => 'Cокращенное описание', 'required' => false))
			->add('RusName', null, array('label' => 'Название', 'required' => true))
			->add('EngName', null, array('label' => 'Латинское', 'required' => true))
			->add('Name', 'text', array('label' => 'URL адрес', 'required' => true))
			->add('ArticleID', 'choice', array('label' => 'Тип документа', 'help' => 'ArticleID', 'required' => true, 'choices' => $articleChoices))
			->add('YearEdition', null, array('label' => 'Год выпуска', 'required' => true))
            ->add('IsShortened', null, array('label' => 'Cокращенное описание', 'required' => false))
            ->add('atcCodes-text', 'text', array('label' => 'Коды АТХ', 'required' => false, 'mapped' => false, 'attr' => array('class' => 'atcCodes-text', 'placeholder' => 'Начните вводить название или код')))
            ->add('nozologies-text', 'text', array('label' => 'Заболевания МКБ-10', 'required' => false, 'mapped' => false, 'attr' => array('class' => 'nozologies-text', 'placeholder' => 'Начните вводить название или код')))
            ->add('clphgpointers-text', 'text', array('label' => 'КФУ', 'required' => false, 'mapped' => false, 'attr' => array('class' => 'clphpointers-text', 'placeholder' => 'Клинико-фармакологические указатели. Начните вводить название или ID')))
            ->add('infoPages-text', 'text', array('label' => 'Представительства', 'required' => false, 'mapped' => false, 'attr' => array('class' => 'infoPages-text', 'placeholder' => 'Начните вводить название')))
            ->add('molecules-text', 'text', array('label' => 'Активные вещества', 'required' => false, 'mapped' => false, 'attr' => array('class' => 'molecules-text', 'placeholder' => 'Начните вводить название или код')))
            ->add('ApproveCode', null, array('label' => 'ApproveCode', 'required' => false))
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
			->add('PregnancyUsing', 'choice', array('label' => 'При беременности', 'required' => false, 'choices' => $usingChoices, 'empty_value' => 'выберите'))
			->add('NursingUsing', 'choice', array('label' => 'При кормлении грудью', 'required' => false, 'choices' => $usingChoices, 'empty_value' => 'выберите'))
			->add('RenalInsufUsing', 'choice', array('label' => 'При нарушениях функции почек', 'required' => false, 'choices' => $usingChoices, 'empty_value' => 'выберите'))
			->add('RenalInsuf', null, array('label' => 'Нарушения функции почек', 'required' => false, 'attr' => array('class' => 'ckeditorfull')))
			->add('HepatoInsufUsing', 'choice', array('label' => 'При нарушениях функции печени', 'required' => false, 'choices' => $usingChoices, 'empty_value' => 'выберите'))
			->add('HepatoInsuf', null, array('label' => 'Нарушение функции печени', 'required' => false, 'attr' => array('class' => 'ckeditorfull')))
			->add('ElderlyInsufUsing', 'choice', array('label' => 'Примение пожилыми пациентами', 'required' => false, 'choices' => $usingChoices, 'empty_value' => 'выберите'))
			->add('ElderlyInsuf', null, array('label' => 'Использование пожилыми пациентами', 'required' => false, 'attr' => array('class' => 'ckeditorfull')))
			->add('ChildInsufUsing', 'choice', array('label' => 'Применение детьми', 'required' => false, 'choices' => $usingChoices, 'empty_value' => 'выберите'))
			->add('ChildInsuf', null, array('label' => 'Использование детьми', 'required' => false, 'attr' => array('class' => 'ckeditorfull')))
            ;
	}

	// Fields to be shown on filter forms
	protected function configureDatagridFilters(DatagridMapper $datagridMapper)
	{
		$datagridMapper
            ->add('DocumentID', null, array('label' => 'ID документа'))
			->add('RusName', null, array('label' => 'Название документа'))
            ->add('EngName', null, array('label' => 'Латинское название документа'))
			->add('ArticleID', null, array('label' => 'Тип документа'))
            ->add('IsShortened', null, array('label' => 'Cокращенное описание'))
            ->add('IsApproved', null, array('label' => 'Одобренный'))
            ->add('inactive', null, array('label' => 'Отключен'))
            ->add('YearEdition', null, array('label' => 'Год выпуска'))
            ;
	}

	// Fields to be shown on lists
	protected function configureListFields(ListMapper $listMapper)
	{
		$listMapper
			->addIdentifier('DocumentID', null, array('label' => 'ID'))
			->add('RusName', null, array('label' => 'Название на русском', 'template' => 'VidalDrugBunde:Sonata:RusName.html.twig'))
			->add('EngName', null, array('label' => 'Название на англиском', 'template' => 'VidalDrugBunde:Sonata:EngName.html.twig'))
            ->add('inactive', null, array('label' => 'Отключен', 'template' => 'VidalDrugBundle:Sonata:swap_inactive.html.twig'))
            ->add('IsApproved', null, array('label' => 'Одобренный', 'template' => 'VidalDrugBundle:Sonata:swap_IsApproved.html.twig'))
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