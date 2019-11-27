<?php

namespace Vidal\DrugBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Doctrine\ORM\EntityRepository;

class AdsAdmin extends Admin
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
            ->add('sliders', 'sonata_type_collection',
                array(
                    'label'              => 'Слайдер (статьи)',
                    'by_reference'       => false,
                    'cascade_validation' => true,
                    'required'           => false,
                ),
                array(
                    'edit'         => 'inline',
                    'inline'       => 'table',
                    'allow_delete' => true
                )
            )
            ->add('type', 'choice', array('label' => 'Вид баннера', 'required' => false, 'choices' => array(
                'none' => '(не указан)',
                'image' => 'Загружаемое изображение',
                'video' => 'Загружаемое видео',
                'youtube' => 'Ролик YouTube',
                'swiffy' => 'Баннер Swiffy',
                'html5' => 'Баннер html5',
            )))
            ->add('href', null, array('label' => 'Ссылка', 'required' => false))
            ->add('products-text', 'text', array('label' => 'Описания препаратов', 'required' => false, 'mapped' => false, 'attr' => array('class' => 'doc')))
            ->add('photo', 'iphp_file', array('label' => 'Загружаемое изображение JPG/PNG/GIF', 'required' => false))
            ->add('photoForUsersOnly', null, array('label' => 'Изображение только для специалистов', 'required' => false))
            ->add('photoStyles', 'text', array('label' => 'Стили изображения (style)', 'required' => false))
            ->add('video', 'iphp_file', array('label' => 'Загружаемое видео', 'required' => false, 'help' => 'HTML5 поддерживает исключительно mp4 формат видео файлов. Обращайтесь к разработчику для конвертации любого видео в mp4'))
            ->add('videoForUsersOnly', null, array('label' => 'Видео только для специалистов', 'required' => false))
            ->add('raw', null, array('label' => 'Ролики на YouTube (iframe)', 'required' => false))
            ->add('mobileBanner', 'iphp_file', array('label' => '[МОБИЛЬНЫЙ] Баннер', 'required' => false))
            ->add('mobileWidth', null, array('label' => '[МОБИЛЬНЫЙ] Ширина', 'required' => false, 'help' => 'Ширина моб. баннера'))
            ->add('mobileHeight', null, array('label' => '[МОБИЛЬНЫЙ] Высота', 'required' => false, 'help' => 'Высота моб. баннера'))
            ->add('htmlBanner', null, array('label' => 'html5 баннер', 'required' => false, 'help' => 'Учесть что в коде могут быть зависимости от внешних скриптов которые будут заблокированы'))
            ->add('htmlBannerWidth', null, array('label' => 'Ширина', 'required' => false, 'help' => 'Ширина html баннера'))
            ->add('htmlBannerHeight', null, array('label' => 'Высота', 'required' => false, 'help' => 'Высота html баннера'))
            ->add('mobileHtmlBanner', null, array('label' => '[МОБИЛЬНЫЙ] html5 баннер', 'required' => false, 'help' => 'Учесть что в коде могут быть зависимости от внешних скриптов которые будут заблокированы'))
            ->add('htmlBannerMobileWidth', null, array('label' => 'Ширина', 'required' => false, 'help' => 'Ширина мобильного html баннера'))
            ->add('htmlBannerMobileHeight', null, array('label' => 'Высота', 'required' => false, 'help' => 'Высота мобильного html баннера'))
            ->add('enabled', null, array('label' => 'Активен', 'required' => false));
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('id')
            ->add('enabled', null, array('label' => 'Активна'));
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('id')
            ->add('enabled', null, array('label' => 'Активен', 'template' => 'VidalDrugBundle:Sonata:swap_enabled.html.twig'))
            ->add('_action', 'actions', array(
                'label' => 'Действия',
                'actions' => array(
                    'edit' => array(),
                    'delete' => array(),
                )
            ));
    }
}