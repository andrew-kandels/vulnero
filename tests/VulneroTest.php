<?php
class VulneroTest extends Vulnero_Test_PHPUnit_ControllerTestCase
{
    public function testVersionNumber()
    {
        $this->assertContains(VULNERO_VERSION, file_get_contents(dirname(__FILE__) . '/../README.markdown'));
        $this->assertContains(VULNERO_VERSION, file_get_contents(dirname(__FILE__) . '/../vulnero.php'));
    }

    public function testApplication()
    {
        $this->assertTrue($GLOBALS['application'] instanceof Vulnero_Application);
    }
}
