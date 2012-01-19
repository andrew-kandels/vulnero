<?php
class Vulnero_ApplicationTest extends Vulnero_Test_PHPUnit_ControllerTestCase
{
    public function testLoadConfig()
    {
        $config = $this->_bootstrap->getOptions();
        $this->assertEquals(
            array(
                'name' => 'Test',
                'description' => 'Fake blog for testing.',
                'wpurl' => 'http://localhost',
                'siteurl' => 'http://localhost',
                'admin_email' => 'me@domain.com',
                'charset' => 'utf8',
                'version' => '1.0.0',
                'html_type' => '',
                'text_direction' => '',
                'language' => 'en_US',
                'stylesheet_url' => '',
                'stylesheet_directory' => '',
                'template_url' => '',
                'pingback_url' => '',
                'tags' => array(),
                'categories' => array(),
                'template' => array(),
            ),
            $config['wordpress']
        );
    }
}
