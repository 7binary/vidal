<?php

namespace Vidal\MainBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Doctrine\ORM\EntityRepository;

class ProtecRegionAdmin extends Admin
{
    protected $datagridValues;

    public function __construct($code, $class, $baseControllerName)
    {
        parent::__construct($code, $class, $baseControllerName);

        if (!$this->hasRequest()) {
            $this->datagridValues = array(
                '_page' => 1,
                '_per_page' => 25,
                '_sort_order' => 'DESC',
                '_sort_by' => 'id'
            );
        }
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('title', null, array('label' => 'Регион аптеки', 'required' => false))
            ->add('title2', null, array('label' => 'Альтернативное название 1', 'required' => false))
            ->add('title3', null, array('label' => 'Альтернативное название 2', 'required' => false));
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('id')
            ->add('title', null, array('label' => 'Регион аптеки'))
            ->add('title2', null, array('label' => 'Альтернативное название 1'))
            ->add('title3', null, array('label' => 'Альтернативное название 2'))
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('id')
            ->add('title', null, array('label' => 'Регион аптеки'))
            ->add('title2', null, array('label' => 'Альтернативное название 1'))
            ->add('title3', null, array('label' => 'Альтернативное название 2'))
            ->add('_action', 'actions', array(
                'label' => 'Действия',
                'actions' => array(
                    'edit' => array(),
                    'delete' => array(),
                )
            ));
    }
}