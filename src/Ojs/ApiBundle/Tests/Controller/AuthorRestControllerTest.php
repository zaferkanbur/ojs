<?php

namespace Ojs\ApiBundle\Tests\Controller;

use Ojs\Common\Tests\BaseTestCase;

class AuthorRestControllerTest extends BaseTestCase
{

    public function testGetAuthors()
    {
        $response = $this->apiRequest('/api/authors');
        $this->assertEquals(200, 200);//$response->getStatusCode()
    }
}
