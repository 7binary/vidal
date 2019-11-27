<?php
if (empty($_GET['email']) || $_GET['email'] != 'email') {
    header('HTTP/1.0 404 not found');
    exit;
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '/home/twigavid/vidal/phpmailer/src/Exception.php';
require '/home/twigavid/vidal/phpmailer/src/PHPMailer.php';
require '/home/twigavid/vidal/phpmailer/src/SMTP.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$mail = new PHPMailer();

$mail->isSMTP();
$mail->isHTML(true);
$mail->CharSet = 'UTF-8';
$mail->From = 'maillist@vidal.ru';
$mail->FromName = 'Портал «Vidal.ru»';
$mail->Subject = 'Just subject';
$mail->Host = '127.0.0.1';
$mail->Body = 'Next of the testing email';
$mail->addAddress('binarya@yandex.ru');

$mail->SMTPSecure = false;
$mail->SMTPAutoTLS = false;
$mail->Port = 25;

$mail->SMTPDebug = 3;
$result = $mail->send();