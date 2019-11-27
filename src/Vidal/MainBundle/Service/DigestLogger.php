<?php
namespace Vidal\MainBundle\Service;

use Symfony\Component\DependencyInjection\ContainerInterface as Container;

class DigestLogger
{
	const FILE_SENT = 'digest_sent.txt';
	const FILE_FAIL = 'digest_fail.txt';

	private $container;
	private $dir;
	private $digestSent = null;
	private $digestFail = null;

	public function __construct(Container $container)
	{
		$this->container = $container;
		$this->dir = $this->container->get('kernel')->getRootDir() . '/logs/';
	}

	public function close()
	{
		try {
			if ($this->digestSent) {
				fclose($this->digestSent);
			}
			if ($this->digestFail) {
				fclose($this->digestFail);
			}
		}
		catch (\Exception $e) {
		}
	}

	public function openRewrite()
	{
		try {
			$this->digestSent = fopen($this->getFileSent(), 'w');
			$this->digestFail = fopen($this->getFileFail(), 'w');
		}
		catch (\Exception $e) {
		}
	}

	public function openAppend()
	{
		try {
			$this->digestSent = fopen($this->getFileSent(), 'a');
			$this->digestFail = fopen($this->getFileFail(), 'a');
		}
		catch (\Exception $e) {
		}
	}

	public function writeSentEmail($email)
	{
		try {
			if ($this->digestSent) {
				fwrite($this->digestSent, $email . "\r\n");
			}
		}
		catch (\Exception $e) {
		}
	}

	public function writeFailEmail($email)
	{
		try {
			if ($this->digestFail) {
				fwrite($this->digestFail, $email . "\r\n");
			}
		}
		catch (\Exception $e) {
		}
	}

	public function getSentEmails()
	{
		try {
			$file = $this->getFileSent();
			return file_exists($file) ? file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : array();
		}
		catch (\Exception $e) {
			return array();
		}
	}

	public function getFailEmails()
	{
		try {
			$file = $this->getFileFail();
			return file_exists($file) ? file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : array();
		}
		catch (\Exception $e) {
			return array();
		}
	}

	public function getFileSent()
	{
		return $this->dir . self::FILE_SENT;
	}

	public function getFileFail()
	{
		return $this->dir . self::FILE_FAIL;
	}

	public function clean()
	{
		file_put_contents($this->getFileSent(), '');
		file_put_contents($this->getFileFail(), '');
	}
}