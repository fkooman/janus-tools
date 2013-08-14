<?php
/**
 * Created by JetBrains PhpStorm.
 * User: rkrol
 * Date: 14-8-13
 * Time: 11:12
 * To change this template use File | Settings | File Templates.
 */

namespace SURFnet\janus\validate\validators;

use SURFnet\janus\log\EntityLog;
use SURFnet\janus\validate\Validate;
use SURFnet\janus\validate\ValidateInterface;
use fkooman\Config\Config;

class SurfCheckDefaultSP  extends Validate implements ValidateInterface{

    private $defaultSPs;

    public function __construct(array $entities, Config $config, EntityLog $log)
    {
        echo "construct SurfCheckDefaultSP";
        $this->defaultSPs     = $config->s('require_surfnet:prodaccepted')->s('sp', false, array())->toArray();
        parent::__construct($entities,$config, $log);
    }



    public function sp($entityData, $metadata, $allowedEntities, $blockedEntities, $arp)
    {
    }

    public function idp($entityData, $metadata, $allowedEntities, $blockedEntities, $disableConsent)
    {
        foreach ($this->defaultSPs as $defSP) {
        if (!in_array($defSP,$allowedEntities)) {
        $this->logWarn(sprintf("Default SURFnet SP not connected: %s", $defSP ));
//          echo "Default SURFnet SP: API not connected";
//            echo sprintf("Default SURFnet SP not connected: %s", $defSP );
    }
        }
    }
}