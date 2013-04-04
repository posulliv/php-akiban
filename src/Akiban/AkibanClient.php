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

    /**
     * Retrieve a single instance of the given entity. Successful response
     * is a JSON array.
     *
     * @param string $entityName name of the entity
     * @param int $entityId identifier of the entity
     * @param string $schemaName schema entity is contained within
     * @return string JSON array
     */
    public function getEntity($entityName, $entityId, $schemaName = null)
    {
        $entityPath = $this->constructEntityPath($entityName, $schemaName);
        $command = $this->getCommand('GetEntity', array('name' => $entityPath, 'id' => $entityId));
        return $this->executeCommand($command, 'data');
    }

    /**
     * Create a new instance of the entity. Successful response is a JSON object
     * containing identifiers for all created entities.
     *
     * @param string $entityName name of the entity
     * @param string $data JSON document with data for entity
     * @param string $schemaName schema entity is contained within
     * @param boolean $createModel whether to create the model for this entity or not
     * @return string JSON array
     */
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

    /**
     * Destroy an instance of this entity.
     *
     * @param string $entityName name of the entity
     * @param int $entityId identifier of the entity
     * @param string $schemaName schema entity is contained within
     * @return string status code returned from server
     */
    public function deleteEntity($entityName, $entityId, $schemaName = null)
    {
        $entityPath = $this->constructEntityPath($entityName, $schemaName);
        $command = $this->getCommand('DeleteEntity', array('name' => $entityPath, 'id' => $entityId));
        return $this->executeCommand($command, 'status');
    }

    /**
     * Execute a single SQL statement.
     *
     * @param string $sql statement to execute
     * @return string JSON array with results
     */
    public function executeSqlQuery($sql)
    {
        $command = $this->getCommand('ExecuteQuery', array('q' => $sql));
        return $this->executeCommand($command, 'data');
    }

    /**
     * Execute multiple SQL statements within a single transaction.
     *
     * @param array $queries each element is an individual SQL statement
     * @return string JSON array with results (1 field per statement executed)
     */
    public function executeMultipleSqlQueries($queries = array())
    {
        $sql = '';
        foreach ($queries as $query) {
            $sql .= $query . ";";
        }
        $command = $this->getCommand('ExecuteQueries', array('queries' => $sql));
        return $this->executeCommand($command, 'data');
    }

    /**
     * Create a model from a JSON document.
     *
     * @param string $entityName name of the entity
     * @param string $data JSON document with specification for entity
     * @param string $schemaName schema entity is contained within
     * @return string JSON array describing the parsed document
     */
    public function createEntityModel($entityName, $data, $schemaName = null)
    {
        $entityPath = $this->constructEntityPath($entityName, $schemaName);
        $command = $this->getCommand('CreateModel', array('name' => $entityPath, 'data' => $data, 'create' => 'true'));
        $command->set('command.headers', array('Content-type' => 'application/json'));
        return $this->executeCommand($command, 'data');
    }

    /**
     * @return string JSON array with server version
     */
    public function getServerVersion()
    {
        return $this->executeCommand($this->getCommand('Version'), 'data');
    }

    /**
     * Execute the given command and from the result object,
     * return the specified element.
     */
    private function executeCommand($command, $returnElement)
    {
        try {
            $response = $this->execute($command);
        } catch (ClientErrorResponseException $e) {
            return $e->getMessage();
        }
        return $response[$returnElement];
    }

    /**
     * Construct a full entity path. Format is:
     *  $schemaName.$entityName
     */
    private function constructEntityPath($entityName, $schemaName)
    {
        return ($schemaName === null ? $entityName : $schemaName . "." . $entityName);
    }
}
