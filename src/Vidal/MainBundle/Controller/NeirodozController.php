<?php
namespace Vidal\MainBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Constraints\NotBlank;
use Vidal\MainBundle\Entity\AstrazenecaFaq;
use Lsw\SecureControllerBundle\Annotation\Secure;

class NeirodozController extends Controller
{
    /**
     * @Route("/neiro_doz", name="neirodoz")
     * @Secure(roles="IS_AUTHENTICATED_REMEMBERED")
     * @Template("VidalMainBundle:NeiroDoz:home.html.twig")
     */
    public function numb11erAction(Request $request)
    {
		if ($request->query->get('test') != 'test') {
			throw $this->createNotFoundException();
		}

        $params = array(
			'noHofitol' => 'true',
		);

        return $params;
    }
}