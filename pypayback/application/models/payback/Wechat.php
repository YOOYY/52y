<?php

require_once APPLICATION_ROOT_PATH . 'models/WechatBase.php';
require_once APPLICATION_ROOT_PATH . 'models/Logs.php';

/**
 * 微信在线支付类
 * 文档 https://pay.weixin.qq.com/wiki/doc/api/native.php?chapter=9_7&index=8
 * @package wechat
 */
class Wechat extends WechatBase {

    var $returnParameters; //返回参数，类型为关联数组
    var $_pay_config;

    public function Notify() {
	
        $xml = $GLOBALS['HTTP_RAW_POST_DATA'];
        // Logs::Write('wechat', 'Type:wechat, ' . $xml . ', Error:http参数');
        if(!isset($xml)){
            $xml = file_get_contents('php://input');
        }
        // Logs::Write('wechat', 'Type:wechat, ' . $xml . ', Error:input参数');
        $tmpData = $this->FromXml($xml);
        
        foreach ($tmpData as $k => $v) {
            Logs::Write('wechat', 'Type:wechat, ' . $k . ':' . ($v) . ', Error:参数');
        }
        // $tmpData = array(
        //     'appid'=>'wxa510a2266741ba47',
        //     'bank_type'=>'OTHERS',
        //     'cash_fee'=>'500',
        //     'fee_type'=>'CNY',
        //     'is_subscribe'=>'N',
        //     'mch_id'=>'1580842911',
        //     'nonce_str'=>'ngp5exwzz7sjw96f04o2nw6qlydtlgf4',
        //     'openid'=>'ozTf-wlACkWLcr-hdMMY8sFfybaM',
        //     'out_trade_no'=>'40003443',
        //     'result_code'=>'SUCCESS',
        //     'return_code'=>'SUCCESS',
        //     'sign'=>'FC93C31F41E50356C5B3498CAD3C4F0B',
        //     'time_end'=>'20200506141948',
        //     'total_fee'=>'500',
        //     'trade_type'=>'APP',
        //     'transaction_id'=>'4200000552202005060579732967'
        // );
        $this->_pay_config = $this->Get_Config('wechat');
        $isSign = $this->checkSign($tmpData,$this->_pay_config['key']);
        //支付ID
        $out_trade_no = $tmpData['out_trade_no'];
        //微信支付交易号
        $trade_no = $tmpData['transaction_id'];
        //交易状态
        $trade_status = $tmpData['result_code'];
        //支付金额
        $total_fee = $tmpData['total_fee']/100;

        if ($isSign == false) {
            Logs::Write('wechat', 'Type:wechat, Oid:' . $out_trade_no . ', Money:' . ($total_fee) . ', Error:数字签名错误');
            return false;
        }

        if ($trade_status == 'SUCCESS') {
            Logs::Write('wechat', 'Type:wechat, Oid:' . $out_trade_no . ', Money:' . ($total_fee) . ', Error:验证成功进行充值');
            $result = $this->Pay_Order($out_trade_no, $trade_no,$total_fee);
            if ($result['error'] == '0') {
                return true;
            } else {
                Logs::Write('wechat', 'Type:wechat, Oid:' . $out_trade_no . ', Money:' . ($total_fee) . ', Error:' . $result['message']);
                return false;
            }
        } else {
            Logs::Write('wechat', 'Type:wechat, Oid:' . $out_trade_no . ', 状态:' . $trade_status);
            return false;
        }
        return false;
    }

    /**
     * 设置返回微信的xml数据
     */
    function setReturnParameter($parameter, $parameterValue) {
        $this->returnParameters[$parameter] = $parameterValue;
    }

    /**
     * 将xml数据返回微信
     */
    function returnXml() {
        return $this->ToXml($this->returnParameters);
    }
}