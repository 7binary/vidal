<?php

namespace Vidal\MainBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Form\FormMapper;

class LogAdmin extends Admin
{
    protected $datagridValues;

    public function __construct($code, $class, $baseControllerName)
    {
        parent::__construct($code, $class, $baseControllerName);

        if (!$this->hasRequest()) {
            $this->datagridValues = array(
                '_page' => 1,
                '_per_page' => 25,
                '_sort_order' => 'ASC',
                '_sort_by' => 'title'
            );
        }
    }

    protected function configureShowField(ShowMapper $showMapper)
    {
        $showMapper
            ->add('id')
            ->add('event', null, array('label' => 'Тип события (создание/обновление/удаление)'))
            ->add('entityId', null, array('label' => 'ID сущности'))
            ->add('entityClass', null, array('label' => 'Класс сущности'))
            ->add('adminId', null, array('label' => 'ID админа'))
            ->add('adminEmail', null, array('label' => 'Email админа'))
            ->add('changes', null, array('label' => 'Изменения'))
            ->add('created', null, array('label' => 'Создано'));
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('event', null, array('label' => 'Тип события (создание/обновление/удаление)'))
            ->add('entityId', null, array('label' => 'ID сущности'))
            ->add('entityClass', null, array('label' => 'Класс сущности'))
            ->add('adminId', null, array('label' => 'ID админа'))
            ->add('adminEmail', null, array('label' => 'Email админа'))
            ->add('changes', null, array('label' => 'Изменения'))
            ->add('created', null, array('label' => 'Создано'));
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('event', null, array('label' => 'Тип события (создание/обновление/удаление)'))
            ->add('entityId', null, array('label' => 'ID сущности'))
            ->add('entityClass', null, array('label' => 'Класс сущности'))
            ->add('adminId', null, array('label' => 'ID админа'))
            ->add('adminEmail', null, array('label' => 'Email админа'));
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('event', null, array('label' => 'Тип события (создание/обновление/удаление)'))
            ->add('entityId', null, array('label' => 'ID сущности'))
            ->add('entityClass', null, array('label' => 'Класс сущности'))
            ->add('adminId', null, array('label' => 'ID админа'))
            ->add('adminEmail', null, array('label' => 'Email админа'))
            ->add('created', null, array('label' => 'Создано'))
            ->add('_action', 'actions', array(
                'label' => 'Действия',
                'actions' => array(
                    'show' => array(),
                    'edit' => array(),
                    'delete' => array(),
                )
            ));
    }
}