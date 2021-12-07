<?php
require_once APPLICATION_ROOT_PATH . 'models/Logs.php';
//修改Alipay.php中的$show_url
/**
 * 在线支付通用类
 * 
 * @package payment
 * @author pigyjump@yahoo.com.cn
 */
class Base {
    private $_url; //接口服务器

    public function __construct() {

        $this->_url = Zend_Registry::get('config')['url'];

    }

    /**
     * 获取基本配置信息
     * 
     * @access protected
     * @param string $code 支付方式代码
     * 
     * @return array
     */
    public function Get_Config($code) {
        require_once 'Zend/Config/Ini.php';
        $pay_config = new Zend_Config_Ini(APPLICATION_ROOT_PATH . 'configs/payment.ini', $code);
        $payment = $pay_config->$code->toArray();
        unset($pay_config);
        return $payment;
    }

    public function Get($url, $data) {
        return $this->curl_post($this->_url . $url,$data);
    }

    public function curl_post($url, $data) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $return = curl_exec($ch);
        // 检查是否有错误发生
        if (curl_errno($ch)) {
            echo 'Curl error: ' . curl_error($ch);
            Logs::Write('getdata', 'curl_errno:' . curl_error($ch));
            die;
        }
        curl_close($ch);
        return $return;
    }

    public function createLinkstringUrlencode($para){
        $arg = "";
        while (list ($key, $val) = each($para)) {
            $arg .= $key . "=" . urlencode($val) . "&";
        }
        $arg = substr($arg, 0, count($arg) - 2);
        if (get_magic_quotes_gpc()) {
            $arg = stripslashes($arg);
        }
        return $arg;
    }

    protected function Pay_Order($order_id, $sn,$amount) {
        $url = 'pay/pay_order';
        Logs::Write('getdata', 'Oid:' . $order_id . 'sn:' . $sn . 'amount:' . $amount);
        $data = array('order_id' => $order_id, 'sn' => $sn, 'status' => 1, 'amount' => $amount);
        //$data = array('order_id' => '79', 'sn' => '4200000220201901081945768862', 'status' => 1);
        $order = $this->Get($url, $data);
        // var_dump($order);

        return json_decode($order, true);
    }

    
    /*
     * 第三方订单创建
     * @param $infullAccount   账号  
     * @param $infullAccountID 账号ID
     * @param $infullAmount    充值金额
     * @param $infullType      类型（string）
     */

    public function Create_Order($account, $accountID, $index, $type, $isExchange) {
        $paytype = Zend_Registry::get('config')['pay_type'];
        $isExchange = $isExchange == 'false' ? '0' : '1';
        $ext = json_encode(array('isexchange' => $isExchange));
        //金额单位转成分
	        Logs::Write('Create_Order', 'pay_type: '.$paytype[$type]. ',isExchange:'.$isExchange. ',account:'.$account. ',account_id:'.$accountID. ',index:'.$index. ',ext:'.$ext);
        $url = 'pay/create_order?account=' . $account . '&account_id=' . $accountID . '&index=' . $index . '&pay_type=' . $paytype[$type] . '&ext=' . $ext;
        $data = array('account' => $account, 'account_id' => $accountID, 'index' => $index, 'pay_type' => $paytype[$type], 'ext' => $ext);
        $order = $this->Get($url,$data);
        return $order;
    }

    /**
     * 获取返回信息地址
     * 
     * @access protected
     * @param string $code 支付方式代码
     * 
     * @return string
     */
    protected function Return_Url($code) {
        $baseurl = Zend_Registry::get('config')['baseUrl'];
        return $baseurl . 'payback/' . $code . 'respond/';
    }

    /**
     * 获取通知信息地址
     * 
     * @access protected
     * @param string $code 支付方式代码
     * 
     * @return string
     */
    protected function Notify_Url($code) {
        $baseurl = Zend_Registry::get('config')['baseUrl'];
        return $baseurl . 'payback/' . $code . 'notify/';
    }

}

?>
