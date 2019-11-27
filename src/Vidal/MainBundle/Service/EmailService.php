<?php

namespace Vidal\MainBundle\Service;

use Symfony\Component\DependencyInjection\ContainerInterface as Container;
use Symfony\Bundle\TwigBundle\TwigEngine as Templating;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EmailService
{
    private $container;
    private $templating;

    public function __construct(Container $container, Templating $templating)
    {
        $this->container = $container;
        $this->templating = $templating;
    }

    /**
     * @param string|array $emails
     * @param array $template
     * @param string $subject
     * @param bool $localhost
     * @param array $headers
     * @param string|null $textPlain
     * @param bool $forceLocal
     *
     * @return int The number of successful recipients. Can be 0 which indicates failure
     */
    public function send($emails, $template, $subject, $localhost = false, array $headers = array(), $textPlain = null, $forceLocal = false)
    {
        # устанавливаем получателя(ей) письма
        if (is_string($emails)) {
            $emails = array($emails);
        }

        # общие настройки письма
        $portal = $this->container->getParameter('portal');
        $fromName = 'Портал ' . $portal;

        # для некоторых случаев отправлять необходимо через ящик Yandex
        $viaYandex = false;
        foreach ($emails as $email) {
            if (preg_match('#@vidal\.ru#i', $email)) {
                $viaYandex = true;
            }
        }

        # устанавливаем содержимое письма
        if (!empty($template['html'])) {
            $body = $template['html'];
        }
        else {
            $templateParams = array('portal' => $portal);
            if (is_string($template)) {
                $templateName = $template;
            }
            else {
                $templateName = $template[0];
                $templateParams = array_merge($templateParams, $template[1]);
            }
            $body = $this->templating->render($templateName, $templateParams);
        }

        # формируем письмо
        $message = (new \Swift_Message($subject))
            ->setFrom('maillist@vidal.ru', $fromName)
            ->setBody($body, 'text/html');

        $message->setTo($emails);

        # добавляем заголовки к письму
        if (!empty($headers)) {
            $messageHeaders = $message->getHeaders();
            foreach ($headers as $name => $value) {
                $messageHeaders->addTextHeader($name, $value);
            }
        }

        # если задана текстовая версия письма
        if (!empty($textPlain)) {
            $message->addPart($textPlain, 'text/plain');
        }

        # принудительный флаг отправки с локалки
        if ($forceLocal) {
            return $this->container->get('mailer')->send($message);
        }

        # проверка адресов на mediexpo.ru
        foreach ($emails as $email) {
            if (strpos($email, '@mediexpo.ru') !== false) {
                $localhost = true;
            }
        }

        # локальная рассылка идет через ЯндексПочту, а обычная с сервера с 127.0.0.1
        if ($localhost || $viaYandex) {
            $username = $this->container->getParameter('yandex_username');
            $password = $this->container->getParameter('yandex_password');

            $transporter = \Swift_SmtpTransport::newInstance('smtp.yandex.ru', 465, 'SSL')
                ->setUsername($username)
                ->setPassword($password)
                ->setStreamOptions(['ssl' => [
                    'allow_self_signed' => true,
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                ]]);

            $mailer = \Swift_Mailer::newInstance($transporter);
            $message->setFrom($username);
            return $mailer->send($message);
        }
        else {
            return $this->container->get('mailer')->send($message);
        }
    }
}