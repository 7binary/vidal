<?php

namespace Vidal\MainBundle\Controller;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Vidal\DrugBundle\Entity\Publication;

class NewsController extends Controller
{
    const PUBLICATIONS_PER_PAGE = 22;
    const PUBLICATIONS_PER_PHARM = 5;

    /** @Route("/novosti/novosti_{id}.{ext}", defaults={"ext"="html"}) */
    public function r1($id)
    {
        return $this->redirect($this->generateUrl('publication', array('id' => $id)), 301);
    }

    /**
     * @Route("/next-publication/{id}", name="next_publication", options={"expose"=true})
     * @Template("VidalMainBundle:News:next_publication.html.twig")
     */
    public function nextPublicationAction(Request $request, $id)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager('drug');
        $project = $request->query->get('project', null);
        /** @var Publication $publication */
        $publication = $em->getRepository('VidalDrugBundle:Publication')
            ->findPrevPublication($id, $project);

        $textLinked = $publication->getBodyLinked();
        $text = empty($textLinked) ? $publication->getBody() : $textLinked;

        $html = $this->renderView('VidalMainBundle:News:next_publication.html.twig', array(
            'publication' => $publication,
            'text' => $text,
        ));

        return new JsonResponse(array(
            'html' => $html,
            'nextId' => $publication->getId(),
        ));
    }

    /**
     * @Route("/more-news/{currPage}", name="more_news", options={"expose"=true})
     * @Template("VidalMainBundle:News:more_news.html.twig")
     */
    public function moreNewsAction(Request $request, $currPage)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager('drug');
        /** @var Publication[] $publications */
        $publications = $em->getRepository('VidalDrugBundle:Publication')
            ->findMoreNews($currPage, self::PUBLICATIONS_PER_PAGE);

        $html = $this->renderView('VidalMainBundle:News:more_news.html.twig', array(
            'publications' => $publications
        ));

        return new JsonResponse($html);
    }

    /**
     * @Route("/novosti/{id}", name="publication")
     * @Template("VidalMainBundle:News:publication.html.twig")
     */
    public function publicationAction(Request $request, $id)
    {
        /** @var EntityManager $em */
        /** @var Publication $publication */

        $em = $this->getDoctrine()->getManager('drug');

        if (is_numeric($id)) {
            if ($publication = $em->getRepository('VidalDrugBundle:Publication')->findOneById($id)) {
                $linkedId = $publication->getLink() . '-' . $id;
                return $this->redirect($this->generateUrl('publication', array('id' => $linkedId)), 301);
            }
        }

        $parts = explode('-', $id);
        $id = array_pop($parts);
        $publication = $em->getRepository('VidalDrugBundle:Publication')->findOneById($id);

        if ((!$publication || $publication->getEnabled() === false) && !$request->query->has('test')) {
            throw $this->createNotFoundException();
        }

        $link = implode('-', $parts);

        if ($link != $publication->getLink()) {
            $linkedId = $publication->getLink() . '-' . $id;
            return $this->redirect($this->generateUrl('publication', array('id' => $linkedId)), 301);
        }

        $invisible = $this->get('security.context')->isGranted('ROLE_INVISIBLE');
        if ($invisible == false && $publication->getInvisible()) {
            throw $this->createNotFoundException();
        }

        $title = $this->strip($publication->getTitle());

        $textLinked = $publication->getBodyLinked();
        $text = empty($textLinked) ? $publication->getBody() : $textLinked;

        $description = $this->stripDescr($publication->getBody(), 185);

        return array(
            'publication' => $publication,
            'text' => $text,
            'menu_left' => 'news',
            'keywords' => '',
            'seotitle' => $title . ' - Новости Видаль - cправочник лекарственных препаратов',
            'ogTitle' => $title,
            'nextPublication' => $em->getRepository('VidalDrugBundle:Publication')->findNextPublication($id),
            'prevPublication' => $em->getRepository('VidalDrugBundle:Publication')->findPrevPublication($id),
            'description' => $description,
        );
    }

    /**
     * Дополнительные случайные новости
     * @Route("/news/random/{id}", name="news_random", requirements={"id":"\d+"}, options={"expose":true})
     */
    public function newsRandomAction(Request $request, $id)
    {
        if ($request->isXmlHttpRequest() == false) {
            throw $this->createNotFoundException();
        }

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager('drug');

        $params = array(
            'randomPublications' => $em->getRepository('VidalDrugBundle:Publication')->findRandomPublications($id)
        );

        $html = $this->renderView("VidalMainBundle:News:news_random.html.twig", $params);

        return new JsonResponse($html);
    }

    /**
     * @Route("/novosti", name="news", options={"expose"=true})
     * @Template("VidalMainBundle:News:news.html.twig")
     */
    public function newsAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager('drug');
        $page = $request->query->get('p', null);
        $testMode = $request->query->has('test');
        $invisible = $this->get('security.context')->isGranted('ROLE_INVISIBLE');

        if ($page == 1) {
            return $this->redirect($this->generateUrl('news'), 301);
        }
        elseif ($page == null) {
            $page = 1;
        }

        $params = array(
            'menu_left' => 'news',
            'title' => 'Новости медицины и фармации',
        );

        if ($page > 1) {
            $params['extra_title'] = ' - страница ' . $page;
            $params['extra_description'] = ' Страница ' . $page . '.';
        }

        if ($page == 1) {
            $params['publicationsPriority'] = $em->getRepository('VidalDrugBundle:Publication')->findLastPriority($testMode);
        }

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $em->getRepository('VidalDrugBundle:Publication')->getQueryEnabled($testMode, $invisible),
            $page,
            self::PUBLICATIONS_PER_PAGE
        );
        $pagination->setTemplate('VidalMainBundle:News:news_pagination.html.twig');

        $params['publicationsPagination'] = $pagination;
        $params['keywords'] = '';
        $params['page'] = $page;

        return $params;
    }

    public function leftAction()
    {
        $em = $this->getDoctrine()->getManager('drug');

        return $this->render('VidalMainBundle:News:left.html.twig', array(
            'publications' => $em->getRepository('VidalDrugBundle:Publication')->findLeft(),
        ));
    }

    private function strip($string)
    {
        $string = strip_tags(html_entity_decode($string, ENT_QUOTES, 'UTF-8'));
        $string = preg_replace('/&nbsp;|®|™/', '', $string);

        return $string;
    }

    private function stripDescr($string, $maxLength = null)
    {
        $string = strip_tags(html_entity_decode($string, ENT_QUOTES, 'UTF-8'));
        $string = preg_replace('/(®|™)/iu', '', $string);
        $string = str_replace('&nbsp;', ' ', $string);
        $string = trim(preg_replace('/\s+/', ' ', $string));
        $string = mb_substr($string, 0, 175, 'utf-8');

        return $string;
    }
}
