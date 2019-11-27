<?php
namespace Vidal\MainBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;

class RbkMoneyAdmin extends Admin
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
				'_sort_by'    => 'id' // field name
			);
        }
	}

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->remove('create');
        $collection->remove('edit');
    }

	protected function configureShowField(ShowMapper $showMapper)
	{
		$showMapper
			->add('id')
			->add('eshopId', null, array('label' => 'Идентификатор магазина VIDAL в RBK'))
            ->add('orderId', null, array('label' => 'Идентификатор платежа'))
            ->add('product', null, array('label' => 'Товар'))
            ->add('price', null, array('label' => 'Цена для участника'))
            ->add('user_email', null, array('label' => 'Email участника'))
            ->add('sent', null, array('label' => 'Перешел на оплату в RbkMoney'))
            ->add('paid', null, array('label' => 'Оплата успешна в RbkMoney'))
            ->add('failed', null, array('label' => 'Оплата неудачна в RbkMoney'))
            ;
	}

	protected function configureDatagridFilters(DatagridMapper $datagridMapper)
	{
		$datagridMapper
			->add('id')
            ->add('eshopId', null, array('label' => 'Идентификатор магазина VIDAL в RBK', 'required' => false))
            ->add('orderId', null, array('label' => 'Идентификатор платежа', 'required' => false))
            ->add('product', null, array('label' => 'Товар', 'required' => false))
            ->add('price', null, array('label' => 'Цена для участника', 'required' => false))
            ->add('user_email', null, array('label' => 'Email участника', 'required' => false))
            ->add('sent', null, array('label' => 'Перешел на оплату в RbkMoney', 'required' => false))
            ->add('paid', null, array('label' => 'Оплата успешна в RbkMoney', 'required' => false))
            ->add('failed', null, array('label' => 'Оплата неудачна в RbkMoney', 'required' => false))
            ;
	}

	protected function configureListFields(ListMapper $listMapper)
	{
		$listMapper
			->add('id')
            ->add('orderId', null, array('label' => 'Идентификатор платежа'))
            ->add('user_email', null, array('label' => 'Email участника'))
            ->add('product', null, array('label' => 'Товар'))
            ->add('price', null, array('label' => 'Цена для участника'))
            ->add('sent', null, array('label' => 'Перешел на оплату в RbkMoney'))
            ->add('paid', null, array('label' => 'Оплата успешна в RbkMoney'))
            ->add('failed', null, array('label' => 'Оплата неудачна в RbkMoney'))
			->add('_action', 'actions', array(
				'label'   => 'Действия',
				'actions' => array(
					'show'   => array(),
					'delete' => array(),
				)
			));
	}
}