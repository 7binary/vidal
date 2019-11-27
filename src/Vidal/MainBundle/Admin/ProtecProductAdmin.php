<?php

namespace Vidal\MainBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Doctrine\ORM\EntityRepository;

class ProtecProductAdmin extends Admin
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
            ->add('title', null, array('label' => 'Препарат', 'required' => true))
            ->add('form', null, array('label' => 'Производитель', 'required' => true))
            ->add('ProductID', null, array('label' => 'ProductID наш', 'required' => false))
        ;

    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('id', null, array('label' => 'ID аптеки'))
            ->add('title', null, array('label' => 'Препарат'))
            ->add('form', null, array('label' => 'Производитель'))
            ->add('ProductID', null, array('label' => 'ProductID наш'))
            ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('id', null, array('label' => 'ID аптеки'))
            ->add('ProductID', null, array('label' => 'ProductID наш'))
            ->add('clicked', null, array('label' => 'Переходов'))
            ->add('title', null, array('label' => 'Препарат'))
            ->add('form', null, array('label' => 'Производитель'))
            ->add('updated', null, array('label' => 'Цены', 'template' => 'VidalDrugBundle:Sonata:protec_price.html.twig'))
            ;
    }
}