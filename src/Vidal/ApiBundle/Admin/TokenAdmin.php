<?php

namespace Vidal\ApiBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Doctrine\ORM\EntityRepository;

class TokenAdmin extends Admin
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
            ->add('enabled', null, array('label' => 'Активен', 'required' => false))
            ->add('userName', null, array('label' => 'Имя пользователя', 'required' => true))
            ->add('userPassword', null, array('label' => 'Пароль пользователя', 'required' => true))
            ->add('maxRequestPerDay', null, array('label' => 'Максимальное кол-во запросов в сутки', 'required' => false, 'help' => 'Если оставить пустым, ограничения не будет'))
            ->add('currentRequestPerDay', null, array('label' => 'Кол-во запросов за последние сутки', 'required' => false))
            ->add('comment', 'textarea', array('label' => 'Комментарий', 'required' => false))
            ->add('lastRequestDate', null, array('label' => 'Дата последнего успешного запроса', 'required' => false));
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('id')
            ->add('enabled', null, array('label' => 'Активна'))
            ->add('userName', null, array('label' => 'Имя пользователя'))
            ->add('comment', null, array('label' => 'Комментарий'));
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('id')
            ->add('userName', null, array('label' => 'Имя пользователя'))
            ->add('maxRequestPerDay', null, array('label' => 'Максимальное кол-во запросов в сутки'))
            ->add('currentRequestPerDay', null, array('label' => 'Кол-во запросов за последние сутки'))
            ->add('lastRequestDate', null, array('label' => 'Дата последнего успешного запроса'))
            ->add('comment', null, array('label' => 'Комментарий'))
            ->add('enabled', null, array('label' => 'Активен', 'template' => 'VidalDrugBundle:Sonata:swap_enabled_big_mama.html.twig'))
            ->add('_action', 'actions', array(
                'label' => 'Действия',
                'actions' => array(
                    'edit' => array(),
                    'delete' => array(),
                )
            ));
    }
}