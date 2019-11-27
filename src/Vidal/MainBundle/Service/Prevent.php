<?php
namespace Vidal\MainBundle\Service;

use Symfony\Component\HttpFoundation\Session\Session;

class Prevent
{
	private $session;

	public function __construct(Session $session)
	{
		$this->session = $session;
	}

	public function doubleClick()
	{
		$now = new \DateTime('now');

		if (!$this->session->has('lastClick')) {
			$this->session->set('lastClick', $now->getTimestamp());

			return false;
		}

		$value     = intval($this->session->get('lastClick'));
		$lastClick = new \DateTime();
		$lastClick->setTimestamp($value);

		$diff = $now->diff($lastClick);

		$this->session->set('lastClick', $now->getTimeStamp());

		return $diff->s < 3;
	}
}