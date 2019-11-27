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
use Symfony\Component\HttpFoundation\JsonResponse;
use Vidal\BigMamaBundle\Entity\Video;

class VideoController extends Controller
{
    const PUBLICATIONS_PER_PAGE = 9;
    const PUBLICATIONS_PER_PHARM = 5;

    protected function isTestMode()
    {
        return $this->container->getParameter('big_mama.testMode');
    }

    /**
     * @Route("/big-mama/video", name="big_mama_video", options={"expose"=true})
     * @Template("VidalBigMamaBundle:Publication:list.html.twig")
     */
    public function publicationsAction(Request $request)
    {
        if($this->isTestMode() && !$request->get('test') && $request->get('test')!='true') {
            throw new AccessDeniedException();
        }

        $em = $this->getDoctrine()->getManager('big_mama');
        $page = $request->query->get('p', null);

        if ($page == 1) {
            return $this->redirect($this->generateUrl('big_mama_video'), 301);
        }elseif ($page == null) {
            $page = 1;
        }

        $params = array(
            'title' => 'Видео',
        );

        if ($page > 1) {
            $params['extra_title'] = ' - страница ' . $page;
            $params['extra_description'] = ' Страница ' . $page . '.';
        }

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $em->getRepository(Video::class)->findActive(false),
            $page,
            self::PUBLICATIONS_PER_PAGE
        );
        $pagination->setTemplate('VidalMainBundle:News:news_pagination.html.twig');

        $params['publicationsPagination'] = $pagination;
        $params['keywords'] = '';
        $params['page'] = $page;
        $params['isPromo'] = true;
        $params['publication_route'] = 'big_mama_video';
        $params['publication_item_route'] = 'big_mama_video_item';
        $params['publication_edit_route'] = 'admin_vidal_bigmama_video_edit';
        $params['publication_more'] = '/big-mama/video/more/';
        $params['imageClass'] = 'video-img';

        return $params;
    }

    /**
     * @Route("/big-mama/video/{link}", name="big_mama_video_item")
     * @Template("VidalBigMamaBundle:Publication:item.html.twig")
     */
    public function publicationAction(Request $request, $link)
    {
        if($this->isTestMode() && !$request->get('test') && $request->get('test')!='true') {
            throw new AccessDeniedException();
        }

        $em = $this->getDoctrine()->getManager('big_mama');
        $publication = $em->getRepository(Video::class)->findOneByLink($link);

        if (!$publication || $publication->getEnabled() === false) {
            throw $this->createNotFoundException();
        }

        $title = $this->strip($publication->getTitle());

        $textLinked = $publication->getBodyLinked();
        $text = empty($textLinked) ? $publication->getBody() : $textLinked;

        $description = $this->stripDescr($publication->getBody(), 185);

        return array(
            'isPromo' => true,
            'publication' => $publication,
            'text' => $text,
            'menu_left' => 'news',
            'keywords' => '',
            'seotitle' => $title,
            'ogTitle' => $title,
            'description' => $description,
            'all_items_route' => 'big_mama_video',
            'all_items_title' => 'Другие видео',
            'publication_edit_route' => 'admin_vidal_bigmama_video_edit'
        );
    }

    /**
     * @Route("/big-mama/video/more/{currPage}", name="big_mama_video_more", options={"expose"=true})
     * @Template("VidalBigMamaBundle:Publication:more.html.twig")
     */
    public function moreNewsAction(Request $request, $currPage)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager('big_mama');
        /** @var Publication[] $publications */
        $publications = $em->getRepository(Video::class)
            ->findMoreNews($currPage, self::PUBLICATIONS_PER_PAGE);

        $html = $this->renderView('VidalBigMamaBundle:Publication:more.html.twig', array(
            'publications' => $publications,
            'publication_item_route' => 'big_mama_video_item',
            'publication_edit_route' => 'admin_vidal_bigmama_video_edit'
        ));

        return new JsonResponse($html);
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
