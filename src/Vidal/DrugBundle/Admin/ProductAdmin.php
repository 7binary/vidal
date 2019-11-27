<?php

namespace Vidal\DrugBundle\Admin;

use Doctrine\ORM\EntityManager;
use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Vidal\DrugBundle\Entity\Product;
use Vidal\DrugBundle\Transformer\DocumentToStringTransformer;

class ProductAdmin extends Admin
{
    protected function configureFormFields(FormMapper $formMapper)
    {
        /** @var Product $subject */
        $subject = $this->getSubject();
        /** @var EntityManager $em */
        /** @var Product $p */
        $em = $this->getModelManager()->getEntityManager($subject);
        $transformer = new DocumentToStringTransformer($em, $subject);
        $picturesSubject = $em->getRepository('VidalDrugBundle:Picture')->findIdsByProduct($subject->getProductID());
        $productId = $subject->getProductID();

        $pictures = $subject->getPictures();

        $pt = array(
            'ALRG' => 'Аллерген',
            'BAD' => 'Биологически активная добавка',
            'GOME' => 'Гомеопатическое средство',
            'DIAG' => 'Диагностикум',
            'DRUG' => 'Лекарственный препарат',
            'MI' => 'Мед. изделие',
            'SRED' => 'Питательная среда',
            'SUBS' => 'Субстанция',
            'NUTR' => 'Лечебное питание',
            'COSM' => 'Лечебная косметика',
        );

        # новому продукту можно проставить идентификатор
        if (!$this->getSubject()->getProductID()) {
            $formMapper
                ->add('ProductID', null, array('label' => 'ID продукта', 'required' => true));
        }

        $formMapper
            ->add('RusName', null, array('label' => 'Название', 'required' => true, 'attr' => array('data-ProductID' => $subject->getProductID(), 'class' => 'RusName', 'data-product-id' => $productId, 'data-picture-ids' => $pictures)))
            ->add('RusName2', null, array('label' => 'Название без специальных символов', 'required' => true))
            ->add('EngName', null, array('label' => 'Латинское', 'required' => true))
            ->add('Name', 'text', array('label' => 'URL адрес (для старых)', 'required' => true))
            ->add('url', 'text', array('label' => 'URL адрес (для новых)', 'required' => false))
            ->add('forms', 'textarea', array('label' => 'Формы выпуска', 'required' => false))
            ->add('formsGrouped', 'textarea', array('label' => 'Формы выпуска сгруппированные', 'required' => false))
            ->add('infoPages-text', 'text', array('label' => 'Представительства', 'required' => false, 'mapped' => false, 'attr' => array('class' => 'infoPages-text', 'placeholder' => 'Начните вводить название')))
            ->add('atcCodes-text', 'text', array('label' => 'Коды АТХ', 'required' => false, 'mapped' => false, 'attr' => array('class' => 'atcCodes-text', 'placeholder' => 'Начните вводить название или код')))
            ->add('molecule-names-text', 'text', array('label' => 'Активные вещества', 'required' => false, 'mapped' => false, 'attr' => array('class' => 'molecule-names-text', 'placeholder' => 'Начните вводить название или ID')))
            ->add('clphgroups-text', 'text', array('label' => 'КФГ', 'required' => false, 'mapped' => false, 'attr' => array('class' => 'clphgroups-text', 'placeholder' => 'Клинико-фармакологические группы. Начните вводить название или ID')))
            ->add('phthgroups-text', 'text', array('label' => 'ФТГ', 'required' => false, 'mapped' => false, 'attr' => array('class' => 'phthgroups-text', 'placeholder' => 'Фармако-терапевтические группы. Начните вводить название или ID')))
            ->add('seoTitle', null, array('label' => 'SEO title', 'required' => false))
            ->add('seoDescription', null, array('label' => 'SEO description', 'required' => false))
            ->add('testMode', null, array('label' => 'Тестовый режим (h2)', 'required' => false))
            ->add('testTitle', null, array('label' => 'Тестовый заголовок (h1)', 'required' => false))
            ->add('redirectId', null, array('label' => 'Редирект 301 на ProductID', 'required' => false))
            ->add('document_manual_id', null, array('label' => 'Выставленный вручную ID документа', 'required' => false, 'help' => 'Для открепления текущего документа введите "0" и сохраните'))
            ->add($formMapper->create('document', 'text', array(
                'label' => 'ID документа',
                'required' => false,
                'by_reference' => true,
                'read_only' => true,
            ))->addModelTransformer($transformer))
            ->add($formMapper->create('document_merge', 'text', array(
                'label' => 'ID документа СЛИЯНИЯ',
                'required' => false,
                'by_reference' => true,
                'read_only' => true,
            ))->addModelTransformer($transformer))
            ->add('MainIDManual', null, array('label' => 'ID препарата основного', 'required' => false, 'help' => 'Сюда указывается ProductID основного препарата. Именно в него будут сливаться препараты с общим документом. Изменение этого поля запустит набор команд для пересчета склеенных препаратов, может занять до 5 минут. Для удаления выставленного раннее значения введите 0 и сохраните'))
            ->add('MainID', null, array('label' => 'ID препарата основного', 'required' => false, 'read_only' => true))
            ->add('ParentID', null, array('label' => 'ID родителя склейки по ParentID', 'read_only' => true, 'required' => false))
            ->add('hasChildrenParentID', null, array('label' => 'Имеет склеенные препараты по ParentID', 'read_only' => true, 'required' => false))
            ->add('hasChildrenMainID', null, array('label' => 'Имеет склеенные препараты по документу', 'read_only' => true, 'required' => false))
            ->add('ProductTypeCode', 'choice', array('label' => 'Тип препарата', 'required' => true, 'choices' => $pt))
            ->add('MarketStatusID', null, array('label' => 'Статус', 'required' => true))
            ->add('ZipInfo', null, array('label' => 'Форма выпуска', 'required' => true))
            ->add('photo', 'iphp_file', array('label' => 'Фотография временная', 'required' => false))
            ->add('photo2', 'iphp_file', array('label' => 'Фотография временная', 'required' => false))
            ->add('photo3', 'iphp_file', array('label' => 'Фотография временная', 'required' => false))
            ->add('photo4', 'iphp_file', array('label' => 'Фотография временная', 'required' => false))
            ->add('photo5', 'iphp_file', array('label' => 'Фотография временная', 'required' => false))
            ->add('photo6', 'iphp_file', array('label' => 'Фотография временная', 'required' => false))
            ->add('Composition', null, array('label' => 'Описание', 'required' => false, 'attr' => array('class' => 'ckeditorfull')))
            ->add('RegistrationDate', null, array('label' => 'Дата регистрации'))
            ->add('RegistrationNumber', null, array('label' => 'Номер регистрации'))
            ->add('DateOfReRegistration', null, array('label' => 'Дата перерегистрации'))
            ->add('NonPrescriptionDrug', null, array('label' => 'Безрецептурный', 'required' => false))
            ->add('StrongMeans', null, array('label' => 'Сильнодействующий', 'required' => false))
            ->add('Poison', null, array('label' => 'Ядовитый', 'required' => false))
            ->add('GNVLS', null, array('label' => 'ЖНВЛП', 'required' => false))
            ->add('DLO', null, array('label' => 'ДЛО', 'required' => false))
            ->add('ValidPeriod', null, array('label' => 'Срок действия', 'required' => false))
            ->add('StrCond', null, array('label' => 'Условия хранения', 'required' => false))
            ->add('productCompany', 'sonata_type_collection',
                array(
                    'label' => 'Компании',
                    'by_reference' => false,
                    'cascade_validation' => false,
                    'required' => false,
                ),
                array(
                    'edit' => 'inline',
                    'inline' => 'table',
                    'allow_delete' => true,
                )
            )
            ->add('hidePhoto', null, array('label' => 'Скрывать фотографию', 'required' => false))
            ->add('inactive', null, array('label' => 'Отключить', 'required' => false, 'help' => 'Исключить препарат из списков выдачи'));
    }

