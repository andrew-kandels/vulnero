<?php
class Controller_DefaultControllerTest extends Vulnero_Test_PHPUnit_ControllerTestCase
{
    public function testHelloWorld()
    {
        $wordpress = (array) $this->dispatch('/helloworld');
        $this->assertController('default');
        $this->assertAction('helloworld');
        $this->assertEquals(array(
            'request' => 'sample-page',
            'query_string' => null,
            'matched_rule' => '(sample\-page)(/.*)$',
            'matched_query' => 'pagename=sample-page&page=',
            'query_vars' => array(
                'page' => null,
                'pagename' => 'sample-page'
            ),
            'extra_query_vars' => array()
        ), $wordpress);
    }

    public function testHelloStatic()
    {
        $wordpress = (array) $this->dispatch('/hellostatic');
        $this->assertController('default');
        $this->assertAction('hellostatic');
    }
}
