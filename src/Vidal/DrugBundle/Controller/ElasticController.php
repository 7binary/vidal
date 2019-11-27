<?php

namespace Vidal\DrugBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Elasticsearch\ClientBuilder;

class ElasticController extends Controller
{
    /** @Route("/elastic/autocomplete/{type}/{term}", name="elastic_autocomplete", options={"expose":true}) */
    public function autocompleteAction(Request $request, $type, $term)
    {
        if ($request->isXmlHttpRequest() == false) {
            throw $this->createNotFoundException();
        }

        $this->openClient();

        $results = $this->searchResults($type, $term);

        if (count($results['hits']['hits']) == 0 && preg_match("/[a-z\\[\\]\\;\\'\\,\\.\\ ]+/", $term)) {
            $results = $this->searchResults($type, $this->modifySearchQuery($term));
        }

        return new JsonResponse($results);
    }

    private function openClient()
    {
        $elasticaClient = new \Elastica\Client();
        $elasticaIndex = $elasticaClient->getIndex('website');
        $elasticaIndex->open();
    }

    private function searchResults($type, $term, $removeTags = true)
    {
        $words = explode(' ', $term);
        $query = implode('* ', $words) . '*';
        $client = ClientBuilder::create()->build();

        $s['index'] = 'website';
        $s['type'] = 'autocomplete';

        $s['body']['size'] = 15;
        $s['body']['query']['bool']['must']['query_string']['query'] = mb_strtolower($query);
        $s['body']['query']['bool']['must']['query_string']['default_field'] = 'name';

        $s['body']['highlight']['fields']['name'] = array("fragment_size" => 100);

        $s['body']['sort']['type']['order'] = 'desc';
        $s['body']['sort']['name']['order'] = 'asc';

        if ($type != 'all') {
            $s['body']['query']['bool']['filter']['term']['type'] = $type;
        }

        $results = $client->search($s);

        if($removeTags && is_array($results)) {
            if(isset($results['hits']) && isset($results['hits']['hits'])) {
                foreach ($results['hits']['hits'] as $index=>$result) {
                    if(isset($result['_source']['name']) && !is_array($result['_source']['name'])) {
                        $results['hits']['hits'][$index]['_source']['name'] = strip_tags($result['_source']['name']);
                    }
                    
                }
            }
        }

        return $results;
    }

    private function modifySearchQuery($query)
    {
        $eng = explode(' ', "q w e r t y u i o p [ ] a s d f g h j k l ; ' z x c v b n m , .");
        $rus = explode(' ', '%D0%B9 %D1%86 %D1%83 %D0%BA %D0%B5 %D0%BD %D0%B3 %D1%88 %D1%89 %D0%B7 %D1%85 %D1%8A %D1%84 %D1%8B %D0%B2 %D0%B0 %D0%BF %D1%80 %D0%BE %D0%BB %D0%B4 %D0%B6 %D1%8D %D1%8F %D1%87 %D1%81 %D0%BC %D0%B8 %D1%82 %D1%8C %D0%B1 %D1%8E');

        return urldecode(str_replace($eng, $rus, $query));
    }

    /** @Route("/elastic/autocomplete_ext/{type}/{term}", name="elastic_autocomplete_ext", options={"expose":true}) */
    public function autocompleteExtAction(Request $request, $type, $term)
    {
        if ($request->isXmlHttpRequest() == false) {
            throw $this->createNotFoundException();
        }

        $results = $this->searchResults($type, $term);

        if (count($results['hits']['hits']) == 0 && preg_match("/[a-z\\[\\]\\;\\'\\,\\.\\ ]+/", $term)) {
            $results = $this->searchResults($type, $this->modifySearchQuery($term));
            
        }

        return new JsonResponse($results);
    }

    /** @Route("/elastic/autocomplete_nozology/{term}", name="elastic_autocomplete_nozology", options={"expose":true}) */
    public function autocompleteNozologyAction(Request $request, $term)
    {
        if ($request->isXmlHttpRequest() == false) {
            throw $this->createNotFoundException();
        }

        $words = explode(' ', $term);
        $query = implode('* ', $words) . '*';
        $client = ClientBuilder::create()->build();

        $s['index'] = 'website';
        $s['type'] = 'autocomplete_nozology';

        $s['body']['size'] = 15;
        $s['body']['query']['bool']['must']['query_string']['query'] = $query;
        $s['body']['highlight']['fields']['name'] = array("fragment_size" => 100);
        $s['body']['sort']['name']['order'] = 'asc';

        $results = $client->search($s);

        return new JsonResponse($results);
    }

    /** @Route("/elastic/autocomplete_article/{term}", name="elastic_autocomplete_article", options={"expose":true}) */
    public function autocompleteArticleAction(Request $request, $term)
    {
        if ($request->isXmlHttpRequest() == false) {
            throw $this->createNotFoundException();
        }

        $words = explode(' ', $term);
        $query = implode('* ', $words) . '*';
        $client = ClientBuilder::create()->build();

        $s['index'] = 'website';
        $s['type'] = 'autocomplete_article';

        $s['body']['size'] = 15;
        $s['body']['query']['bool']['must']['query_string']['query'] = $query;
        $s['body']['query']['bool']['must']['query_string']['default_field'] = 'title';
        $s['body']['highlight']['fields']['title'] = array("fragment_size" => 100);
        $s['body']['sort']['title']['order'] = 'asc';

        $results = $client->search($s);

        return new JsonResponse($results);
    }

