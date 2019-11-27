<?php

namespace Vidal\DrugBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;

class AdsSliderAdmin extends Admin
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
            ->add('article', null, array('label' => 'Статья энциклопедии', 'required' => false))
            ->add('art', null, array('label' => 'Статья специалистам', 'required' => false))
            ->add('video', 'iphp_file', array('label' => 'Загружаемое видео', 'required' => false, 'attr' => array('style' => 'width:140px;overflow:hidden;')))
            ->add('slideNumber', null, array('label' => 'Номер слайда', 'required' => true, 'attr' => array('style' => 'width:60px')))
            ->add('priority', null, array('label' => 'Позиция в слайде', 'required' => true, 'attr' => array('style' => 'width:60px')))
            ->add('videoForUsersOnly', null, array('label' => 'Видео только для специалистов', 'required' => false))
            ->add('raw', null, array('label' => 'Ролики на YouTube (iframe)', 'required' => false, 'attr' => array('style' => 'width:240px !important')))
            ->add('enabled', null, array('label' => 'Активен', 'required' => false, 'attr' => array('style' => 'width:60px')));
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
            ->add('_action', 'actions', array(
                'label' => 'Действия',
                'actions' => array(
                    'edit' => array(),
                    'delete' => array(),
                )
            ));
    }
}