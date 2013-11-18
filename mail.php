<?php

require_once 'vendor/autoload.php';

use SURFnet\janus\log\EntityLog;

try {
    $configFile = __DIR__ . DIRECTORY_SEPARATOR . "config" . DIRECTORY_SEPARATOR . "config.ini";
    $config = \fkooman\Config\Config::fromIniFile($configFile);

    $logDir         = $config->s('output')->l('logDir', true); // REQ

    $timezone       = $config->l('timezone', false, "Europe/Amsterdam");
    date_default_timezone_set($timezone);

    $mailTo         = $config->s('mail')->s('to', true)->toArray(); // REQ
    $mailFrom       = $config->s('mail')->l('from', true); // REQ

    $inputFile = $logDir . DIRECTORY_SEPARATOR . "log.json";
    $logJsonData = @file_get_contents($inputFile);
    if (false === $logJsonData) {
        throw new Exception(sprintf("unable to read JSON file '%s' from disk", $inputFile));
    }
    $logData = json_decode($logJsonData, true);

    $mailContent = sprintf('Generated at %s' . PHP_EOL, date("r", time()));

    $mailMessages = array(
        "idp" => array(),
        "sp" => array()
    );

    foreach ($logData['saml20-idp'] as $entityid => $entity) {
        foreach ($entity['messages'] as $m) {
            if (EntityLog::ERROR === $m['level']) {
                $mailMessages['idp'][$entityid] = $m['message'] . PHP_EOL;
            }
        }
    }
    foreach ($logData['saml20-sp'] as $entityid => $entity) {
        foreach ($entity['messages'] as $m) {
            if (EntityLog::ERROR === $m['level']) {
                $mailMessages['sp'][$entityid] = $m['message'] . PHP_EOL;
            }
        }
    }

    $transport = Swift_SendmailTransport::newInstance();
    $mailer = Swift_Mailer::newInstance($transport);

    foreach ($mailMessages['idp'] as $entityid => $mailContent) {
        $message = Swift_Message::newInstance(sprintf('JANUS Log IdP [%s]', $entityid));
        $message->setBody($mailContent)->setFrom($mailFrom)->setTo($mailTo);
        // echo $message->toString();
        if (!$mailer->send($message)) {
            echo "FAIL" . PHP_EOL;
        }
    }

    foreach ($mailMessages['sp'] as $entityid => $mailContent) {
        $message = Swift_Message::newInstance(sprintf('JANUS Log SP [%s]', $entityid));
        $message->setBody($mailContent)->setFrom($mailFrom)->setTo($mailTo);
        // echo $message->toString();
        if (!$mailer->send($message)) {
            echo "FAIL" . PHP_EOL;
        }
    }
} catch (Exception $e) {
    echo sprintf("ERROR: %s", $e->getMessage());
    die(PHP_EOL);
}