    /** @Route("/elastic/autocomplete_pharm/{term}", name="elastic_autocomplete_pharm", options={"expose":true}) */
    public function autocompletePharmAction(Request $request, $term)
    {
        if ($request->isXmlHttpRequest() == false) {
            throw $this->createNotFoundException();
        }

        $words = explode(' ', $term);
        $query = implode('* ', $words) . '*';
        $client = ClientBuilder::create()->build();

        $s['index'] = 'website';
        $s['type'] = 'autocomplete_pharm';

        $s['body']['size'] = 15;
        $s['body']['query']['bool']['must']['query_string']['query'] = $query;
        $s['body']['query']['bool']['must']['query_string']['default_field'] = 'name';
        $s['body']['highlight']['fields']['name'] = array("fragment_size" => 100);
        $s['body']['sort']['name']['order'] = 'asc';

        $results = $client->search($s);

        return new JsonResponse($results);
    }

    /** @Route("/elastic/autocomplete_product/{term}", name="elastic_autocomplete_product", options={"expose":true}) */
    public function autocompleteProductAction(Request $request, $term)
    {
        if ($request->isXmlHttpRequest() == false) {
            throw $this->createNotFoundException();
        }

        $words = explode(' ', $term);
        $query = implode('* ', $words) . '*';
        $client = ClientBuilder::create()->build();

        $s['index'] = 'website';
        $s['type'] = 'autocomplete_product';

        $s['body']['size'] = 15;
        $s['body']['query']['bool']['must']['query_string']['query'] = $query;
        $s['body']['query']['bool']['must']['query_string']['default_field'] = 'name';
        $s['body']['highlight']['fields']['name'] = array("fragment_size" => 100);
        $s['body']['sort']['name']['order'] = 'asc';

        $results = $client->search($s);

        return new JsonResponse($results);
    }

    /** @Route("/elastic/autocomplete_interaction/{term}", name="elastic_autocomplete_interaction", options={"expose":true}) */
    public function autocompleteInteractionAction(Request $request, $term)
    {
        if ($request->isXmlHttpRequest() == false) {
            throw $this->createNotFoundException();
        }

        $words = explode(' ', $term);
        $query = implode('* ', $words) . '*';
        $client = ClientBuilder::create()->build();

        $s['index'] = 'website';
        $s['type'] = 'autocomplete_interaction';

        $s['body']['size'] = 15;
        $s['body']['query']['bool']['must']['query_string']['query'] = $query;
        $s['body']['query']['bool']['must']['query_string']['default_field'] = 'name';
        $s['body']['highlight']['fields']['name'] = array("fragment_size" => 100);
        $s['body']['sort']['name']['order'] = 'asc';

        $results = $client->search($s);

        return new JsonResponse($results);
    }

    /** @Route("/elastic/autocomplete_veterinar/{term}", name="elastic_autocomplete_veterinar", options={"expose":true}) */
    public function autocompleteVeterinarAction(Request $request, $term)
    {
        if ($request->isXmlHttpRequest() == false) {
            throw $this->createNotFoundException();
        }

        $type = $request->query->get('type', 'all');
        $results = $this->searchVeterinarResults($term, $type);

        if (count($results['hits']['hits']) == 0 && preg_match("/[a-z\\[\\]\\;\\'\\,\\.\\ ]+/", $term)) {
            $results = $this->searchVeterinarResults($this->modifySearchQuery($term), $type);
        }

        return new JsonResponse($results);
    }

    /** @Route("/elastic/autocomplete_city/{term}", name="elastic_autocomplete_city", options={"expose":true}) */
    public function autocompleteCityAction(Request $request, $term)
    {
        if($request->isXmlHttpRequest() == false) {
            throw $this->createNotFoundException();
        }

        $words  = explode(' ', $term);
        $query  = implode('* ', $words) . '*';
        $client = ClientBuilder::create()->build();

        $s['index'] = 'website';
        $s['type']  = 'autocomplete_city';

        $s['body']['size']                                                 = 15;
        $s['body']['query']['bool']['must']['query_string']['query'] = $query;
        $s['body']['query']['bool']['must']['query_string']['default_field'] = 'name';
        $s['body']['highlight']['fields']['name']                          = array("fragment_size" => 100);
        $s['body']['sort']['name']['order']                                = 'asc';

        $results = $client->search($s);

        return new JsonResponse($results);
    }

    private function searchVeterinarResults($term, $type)
    {
        $words = explode(' ', $term);
        $query = implode('* ', $words) . '*';
        $client = ClientBuilder::create()->build();

        $s['index'] = 'website';

        switch ($type) {
            case 'p':
                $s['type'] = 'autocomplete_veterinar';
                break;
            case 'c':
                $s['type'] = 'vac';
                break;
            case 'r':
                $s['type'] = 'vai';
                break;
            default:
                $s['type'] = 'autocomplete_veterinar_all';
        }

        $s['body']['size'] = 15;
        $s['body']['query']['bool']['must']['query_string']['query'] = $query;
        $s['body']['query']['bool']['must']['query_string']['default_field'] = 'name';
        $s['body']['highlight']['fields']['name'] = array("fragment_size" => 100);
        $s['body']['sort']['name']['order'] = 'asc';

        $results = $client->search($s);

        return $results;
    }
}
