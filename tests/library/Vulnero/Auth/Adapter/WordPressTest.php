<?php
class Vulnero_Auth_Adapter_WordPressTest extends Vulnero_Test_PHPUnit_ControllerTestCase
{
    public function testAuth()
    {
        $adapter = new Vulnero_Auth_Adapter_WordPress($this->_bootstrap->bootstrap('wordPress')
                                                                       ->getResource('wordPress'));
        $auth = Zend_Auth::getInstance();
        $result = $auth->authenticate($adapter);
        $this->assertEquals(Zend_Auth_Result::SUCCESS, $result->getCode());
        $this->assertTrue($result->isValid());
        $identity = $auth->getIdentity();
        $this->assertEquals('Mr. Tester', $identity->data->display_name);
        $this->assertContains('manage_options', array_keys($identity->allcaps));
    }
}
