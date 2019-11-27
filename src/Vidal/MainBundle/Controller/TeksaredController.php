<?php

namespace Vidal\MainBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Lsw\SecureControllerBundle\Annotation\Secure;

class TeksaredController extends Controller
{
    /**
     * @Route("/pomoch-pri-boli", name="pomoch-pri-boli")
	 * @Template("VidalMainBundle:Teksared:index.html.twig")
     */
    public function indexAction(Request $request)
    {
        return [];
    }
}
