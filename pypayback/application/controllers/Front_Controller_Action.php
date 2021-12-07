<?php

/**
 *  这是所有action的起点,对ZF做设置.所有action都继承于此
 *  @package base
 */
require_once 'Zend/Controller/Action.php';

class Front_Controller_Action extends Zend_Controller_Action {

    /**
     * config 系统的配置文件
     * 
     * @var array
     * @access protected
     */
    protected $config = array();

    public function init() {
        //配置视图
        $this->_configView();
    }

    /**
     * _configView 配置视图
     * 
     * @access private
     * @return void
     * @author RD
     */
    private function _configView() /* {{{ */ {
        //模板的后缀为php
        $this->_helper->viewRenderer->setViewSuffix('php');
        //不自动输出模板内容
        $this->_helper->viewRenderer->setNoRender(true);
    }

    /* }}} */
}
