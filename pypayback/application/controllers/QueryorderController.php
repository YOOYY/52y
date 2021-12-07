<?php

require_once APPLICATION_ROOT_PATH . 'models/payquery/Queryorder.php';

/**
 * 补单控制类
 * 
 * @package payback
 * @author ydl <hehaiyishu@live.cn>
 */
class QueryorderController extends Front_Controller_Action {

    /**
     * 初始化,获取db对象，创建分类对象,设置baseUrl
     */
    public function preDispatch() {
        $this->_qo = new Queryorder();
    }

    /**
     * 第三方支付查询
     * paycode: alipay | tenpay | jxpayszx 
     */
    public function reorderAction() {
        $type = $_GET['type'];
        $sn = $_GET['sn'];
        $result = $this->_qo->Query_Order($type, $sn);
        var_dump($result);
        die;
        return $result;
    }

}
