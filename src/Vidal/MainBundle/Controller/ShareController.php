<?php

namespace Vidal\MainBundle\Controller;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

use Vidal\MainBundle\Entity\Share;

class ShareController extends Controller
{
    /** @Route("/share-counter/{class}/{target}", name="share_counter", options={"expose":true}) */
    public function counterAction(Request $request, $class, $target)
    {
        if($request->isXmlHttpRequest() == false) {
            throw $this->createNotFoundException();
        }

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();
        $count = $em->getRepository('VidalMainBundle:Share')->countBy($class, $target);

        return new JsonResponse($count);
    }

    /** @Route("/share-click/{class}/{target}", name="share_click", options={"expose":true}) */
    public function clickAction(Request $request, $class, $target)
    {
        if($request->isXmlHttpRequest() == false) {
            throw $this->createNotFoundException();
        }

        if ($this->get('prevent')->doubleClick()) {
            return new JsonResponse('DoubleClick');
        }

        $share = new Share();
        $share->setClass($class);
        $share->setTarget($target);

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();
        $em->persist($share);
        $em->flush($share);

        $count = $em->getRepository('VidalMainBundle:Share')->countBy($class, $target);

        return new JsonResponse($count);
    }

    /** @Route("/share/{class}/{target}", name="share", options={"expose":true}) */
    public function shareAction(Request $request, $class, $target)
    {
        if($request->isXmlHttpRequest() == false) {
            throw $this->createNotFoundException();
        }

        $my = trim($request->request->get('my', ''));
        $friends = $request->request->get('friends', null);
        $text = $request->request->get('text', null);

        if (!empty($my) && !empty($friends) && filter_var($request->request->get('my'), FILTER_VALIDATE_EMAIL)) {
            $emails = explode(';', $friends);
            $to = array();

            # проверяем валидность адресов
            foreach ($emails as $email) {
                $email = trim($email);
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    return new JsonResponse('FAIL');
                }
                $to[] = $email;
            }

            $url = $request->request->get('url', '');
            $title = urldecode($request->request->get('title', ''));

            # предотвратить отправку нескольких
            if ($this->get('prevent')->doubleClick()) {
                return new JsonResponse('DoubleClick');
            }

            # рассылаем
            $this->get('email.service')->send(
                $to,
                array('VidalMainBundle:Email:share.html.twig', array(
                    'text' => $text,
                    'url' => $url,
                    'title' => $title,
                )),
                $my . ' поделился(-ась) с Вами: ' . $title
            );

            $share = new Share();
            $share->setClass($class);
            $share->setTarget($target);

            /** @var EntityManager $em */
            $em = $this->getDoctrine()->getManager();
            $em->persist($share);
            $em->flush($share);

            return new JsonResponse(implode('; ', $to));
        }

        return new JsonResponse('FAIL');
    }
}
