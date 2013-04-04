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

namespace Akiban\Tests\Client;

use Akiban\AkibanClient;
use Guzzle\Tests\GuzzleTestCase;

class ClientTest extends GuzzleTestCase
{
    public function testFactoryInitializesClient()
    {
        $client = AkibanClient::factory(array('hostname' => 'localhost', 'username' => 'user', 'password' => 'pass'));
        $this->assertEquals('http://user:pass@localhost:8091/', $client->getBaseUrl());
    }
}

