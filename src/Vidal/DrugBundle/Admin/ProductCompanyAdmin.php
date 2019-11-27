<?php
namespace Vidal\DrugBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Doctrine\ORM\EntityRepository;

class ProductCompanyAdmin extends Admin
{
	protected function configureFormFields(FormMapper $formMapper)
	{
		$formMapper
			->add('CompanyID', null, array('label' => 'Компания', 'required' => true))
			->add('CompanyRusNote', null, array('label' => 'Форма дистрибьюции', 'required' => false))
            ->add('Ranking', null, array('label' => 'Позиция в списке', 'required' => false))
			->add('ItsMainCompany', null, array('label' => 'Владелец рег.уд.', 'required' => false))
		;
	}
}