<?php

namespace Vidal\MainBundle\Tests\Controller;

class AuthControllerTest extends TestCase
{
	public function testRegistration()
	{
		$client  = static::createClient();
		$crawler = $client->request('GET', '/registration');

		$this->assertTrue($crawler->filter('#register_form')->count() > 0);
	}

	public function testProfile()
	{
		$client = static::createClient();

		$this->logIn($client);

		//$url     = $client->getContainer()->get('router')->generate('profile', array(), false);
		$crawler = $client->request('GET', '/profile');

		$this->assertTrue($crawler->filter('#profile_form')->count() > 0);
	}
}
