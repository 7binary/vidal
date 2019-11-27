<?php

namespace Vidal\DrugBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

class AutocompleteController extends Controller
{
	/** @Route("/autocomplete/atc/{term}", name="autocomplete_atc", options={"expose"=true}) */
	public function autocompleteAtcAction($term)
	{
		$em   = $this->getDoctrine()->getManager('drug');
		$data = $em->getRepository('VidalDrugBundle:ATC')->adminAutocomplete($term);

		return new JsonResponse($data);
	}

	/** @Route("/autocomplete/nozology/{term}", name="autocomplete_nozology", options={"expose"=true}) */
	public function autocompleteNozologyAction($term)
	{
		$em   = $this->getDoctrine()->getManager('drug');
		$data = $em->getRepository('VidalDrugBundle:Nozology')->adminAutocomplete($term);

		return new JsonResponse($data);
	}

	/** @Route("/autocomplete/molecule/{term}", name="autocomplete_molecule", options={"expose"=true}) */
	public function autocompleteMoleculeAction($term)
	{
		$em   = $this->getDoctrine()->getManager('drug');
		$data = $em->getRepository('VidalDrugBundle:Molecule')->adminAutocomplete($term);

		return new JsonResponse($data);
	}

    /** @Route("/autocomplete/molecule_name/{term}", name="autocomplete_molecule_name", options={"expose"=true}) */
    public function autocompleteMoleculeNameAction($term)
    {
        $em   = $this->getDoctrine()->getManager('drug');
        $data = $em->getRepository('VidalDrugBundle:MoleculeName')->adminAutocomplete($term);

        return new JsonResponse($data);
    }

    /** @Route("/autocomplete/clphgroups/{term}", name="autocomplete_clphgroups", options={"expose"=true}) */
    public function autocompleteClPhGroups($term)
    {
        $em   = $this->getDoctrine()->getManager('drug');
        $data = $em->getRepository('VidalDrugBundle:ClPhGroups')->adminAutocomplete($term);

        return new JsonResponse($data);
    }

    /** @Route("/autocomplete/clphpointers/{term}", name="autocomplete_clphpointers", options={"expose"=true}) */
    public function autocompleteClPhPointers($term)
    {
        $em   = $this->getDoctrine()->getManager('drug');
        $data = $em->getRepository('VidalDrugBundle:ClinicoPhPointers')->adminAutocomplete($term);

        return new JsonResponse($data);
    }

    /** @Route("/autocomplete/phthgroups/{term}", name="autocomplete_phthgroups", options={"expose"=true}) */
    public function autocompletePhThGroups($term)
    {
        $em   = $this->getDoctrine()->getManager('drug');
        $data = $em->getRepository('VidalDrugBundle:PhThGroups')->adminAutocomplete($term);

        return new JsonResponse($data);
    }

	/** @Route("/autocomplete/infopage/{term}", name="autocomplete_infopage", options={"expose"=true}) */
	public function autocompleteInfopageAction($term)
	{
		$em   = $this->getDoctrine()->getManager('drug');
		$data = $em->getRepository('VidalDrugBundle:InfoPage')->adminAutocomplete($term);

		return new JsonResponse($data);
	}
}
