<?php

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testExample()
    {
        $response = $this->get('/');
		$response->assertStatus(200);
        $this->assertEquals(
            $this->app->version(), $this->response->getContent()
        );
    }
	
}
