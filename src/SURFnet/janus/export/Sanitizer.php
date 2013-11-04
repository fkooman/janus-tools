<?php

namespace SURFnet\janus\export;

class Sanitizer
{
    /** @var array */
    private $entities;

    public function __construct(array $entities)
    {
        $this->entities = $entities;
    }

    public function sanitizeAll()
    {
        $this->removeSecrets();
        // $this->otherSanitzer();
    }

    public function removeSecrets()
    {
        foreach ($this->entities as $index => $entity) {
            if (isset($entity['metadata']['coin']['oauth']['secret'])) {
                $this->entities[$index]['metadata']['coin']['oauth']['secret'] = 'REPLACED_BY_EXPORT_SCRIPT';
            }
            if (isset($entity['metadata']['coin']['oauth']['consumer_secret'])) {
                $this->entities[$index]['metadata']['coin']['oauth']['consumer_secret'] = 'REPLACED_BY_EXPORT_SCRIPT';
            }
            if (isset($entity['metadata']['coin']['provision_password'])) {
                $this->entities[$index]['metadata']['coin']['provision_password'] = 'REPLACED_BY_EXPORT_SCRIPT';
            }
        }
    }

    public function getEntityData()
    {
        return $this->entities;
    }
}
