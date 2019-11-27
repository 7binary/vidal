<?php

namespace Vidal\BigMamaBundle\Controller;

use Doctrine\ORM\EntityManager;
use Lsw\SecureControllerBundle\Annotation\Secure;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Validator\Constraints\NotBlank;
use Vidal\BigMamaBundle\Entity\Question;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Vidal\BigMamaBundle\Entity\Category;
use Vidal\BigMamaBundle\Entity\Publication;
use Vidal\BigMamaBundle\Entity\Audio;
use Vidal\BigMamaBundle\Entity\Video;
use Vidal\BigMamaBundle\Entity\Specialist;

class BigMamaController extends Controller
{
    protected function isTestMode()
    {
        return $this->container->getParameter('big_mama.testMode');
    }

    /**
     * @Route("/big-mama/about", name="big_mama_about")
     * @Template("VidalBigMamaBundle:BigMama:about.html.twig")
     */
    public function aboutAction(Request $request)
    {
        if($this->isTestMode() && !$request->get('test') && $request->get('test')!='true') {
            throw new AccessDeniedException();
        }

        return array('title' => 'О проекте', 'description' =>'О проекте');
    }

    /**
     * @Route("/big-mama", name="big_mama")
     * @Template("VidalBigMamaBundle:BigMama:home.html.twig")
     */
    public function homeAction(Request $request)
    {
        if($this->isTestMode() && !$request->get('test') && $request->get('test')!='true') {
            throw new AccessDeniedException();
        }

        $params = array(
            'title' => 'Большая Мама',
            'menu_left' => 'big_mama',
        );

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager('big_mama');

        $params['categories'] = $em->getRepository(Category::class)->findActive(5);
        $params['publications'] = $em->getRepository(Publication::class)->findActive(5);
        $params['audios'] = $em->getRepository(Audio::class)->findActive(5);
        $params['videos'] = $em->getRepository(Video::class)->findActive(5);
        $params['questions'] = $em->getRepository(Question::class)->findActive(5);
        $params['specialists'] = $em->getRepository(Specialist::class)->findActive(5);

        # форма задать вопрос
        $faq = new Question();

        $builder = $this->createFormBuilder($faq);
        $builder
            ->add('authorFirstName', null, array('label' => 'Ваше имя', 'required' => true, 'constraints' => new NotBlank(array('message' => "Пожалуйста, укажите Имя"))))
            ->add('authorEmail', null, array('label' => 'Ваш e-mail', 'required' => true, 'constraints' => new NotBlank(array('message' => "Пожалуйста, укажите Email"))))
            ->add('question', null, array('label' => 'Вопрос', 'attr' => array('class' => 'ckeditor')))
            ->add('captcha', 'captcha', array('label' => 'Введите код с картинки'))
            ->add('submit', 'submit', array('label' => 'Отправить', 'attr' => array('class' => 'btn')));

        $form = $builder->getForm();
        $form->handleRequest($request);

        if ($request->isMethod('POST')) {
            if ($form->isValid()) {
                $faq = $form->getData();
                $faq->setEnabled(0);
                $em->persist($faq);
                $em->flush();

                $this->get('session')->getFlashBag()->add('questioned', '');

                return $this->redirect($this->generateUrl('big_mama'));
            }
        }

        $params['form'] = $form->createView();
        $params['isPromo'] = true;

        return $params;
    }
}
