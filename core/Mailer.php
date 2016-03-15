<?php

class Mailer {

	private static $mailer = false;
	private $mailerInstance;

	public static function getInstance() {
		if (self::$mailer == false) {
			self::$mailer = new Mailer();
		}
		return self::$mailer;
	}

	private function __construct() {
		$transport = Swift_SmtpTransport::newInstance(Conf::get('smtp.host'), Conf::get('smtp.port'), Conf::get('smtp.encryption'))
				->setUsername(Conf::get('smtp.username'))
				->setPassword(Conf::get('smtp.password'));

		$this->mailerInstance = Swift_Mailer::newInstance($transport);
	}

	public function send($title, $body, $receiver) {
		if (!is_array($receiver))
			$receiver = array($receiver);
		
		$message = Swift_Message::newInstance($title)
				->setFrom(array(Conf::get('smtp.email') => Conf::get('smtp.from')))
				->setTo($receiver)
				->setBody($body, 'text/html');
		return $this->mailerInstance->send($message);
	}

}
