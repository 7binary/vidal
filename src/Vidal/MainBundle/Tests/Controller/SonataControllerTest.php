<?php

namespace Vidal\MainBundle\Tests\Controller;

class SonataControllerTest extends TestCase
{
	public function testAdminList()
	{
		ini_set('memory_limit', -1);

		$client = static::createClient();

		$this->logIn($client);

		foreach ($this->getUrls() as $url) {
			$crawler = $client->request('GET', $url);
			$this->assertTrue($crawler->filter('.container-fluid')->count() > 0);
		}
	}

	private function getUrls()
	{
		return array(
			'/admin/dashboard',
			'/admin/vidal/main/user/list',
			'/admin/vidal/drug/publication/list',
			'/admin/vidal/drug/publication/create',
			'/admin/vidal/drug/article/list',
			'/admin/vidal/drug/article/create',
			'/admin/vidal/drug/art/list',
			'/admin/vidal/drug/art/create',
			'/admin/vidal/drug/document/list',
			'/admin/vidal/drug/document/create',
			'/admin/vidal/drug/product/list',
			'/admin/vidal/drug/product/create',
		);
	}
}
