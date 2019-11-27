<?php
namespace Vidal\MainBundle\Controller;

use Vidal\DrugBundle\Controller\SonataController;
use Vidal\DrugBundle\Entity\Publication;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use FOS\RestBundle\Controller\Annotations as Rest;
use albaraam\gcmapns\Message;
use albaraam\gcmapns\Client;
use Symfony\Component\Validator\Constraints\NotBlank;
use Vidal\MainBundle\Entity\UserDevice;

class PushController extends Controller
{
    /**
     * @Route("/push3" ,name="api_push_3")
     * @Template("VidalMainBundle:Push:push_3.html.twig")
     */
    public function push3Action(Request $request)
    {
        $params = array('title' => 'Тестирование Google Cloud Messaging');

        $form = $this->createFormBuilder()
            ->add('fields', 'textarea', array(
                'label' => 'Введите поля формата JSON',
                'required' => true,
                'constraints' => new NotBlank(array('message' => 'Введите поля формата JSON'))
            ))
            ->add('submit', 'submit', array('label' => 'Отправить'))
            ->getForm();

        $form->handleRequest($request);

        if ($form->isValid()) {
            $formData = $form->getData();
            $fields = $formData['fields'];
            $key = \Vidal\DrugBundle\Controller\SonataController::PUSH_ACCESS_KEY;

            $headers = array
            (
                'Authorization: key=' . $key,
                'Content-Type: application/json'
            );

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://android.googleapis.com/gcm/send');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);

            $params['result'] = curl_exec($ch);
            curl_close($ch);
        }

        $params['form'] = $form->createView();

        return $params;
    }

    /**
     * @Route("/push-test/{id}/{email}/{ios}" ,name="api_push_test")
     */
    public function pushTestAction($id, $email, $ios = null)
    {
        /** @var EntityManager $emDrug */
        $emDrug = $this->getDoctrine()->getManager('drug');
        /** @var EntityManager $emMain */
        $emMain = $this->getDoctrine()->getManager();

        /** @var Publication $publication */
        $publication = $emDrug->getRepository('VidalDrugBundle:Publication')->findOneById($id);

        if (!$publication) {
            return;
        }

		$emails = array($email);

        $deviceIds = $emMain->getRepository('VidalMainBundle:UserDevice')->findByEmails($emails, $ios);

		$deviceGroups = array(
			'AAAAKRinc0c:APA91bGqaRMPOYs5ygk3Epe7U3UGL51j360aMR26MWZC8fFal3FEJltMiAq023aCsWbdU_84xB3KguBz_nAaQyQCBkgDudfFk6Gmk1Lh3HoMCTuv7H0D7WJo_HiwU1J4T1vtgNLDNUgUg9L5HHfqrWcVP4WkWp97Nw' => $deviceIds,
		);

        foreach ($deviceGroups as $gcm => $devices) {
            $this->send($devices, $gcm, $publication, $ios);
        }
        exit;
    }

	/**
	 * @Route("/push-test-neuro/{id}/{email}/{ios}" ,name="api_push_test_neuro")
	 */
	public function pushTestNeuroAction($id, $email, $ios = null)
	{
		/** @var EntityManager $emDrug */
		$emDrug = $this->getDoctrine()->getManager('drug');
		/** @var EntityManager $emMain */
		$emMain = $this->getDoctrine()->getManager();

		/** @var Publication $publication */
		$publication = $emDrug->getRepository('VidalDrugBundle:Publication')->findOneById($id);

		if (!$publication) {
			return;
		}

		$emails = array($email);

		$deviceIds = $emMain->getRepository('VidalMainBundle:UserDevice')->findNeuroByEmails($emails, $ios);

		$deviceGroups = array(
			'AAAAKRinc0c:APA91bGqaRMPOYs5ygk3Epe7U3UGL51j360aMR26MWZC8fFal3FEJltMiAq023aCsWbdU_84xB3KguBz_nAaQyQCBkgDudfFk6Gmk1Lh3HoMCTuv7H0D7WJo_HiwU1J4T1vtgNLDNUgUg9L5HHfqrWcVP4WkWp97Nw' => $deviceIds,
		);

		foreach ($deviceGroups as $gcm => $devices) {
			$this->send($devices, $gcm, $publication, $ios);
		}
		exit;
	}

