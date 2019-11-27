<?php

namespace Vidal\BigMamaBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Doctrine\ORM\EntityRepository;

class VideoAdmin extends Admin
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
            ->add('category', null, array(
                'label'         => 'Раздел',
                'required'      => true,
                'attr'          => array('class' => 'art-rubrique'),
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('c')
                        ->orderBy('c.title', 'ASC');
                },
            ))
            ->add('video', 'iphp_file', array('label' => 'Загружаемый MP4 файл', 'required' => false))
            ->add('photo', 'iphp_file', array('label' => 'Фотография', 'required' => false))
            ->add('title', 'textarea', array('label' => 'Заголовок', 'required' => true, 'attr' => array('class' => 'ckeditormizer')))
            ->add('linkManual', null, array('label' => 'URL-адрес вручную', 'required' => false))
            ->add('link', null, array('label' => 'URL-адрес авто', 'required' => false, 'disabled' => true))
            ->add('position', null, array('label' => 'Позиция', 'required' => false))
            ->add('body', null, array('label' => 'Описание', 'required' => false, 'attr' => array('class' => 'ckeditorfull')))
            ->add('date', null, array('label' => 'Дата создания', 'required' => false, 'years' => range(2000, date('Y'))))
            ->add('enabled', null, array('label' => 'Активен', 'required' => false));
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('id')
            ->add('title', null, array('label' => 'Заголовок'))
            ->add('link', null, array('label' => 'URL-адрес'))
            ->add('enabled', null, array('label' => 'Активна'));
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('id')
            ->add('title', null, array('label' => 'Заголовок'))
            ->add('link', null, array('label' => 'URL-адрес'))
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