<?php
/**
 * Created by JetBrains PhpStorm.
 * User: rkrol
 * Date: 14-8-13
 * Time: 11:12
 * To change this template use File | Settings | File Templates.
 */

namespace SURFnet\janus\validate\validators;


class SurfCheckDefaultSP {
    public function sp($entityData, $metadata, $allowedEntities, $blockedEntities, $arp)
    {
    }

    public function idp($entityData, $metadata, $allowedEntities, $blockedEntities, $disableConsent)
    {
        echo 'SurfCheckDefaultSP';
        if (!in_array('"https://api.surfconext.nl/',$allowedEntities)) {
        $this->logWarn("Default SURFnet SP: API not connected" );
            echo "Default SURFnet SP: API not connected";
    }
    }
}