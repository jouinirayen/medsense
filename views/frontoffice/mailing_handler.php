<?php
require_once __DIR__ . '/../../controllers/MailerService.php';

function sendConfirmationEmail($toEmail, $userName, $doctorName, $date, $time, $healthTip = null)
{
    $mailer = new MailerService();
    return $mailer->sendConfirmationEmail($toEmail, $userName, $doctorName, $date, $time, $healthTip);
}

function sendCancellationEmail($toEmail, $userName, $doctorName, $date, $time)
{
    $mailer = new MailerService();
    return $mailer->sendCancellationEmail($toEmail, $userName, $doctorName, $date, $time);
}
