<?php

require_once APPLICATION_ROOT_PATH . '/models/WechatBase.php';

/**
 * 易宝付在线支付类
 * 
 * @package wechat
 */
class Wechatquery extends WechatBase {

    public $data; //接收到的数据，类型为关联数组
    var $returnParameters; //返回参数，类型为关联数组
    var $_pay_config;

    /**
     * 将微信的请求xml转换成关联数组，以方便数据处理
     */
    public function srchpay($sn) {
        $this->_pay_config = $this->Get_Config('wechat');
        $data = array('appid' => $this->_pay_config['appid'], 'mch_id' => $this->_pay_config['mchid'], 'nonce_str' => $this->getNonceStr(), 'out_trade_no' => $sn);
        try {
            $url = "https://api.mch.weixin.qq.com/pay/orderquery";
            $timeOut = 6;
            $data['sign'] = $this->SetSign($data); //签名
            $xml = $this->ToXml($data);
            $response = $this->postXmlCurl($xml, $url, false, $timeOut);
            $data = $this->Init($response);
        } catch (Exception $e) {
            throw new Exception('100');
        }

        if ($data['return_code'] == 'SUCCESS') {
            if ($data['result_code'] == 'SUCCESS') {
                $result = array(
                    'order_id' => (string) $data['out_trade_no'],
                    'sn' => (string) $data['transaction_id'],
                    'status' => (string) $data['trade_state'],
                );
                return $result;
            }
            throw new Exception('103');
        } else {
            throw new Exception('101');
        }
    }

    /**
     * 将xml转为array
     * @param string $xml
     * @throws WxPayException
     */
    public function Init($xml) {
        $data = $this->FromXml($xml);
        $this->CheckSign($data,$this->_pay_config['key']);
        return $data;
    }

    public function SetSign($data) {
        return $this->MakeSign($data,$this->_pay_config['key']);
    }

}
