<?php

require_once APPLICATION_ROOT_PATH . 'models/AlipayBase.php';
require_once APPLICATION_ROOT_PATH . 'models/Logs.php';

/**
 * 支付宝在线支付类
 * 
 * @package yeepay
 */
class Alipay extends AlipayBase {
//    var $alipay_gateway_new = 'https://mapi.alipay.com/gateway.do?';

    /**
     * 生成支付代码
     * 
     * @access public
     * @param array $order 订单信息
     * 
     * @return string
     */
    public function Pay_Code($order, $type = 'web') {
        $payment = $this->Get_Config('alipay');

        //业务类型
        $payment_type = '1';
        //必填，不能修改
        //服务器异步通知页面路径

        $notify_url = $this->Notify_Url('alipay');
        //需http://格式的完整路径，不能加?id=123这类自定义参数
        //页面跳转同步通知页面路径
        $return_url = $this->Return_Url('alipay');
        //卖家支付宝帐户
        $seller_email = $payment['email'];
        //必填
        //商户订单号
        $out_trade_no = $order['order_id'];

        //商户网站订单系统中唯一订单号，必填
        //订单名称
        $subject = '牌缘3D钻石';
        //必填
        //付款金额 单位从分转成元
        //98折
        // $total_fee = $order['amount']*98/10000;
        // $pay_config = new Zend_Config_Ini(APPLICATION_ROOT_PATH . 'configs/payment.ini', 'base');
        // if(!in_array($order['infullAmount'], explode(',', $pay_config->denomination->not98))){
        //     $total_fee = ($order['amount']/100)*0.98;
        // }else{
        //     $total_fee = ($order['amount']/100);
        // }
        $total_fee = ($order['amount']/100);
        //必填
        //订单描述
        $body = '牌缘3D钻石';
        //防钓鱼时间戳
//        $anti_phishing_key = $this->query_timestamp();
        $anti_phishing_key = '';
        //若要使用请调用类文件submit中的query_timestamp函数
        //客户端的IP地址
        $exter_invoke_ip = '';
        //非局域网的外网IP地址，如：221.0.0.1

        $parameter = array(
            "partner" => trim($payment['account']),
            "payment_type" => $payment_type,
            "notify_url" => $notify_url,
            "return_url" => $return_url,
            "out_trade_no" => $out_trade_no,
            "subject" => $subject,
            "_input_charset" => trim(strtolower('utf-8')),
            "body" => $body
        );

        if ($type == 'wap') {
            $param = array(
		        "show_url" => 'http://m.52y.com?playerid='.trim($order['playerid']),
                "service" => 'alipay.wap.create.direct.pay.by.user',
                "seller_id" => trim($payment['account']),
                "total_fee" => round($total_fee,2),
                "app_pay" => "Y", //启用此参数能唤起钱包APP支付宝
            );
        } else {
            $param = array(
		        "show_url" => 'http://www.52y.com/html/index/index.html',
                "service" => "create_direct_pay_by_user",
                "seller_email" => $seller_email,
                "total_fee" => $total_fee,
                "anti_phishing_key" => $anti_phishing_key,
                "exter_invoke_ip" => $exter_invoke_ip,
            );
        }
        $parameter = array_merge($parameter,$param);
        foreach($parameter as $index => $val){
            Logs::write('alipaypayment','Type:alipay, ' .$index.":".$val. ', Error:参数');
        }
        // 签名
        $html_text = $this->buildRequestForm($parameter, "post", $out_trade_no, $type);
        return $html_text;
    }

    /**
     * 建立请求，以表单HTML形式构造（默认）
     * @param $para_temp 请求参数数组
     * @param $method 提交方式。两个值可选：post、get
     * @param $button_name 确认按钮显示文字
     * @return 提交表单HTML文本
     */
    function buildRequestForm($para_temp, $method, $out_trade_no, $type) {
        //待请求参数数组
        $para = $this->buildSign($para_temp,'MD5');
        $sHtml = "<form target='_self' id='alipaysubmit' name='alipaysubmit' action='https://mapi.alipay.com/gateway.do?_input_charset=" . trim(strtolower('utf-8')) . "' method='" . $method . "'>";
        while (list ($key, $val) = each($para)) {
            $sHtml.= "<input type='hidden' name='" . $key . "' value='" . $val . "'/>";
        }
        $value = $type == 'wap' ? '支付宝支付' : '';
        $onclick = $type == 'wap' ? 'AliiosJsAPI' : 'BankWebInfull';
        //submit按钮控件请不要含有name属性
        $sHtml = $sHtml . "<div class='item item-btns'><input type='submit' class='button2 btn-login' value='" . $value . "' id='quickpay' class='alipaySub' onclick='".$onclick.".topay();'></div></form>";
//        $sHtml = $sHtml . "<script>document.getElementById('form').submit();</script>";
        return $sHtml;
    }

}
/*
构造要请求的参数数组，无需改动
       $parameter = array(
           "partner" => trim($payment['account']),
           "payment_type" => $payment_type,
           "notify_url" => $notify_url,
           "return_url" => $return_url,
           "out_trade_no" => $out_trade_no,
           "subject" => $subject,
           "body" => $body,
           "show_url" => $show_url,
           "_input_charset" => trim(strtolower('utf-8')),
       );
       if ($type == 'wap') {
           $parameter['service'] = "alipay.wap.create.direct.pay.by.user";
           $parameter['app_pay'] = "Y";
           $parameter['seller_id'] = $seller_id;
           $parameter['total_fee'] = $total_fee . '.00';
       } else {
           $parameter['service'] = "create_direct_pay_by_user";
           $parameter['seller_email'] = $seller_email;
           $parameter['total_fee'] = $total_fee;
           $parameter['anti_phishing_key'] = $anti_phishing_key;
           $parameter['exter_invoke_ip'] = $exter_invoke_ip;
}
*/
