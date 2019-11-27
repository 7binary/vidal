<?php

namespace Vidal\MainBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Vidal\MainBundle\Entity\Poll;
use Vidal\MainBundle\Entity\PollQuestion;
use Vidal\MainBundle\Entity\PollAnswer;

/**
 * Class PollController
 *
 * @package Vidal\MainBundle\Controller
 */
class PollController extends Controller
{

	/**
	 * @param Request $request
	 * @param Integer $pollId Индентификатор теста
	 * @param Integer $qId индентификатор предыдущего вопроса. Если 0, то вопрос первый
	 * @return array | Response
	 * @Route("/poll/{pollId}/{qId}", name="poll", defaults={"qId" = "0"}, options={"expose"=true})
	 * @Template()
	 */
	public function pollAction(Request $request, $pollId, $qId = 0)
	{
		$session = $request->getSession();

		$em   = $this->getDoctrine()->getManager();
		$poll = $em->getRepository('VidalMainBundle:Poll')->findOneById($pollId);

		if (!$poll) {
			return null;
		}

		$question = $em->getRepository('VidalMainBundle:PollQuestion')->findOneById($qId);
		$count    = count($em->getRepository('VidalMainBundle:PollQuestion')->findByPoll($poll));

		if ($request->getMethod() == 'POST') {
			$answer = new PollAnswer();
			$answer->setPoll($poll);
			$answer->setQuestion($question);
			$answer->setTitle($request->request->get('answer'));
			$em->persist($answer);
			$em->flush($answer);
		}
		if ($qId == 0) {
			#Первый вопрос
			$question = $em->getRepository('VidalMainBundle:PollQuestion')->findFirst($poll);
			$options  = $question->getOptions();
			if ($session->get('poll') != null) {
				exit;
			}
			else {
				return array('question' => $question, 'questionNumber' => 1, 'questionCount' => $count, 'poll' => $poll, 'options' => $options);
			}
		}
		else {
			#Следующий вопрос
			$question = $em->getRepository('VidalMainBundle:PollQuestion')->findNext($poll, $qId);
			$opts     = null;
			if ($question) {
				$options = $question->getOptions();
				foreach ($options as $val) {
					$opts[] = $val->getTitle();
				}
				$options = $opts;
			}

			if ($question) {
				#Есть еще один вопрос
				$data = array(
					'next'    => 'Ok',
					'pollId'  => $question->getPoll()->getId(),
					'id'      => $question->getId(),
					'title'   => $question->getTitle(),
					'options' => $options
				);
			}
			else {
				#Больше нету, показываем концовку
				$session->set('poll', 1);

				$data = array(
					'next'    => 'End',
					'pollId'  => $poll->getId(),
					'id'      => null,
					'title'   => '<div style="text-align: center"><b>Опрос завершен.</b><br /> Спасибо за внимание</div>',
					'options' => null
				);
			}
			$response = new JsonResponse(array('data' => $data));
			return $response;
		}
	}

}
