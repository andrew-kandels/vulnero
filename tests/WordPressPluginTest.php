<?php
class WordPressPluginTest extends Vulnero_Test_PHPUnit_ControllerTestCase
{
    public function testVersionNumber()
    {
        $lines = file(PLUGIN_BASE_PATH . '/wordpress-plugin.php');
        $version = 'unknown';
        foreach ($lines as $line) {
            if (preg_match('/^Version: (.*)/', $line, $matches)) {
                $version = trim($matches[1]);
            }
        }

        $this->assertContains($version, file_get_contents(dirname(__FILE__) . '/../README.markdown'));
    }

    public function testApplication()
    {
        $this->assertTrue($GLOBALS['application'] instanceof Vulnero_Application);
    }
}
