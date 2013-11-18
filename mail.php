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

    $mailContent .= PHP_EOL . '**** IdPs *****' . PHP_EOL;

    foreach ($logData['saml20-idp'] as $entityid => $entity) {
        foreach ($entity['messages'] as $m) {
            if (EntityLog::ERROR === $m['level']) {
                $mailContent .= $entityid . PHP_EOL;
                $mailContent .= '    ' . $m['message'] . PHP_EOL;
            }
        }
    }

    $mailContent .= PHP_EOL . '**** SPs *****' . PHP_EOL;

    foreach ($logData['saml20-sp'] as $entityid => $entity) {
        foreach ($entity['messages'] as $m) {
            if (EntityLog::ERROR === $m['level']) {
                $mailContent .= $entityid . PHP_EOL;
                $mailContent .= '    ' . $m['message'] . PHP_EOL;
            }
        }
    }

    $message = Swift_Message::newInstance(sprintf('[%s] JANUS Log', date("Y-m-d H:i:s")));
    $message->setBody($mailContent)->setFrom($mailFrom)->setTo($mailTo);
    // echo $message->toString();

    // Create the Transport
    $transport = Swift_SendmailTransport::newInstance();

    // Create the Mailer using your created Transport
    $mailer = Swift_Mailer::newInstance($transport);

    if ($mailer->send($message)) {
        echo "OK" . PHP_EOL;
    } else {
        echo "FAIL" . PHP_EOL;
    }

} catch (Exception $e) {
    echo sprintf("ERROR: %s", $e->getMessage());
    die(PHP_EOL);
}
