<?php
class Controller_DefaultControllerTest extends Vulnero_Test_PHPUnit_ControllerTestCase
{
    public function testHelloWorld()
    {
        $pageId    = $this->_wordPress->getOption('page_on_front');
        $wordpress = (array) $this->dispatch('helloworld');
        $this->assertModule('default');
        $this->assertController('default');
        $this->assertAction('helloworld');
        $this->assertEquals(array(
            'request'           => '',
            'query_string'      => '',
            'matched_rule'      => '()(/.*)$',
            'matched_query'     => 'pagename=&page=' . $pageId,
            'query_vars'        => array(
                                       'page'      => $pageId,
                                       'pagename'  => ''
                                   ),
            'extra_query_vars'  => array()
        ), $wordpress);
    }

    public function testHelloStatic()
    {
        $wordpress = (array) $this->dispatch('hellostatic');
        $this->assertController('default');
        $this->assertAction('hellostatic');
    }
}