    // Fields to be shown on filter forms
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('ProductID', null, array('label' => 'ID'))
            ->add('RusName', null, array('label' => 'Название'))
            ->add('EngName', null, array('label' => 'Латинское'))
            ->add('ProductTypeCode', null, array('label' => 'Тип препарата'))
            ->add('MarketStatusID', null, array('label' => 'Статус'))
            ->add('ZipInfo', null, array('label' => 'Форма выпуска'))
            ->add('RegistrationDate', null, array('label' => 'Дата регистр.'))
            ->add('inactive', null, array('label' => 'Отключен'));
    }

    // Fields to be shown on lists
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('ProductID', null, array('label' => 'ID'))
            ->add('RusName', null, array('label' => 'Название', 'template' => 'VidalDrugBundle:Sonata:RusName.html.twig'))
            ->add('EngName', null, array('label' => 'Латинское', 'template' => 'VidalDrugBundle:Sonata:EngName.html.twig'))
            ->add('ProductTypeCode', null, array('label' => 'Тип препарата'))
            ->add('MarketStatusID', null, array('label' => 'Статус'))
            ->add('ZipInfo', null, array('label' => 'Форма выпуска'))
            ->add('RegistrationDate', null, array('label' => 'Дата регистр.'))
            ->add('inactive', null, array('label' => 'Отключен', 'template' => 'VidalDrugBundle:Sonata:swap_inactive.html.twig'))
            ->add('_action', 'actions', array(
                'label' => 'Действия',
                'actions' => array(
                    'edit' => array(),
                    'delete' => array(),
                )
            ));
    }
}