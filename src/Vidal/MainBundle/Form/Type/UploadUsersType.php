<?php

namespace Vidal\MainBundle\Form\Type;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class UploadUsersType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('file', 'iphp_file', array('label' => 'Файл', 'required' => true))
            ->add('fields', 'textarea', array('label' => 'Поля через ,', 'required' => true))
            ->add('skipFirstLine', null, array('label' => 'Пропустить первую строку', 'required' => false))
            ->add('preview', null, array('label' => 'Предпросмотр записи', 'required' => false))
            ->add('submit_btn', 'submit', array('label' => 'Загрузить'));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array('data_class' => 'Vidal\MainBundle\Entity\UploadUsers'));
    }

    public function getName()
    {
        return 'upload_users';
    }
}