<?php
namespace Vidal\MainBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;

class LinkAdmin extends Admin
{
	protected $datagridValues;

	public function __construct($code, $class, $baseControllerName)
	{
		parent::__construct($code, $class, $baseControllerName);

		if (!$this->hasRequest()) {
			$this->datagridValues = array(
				'_page'       => 1,
				'_per_page'   => 25,
				'_sort_order' => 'ASC', // sort direction
				'_sort_by'    => 'id' // field name
			);
        }
	}

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->remove('create');
    }

	protected function configureShowField(ShowMapper $showMapper)
	{
		$showMapper
			->add('id')
			->add('text', null, array('label' => 'Список ссылок для исключения "nofollow"'));
	}

	protected function configureFormFields(FormMapper $formMapper)
	{
		$formMapper
            ->add('text', null, array('label' => 'Список ссылок для исключения "nofollow"', 'attr' => array('class'=>'ckeditorfull')));
	}

	protected function configureDatagridFilters(DatagridMapper $datagridMapper)
	{
		$datagridMapper
			->add('id');
	}

	protected function configureListFields(ListMapper $listMapper)
	{
		$listMapper
			->add('id')
            ->add('text', 'raw', array('label' => 'Список'))
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