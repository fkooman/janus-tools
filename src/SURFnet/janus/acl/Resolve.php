<?php

namespace SURFnet\janus\acl;

class Resolve
{
    /** @var array */
    private $entities;

    private $idpEntityIds;
    private $spEntityIds;

    public function __construct(array $entities)
    {
        $this->entities = $entities;

        $this->idpEntityIds = array();
        $this->spEntityIds = array();

        foreach ($entities as $e) {
            if ("saml20-idp" === $e['entityData']['type']) {
                $this->idpEntityIds[] = $e['entityData']['entityid'];
            }
            if ("saml20-sp" === $e['entityData']['type']) {
                $this->spEntityIds[] = $e['entityData']['entityid'];
            }
        }
    }

    public function aclAllowedSps($idpEntityId, $requireStateMatch = false)
    {
        $allowedSps = array();
        foreach ($this->spEntityIds as $spEntityId) {
            if ($this->aclAllowedByEntityId($idpEntityId, $spEntityId, $requireStateMatch)) {
                $allowedSps[] = $spEntityId;
            }
        }

        return $allowedSps;
    }

    public function aclAllowedIdps($spEntityId, $requireStateMatch = false)
    {
        $allowedIdps = array();
        foreach ($this->idpEntityIds as $idpEntityId) {
            if ($this->aclAllowedByEntityId($idpEntityId, $spEntityId, $requireStateMatch)) {
                $allowedIdps[] = $idpEntityId;
            }
        }

        return $allowedIdps;
    }

    public function aclAllowedByEntityId($idpEntityId, $spEntityId, $requireStateMatch = false)
    {
        $idpEntity = false;
        $spEntity = false;

        foreach ($this->entities as $k => $v) {
            if ("saml20-idp" === $v['entityData']['type']) {
                if ($idpEntityId === $v['entityData']['entityid']) {
                    $idpEntity = $v;
                }
            }
            if ("saml20-sp" === $v['entityData']['type']) {
                if ($spEntityId === $v['entityData']['entityid']) {
                    $spEntity = $v;
                }
            }
        }

        if (false === $idpEntity) {
            return false;
        }
        if (false === $spEntity) {
            return false;
        }

        return $this->aclAllowedByEntity($idpEntity, $spEntity, $requireStateMatch);
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
