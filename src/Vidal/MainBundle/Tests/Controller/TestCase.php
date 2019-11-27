<?php

namespace Vidal\MainBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class TestCase extends WebTestCase
{
	protected function logIn(Client $client)
	{
		$em   = $client->getContainer()->get('doctrine')->getManager();
		$user = $em->getRepository('VidalMainBundle:User')->findOneByUsername('7binary@bk.ru');

		$session = $client->getContainer()->get('session');

		$firewall = 'everything';
		$token    = new UsernamePasswordToken($user, null, $firewall, $user->getRoles());
		$token->setUser($user);
		$session->set('_security_' . $firewall, serialize($token));
		$session->save();

		$cookie = new Cookie($session->getName(), $session->getId());
		$client->getCookieJar()->set($cookie);
	}
}