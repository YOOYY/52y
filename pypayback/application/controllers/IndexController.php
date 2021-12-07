<?php

require_once APPLICATION_ROOT_PATH . 'models/Logs.php';
require_once 'Zend/Config/Ini.php';

/**
 * PaymentController 充值入口
 * @package payment
 * @author RD
 * @version 0.1
 */
class IndexController extends Front_Controller_Action {


    public function indexAction() {
        echo '充值返回';
    }
}
