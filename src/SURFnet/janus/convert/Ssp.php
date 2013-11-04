<?php

namespace SURFnet\janus\convert;

/**
 * Convert the export and ACL to a simpleSAMLphp configuration file (as JSON)
 */
class Ssp
{
    /** @var array */
    private $idp;

    /** @var array */
    private $sp;

    public function __construct(array $entities, array $acl)
    {
        $this->idp = array();
        $this->sp = array();

        foreach ($entities as $entity) {
            foreach ($entity['entityData'] as $k => $v) {
                if (!array_key_exists($k, $entity)) {
                    $entity[$k] = $v;
                }
            }
            foreach ($entity['metadata'] as $k => $v) {
                if (!array_key_exists($k, $entity)) {
                    $entity[$k] = $v;
                }
            }

            if (array_key_exists('disableConsent', $entity)) {
                $entity['consent.disable'] = $entity['disableConsent'];
            }

            unset($entity['entityData']);
            unset($entity['metadata']);
            unset($entity['allowedEntities']);
            unset($entity['blockedEntities']);
            unset($entity['disableConsent']);

            $type = $entity['type'];
            if ("saml20-idp" === $type) {
                $this->idp[] = $entity;
            }
            if ("saml20-sp" === $type) {
                $entityId = $entity['entityid'];
                $entity['IDPList'] = $acl['saml20-sp'][$entityId];
                if (array_key_exists('arp', $entity) && is_array($entity['arp'])) {
                    $entity['attributes'] = array_keys($entity['arp']);
                    unset($entity['arp']);
                } else {
                    $entity['attributes'] = array();
                }
                $this->sp[] = $entity;
            }
        }
    }

    public function getIdps()
    {
        return array_values($this->idp);
    }

    public function getSps()
    {
        return array_values($this->sp);
    }
}
