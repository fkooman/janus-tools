<?php

namespace SURFnet\janus\acl;

class Resolve
{
    /** @var array */
    private $entities;

    public function __construct(array $entities)
    {
        $this->entities = array();
        foreach ($entities as $e) {
            $type = $e['entityData']['type'];
            $entityId = $e['entityData']['entityid'];
            $this->entities[$type][$entityId] = $e;
        }
    }

    public function idpAclDump($requireStateMatch = false)
    {
        $idpAcl = array();
        foreach (array_keys($this->entities['saml20-idp']) as $idpEntityId) {
            //echo "[idp] " . $idpEntityId . PHP_EOL;
            $idpAcl[$idpEntityId] = $this->aclAllowedSps($idpEntityId, $requireStateMatch);
        }

        return $idpAcl;
    }

    public function spAclDump($requireStateMatch = false)
    {
        $spAcl = array();
        foreach (array_keys($this->entities['saml20-sp']) as $spEntityId) {
            //echo "[sp] " . $spEntityId . PHP_EOL;
            $spAcl[$spEntityId] = $this->aclAllowedIdps($spEntityId, $requireStateMatch);
        }

        return $spAcl;
    }

    public function aclAllowedSps($idpEntityId, $requireStateMatch = false)
    {
        $allowedSps = array();
        foreach (array_keys($this->entities['saml20-sp']) as $spEntityId) {
            if ($this->aclAllowedByEntityId($idpEntityId, $spEntityId, $requireStateMatch)) {
                $allowedSps[] = $spEntityId;
            }
        }

        return $allowedSps;
    }

    public function aclAllowedIdps($spEntityId, $requireStateMatch = false)
    {
        $allowedIdps = array();
        foreach (array_keys($this->entities['saml20-idp']) as $idpEntityId) {
            if ($this->aclAllowedByEntityId($idpEntityId, $spEntityId, $requireStateMatch)) {
                $allowedIdps[] = $idpEntityId;
            }
        }

        return $allowedIdps;
    }

    public function aclAllowedByEntityId($idpEntityId, $spEntityId, $requireStateMatch = false)
    {
        if (!array_key_exists($idpEntityId, $this->entities['saml20-idp'])) {
            return false;
        }

        if (!array_key_exists($spEntityId, $this->entities['saml20-sp'])) {
            return false;
        }

        return $this->aclAllowedByEntity(
            $this->entities['saml20-idp'][$idpEntityId],
            $this->entities['saml20-sp'][$spEntityId],
            $requireStateMatch
        );
    }

    // FIXME: figure out how to implement blockedEntities
    public function aclAllowedByEntity(array $idpEntity, array $spEntity, $requireStateMatch = false)
    {
        $idpOkay = false;
        $spOkay = false;

        // type check
        if ("saml20-idp" !== $idpEntity['entityData']['type']) {
            return false;
        }
        if ("saml20-sp" !== $spEntity['entityData']['type']) {
            return false;
        }

        // require state match
        if ($requireStateMatch) {
            // the state needs to be the same, e.g.: both prodaccepted or both
            // testaccepted
            if ($idpEntity['entityData']['state'] !== $spEntity['entityData']['state']) {
                return false;
            }
        }

        if ("yes" === $idpEntity['entityData']['allowedall']) {
            $idpOkay = true;
        } else {
            if (in_array($spEntity['entityData']['entityid'], $idpEntity['allowedEntities'])) {
                $idpOkay = true;
            }
        }

        if ("yes" === $spEntity['entityData']['allowedall']) {
            $spOkay = true;
        } else {
            if (in_array($idpEntity['entityData']['entityid'], $spEntity['allowedEntities'])) {
                $spOkay = true;
            }
        }

        return $idpOkay && $spOkay;
    }
}
