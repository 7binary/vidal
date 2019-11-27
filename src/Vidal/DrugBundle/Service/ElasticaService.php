<?php

namespace Vidal\DrugBundle\Service;

class ElasticaService
{
	private $linguaService;

	public function __construct($linguaService)
	{
		$this->linguaService = $linguaService;
	}

	public function query($type, $q)
	{
		$q = $this->linguaService->stem_string($q);

		$client = new \Elastica\Client();
		$index  = $client->getIndex('website');
		$type   = $index->getType($type);

		$query = array(
			'query' => array(
				'query_string' => array(
					'query'    => $q . '*',
					'analyzer' => 'ru'
				)
			)
		);

		$path          = $index->getName() . '/' . $type->getName() . '/_search';
		$response      = $client->request($path, \Elastica\Request::GET, $query);
		$responseArray = $response->getData();
		$results       = array();

        foreach ($responseArray['hits']['hits'] as $result) {
            $results[] = $result['_source'];
        }

		return $results;
	}
}