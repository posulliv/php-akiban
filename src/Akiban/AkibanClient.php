<?php
/**
 * Copyright 2013 Akiban Technologies, Inc. or its affiliates. All Rights Reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License").
 * You may not use this file except in compliance with the License.
 * A copy of the License is located at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * or in the "license" file accompanying this file. This file is distributed
 * on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either
 * express or implied. See the License for the specific language governing
 * permissions and limitations under the License.
 */

namespace Akiban;

use Guzzle\Common\Collection;
use Guzzle\Service\Client;
use Guzzle\Service\Description\ServiceDescription;
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
        $entityPath = $this->constructEntityPath($entityName, $schemaName);
        $command = $this->getCommand('GetEntity', array('name' => $entityPath, 'id' => $entityId));
        return $this->executeCommand($command, 'data');
    }

    public function createEntity($entityName, $data, $schemaName = null, $createModel = false)
    {
        $entityPath = $this->constructEntityPath($entityName, $schemaName);
        if ($createModel) {
            $this->createEntityModel($entityPath, $data);
        }
        $command = $this->getCommand('CreateEntity', array('name' => $entityPath, 'data' => $data));
        $command->set('command.headers', array('Content-type' => 'application/json'));
        return $this->executeCommand($command, 'data');
    }

    public function deleteEntity($entityName, $entityId, $schemaName = null)
    {
        $entityPath = $this->constructEntityPath($entityName, $schemaName);
        $command = $this->getCommand('DeleteEntity', array('name' => $entityPath, 'id' => $entityId));
        return $this->executeCommand($command, 'status');
    }

    public function executeSqlQuery($sql)
    {
        $command = $this->getCommand('ExecuteQuery', array('q' => $sql));
        return $this->executeCommand($command, 'data');
    }

    public function executeMultipleSqlQueries($queries = array())
    {
        $sql = '';
        foreach ($queries as $query) {
            $sql .= $query . ";";
        }
        $command = $this->getCommand('ExecuteQueries', array('queries' => $sql));
        return $this->executeCommand($command, 'data');
    }

    public function createEntityModel($entityName, $data, $schemaName = null)
    {
        $entityPath = $this->constructEntityPath($entityName, $schemaName);
        $command = $this->getCommand('CreateModel', array('name' => $entityPath, 'data' => $data, 'create' => 'true'));
        $command->set('command.headers', array('Content-type' => 'application/json'));
        return $this->executeCommand($command, 'data');
    }

    public function getServerVersion()
    {
        return $this->executeCommand($this->getCommand('Version'), 'data');
    }

    private function executeCommand($command, $returnElement)
    {
        try {
            $response = $this->execute($command);
        } catch (ClientErrorResponseException $e) {
            return $e->getMessage();
        }
        return $response[$returnElement];
    }

    private function constructEntityPath($entityName, $schemaName)
    {
        return ($schemaName === null ? $entityName : $schemaName . "." . $entityName);
    }
}
