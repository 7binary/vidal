<?php

namespace Vidal\MainBundle\Controller;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Lsw\SecureControllerBundle\Annotation\Secure;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Vidal\MainBundle\Command\DeliveryStatsCommand;
use Vidal\MainBundle\Entity\Delivery;
use Vidal\MainBundle\Entity\Digest;
use Vidal\MainBundle\Entity\DeliveryOpen;
use Vidal\MainBundle\Entity\DigestOpened;
use Vidal\MainBundle\Entity\Region;
use Vidal\MainBundle\Entity\Specialty;
use Vidal\MainBundle\Entity\User;

class DeliveryController extends Controller
{
    /**
     * @Route("/delivery/stop", name="delivery_stop")
     */
    public function deliveryStopAction()
    {
        $this->checkRole();

        $em = $this->getDoctrine()->getManager();
        $em->createQuery('UPDATE VidalMainBundle:Digest d SET d.progress = 0')->execute();

        @unlink(__DIR__ . '/../Command/.delivery');

        $this->get('session')->getFlashBag()->add('msg', 'Рассылка остановлена');

        return $this->redirect($this->generateUrl('delivery_control'), 301);
    }

    /**
     * @Route("/delivery/start", name="delivery_start")
     */
    public function deliveryStartAction()
    {
        $this->checkRole();

        $em = $this->getDoctrine()->getManager();

        $em->createQuery('UPDATE VidalMainBundle:Digest d SET d.progress = 1')->execute();
        $this->get('session')->getFlashBag()->add('msg', 'Рассылка будет запущена в течении минуты');

        return $this->redirect($this->generateUrl('delivery_control'), 301);
    }

    /**
     * @Route("/excluded-products", name="excluded_products")
     * @Template("VidalMainBundle:Digest:excluded_products.html.twig")
     * @Secure(roles="ROLE_SUPERADMIN")
     */
    public function excludedProductsAction(Request $request)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();
        /** @var Digest $digest */
        $digest = $em->getRepository('VidalMainBundle:Digest')->get();
        $this->calculateDigest($em, $digest);

        $form = $this->createFormBuilder($digest)
            ->add('excludedProducts', null, array('label' => 'Препараты, исключающиеся из автоматической перелинковки. Названия через ;'))
            ->add('submit', 'submit', array('label' => 'Сохранить', 'attr' => array('class' => 'btn-red')))
            ->getForm();

        $form->handleRequest($request);

        if ($form->isValid()) {
            $em->flush();
            $this->get('session')->getFlashBag()->add('msg', 'Изменения сохранены');

            return $this->redirect($this->generateUrl('excluded_products'), 301);
        }

        $params = array(
            'title' => 'Исключение препаратов из перелинковки',
            'form' => $form->createView(),
            'digest' => $digest,
        );

