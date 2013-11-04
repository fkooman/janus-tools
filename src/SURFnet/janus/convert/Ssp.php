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
        foreach ($entities as $index => $entity) {
            foreach ($entity['entityData'] as $k => $v) {
                $entity[$k] = $v;
            }
            foreach ($entity['metadata'] as $k => $v) {
                $entity[$k] = $v;
            }

            $type = $entity['type'];
            if ("saml20-idp" === $type) {
                $this->idp[$index] = $entity;
            }
            if ("saml20-sp" === $type) {
                $entityId = $entity['entityid'];
                $this->sp[$index] = $entity;
                // add ACL
                $this->sp[$index]['IDPList'] = $acl['saml20-sp'][$entityId];
            }
        }
    }

    public function getIdps()
    {
        return $this->idp;
    }

    public function getSps()
    {
        return $this->sp;
    }
}
