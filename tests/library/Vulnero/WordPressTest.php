<?php
class Vulnero_WordPressTest extends Vulnero_Test_PHPUnit_ControllerTestCase
{
    public function testRegisterActivationHook()
    {
        $w = new Vulnero_WordPress($this->_bootstrap);
        $w->registerActivationHook('test', 'func');
        $this->assertEquals(array(array('file' => 'test', 'func' => 'func')), $w->getActivationHooks());
    }

    public function testAddAction()
    {
        $w = new Vulnero_WordPress($this->_bootstrap);
        $w->addAction('test', 3);
        $this->assertEquals(array('test'), $w->getActions());
    }

    public function testAddFilter()
    {
        $w = new Vulnero_WordPress($this->_bootstrap);
        $w->addFilter('test');
        $this->assertEquals(array('test'), $w->getFilters());
    }

    public function testGetSidebar()
    {
        $w = new Vulnero_WordPress($this->_bootstrap);
        $this->assertEquals('', $w->getSidebar());
    }

    public function testRegisterWidget()
    {
        $w = new Vulnero_WordPress($this->_bootstrap);
        $w->registerWidget('test');
        $this->assertEquals(array('test'), $w->getWidgets());
    }

    public function testGetActivationHooks()
    {
        $w = new Vulnero_WordPress($this->_bootstrap);
        $this->assertEquals(array(), $w->getActivationHooks());
    }

    public function testGetFilters()
    {
        $w = new Vulnero_WordPress($this->_bootstrap);
        $this->assertEquals(array(), $w->getFilters());
    }

    public function testWidgets()
    {
        $w = new Vulnero_WordPress($this->_bootstrap);
        $this->assertEquals(array(), $w->getWidgets());
    }

    public function testActions()
    {
        $w = new Vulnero_WordPress($this->_bootstrap);
        $this->assertEquals(array(), $w->getActions());
    }

    public function testGetSidebars()
    {
        $w = new Vulnero_WordPress($this->_bootstrap);
        $this->assertEquals(array(), $w->getSidebars());
    }

    public function testGetBlogInfo()
    {
        $w = new Vulnero_WordPress($this->_bootstrap);
        $this->assertEquals('Test', $w->getBlogInfo('name'));
        $this->assertEquals(null, $w->getBlogInfo('bad key'));
    }

    public function testGetThemeRoot()
    {
        $w = new Vulnero_WordPress($this->_bootstrap);
        $this->assertEquals(PROJECT_BASE_PATH, $w->getThemeRoot());
    }

    public function testGetTemplate()
    {
        $w = new Vulnero_WordPress($this->_bootstrap);
        $this->assertEquals(array(), $w->getTemplate());
    }

    public function testGetTags()
    {
        $w = new Vulnero_WordPress($this->_bootstrap);
        $this->assertEquals(array(), $w->getTags());
    }

    public function testGetCategory()
    {
        $w = new Vulnero_WordPress($this->_bootstrap);
        $this->assertTrue($w->getCategory('test') instanceof stdclass);
    }

    public function testGetPostCateories()
    {
        $w = new Vulnero_WordPress($this->_bootstrap);
        $this->assertEquals(array(), $w->getPostCategories());
    }

    public function testGetDatabase()
    {
        $w = new Vulnero_WordPress($this->_bootstrap);
        $this->assertTrue($w->getDatabase() instanceof Zend_Db_Adapter_Pdo_Sqlite);
    }

    public function testLocateTemplate()
    {
        $w = new Vulnero_WordPress($this->_bootstrap);
        $this->assertEquals(
            array(realpath(PROJECT_BASE_PATH . '/../../themes') . '/page.php'),
            $w->locateTemplate('test')
        );
    }

    public function testApplyFilters()
    {
        $w = new Vulnero_WordPress($this->_bootstrap);
        $w->applyFilters('test', 'text');
        $this->assertEquals(array('test'), $w->getFilters());
    }
}
