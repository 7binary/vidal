<?php

namespace Vidal\ApiBundle\Services;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Vidal\ApiBundle\Entity\Token;
use Vidal\MainBundle\Entity\KeyValue;

class ApiVoter
{
    const NOT_VALID_LOGIN = "Не верный логин или пароль!";
    const EMPTY_LOGIN = "Для доступа необходимо ввести логин и пароль";
    const REQUEST_COUNT = "Превышено кол-во запросов в день";
    const NOT_VALID_XTOKEN = "Передан не верный заголовок X-Token";

    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * @var string
     */
    protected $lastError;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * Список АПИ роутов
     *
     * @return array
     */
    protected function getApiRoutes()
    {
        return array("api_drug_equal_full_analogs");
    }

    /**
     * Текущий запрос к АПИ ?
     *
     * @param  Request $request
     * @return boolean
     */
    public function isApiRoute(Request $request)
    {
        $route = $request->get('_route');

        if(!$route || !in_array($route, $this->getApiRoutes())) {
            return false;
        }
        return true;
    }

    /**
     * Последняя ошибка
     *
     * @return null|string
     */
    public function getLastError()
    {
        return $this->lastError;
    }

    /**
     * Новый запрос АПИ
     *
     * @param  Request $request
     * @return boolean
     */
    public function newRequest(Request $request)
    {
        if (!isset($_SERVER['PHP_AUTH_USER']) || !isset($_SERVER['PHP_AUTH_PW'])) {
            header('WWW-Authenticate: Basic realm="Vidal"');
            header('HTTP/1.0 401 Unauthorized');
            $this->lastError = self::EMPTY_LOGIN;
            return false;
        }

        $httpUser = $_SERVER['PHP_AUTH_USER'];
        $httpPasword = $_SERVER['PHP_AUTH_PW'];


        $token = $this->em->getRepository(Token::class)->getToken($httpUser, $httpPasword);

        if(!$token) {
            $this->lastError = self::NOT_VALID_LOGIN;
            return false;
        }

        $xToken = $request->headers->get('X-Token');
        if (!$xToken || !$this->em->getRepository(KeyValue::class)->checkMatch(KeyValue::XTOKEN, $xToken)) {
            $this->lastError = self::NOT_VALID_XTOKEN;
            return false;
        }

        // Есть ограничение на кол-во запросов
        if($token->getMaxRequestPerDay() > 0) {
            $currentRequestPerDay = $token->increaseCurrentRequestPerDay();

            if($currentRequestPerDay > $token->getMaxRequestPerDay()) {
                $this->lastError = self::REQUEST_COUNT;
                return false;
            }
        } 
        $token->setLastRequestDate(new \DateTime());
        $this->em->flush($token);

        return true;
    }
}