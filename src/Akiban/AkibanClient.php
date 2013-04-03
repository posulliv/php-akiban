<?php

namespace Akiban;

use Guzzle\Common\Collection;
use Guzzle\Service\Client;
use Guzzle\Service\Description\ServiceDescription;
use Guzzle\Http\Exception\BadResponseException;
use Guzzle\Http\Exception\ClientErrorResponseException;

class AkibanClient extends Client
{
    /**
     * Factory method to create a new Akiban client using an array of configuration options.
     */
    public static function factory($config = array())
    {
        $default = array(
            'base_url' => '{scheme}://{username}:{password}@{hostname}:{port}/',
            'scheme' => 'http',
            'hostname' => 'localhost',
            'port' => 8091
        );
        $required = array('base_url');
        $config = Collection::fromConfig($config, $default, $required);

        $client = new self($config->get('base_url'), $config);
        // Attach a service description to the client
        $description = ServiceDescription::factory(__DIR__ . '/Resources/v1.json');
        $client->setDescription($description);

        return $client;
    }

    public function getEntity($entityName, $entityId, $schemaName = null)
    {
        $entityPath = $schemaName === null ? $entityName : $schemaName . "." . $entityName;
        $command = $this->getCommand('GetEntity', array('name' => $entityPath, 'id' => $entityId));
        try {
            $response = $this->execute($command);
        } catch (ClientErrorResponseException $e) {
            return $e->getMessage();
        }
        return $response['data'];
    }

    public function createEntity($entityName, $data, $schemaName = null)
    {
        $entityPath = $schemaName === null ? $entityName : $schemaName . "." . $entityName;
        $command = $this->getCommand('CreateEntity', array('name' => $entityPath, 'data' => $data));
        $command->set('command.headers', array('Content-type' => 'application/json'));
        try {
            $reponse = $this->execute($command);
        } catch (ClientErrorResponseException $e) {
            return $e->getMessage();
        }
        return $response['data'];
    }

    public function deleteEntity($entityName, $entityId, $schemaName = null)
    {
        $entityPath = $schemaName === null ? $entityName : $schemaName . "." . $entityName;
        $command = $this->getCommand('DeleteEntity', array('name' => $entityPath, 'id' => $entityId));
        try {
            $response = $this->execute($command);
        } catch (ClientErrorResponseException $e) {
            return $e->getMessage();
        }
        return $response['status'];
    }
}
