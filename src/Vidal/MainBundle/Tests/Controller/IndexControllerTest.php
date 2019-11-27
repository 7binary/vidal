<?php

namespace Vidal\MainBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class IndexControllerTest extends WebTestCase
{
	public function testIndex()
	{
		$client  = static::createClient();
		$crawler = $client->request('GET', '/');

		$this->assertTrue($crawler->filter('div.article')->count() > 0);
		$this->assertTrue($crawler->filter('div.publication')->count() > 0);
	}
}
