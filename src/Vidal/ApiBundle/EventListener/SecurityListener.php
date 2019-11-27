<?php

namespace Vidal\ApiBundle\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpFoundation\Response;
use Vidal\ApiBundle\Services\ApiVoter;

class SecurityListener implements EventSubscriberInterface
{
    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * @var ApiVoter
     */
    protected $apiVoter;

    public function __construct(EntityManagerInterface $em, ApiVoter $apiVoter)
    {
        $this->em = $em;
        $this->apiVoter = $apiVoter;
    }

    public function getApiRoutes()
    {
        return array("api_drug_equal_full_analogs");
    }

    /**
     * Проверка API-ключа
     *
     * @param  GetResponseEvent $event
     * @return void
     */
    public function onRequest(GetResponseEvent $event)
    {
        // TODO in symfony 2.8 add check
        //if (!$event->isMasterRequest()) {
        // return;
        //}

        $request = $event->getRequest();
        if(!$this->apiVoter->isApiRoute($request)) {
            return;
        }

        $accessIsAllowed = $this->apiVoter->newRequest($request);

        if(!$accessIsAllowed) {
            $event->setResponse($this->getErrorResponse());
        }
    }

    protected function getErrorResponse()
    {
        $error = array("error" => $this->apiVoter->getLastError());
        $response = new Response(json_encode($error, JSON_UNESCAPED_UNICODE),401);
        $response->headers->set('Content-Type', 'application/json; charset=UTF-8');
        return $response;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => 'onRequest',
        ];
    }
}