    /**
     * @Route("/pushed/{project}/{publication_id}/{email}/{ios}" ,name="pushed")
     */
    public function pushesAction($project, $publication_id, $email, $ios = null)
    {
        /** @var EntityManager $emDrug */
        $emDrug = $this->getDoctrine()->getManager('drug');
        /** @var EntityManager $emMain */
        $emMain = $this->getDoctrine()->getManager();

        /** @var Publication $publication */
        $publication = $emDrug->getRepository('VidalDrugBundle:Publication')->findOneById($publication_id);

        if (!$publication) {
            echo "<span style='font-family: Helvetica, Arial, sans-serif'>Ошибка: не найдена
                новость по идентфикатору -- <b>$publication_id</b></span>";
            exit;
        }

        $emails = array($email);
        $deviceIds = $emMain->getRepository('VidalMainBundle:UserDevice')->devicesByEmails($project, $emails, $ios);

        if (empty($deviceIds)) {
            echo "<span style='font-family: Helvetica, Arial, sans-serif'>Ошибка: не найдено 
                ни одного устройства по E-mail -- <b>$email</b></span>";
            exit;
        }

        $deviceGroups = array(
            'AAAAKRinc0c:APA91bGqaRMPOYs5ygk3Epe7U3UGL51j360aMR26MWZC8fFal3FEJltMiAq023aCsWbdU_84xB3KguBz_nAaQyQCBkgDudfFk6Gmk1Lh3HoMCTuv7H0D7WJo_HiwU1J4T1vtgNLDNUgUg9L5HHfqrWcVP4WkWp97Nw' => $deviceIds,
        );

        foreach ($deviceGroups as $gcm => $devices) {
            $this->send($devices, $gcm, $publication, $ios);
        }

        exit;
    }

    private function send($deviceIds, $gcmKey, Publication $publication, $ios)
    {
		if (empty($deviceIds)) {
			return;
		}

		/** @var EntityManager $emMain */
		$emMain = $this->getDoctrine()->getManager();
		/** @var UserDevice $device */
		$device = $emMain->getRepository('VidalMainBundle:UserDevice')->findOneByAndroidId($deviceIds[0]);

		if (empty($device)) {
			return;
		}

		if ($device->getIos() || $ios) {
			echo "<h1>IOS TEST</h1>" . PHP_EOL;
			$fields = array(
				"registration_ids" => $deviceIds,
				'data' => array(
					'id' => $publication->getId()
				),
				'notification' => array(
					'title' => $this->strip($publication->getTitle()),
					'body' => $this->strip($publication->getAnnounce()),
					'badge' => 1,
					'sound' => 'default',
				)
			);
		}
		else {
			echo "<h1>ANDROID TEST</h1>" . PHP_EOL;
			$fields = array(
				"registration_ids" => $deviceIds,
				"data" => array(
					'body' => $this->strip($publication->getAnnounce()),
					'title' => $this->strip($publication->getTitle()),
					'badge' => 1,
					'sound' => 'default',
					'id' => $publication->getId(),
				),
			);
		}

        $fields = json_encode($fields);

        $headers = array(
            'Authorization: key=' . $gcmKey,
            'Content-Type: application/json'
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://android.googleapis.com/gcm/send');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);

        $result = curl_exec($ch);
        curl_close($ch);

        echo "<h1>FIELDS for GCM-KEY: $gcmKey</h1>" . PHP_EOL;
        echo $fields;
        echo PHP_EOL . '<hr/>' . PHP_EOL;
        echo "<h1>RESULT for GCM-KEY: $gcmKey</h1>" . PHP_EOL;
        echo $result;
        echo PHP_EOL . '<hr/>' . PHP_EOL;
    }

    private function strip($string)
    {
        $string = strip_tags(html_entity_decode($string, ENT_QUOTES, 'UTF-8'));

        return trim(str_replace(explode(' ', '® ™'), '', $string));
    }

	//    const PUSH_ACCESS_KEY = 'AIzaSyB1uwAYUc3fACSgbPeX6Yo_50t0jToTvZo'; // - старый
	const PUSH_ACCESS_KEY = 'AIzaSyA8GAOCF76ZZ6PVafYY9FD6HUvh2jzEZvg'; // - новый
	//'AIzaSyAx8MEOFMosD2D8VGPLyLe-diBed6tv4to' - новый 2
}
