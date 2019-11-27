<?php

namespace Vidal\MainBundle\Controller;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Vidal\MainBundle\Entity\Banner;
use Vidal\MainBundle\Entity\BannerGroup;

class BannerController extends Controller
{
    public static $used = array();
    public static $exclude_banner_mkb = true;

    /** @var \Google_Client */
    protected $client;
    /** @var \Google_Service_Analytics */
    protected $analytics;
    protected $analyticsViewId = 'ga:78472229';

    /**
     * @Route("/banner/spec-only", name="banner_spec_only")
     * @Template("VidalMainBundle:Banner:spec_only.html.twig")
     */
    public function specOnlyAction(Request $request)
    {
        $params = array(
            'title' => 'Вы являетесь дипломированным медицинским специалистом?',
            'url' => $request->query->get('url'),
        );

        return $params;
    }

    public function renderMobileGroupAction(Request $request, $groupId = null, $indexPage = false, $productPage = false, $vetPage = false, $ProductID = null, $isLogged = false)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine();

        $excludeRotate = true;

        /** @var Banner[] $banners */
        $banners = $em->getRepository('VidalMainBundle:Banner')->findMobile($groupId, $isLogged, $excludeRotate, $ProductID);

        if (empty($banners)) {
            return new Response();
        }

        $pathInfo = str_replace('/app_dev.php', '', $request->getRequestUri());
        $routeName = $request->get('_route');
        $style = null;

        if ($groupId == BannerGroup::TOP) {
            $style = 'margin-bottom:28px;';
        }
        elseif ($groupId == BannerGroup::BOTTOM) {
            $style = 'margin-top:0;';
        }

