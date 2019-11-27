<?php
namespace Vidal\MainBundle\EventListener;

use AppBundle\Controller\TokenAuthenticatedController;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use \Rollbar\Rollbar;
use \Rollbar\Payload\Level;
use Symfony\Component\Console\Event\ConsoleCommandEvent;

class RollbarInit
{
    /** @var string $token */
    private $token;

    /** @var string $token */
    private $rollbalEnv;

    /** @var string $token */
    private $env;

    public function __construct($token, $rollbalEnv, $env)
    {
        $this->token = $token;
        $this->rollbalEnv = $rollbalEnv;
        $this->env = $env;
    }

    private function initRollbar()
    {
        if($this->token)
        {
            if($this->rollbalEnv && $this->rollbalEnv!=$this->env) {
                return false;
            }

            Rollbar::init(
                array(
                    'access_token' => $this->token,
                    'environment' => $this->env
                )
            );
        }
    }

    public function onCommand(ConsoleCommandEvent $event)
    {
        $this->initRollbar();
    }

    public function onController(FilterControllerEvent $event)
    {
        $this->initRollbar();
    }
}