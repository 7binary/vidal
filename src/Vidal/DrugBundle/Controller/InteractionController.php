<?php

namespace Vidal\DrugBundle\Controller;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Vidal\DrugBundle\Entity\Interaction;

class InteractionController extends Controller
{
    const ITEMS_PER_PAGE = 50;

    /**
     * @Route("/drugs/more-interactions/{currPage}", name="more_interactions", options={"expose"=true})
     */
    public function moreNewsAction(Request $request, $currPage)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager('drug');
        $interactions = $em->getRepository('VidalDrugBundle:Interaction')
            ->findMoreInteractions($currPage, self::ITEMS_PER_PAGE);

        $html = $this->renderView('VidalDrugBundle:Interaction:more_interactions.html.twig', array(
            'interactions' => $interactions
        ));

        return new JsonResponse($html);
    }

    /**
     * @Route("/drugs/interaction", name="interaction")
     * @Template("VidalDrugBundle:Interaction:interaction.html.twig")
     */
    public function interactionAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager('drug');
        $l = $request->query->get('l', null);
        $q = $request->query->get('q', null);
        $p = $request->query->get('p', 1);

        $params = array(
            'title' => 'Лекарственное взаимодействие',
            'l' => $l,
            'q' => $q,
            'p' => $p,
            'keywords' => '',
            'seotitle' => 'Лекарственное взаимодействие | Vidal.ru - справочник лекарственных препаратов',
            'description' => 'Проверка лекарственного взаимодействия производится только для активных веществ лекарственных средств. Вы получаете перечень наиболее вероятных взаимодействий для данного активного вещества',
        );

        if ($l) {
            $params['interactions'] = $em->getRepository('VidalDrugBundle:Interaction')->findByLetter($l);
            $params['description'] = "Проверка лекарственного взаимодействия производится только для активных веществ лекарственных средств. Буква "
                . $l . ". Вы получаете перечень наиболее вероятных взаимодействий для данного активного вещества";
            $params['seotitle'] = "Лекарственное взаимодействие, буква "
                . $l . " | Vidal.ru - справочник лекарственных препаратов»";
        }
        elseif ($q) {
            $params['interactions'] = mb_strlen($q, 'utf-8') < 2
                ? null
                : $em->getRepository('VidalDrugBundle:Interaction')->findByQuery($q);
        }
        else {
            $query = $em->getRepository('VidalDrugBundle:Interaction')->getQuery();
            $pagination = $this->get('knp_paginator')->paginate($query, $p, self::ITEMS_PER_PAGE);
            $pagination->setTemplate('VidalMainBundle:News:news_pagination.html.twig');
            $params['pagination'] = true;
            $params['interactions'] = $pagination;

            if ($p > 1) {
                $params['description'] = "Проверка лекарственного взаимодействия производится только для активных веществ лекарственных средств. Страница "
                    . $p . ". Вы получаете перечень наиболее вероятных взаимодействий для данного активного вещества";
                $params['seotitle'] = "Лекарственное взаимодействие, страница "
                    . $p . " | Vidal.ru - справочник лекарственных препаратов";
            }
        }

        if ($p > 1) {
            $params['extra_title'] = ' - страница ' . $p;
            $params['extra_description'] = ' Страница ' . $p . '.';
        }

        return $params;
    }

    /**
     * @Route("/drugs/interaction/{EngName}", name="interaction_item")
     * @Template("VidalDrugBundle:Interaction:interaction_item.html.twig")
     */
    public function interactionItemAction($EngName)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager('drug');
        /** @var Interaction $interaction */
        $interaction = $em->getRepository('VidalDrugBundle:Interaction')->findOneByEngName($EngName);

        if (!$interaction) {
            throw $this->createNotFoundException();
        }

        $text = $interaction->getTextLinked();
        $text = str_replace('Эффекты при одновременном применении с препаратами', '<b>Эффекты при одновременном применении с препаратами</b>', $text);

        $params = array(
            'interaction' => $interaction,
            'keywords' => '',
            'text' => $text,
        );

        $rusName = $interaction->getRusName();

        if (!empty($rusName)) {
            $rusName = mb_strtoupper(mb_substr($rusName, 0, 1, 'utf-8'), 'utf-8') . mb_strtolower(mb_substr($rusName, 1, null, 'utf-8'), 'utf-8');
            $title = $rusName
                . " лекарственное взаимодействие с другими препаратами (совместимость) | Vidal.ru - справочник лекарственных препаратов";
            $params['seotitle'] = $title;
            $params['breadcrumb'] = $title;
            $params['description'] = "Лекарственное взаимодействие средства "
                . mb_strtolower($rusName, 'utf-8')
                . " - побочные эффекты при одновременном применении (совместимость) с другими препаратами в справочнике Видаль 2018";
        }

        $params['rusName'] = $rusName;

        return $params;
    }
}
