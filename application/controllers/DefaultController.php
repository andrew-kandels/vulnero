<?php
class DefaultController extends Zend_Controller_Action
{
    /**
     * Default hello world action to indicate the Vulnero plugin
     * is installed and working in your WordPress installation.
     *
     * @return  void
     */
    public function helloworldAction()
    {
    }

    /**
     * Default hello world action to indicate the Vulnero plugin
     * is installed and working in your WordPress installation.
     *
     * @return  void
     */
    public function hellostaticAction()
    {
    }

    /**
     * This action is registered and used as part of the Vulnero unit
     * tests. If you remove it, some of the unit tests will fail.
     *
     * @return void
     */
    public function unittestAction() {
        // do nothing
    }
}
