<?php

namespace Vidal\MainBundle\Controller;

use Doctrine\ORM\EntityManager;
use Elasticsearch\Common\Exceptions\Forbidden403Exception;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Lsw\SecureControllerBundle\Annotation\Secure;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class InfoController extends Controller
{
    /**
     * @Route("o-nas/Priobreteniye-spravochnikov", name="priobretenie")
     * @Template()
     */
    public function priobretenieAction()
    {
        return array();
    }

    /**
     * @Route("/shkola-zdorovya/calculator.html", name="calculate")
     * @Template("VidalMainBundle:Info:calculate.html.twig")
     */
    public function calculateAction()
    {
        return array(
            'title' => 'Калькулятор подбора полезной воды'
        );
    }

    /** @Route("/check-keyvalue/{key}/{value}", name="check_keyvalue", options={"expose":true}) */
    public function checkValue(Request $request, $key, $value)
    {
        if ($request->isXmlHttpRequest() == false) {
            throw $this->createNotFoundException();
        }

        $em = $this->getDoctrine()->getManager();
        $isMatch = $em->getRepository('VidalMainBundle:KeyValue')->checkMatch($key, $value);

        return new JsonResponse($isMatch);
    }

    /**
     * @Route("/download", name="download", options={"expose":true})
     */
    public function downloadAction(Request $request)
    {
        $filename = $request->query->get('filename', null);

        if (!$this->get('security.context')->isGranted('ROLE_DOCTOR')) {
            return $this->redirect($this->generateUrl('no_download', array('filename' => $filename)), 301);
        }

        if (empty($filename)) {
            throw $this->createNotFoundException();
        }

        if (preg_match('/^users(.*)\\.xlsx?$/i', $filename)) {
            $em = $this->getDoctrine()->getManager();
            $pw = $request->query->get('pw', null);
            $hasAccess = $em->getRepository('VidalMainBundle:KeyValue')->checkMatch('users', $pw);

            if (!$hasAccess) {
                throw $this->createNotFoundException();
            }
        }

        if (preg_match('/^upload_users/i', $filename)) {
            if (false === $this->get('security.context')->isGranted('ROLE_ADMIN')) {
                return $this->redirect($this->generateUrl('no_download', array('filename' => $filename)), 301);
            }
        }

        header("Location: https://vidal.ru/archive/" . $filename);
        die();
    }

    /**
     * @Route("/no-download", name="no_download")
     * @Template("VidalMainBundle:Info:no_download.html.twig")
     */
    public function noDownloadAction(Request $request)
    {
        $filename = $request->query->get('filename', null);

        return array('filename' => $filename);
    }

    /**
     * @Route("/opcache-reset", name="opcache_reset")
     */
    public function opcacheResetAction()
    {
        if ($this->container->getParameter('kernel.environment') !== 'dev') {
            throw $this->createNotFoundException();
        }

        opcache_reset();
        echo 'OPCACHE_RESET';
        exit;
    }
}
