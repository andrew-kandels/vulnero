<?php
class Controller_DefaultControllerTest extends Vulnero_Test_PHPUnit_ControllerTestCase
{
    public function testIndex()
    {
        $this->dispatch('/');
        $this->assertController('default');
        $this->assertAction('helloworld');
    }
}