        return $params;
    }

    /**
     * @Route("/delivery/control/{id}", name="delivery_control")
     * @Template("VidalMainBundle:Digest:delivery_control.html.twig")
     */
    public function deliveryControlAction(Request $request, $id = null)
    {
        $this->checkRole();

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();
        /** @var Digest $digest */
        $digest = $em->getRepository('VidalMainBundle:Digest')->get($id);
        $this->calculateDigest($em, $digest);

        $form = $this->createFormBuilder($digest)
            ->add('regions', null, array('label' => 'Регионы', 'required' => false))
            ->add('specialties', null, array('label' => 'Специальности', 'required' => false))
            ->add('allSpecialties', null, array('label' => 'Всем специальностям', 'required' => false))
            ->add('total', null, array('label' => 'Всего к отправке', 'required' => false, 'disabled' => true))
            ->add('totalSend', null, array('label' => 'Уже отправлено по рассылке ' . $digest->getUniqueid(), 'required' => false, 'disabled' => true))
            ->add('totalLeft', null, array('label' => 'Осталось отправить', 'required' => false, 'disabled' => true))
            ->add('totalLimit', null, array('label' => 'Лимит общий, по достижении лимита полная остановка рассылки'))
            ->add('limit', null, array('label' => 'Лимит продолжающейся отправки, по достижении лимита пауза в рассылке 1 час', 'required' => false))
            ->add('uniqueid', null, array('label' => 'Текстовый идентификатор рассылки: латинские буквы, цифры, _, -'))
            ->add('submit', 'submit', array('label' => 'Сохранить', 'attr' => array('class' => 'btn-red')))
            ->getForm();

        $form->handleRequest($request);

        if ($form->isValid()) {
            $em->flush();
            $this->get('session')->getFlashBag()->add('msg', 'Изменения сохранены');

            return $this->redirect($this->generateUrl('delivery_control', array('id' => $id)), 301);
        }

        $params = array(
            'title' => 'Рассылка - управление',
            'form' => $form->createView(),
            'digest' => $digest,
        );

        return $params;
    }

    /**
     * @Route("/delivery/stats", name="delivery_stats")
     * @Template("VidalMainBundle:Digest:delivery_stats.html.twig")
     */
    public function deliveryStatsAction(Request $request)
    {
        $this->checkRole();

        $file = $this->container->getParameter('download_dir') . DIRECTORY_SEPARATOR . 'delivery_stats.json';
        $grouped = json_decode(file_get_contents($file), true);

        return array(
            'title' => 'Рассылка - статистика',
            'grouped' => $grouped,
        );
    }

    /**
     * @Route("/delivery/{id}", name="delivery")
     * @Template("VidalMainBundle:Digest:delivery.html.twig")
     */
    public function deliveryAction(Request $request, $id = null)
    {
        $this->checkRole();

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();
        /** @var Digest $digest */
        $digest = $em->getRepository('VidalMainBundle:Digest')->get($id);

        $form = $this->createFormBuilder($digest)
            ->add('text', null, array('label' => 'Текст письма', 'required' => true, 'attr' => array('class' => 'ckeditorfull')))
            ->add('textPlain', null, array('label' => 'Текстовая версия рассылки', 'required' => true))
            ->add('subject', null, array('label' => 'Тема письма', 'required' => true))
            ->add('font', null, array('label' => 'Название шрифта без кавычек', 'required' => true))
            ->add('fontSize', null, array('label' => 'Размер шрифта (был раньше 14)', 'required' => true))
            ->add('emails', null, array('label' => 'Тестовые e-mail через ;', 'required' => false))
            ->add('submit', 'submit', array('label' => 'Сохранить', 'attr' => array('class' => 'btn-red')))
            ->getForm();

        $form->handleRequest($request);

        if ($form->isValid()) {
            $digest->updateTextImages();
            $em->flush();
            $this->get('session')->getFlashBag()->add('msg', 'Изменения сохранены');

            return $this->redirect($this->generateUrl('delivery', array('id' => $id)), 301);
        }

        $params = array(
            'title' => 'Рассылка - письмо',
            'digest' => $digest,
            'form' => $form->createView(),
            'total' => $em->getRepository('VidalMainBundle:User')->total(),
            'subscribed' => $em->getRepository('VidalMainBundle:Digest')->countSubscribed(),
            'unsubscribed' => $em->getRepository('VidalMainBundle:Digest')->countUnsubscribed(),
        );

        return $params;
    }

    /**
     * @Route("/delivery/edit/{uniqueid}", name="delivery_edit")
     * @Template("VidalMainBundle:Digest:delivery_edit.html.twig")
     */
    public function deliveryEditAction(Request $request, $uniqueid)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();
        /** @var Delivery $delivery */
        $delivery = $em->getRepository('VidalMainBundle:Delivery')->getOrCreate($uniqueid);
        $title = $delivery->getTitle();

        if (empty($title)) {
            $delivery->setTitle($delivery->getName());
            $em->flush($delivery);
        }

        $form = $this->createFormBuilder($delivery)
            ->add('coef', 'text', array('label' => 'Коэффициент умножения открытий рассылки. Пример: 1.8', 'required' => true))
            ->add('coefSent', 'text', array('label' => 'Коэффициент умножения отправленных писем. Пример: 1.5', 'required' => true))
            ->add('title', 'text', array('label' => 'Отображаемое название рассылки', 'required' => true))
            ->add('submit', 'submit', array('label' => 'Сохранить', 'attr' => array('class' => 'btn-red')))
            ->getForm();

        $form->handleRequest($request);

        if ($form->isValid()) {
            $em->flush();
            $this->get('session')->getFlashBag()->add('msg', 'Изменения сохранены');
            $this->runCommand((new DeliveryStatsCommand()));

            return $this->redirect($this->generateUrl('delivery_edit', array('uniqueid' => $uniqueid)), 301);
        }

        $params = array(
            'title' => 'Рассылка: ' . $delivery->getTitle(),
            'uniqueid' => $uniqueid,
            'delivery' => $delivery,
            'form' => $form->createView(),
        );

        return $params;
    }

    private function runCommand(ContainerAwareCommand $command)
    {
        $command->setContainer($this->container);
        $input = new ArrayInput(array());
        $output = new NullOutput();
        $command->run($input, $output);
    }

    /**
     * @Route("/delivery/test-by-id/{id}", name="delivery_test_by_id")
     */
    public function deliveryTestAction($id = null)
    {
        $this->checkRole();

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();
        /** @var Digest $digest */
        $digest = $em->getRepository('VidalMainBundle:Digest')->get($id);
        $em->refresh($digest);

        $emails = explode(';', $digest->getEmails());
        $this->testTo($emails, $digest);
        $this->get('session')->getFlashBag()->add('msg', 'Было отправлено на адреса: ' . $digest->getEmails());

        return $this->redirect($this->generateUrl('delivery', array('id' => $id)), 301);
    }

    /**
     * @Route("/delivery/stats/{deliveryName}", name="delivery_stats_item")
     * @Template("VidalMainBundle:Digest:delivery_stats_item.html.twig")
     */
    public function deliveryStatsItemAction(Request $request, $deliveryName)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();
        /** @var Digest $digest */
        $digest = $em->getRepository('VidalMainBundle:Digest')->get();
        /** @var Delivery $delivery */
        $delivery = $em->getRepository('VidalMainBundle:Delivery')->getOrCreate($deliveryName);
        $logs = $em->getRepository('VidalMainBundle:DeliveryLog')->days($deliveryName);
        $logsTotal = $em->getRepository('VidalMainBundle:DeliveryLog')->total($deliveryName);

        $openedTotal = $em->getRepository('VidalMainBundle:DigestOpened')->total($deliveryName);
        $opens = $em->getRepository('VidalMainBundle:DigestOpened')->days($deliveryName);
        $opensHour = $em->getRepository('VidalMainBundle:DigestOpened')->hour($deliveryName);

        $title = $delivery->getTitle();
        if (empty($title)) {
            $delivery->setTitle($delivery->getName());
            $em->flush($delivery);
        }

        # если выставили вручную коэфициент открытий
        if ($delivery->getCoef() && $delivery->getCoef() !== 1) {
            $coef = $delivery->getCoef();
            $openedTotal = floor($openedTotal * $coef);
            foreach ($opens as &$open) {
                $open['value'] = floor($open['value'] * $coef);
            }
            foreach ($opensHour as &$open) {
                $open['value'] = floor($open['value'] * $coef);
            }
        }

        # если выставили вручную коэфициент отправок
        if ($delivery->getCoefSent() && $delivery->getCoefSent() !== 1) {
            $coefSent = $delivery->getCoefSent();
            $logsTotal =  floor($logsTotal  * $coefSent);
            foreach ($logs as &$log) {
                $log['value'] = floor($log['value'] * $coefSent);
            }
        }

        $params = array(
            'title' => 'Рассылка: ' . $title,
            'digest' => $digest,
            'delivery' => $delivery,
            'opens' => json_encode($opens),
            'opensHour' => json_encode($opensHour),
            'logs' => json_encode($logs),
            'openedTotal' => $openedTotal,
            'logsTotal' => $logsTotal,
        );

        return $params;
    }

    /**
     * Открыли письмо - записали в БД и вернули как бы картинку
     * @Route("/digest_q/opened/{digestName}/{doctorId}")
     */
    public function digestOpenedAction($digestName, $doctorId)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $do = new DigestOpened();
        $do->setUser($doctorId);
        $do->setUniqueid($digestName);

        $em->persist($do);
        $em->flush();

        $imagePath = $this->get('kernel')->getRootDir() . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR
            . 'web' . DIRECTORY_SEPARATOR . 'bundles' . DIRECTORY_SEPARATOR . 'vidalmain' . DIRECTORY_SEPARATOR
            . 'images' . DIRECTORY_SEPARATOR . 'delivery' . DIRECTORY_SEPARATOR . '1px.png';

        $file = readfile($imagePath);

        $headers = array(
            'Cache-Control' => 'no-cache',
            'Expires' => '0',
            'Content-Type' => 'image/png',
            'Content-Disposition' => 'inline; filename="' . uniqid() . '.png"'
        );

        return new Response($file, 200, $headers);
    }

    private function checkRole()
    {
        $security = $this->get('security.context');
        if (!$security->isGranted('ROLE_SUPERADMIN') && !$security->isGranted('ROLE_ADMIN_VIDAL_RU')) {
            throw new AccessDeniedException();
        }
    }

    private function testTo($emails, Digest $digest)
    {
        $emailService = $this->get('email.service');
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();
        $localhost = $this->container->getParameter('env_local');

        foreach ($emails as $email) {
            $email = trim($email);
            /** @var User $user */
            $user = $em->getRepository('VidalMainBundle:User')->findOneByUsername($email);
            if ($user) {
                $created = $user->getCreated()->format('Y-m-d_H:i:s');
                $emailService->send(
                    $email,
                    array('VidalMainBundle:Email:digest.html.twig', array('digest' => $digest, 'user' => $user)),
                    $digest->getSubject(),
                    $localhost,
                    array(
                        'Precedence' => 'list',
                        'List-Unsubscribe' => "https://www.vidal.ru/unsubscribe-digest/{$user->getId()}/$created",
                    ),
                    $digest->getTextPlain()
                );
            }
            else {
                $emailService->send(
                    $email,
                    array('VidalMainBundle:Email:digest-test.html.twig', array('digest' => $digest)),
                    $digest->getSubject(),
                    $localhost,
                    array(
                        'Precedence' => 'list',
                        'List-Unsubscribe' => "https://www.vidal.ru/unsubscribe-digest/7/2014-02-20_15:22:18",
                    ),
                    $digest->getTextPlain()
                );
            }
        }
    }

    private function calculateDigest(EntityManager $em, Digest $digest)
    {
        # считаем, сколько всего к отправке
        $uniqueid = $digest->getUniqueid();

        $qb = $em->createQueryBuilder();
        $qb->select("COUNT(u.id)")
            ->from('VidalMainBundle:User', 'u')
            ->andWhere('u.digestSubscribed = 1')
            ->andWhere('(u.mail_delete_counter IS NULL OR u.mail_delete_counter <= 5)');

        # специальности
        /** @var Specialty[] $specialties */
        $specialties = $digest->getSpecialties();

        if (count($specialties)) {
            $ids = array();
            foreach ($specialties as $specialty) {
                $ids[] = $specialty->getId();
            }
            $qb->andWhere('(u.primarySpecialty IN (:ids) OR u.secondarySpecialty IN (:ids))')
                ->setParameter('ids', $ids);
        }

        # регионы
        /** @var Region[] $regions */
        $regions = $digest->getRegions();

        if (count($regions)) {
            $regionIds = array();
            foreach ($regions as $region) {
                $regionIds[] = $region->getId();
            }
            $qb->andWhere('u.region IN (:regionIds)')
                ->setParameter('regionIds', $regionIds);
        }

        $total = $qb->getQuery()->getSingleScalarResult();
        $digest->setTotal($total);

        # этим уже отправили
        $qb2 = $em->createQueryBuilder();
        $qb2->select('l.email')
            ->from('VidalMainBundle:DeliveryLog', 'l')
            ->where("l.uniqueid = '$uniqueid'");
        $qb->andWhere($qb->expr()->notIn('u.username', $qb2->getDQL()));
        $totalLeft = $qb->getQuery()->getSingleScalarResult();
        $digest->setTotalLeft($totalLeft);

        $totalSend = $total - $totalLeft;
        $digest->setTotalSend($totalSend);

        $em->flush($digest);
    }
}
