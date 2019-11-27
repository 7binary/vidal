<?php

namespace Vidal\MainBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\DateTime;
use Doctrine\ORM\EntityManager;
use Vidal\MainBundle\Form\DataTransformer\CityToStringTransformer;
use Vidal\MainBundle\Form\DataTransformer\YearToNumberTransformer;
use Vidal\MainBundle\Entity\User;
use Doctrine\ORM\EntityRepository;

class RegisterType extends AbstractType
{
    protected $em;
    protected $isApi;

    public function __construct(EntityManager $em, $isApi = false)
    {
        $this->em = $em;
        $this->isApi = $isApi;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $cityToStringTransformer = new CityToStringTransformer($this->em);
        $yearToNumberTransformer = new YearToNumberTransformer($this->em);

        $years = array();
        for ($i = date('Y') + 6; $i > date('Y') - 70; $i--) {
            $years[$i] = $i;
        }

        $builder
            ->add('username', null, array('label' => 'E-mail'))
            ->add('password', 'password', array(
                'label'       => 'Придумайте пароль',
                'constraints' => array(new Regex(array(
                    'pattern' => '/[а-яА-Я]/',
                    'match'   => false,
                    'message' => 'Русские буквы в пароле недопустимы'
                )))
            ))
            ->add('lastName', null, array(
                'label'       => 'Фамилия',
                'constraints' => array(new NotBlank(array(
                    'message' => 'Укажите свою фамилию'
                )))
            ))
            ->add('firstName', null, array(
                'label'       => 'Имя',
                'constraints' => array(new NotBlank(array(
                    'message' => 'Укажите свое имя'
                )))
            ))
            ->add('surName', null, array('label' => 'Отчество', 'required' => false))
            ->add('birthdate', 'date', array(
                'label'       => 'Дата рождения',
                'years'       => range(date('Y') - 111, date('Y')),
				'empty_value' => '',
                'format'      => 'dd MMMM yyyy',
                'constraints' => array(
                    new NotBlank(array('message' => 'Укажите дату своего рождения')),
                    new DateTime(array('message' => 'Дата рождения указана в неверно')),
                )
            ))
            ->add(
                $builder->create('city', 'text', array(
                    'label'       => 'Город',
                    'required'    => true,
                    'constraints' => array(
                        new NotBlank(array('message' => 'Укажите свой город')),
                    )
                ))->addModelTransformer($cityToStringTransformer)
            )
            ->add('university', null, array('label' => 'Выберите учебное заведение из списка', 'required' => false, 'empty_value' => 'выберите'))
            ->add('school', null, array('label' => 'Или укажите другое'))
            ->add(
                $builder->create('graduateYear', 'choice', array('label' => 'Год окончания учебного заведения', 'choices' => $years, 'empty_value' => 'выберите', 'required' => false))->addModelTransformer($yearToNumberTransformer)
            )
            ->add('primarySpecialty', 'entity', array(
                'label'         => 'Основная специальность',
                'empty_value'   => 'выберите',
                'required'      => true,
                'constraints'   => array(
                    new NotBlank(array('message' => 'Укажите свою специальность')),
                ),
                'class'         => 'VidalMainBundle:Specialty',
                'query_builder' => function (EntityRepository $er) {
                    return $er->userSelectQb();
                }
            ))
            ->add('secondarySpecialty', 'entity', array(
                'label'         => 'Дополнительная специальность',
                'empty_value'   => 'выберите',
                'required'      => false,
                'class'         => 'VidalMainBundle:Specialty',
                'query_builder' => function (EntityRepository $er) {
                    return $er->userSelectQb();
                }
            ))
            ->add('academicDegree', 'choice', array('label' => 'Ученая степень', 'choices' => User::getAcademicDegrees(), 'required' => false, 'data' => 'Нет'));


        if ($this->isApi === false) {
            $builder->add('captcha', 'captcha', array(
                'label'    => 'Проверочный код',
                'mapped'   => false,
                'required' => true,
            ))
                ->add('eula', 'checkbox', array(
                    'label'       => 'Пользовательское соглашение',
                    'mapped'      => false,
                    'required'    => false,
                    'constraints' => new \Symfony\Component\Validator\Constraints\IsTrue(array(
                        'message' => 'Пожалуйста, подтвердите, что Вы согласны с пользовательским соглашением'
                    ))
                ))
                ->add('submit', 'submit', array('label' => 'Зарегистрироваться'));
        }
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array('data_class' => 'Vidal\MainBundle\Entity\User'));
    }

    public function getName()
    {
        return 'register';
    }
}