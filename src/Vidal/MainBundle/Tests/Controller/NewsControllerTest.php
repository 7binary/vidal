<?php

namespace Vidal\MainBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class NewsControllerTest extends WebTestCase
{
	public function testNovosti()
	{
		$client  = static::createClient();
		$crawler = $client->request('GET', '/novosti');
		
		$this->assertTrue($crawler->filter('div.publication')->count() > 0);
	}
}
