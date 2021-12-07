<?php

require_once APPLICATION_ROOT_PATH . 'models/WechatBase.php';

/**
 * 支付宝在线支付类
 * @package wechat
 */
class Wechat extends WechatBase {

    private $_payment; //充值配置

    /**
     * 生成支付代码
     * @access public
     * @param array $order 订单信息
     * @return string
    */

    public function Pay_Code($order, $type = false) {
        if($type == true){
            $type = 'MWEB';
        }else{
            $type = 'NATIVE';
        }
        $url = "https://api.mch.weixin.qq.com/pay/unifiedorder";
        $this->_payment = $this->Get_Config('wechat');

        $input['body'] = Zend_Registry::get('config')['goodName'];
        $input['attach'] = 'web';
        $input['out_trade_no'] = $order['order_id'];
        $input['total_fee'] = $order['amount'];
        $input['time_start'] = date("YmdHis");
        $input['time_expire'] = date("YmdHis", time() + 600);
        $input['goods_tag'] = '';
        $input['notify_url'] = $this->Notify_Url('wechat');
        $input['trade_type'] = $type;
        $input['product_id'] = $order['amount'];
        $input['appid'] = $this->_payment['appid'];
        $input['mch_id'] = $this->_payment['mchid'];
        $input['spbill_create_ip'] = util::getip();
        $input['nonce_str'] = $this->getNonceStr();
        $input['sign'] = $this->MakeSign($input,$this->_payment['key']);

        $xml = $this->ToXml($input);
        $response = $this->postXmlCurl($xml, $url, false, 6);
        $result = $this->FromXml($response);

        if ($result['return_code'] == 'FAIL') {
            throw new Exception(print_r($result));
        }
        if($type == 'JSAPI'){
            $result = $this->GetJsApiParameters($result);
        }
        $this->CheckSign($result,$this->_payment['key']);
        return $result;
    }

    /**
     * 
     * 获取jsapi支付的参数
     * @param array $UnifiedOrderResult 统一支付接口返回的数据
     * @return json数据，可直接填入js函数作为参数
     */
    public function GetJsApiParameters($order) {
        if (!array_key_exists("appid", $order) || !array_key_exists("prepay_id", $order) || $order['prepay_id'] == "") {
            throw new Exception("参数错误");
        }
        $parameters['appId'] = $order['appid'];
        $parameters['nonceStr'] = $this->getNonceStr();
        $parameters['timeStamp'] = time();
        $parameters['package'] = 'prepay_id=' . $order['prepay_id'];
        $parameters['signType'] = 'MD5';
        $parameters['paySign'] = $this->MakeSign($parameters);
        $parameters = json_encode($parameters);
        return $parameters;
    }
}