        return $this->render('VidalMainBundle:Banner:render.html.twig', array(
            'request' => $request,
            'banners' => $banners,
            'indexPage' => $indexPage,
            'productPage' => $productPage,
            'pathInfo' => $pathInfo,
            'routeName' => $routeName,
            'vetPage' => $vetPage,
            'style' => $style,
        ));
    }

    public function renderMobileAction(Request $request, $indexPage = false, $productPage = false, $vetPage = false, $isLogged = false)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine();
        /** @var Banner[] $banners */
        $banners = $em->getRepository('VidalMainBundle:Banner')->findMobile(null, $isLogged);

        if (empty($banners)) {
            return $this->render("VidalMainBundle::blank.html.twig");
        }

        $pathInfo = str_replace('/app_dev.php', '', $request->getRequestUri());
        $routeName = $request->get('_route');

        foreach ($banners as $banner) {
            if ($banner->getMobileProduct()) {
                $banner->setMustShow(true);
            }
        }

        return $this->render('VidalMainBundle:Banner:render.html.twig', array(
            'request' => $request,
            'banners' => $banners,
            'indexPage' => $indexPage,
            'productPage' => $productPage,
            'pathInfo' => $pathInfo,
            'routeName' => $routeName,
            'vetPage' => $vetPage,
        ));
    }

    public function renderMobileProductAction(Request $request, $indexPage = false, $vetPage = false, $nofollow = false, $isLogged = false, $banner_mkb = null)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine();
        /** @var Banner[] $banners */

        if ($banner_mkb && $bannerMkb = $em->getRepository('VidalMainBundle:Banner')->getBannerMkb()) {
            $banners = array($bannerMkb);
            self::$used[] = $bannerMkb->getId();
        }
        else {
            $banners = $em->getRepository('VidalMainBundle:Banner')->findMobileProduct($isLogged);
        }

        if (empty($banners)) {
            return new Response();
        }

        $pathInfo = str_replace('/app_dev.php', '', $request->getRequestUri());
        $routeName = $request->get('_route');

        return $this->render('VidalMainBundle:Banner:render.html.twig', array(
            'request' => $request,
            'banners' => $banners,
            'indexPage' => false,
            'productPage' => true,
            'mustShow' => true,
            'pathInfo' => $pathInfo,
            'routeName' => $routeName,
            'style' => 'margin-top:20px',
            'vetPage' => $vetPage,
            'nofollow' => $nofollow,
        ));
    }

    /**
     * Рендеринг баннеров асинхронно
     */
    public function renderAjaxAction(Request $request, $groupId, $indexPage = false, $vetPage = false, $nofollow = false, $ProductID = null, $isLogged = false)
    {
        return $this->render('VidalMainBundle:Banner:render_ajax.html.twig', array(
            'request' => $request,
            'indexPage' => $indexPage,
            'productPage' => false,
            'vetPage' => $vetPage,
            'nofollow' => $nofollow,
            'groupId' => $groupId,
            'used' => BannerController::$used,
            'exclude_banner_mkb' => BannerController::$exclude_banner_mkb ? 1 : 0,
            'ProductID' => $ProductID,
            'isLogged' => $isLogged,
        ));
    }

    /**
     * Рендеринг баннеров (с возможностью асинхронно)
     * @Route("/banner-render/{groupId}/{indexPage}/{vetPage}/{nofollow}/{via_ajax}/{exclude_banner_mkb}/{used}", name="banner_render", options={"expose":true})
     */
    public function renderAction(Request $request, $groupId, $indexPage = false, $vetPage = false, $nofollow = false, $via_ajax = false, $isLogged = null, $exclude_banner_mkb = true, $used = null, $ProductID = null)
    {
        if ($isLogged === null) {
            $isLogged = $request->query->get('isLogged', false);
        }

        /** @var EntityManager $em */
        $em = $this->getDoctrine();
        $device = $this->get('mobile_detect.mobile_detector');

        if ($exclude_banner_mkb && $bannerMkb = $em->getRepository("VidalMainBundle:Banner")->getBannerMkb()) {
            BannerController::$used[] = $bannerMkb->getId();
        }

        if (empty($ProductID)) {
            $ProductID = $request->query->get('ProductID', null);
        }

        /** @var Banner[] $banners */
        $banners = $em->getRepository('VidalMainBundle:Banner')->findByGroup($groupId, $isLogged, true, $device->isMobile(), $ProductID);

        if (empty($banners)) {
            return $this->render("VidalMainBundle::blank.html.twig");
        }

        $pathInfo = str_replace('/app_dev.php', '', $request->getRequestUri());
        $routeName = $request->get('_route');

        if ($via_ajax) {
            if ($request->isXmlHttpRequest() == false) {
                throw $this->createNotFoundException();
            }

            $bannersHtml = $this->renderView('VidalMainBundle:Banner:render.html.twig', array(
                'request' => $request,
                'banners' => $banners,
                'indexPage' => $indexPage,
                'productPage' => false,
                'pathInfo' => $pathInfo,
                'routeName' => $routeName,
                'vetPage' => $vetPage,
                'nofollow' => $nofollow,
                'via_ajax' => $via_ajax,
            ));

            return new Response($bannersHtml);
        }
        else {
            return $this->render('VidalMainBundle:Banner:render.html.twig', array(
                'request' => $request,
                'banners' => $banners,
                'indexPage' => $indexPage,
                'productPage' => false,
                'pathInfo' => $pathInfo,
                'routeName' => $routeName,
                'vetPage' => $vetPage,
                'nofollow' => $nofollow,
                'via_ajax' => $via_ajax,
            ));
        }
    }

    public function renderMobileGroupTopAction(Request $request, $groupId = null, $indexPage = false,
                                               $productPage = false, $vetPage = false, $isLogged = false, $ProductID = null)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine();
        /** @var Banner[] $banners */
        $banners = $em->getRepository('VidalMainBundle:Banner')->findMobile($groupId, $isLogged, true, $ProductID);

        if (empty($banners)) {
            return new Response();
        }

        $pathInfo = str_replace('/app_dev.php', '', $request->getRequestUri());
        $routeName = $request->get('_route');
        $style = null;

        if ($groupId == BannerGroup::TOP) {
            $style = 'margin-bottom:28px;';
        }
        elseif ($groupId == BannerGroup::BOTTOM) {
            $style = 'margin-top:0;';
        }

        return $this->render('VidalMainBundle:Banner:render.html.twig', array(
            'request' => $request,
            'banners' => $banners,
            'indexPage' => $indexPage,
            'productPage' => $productPage,
            'pathInfo' => $pathInfo,
            'routeName' => $routeName,
            'vetPage' => $vetPage,
            'style' => $style,
            'mustShow' => true,
            'ProductID' => $ProductID
        ));
    }

    public function renderSingleAction(Request $request, $bannerId)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine();
        /** @var Banner $banner */
        $banner = $em->getRepository('VidalMainBundle:Banner')->findEnabledById($bannerId);

        if (null == $banner) {
            return new Response();
        }

        $pathInfo = str_replace('/app_dev.php', '', $request->getRequestUri());
        $routeName = $request->get('_route');

        return $this->render('VidalMainBundle:Banner:render.html.twig', array(
            'request' => $request,
            'banners' => array($banner),
            'indexPage' => false,
            'productPage' => false,
            'pathInfo' => $pathInfo,
            'routeName' => $routeName,
        ));
    }

    /**
     * Добавить клик по банеру
     * @Route("/banner-clicked/{bannerId}", name="banner_clicked", options={"expose"=true})
     */
    public function bannerClickedAction($bannerId)
    {
        $this->getDoctrine()
            ->getRepository('VidalMainBundle:Banner')
            ->countClick($bannerId);

        return new Response();
    }

    private function checkRole()
    {
        $security = $this->get('security.context');
        if (!$security->isGranted('ROLE_SUPERADMIN') && !$security->isGranted('ROLE_ADMIN_VIDAL_RU')) {
            throw new AccessDeniedException();
        }
    }

    /**
     * @Route("/move-banners/{group}", name="move_banners")
     * @Template("VidalMainBundle:Banner:move_banners.html.twig")
     */
    public function moveBannersAction($group)
    {
        $this->checkRole();
        $bannersGrouped = $this->findBannersByGroup($group);

        $params = array(
            'title' => 'Перемещение баннеров',
            'bannersGrouped' => $bannersGrouped,
            'group' => $group,
        );

        return $params;
    }

    /**
     * @param string $group
     * @return array
     */
    private function findBannersByGroup($group)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        if ($group == 'mobile') {
            $bannersGrouped = $em->getRepository("VidalMainBundle:Banner")->findMobile(null, false, false, null, true);
        }
        elseif ($group == 'mobile-product') {
            $bannersGrouped = $em->getRepository("VidalMainBundle:Banner")->findMobileProduct(false, false, null, true);
        }
        elseif ($group == 'right') {
            $bannersGrouped = $em->getRepository("VidalMainBundle:Banner")->findByGroup(Banner::GROUP_RIGHT, false, false, false, null, true);
        }
        elseif ($group == 'left') {
            $bannersGrouped = $em->getRepository("VidalMainBundle:Banner")->findByGroup(Banner::GROUP_LEFT, false, false, false, null, true);
        }
        else {
            throw $this->createNotFoundException();
        }

        return $bannersGrouped;
    }

    /**
     * @Route("/move-banners-up/{group}/{bannerId}", name="move_banners_up")
     */
    public function moveBannersUpAction($group, $bannerId)
    {
        $this->checkRole();
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();
        $pdo = $em->getConnection();

        $currPosition = null;

        # выставляем у всех в группе ротации одинаковую позицию
        $bannersGrouped = $this->findBannersByGroup($group);
        foreach ($bannersGrouped as $key => $banners) {
            /** @var Banner[] $banners */
            if ($group == 'mobile') {
                $position = $banners[0]->getMobilePosition();
                foreach ($banners as $banner) {
                    $banner->setMobilePosition($position);
                    $em->flush();
                }
            }
            elseif ($group == 'mobile-product') {
                $position = $banners[0]->getMobileProductPosition();
                foreach ($banners as $banner) {
                    $banner->setMobilePosition($position);
                    $em->flush();
                }
            }
            else {
                $position = $banners[0]->getPosition();
                foreach ($banners as $banner) {
                    $banner->setPosition($position);
                    $em->flush();
                }
            }
            if ($key == $bannerId) {
                $currPosition = $position;
            }
        }

        # находим следующую ближайшую позицию
        $prevPosition = null;
        foreach ($bannersGrouped as $key => $banners) {
            /** @var Banner[] $banners */
            if ($group == 'mobile') {
                $position = $banners[0]->getMobilePosition();
                if ($position < $currPosition) {
                    $prevPosition = $position;
                }
            }
            elseif ($group == 'mobile-product') {
                $position = $banners[0]->getMobileProductPosition();
                if ($position < $currPosition) {
                    $prevPosition = $position;
                }
            }
            else {
                $position = $banners[0]->getPosition();
                if ($position < $currPosition) {
                    $prevPosition = $position;
                }
            }
        }

        # переставляем местами
        if ($prevPosition && $prevPosition < $currPosition) {
            if ($group == 'mobile') {
                $tmpPosition = 10000001;
                $pdo->prepare("UPDATE banner SET mobilePosition = {$tmpPosition} WHERE mobilePosition = {$prevPosition}")->execute();
                $pdo->prepare("UPDATE banner SET mobilePosition = {$prevPosition} WHERE mobilePosition = {$currPosition}")->execute();
                $pdo->prepare("UPDATE banner SET mobilePosition = {$currPosition} WHERE mobilePosition = {$tmpPosition}")->execute();
            }
            elseif ($group == 'mobile-product') {
                $tmpPosition = 10000002;
                $pdo->prepare("UPDATE banner SET mobileProductPosition = {$tmpPosition} WHERE mobileProductPosition = {$prevPosition}")->execute();
                $pdo->prepare("UPDATE banner SET mobileProductPosition = {$prevPosition} WHERE mobileProductPosition = {$currPosition}")->execute();
                $pdo->prepare("UPDATE banner SET mobileProductPosition = {$currPosition} WHERE mobileProductPosition = {$tmpPosition}")->execute();
            }
            else {
                $tmpPosition = 10000003;
                $pdo->prepare("UPDATE banner SET position = {$tmpPosition} WHERE position = {$prevPosition}")->execute();
                $pdo->prepare("UPDATE banner SET position = {$prevPosition} WHERE position = {$currPosition}")->execute();
                $pdo->prepare("UPDATE banner SET position = {$currPosition} WHERE position = {$tmpPosition}")->execute();
            }
        }

        return $this->redirect($this->generateUrl('move_banners', array('group' => $group)));
    }

    /**
     * @Route("/move-banners-down/{group}/{bannerId}", name="move_banners_down")
     */
    public function moveBannersdownAction($group, $bannerId)
    {
        $this->checkRole();
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();
        $pdo = $em->getConnection();

        $currPosition = null;

        # выставляем у всех в группе ротации одинаковую позицию
        $bannersGrouped = $this->findBannersByGroup($group);
        foreach ($bannersGrouped as $key => $banners) {
            /** @var Banner[] $banners */
            if ($group == 'mobile') {
                $position = $banners[0]->getMobilePosition();
                foreach ($banners as $banner) {
                    $banner->setMobilePosition($position);
                    $em->flush();
                }
            }
            elseif ($group == 'mobile-product') {
                $position = $banners[0]->getMobileProductPosition();
                foreach ($banners as $banner) {
                    $banner->setMobilePosition($position);
                    $em->flush();
                }
            }
            else {
                $position = $banners[0]->getPosition();
                foreach ($banners as $banner) {
                    $banner->setPosition($position);
                    $em->flush();
                }
            }
            if ($key == $bannerId) {
                $currPosition = $position;
            }
        }

        # находим следующую ближайшую позицию
        $nextPosition = null;
        foreach ($bannersGrouped as $key => $banners) {
            /** @var Banner[] $banners */
            if ($group == 'mobile') {
                $position = $banners[0]->getMobilePosition();
                if ($nextPosition === null && $position > $currPosition) {
                    $nextPosition = $position;
                }
            }
            elseif ($group == 'mobile-product') {
                $position = $banners[0]->getMobileProductPosition();
                if ($nextPosition === null && $position < $currPosition) {
                    $nextPosition = $position;
                }
            }
            else {
                $position = $banners[0]->getPosition();
                if ($nextPosition === null && $position < $currPosition) {
                    $nextPosition = $position;
                }
            }
        }

        # переставляем местами
        if ($nextPosition && $nextPosition > $currPosition) {
            if ($group == 'mobile') {
                $tmpPosition = 10000004;
                $pdo->prepare("UPDATE banner SET mobilePosition = {$tmpPosition} WHERE mobilePosition = {$nextPosition}")->execute();
                $pdo->prepare("UPDATE banner SET mobilePosition = {$nextPosition} WHERE mobilePosition = {$currPosition}")->execute();
                $pdo->prepare("UPDATE banner SET mobilePosition = {$currPosition} WHERE mobilePosition = {$tmpPosition}")->execute();
            }
            elseif ($group == 'mobile-product') {
                $tmpPosition = 10000005;
                $pdo->prepare("UPDATE banner SET mobileProductPosition = {$tmpPosition} WHERE mobileProductPosition = {$nextPosition}")->execute();
                $pdo->prepare("UPDATE banner SET mobileProductPosition = {$nextPosition} WHERE mobileProductPosition = {$currPosition}")->execute();
                $pdo->prepare("UPDATE banner SET mobileProductPosition = {$currPosition} WHERE mobileProductPosition = {$tmpPosition}")->execute();
            }
            else {
                $tmpPosition = 10000006;
                $pdo->prepare("UPDATE banner SET position = {$tmpPosition} WHERE position = {$nextPosition}")->execute();
                $pdo->prepare("UPDATE banner SET position = {$nextPosition} WHERE position = {$currPosition}")->execute();
                $pdo->prepare("UPDATE banner SET position = {$currPosition} WHERE position = {$tmpPosition}")->execute();
            }
        }

        return $this->redirect($this->generateUrl('move_banners', array('group' => $group)));
    }

    /**
     * @Route("/banner/stats/{id}/{hash}", name="banners_stats_item")
     * @Template("VidalMainBundle:Banner:banner_stats_item.html.twig")
     */
    public function bannerStatsItemAction(Request $request, $id, $hash)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();
        $clickDays = $em->getRepository('VidalMainBundle:Banner')->statsDays($id);
        $clickHours = $em->getRepository('VidalMainBundle:Banner')->statsHours($id);
        $clickTotal = $em->getRepository('VidalMainBundle:Banner')->clickTotal($id);

        /** @var Banner $banner */
        $banner = $em->getRepository('VidalMainBundle:Banner')->findOneById($id);
        $md5 = md5($banner->getId() . '_secret');

        if ($banner == null || $md5 !== $hash) {
            throw $this->createNotFoundException();
        }

        $security = $this->get('security.context');
        if ($banner->getOpened() == false && $security->isGranted('ROLE_ADMIN') == false) {
            throw $this->createNotFoundException();
        }

        $title = $banner->getTitle();
        $totalShows = 0;

        $this->initializeAnalytics();
        $startDate = '2014-01-01';
        $endDate = '2030-01-01';

        $filters = 'ga:eventCategory==Показ баннера: ' . $title;
        $gaResult = $this->analytics->data_ga->get($this->analyticsViewId, $startDate, $endDate, 'ga:totalEvents', array(
            'dimensions' => 'ga:eventCategory, ga:eventAction, ga:eventLabel',
            'filters' => $filters,
        ));

        $params = array(
            'title' => 'Статистика по баннеру: ' . $title,
            'banner' => $banner,
            'clickDays' => json_encode($clickDays),
            'clickHours' => json_encode($clickHours),
            'clickTotal' => $clickTotal,
            'gaResult' => $gaResult,
        );

        if (!empty($gaResult['totalsForAllResults']) && !empty($gaResult['totalsForAllResults']['ga:totalEvents'])) {
            $totalShows += intval($gaResult['totalsForAllResults']['ga:totalEvents']);
        }

        # если для мобильного баннера создавали отдельное событие
        $titleMobile = $banner->getTitleMobile();

        if (!empty($titleMobile)) {
            $filtersMobile = 'ga:eventCategory==Показ баннера: ' . $titleMobile;
            $gaResultMobile = $this->analytics->data_ga->get($this->analyticsViewId, $startDate, $endDate, 'ga:totalEvents', array(
                'dimensions' => 'ga:eventCategory, ga:eventAction, ga:eventLabel',
                'filters' => $filtersMobile,
            ));
            $params['gaResultMobile'] = $gaResultMobile;

            if (!empty($gaResultMobile['totalsForAllResults']) && !empty($gaResultMobile['totalsForAllResults']['ga:totalEvents'])) {
                $totalShows += intval($gaResultMobile['totalsForAllResults']['ga:totalEvents']);
            }
        }

        $params['totalShows'] = $totalShows;

        return $params;
    }

    private function initializeAnalytics()
    {
        $key = $this->container->get('kernel')->getRootDir() . '/../src/Vidal/DrugBundle/Command/Analytics/ga.json';

        $client = new \Google_Client();
        $client->setApplicationName("Hello Analytics Reporting");
        $client->setAuthConfig($key);
        $client->setScopes(['https://www.googleapis.com/auth/analytics.readonly']);
        $client->addScope('https://www.googleapis.com/auth/webmasters.readonly');
        $client->addScope('https://www.googleapis.com/auth/webmasters');

        $guzzleClient = new \GuzzleHttp\Client(['verify' => false]);
        $client->setHttpClient($guzzleClient);

        $this->client = $client;
        $this->analytics = new \Google_Service_Analytics($this->client);
    }

    /**
     * @param $bannerId
     * @return Banner
     */
    private function findBanner($bannerId)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $banner = $em->getRepository('VidalMainBundle:Banner')->findOneById($bannerId);

        if ($banner == null) {
            throw $this->createNotFoundException('Banner not found');
        }

        return $banner;
    }
}
