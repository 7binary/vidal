<?php

namespace Vidal\MainBundle\Controller;

use Doctrine\ORM\EntityManager;
use Lsw\SecureControllerBundle\Annotation\Secure;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Vidal\MainBundle\Entity\RbkMoney;

class RbkMoneyController extends Controller
{
    /**
     * @Route("/rbkmoney/create", name="rbkmoney_create")
     * @Template("VidalMainBundle:RbkMoney:create.html.twig")
     */
    public function createAction(Request $request)
    {
        $price = $request->get('price', null);
        $product = $request->get('product', null);

        if (empty($price) || empty($product)) {
            throw $this->createNotFoundException();
        }

        $rbk = new RbkMoney;
        $rbk->setPrice($price);
        $rbk->setProduct($product);

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getEntityManager();
        $em->persist($rbk);
        $em->flush($rbk);

        return array(
            'title' => 'РБК - создание счета к оплате',
            'noYad' => true,
            'rbk' => $rbk,
        );
    }

    /**
     * @Route("/rbkmoney/sent", name="rbkmoney_sent", options={"expose":true})
     * @Template("VidalMainBundle:RbkMoney:create.html.twig")
     */
    public function sentAction(Request $request)
    {
        $orderId = $request->get('orderId', null);
        $user_email = $request->get('user_email', null);

        if($request->isXmlHttpRequest() == false || empty($orderId)) {
            throw $this->createNotFoundException();
        }

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getEntityManager();
        /** @var RbkMoney $rbk */
        $rbk = $em->getRepository("VidalMainBundle:RbkMoney")->findOneByOrderId($orderId);

        if ($rbk == null) {
            throw $this->createNotFoundException();
        }

        $rbk->setSent(true);
        $rbk->setUserEmail($user_email);
        $em->flush($rbk);

        return new JsonResponse('OK');
    }

    /**
     * @Route("/rbkmoney/status", name="rbkmoney_status")
     */
    public function statusAction(Request $request)
    {
        $data = $request->request->all();
        $status = json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        $orderId = $request->get('orderId', null);

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getEntityManager();
        /** @var RbkMoney $rbk */
        $rbk = $em->getRepository("VidalMainBundle:RbkMoney")->findOneByOrderId($orderId);

        if ($rbk == null) {
            return new JsonResponse("ERROR: not found orderId: $orderId");
        }

        $rbk->setStatus($status);
        $em->flush($rbk);

        return new JsonResponse("OK");
    }

    /**
     * @Route("/rbkmoney/status-test", name="rbkmoney_status_test")
     * @Template("VidalMainBundle:RbkMoney:status_test.html.twig")
     */
    public function statusTestAction()
    {
        return array(
            'title' => 'РБК - проверка обновления данных по платежу',
            'shopId' => RbkMoney::DEFAULT_ESHOP_ID,
            'noYad' => true,
        );
    }

    /**
     * @Route("/rbkmoney/paid/{orderId}", name="rbkmoney_paid")
     * @Template("VidalMainBundle:RbkMoney:paid.html.twig")
     */
    public function paidAction($orderId)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();
        /** @var RbkMoney $rbk */
        $rbk = $em->getRepository("VidalMainBundle:RbkMoney")->findOneByOrderId($orderId);

        if ($rbk == null) {
            throw $this->createNotFoundException();
        }

        $rbk->setPaid(true);
        $em->flush($rbk);

        return array(
            'title' => 'РБК: платеж успешно обработан системой',
            'shopId' => RbkMoney::DEFAULT_ESHOP_ID,
            'rbk' => $rbk,
            'noYad' => true,
        );
    }

    /**
     * @Route("/rbkmoney/failed/{orderId}", name="rbkmoney_failed")
     * @Template("VidalMainBundle:RbkMoney:failed.html.twig")
     */
    public function failedAction($orderId)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();
        /** @var RbkMoney $rbk */
        $rbk = $em->getRepository("VidalMainBundle:RbkMoney")->findOneByOrderId($orderId);

        if ($rbk == null) {
            throw $this->createNotFoundException();
        }

        $rbk->setFailed(true);
        $em->flush($rbk);

        return array(
            'title' => 'РБК: ошибка при обработке платежа',
            'shopId' => RbkMoney::DEFAULT_ESHOP_ID,
            'rbk' => $rbk,
            'noYad' => true,
        );
    }
}