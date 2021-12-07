<?php

require_once APPLICATION_ROOT_PATH . '/models/AlipayBase.php';

/**
 * 支付宝在线支付类
 * 
 * @package alipay
 */
class Alipay extends AlipayBase {

//    var $alipay_gateway_new = 'https://mapi.alipay.com/gateway.do?';
    var $http_verify_url = 'http://notify.alipay.com/trade/notify_query.do?';
    var $_pay_config;

    /**
     * 支付宝支付响应操作
     * 
     * @access public
     * @param
     * 
     * @return bool
     */
    public function Respond() {
        $this->_pay_config = $this->Get_Config('alipay');
        $this->_pay_config['cacert'] = getcwd() . '/key/cacert.pem';
        foreach ($_GET as $k => $v) {
            Logs::Write('alipay', 'Type:alipay, ' . $k . ':' . ($v) . ', Error:参数');
        }

        // $_GET = array(
        //     "body"=>"牌缘3D钻石",
        //     "buyer_email"=>"zws74110@qq.com",
        //     "buyer_id"=>"2088212910630730",
        //     "exterface"=>"create_direct_pay_by_user",
        //     "is_success"=>"T",
        //     "notify_id"=>"RqPnCoPT3K9%2Fvwbh3Ih%2BfjPY1CtZxpUTI9TlyM5CxTcIhiDGgu%2FmvExOFDwe9UpGMezv",
        //     "notify_time"=>"2020-05-11 15:38:10",
        //     "notify_type"=>"trade_status_sync",
        //     "out_trade_no"=>"40003757",
        //     "payment_type"=>"1",
        //     "seller_email"=>"py@52y.com",
        //     "seller_id"=>"2088431600316480",
        //     "subject"=>"牌缘3D钻石",
        //     "total_fee"=>"98.00",
        //     "trade_no"=>"2020051122001430731454566789",
        //     "trade_status"=>"TRADE_SUCCESS",
        //     "sign"=>"66374225beb8ef90185388e5aa62b7a6",
        //     "sign_type"=>"MD5"
        // );
        //生成签名结果
        $isSign = $this->getSignVeryfy($_GET, $_GET["sign"], 'MD5');
        //商户订单号
        $order_id = $_GET['out_trade_no'];
        //支付宝交易号
        $trade_no = $_GET['trade_no'];
        //支付金额
        //礼包不需要98折
        // if((int)($_GET['total_fee']*100)%98 == 0){
        //     $total_fee = $_GET['total_fee']*100/98;
        // }else{
        //     $total_fee = $_GET['total_fee'];
        // }
        $total_fee = $_GET['total_fee'];

        if ($isSign == false) {
            Logs::Write('alipay', 'Type:alipay, Oid:' . $order_id . ', Money:' . ($total_fee) . ', Error:数字签名错误');
            return false;
        }
//        $responseTxt = 'false';
//        if (!empty($_GET["notify_id"])) {
//            $responseTxt = $this->getResponse($_GET["notify_id"]);
//            Logs::Write('alipay', 'Type:alipay, $responseTxt:' . ($responseTxt) . ', Error:数字签名错误');
//        }
//        if ($_GET['trade_status'] == 'TRADE_SUCCESS' && $isSign && preg_match("/true$/i", $responseTxt)) {
        if ($_GET['trade_status'] == 'TRADE_SUCCESS' && $isSign) {
            Logs::Write('alipay', 'Type:alipay, Oid:' . $order_id . ', Money:' . ($total_fee) . ', Error:验证成功进行充值');
            try {
                $result = $this->Pay_Order($order_id, $trade_no,$total_fee);
                if ($result['error'] == '0') {
                    return true;
                } else {
                    Logs::Write('alipay', 'Type:alipay, Oid:' . $order_id . ', Money:' . ($total_fee) . ', Error:' . $result['message']);
                    return false;
                }
            } catch (Exception $exc) {
                return false;
            }
        }
        return false;
    }

    /**
     * 针对notify_url验证消息是否是支付宝发出的合法消息
     * web专用
     * @return 验证结果
     */
    function verifyNotify() {
        $this->_pay_config = $this->Get_Config('alipay');
        $this->_pay_config['cacert'] = APPLICATION_ROOT_PATH . 'models/payback/key/cacert.pem';
       foreach ($_POST as $k => $v) {
           Logs::Write('alipay', 'Type:alipay, ' . $k . ':' . ($v) . ', Error:参数');
       }
    //     $_POST = ['discount' => '0.00',
    //     'payment_type' => '1',
    //     'subject' => '牌缘掼蛋钻石',
    //     'trade_no' => '2019010922001442420505129549',
    //     'buyer_email' => '132****5320',
    //     'gmt_create' => '2019-01-09 10:30:30',
    //     'notify_type' => 'trade_status_sync',
    //     'quantity' => '1',
    //     'out_trade_no' => '95',
    //     'seller_id' => '2088431105196907',
    //     'notify_time' => '2019-01-09 10:32:57',
    //     'body' => '牌缘掼蛋钻石',
    //     'trade_status' => 'TRADE_SUCCESS',
    //     'is_total_fee_adjust' => 'N',
    //     'total_fee' => '5.00',
    //     'gmt_payment' => '2019-01-09 10:32:57',
    //     'seller_email' => 'hfpy@52y.com',
    //     'price' => '5.00',
    //     'buyer_id' => '2088612757942421',
    //     'notify_id' => '2019010900222103257042420585985457',
    //     'sign_type' => 'MD5',
    //     'sign' => '0d36bbc17b5576d3f490b6b15b8c2ae7',
    //     'use_coupon' => 'N',
    // ];
        $isSign = $this->getSignVeryfy($_POST, $_POST["sign"], 'MD5');
        if ($isSign == false) {
            Logs::Write('alipay', 'Type:alipay, Oid:' . $order_id . ', Money:' . ($total_fee) . ', Error:数字签名错误');
            return false;
        }
//        $responseTxt = 'false';
//        if (!empty($_POST["notify_id"])) {
//            $responseTxt = $this->getResponse($_POST["notify_id"]);
//        }
        $order_id = $_POST['out_trade_no'];
        //支付宝交易号
        $trade_no = $_POST['trade_no'];
        //支付金额
        //礼包不需要98折
        if(($_POST['total_fee']*100)%98 == 0){
            $total_fee = $_POST['total_fee']*100/98;
        }else{
            $total_fee = $_POST['total_fee'];
        }

//        if ($_POST['trade_status'] == 'TRADE_SUCCESS' && $isSign && preg_match("/true$/i", $responseTxt)) {
        if ($_POST['trade_status'] == 'TRADE_SUCCESS' && $isSign) {
            Logs::Write('alipay', 'Type:alipay, Oid:' . $order_id . ', Money:' . ($total_fee) . ', Error:验证成功进行充值');
            try {
                $result = $this->Pay_Order($order_id, $trade_no , $total_fee);
                if ($result['error'] == '0') {
                    return true;
                } else {
                    Logs::Write('alipay', 'Type:alipay, Oid:' . $order_id . ', Money:' . ($total_fee) . ', Error:' . $result['message']);
                    return false;
                }
            } catch (Exception $exc) {
                return false;
            }
        }
        return false;
    }

    /**
     * 针对notify_url验证消息是否是支付宝发出的合法消息
     * app专用
     * @return 验证结果
     */
    function appNotify() {
        foreach ($_POST as $k => $v) {
           Logs::Write('alipay', 'Type:alipay, ' . $k . ':' . ($v) . ', Error:参数');
        }
        $this->_pay_config = $this->Get_Config('alipay');
        //商户的私钥（后缀是.pen）文件相对路径
        $this->_pay_config['private_key_path'] = APPLICATION_ROOT_PATH . 'models/payback/key/rsa_private_key.pem';
        //支付宝公钥（后缀是.pen）文件相对路径
        $this->_pay_config['ali_public_key_path'] = APPLICATION_ROOT_PATH . 'models/payback/key/alipay_public_key.pem';
        $this->_pay_config['cacert'] = APPLICATION_ROOT_PATH . 'models/payback/key/cacert.pem';
    //    $_POST = ['discount' => '0.00',
    //        'payment_type' => '1',
    //        'subject' => '牌缘掼蛋钻石',
    //        'trade_no' => '2019010922001442420503983602',
    //        'buyer_email' => 'soarskyforgame@163.com',
    //        'gmt_create' => '2019-01-09 09:45:21',
    //        'notify_type' => 'trade_status_sync',
    //        'quantity' => '1',
    //        'out_trade_no' => '93',
    //        'seller_id' => '2088431105196907',
    //        'notify_time' => '2019-01-09 09:45:37',
    //        'body' => '牌缘掼蛋钻石',
    //        'trade_status' => 'TRADE_SUCCESS',
    //        'is_total_fee_adjust' => 'N',
    //        'total_fee' => '5.00',
    //        'gmt_payment' => '2019-01-09 09:45:37',
    //        'seller_email' => 'hfpy@52y.com',
    //        'price' => '5.00',
    //        'buyer_id' => '2088612757942421',
    //        'notify_id' => '2019010900222094537042420585657528',
    //        'use_coupon' => 'N',
    //        'sign_type' => 'MD5',
    //        'sign' => 'WiB765BxjW5QLuY7guVEcHghfDnBS+SlgYkqu7IJG8BT5ecnotJo2yk6Tt1mtpV6FKmVpv4Pzc+78brce3/iuSmiHwSWqjAhUhGxMlTnMGUU/sZAEyxwLn0fBAt2WHzgLHdzNPDGWeyST08uP461K4QvLd16y8z71RcQ03Oqn+g=',
    //    ];
//        foreach ($_POST as $k => $v) {
//            Logs::Write('alipay', 'Type:alipay, ' . $k . ':' . ($v) . ', Error:参数');
//        }
        if (empty($_POST)) {//判断POST来的数组是否为空
            return false;
        } else {
            //生成签名结果
            $isSign = $this->getSignVeryfy($_POST, $_POST["sign"], 'RSA');
            if ($isSign == false) {
                Logs::Write('alipay', 'Type:alipay, Oid:' . $_POST['out_trade_no'] . ', Money:' . ($_POST['total_fee']) . ', Error:数字签名错误');
                return false;
            }
            $responseTxt = 'false';
            if (!empty($_POST["notify_id"])) {
                $responseTxt = $this->getResponse($_POST["notify_id"]);
            }
            //logResult($log_text);
            //获取支付宝远程服务器ATN结果（验证是否是支付宝发来的消息）

            if ($_POST['trade_status'] == 'TRADE_SUCCESS' && $isSign) {
                //平台订单号
                $out_trade_no = $_POST['out_trade_no'];
                //支付宝交易号
                $trade_no = $_POST['trade_no'];
                //支付金额
                //礼包不需要98折
                if(($_POST['total_fee']*100)%98 == 0){
                    $total_fee = $_POST['total_fee']*100/98;
                }else{
                    $total_fee = $_POST['total_fee'];
                }
                Logs::Write('alipay', 'Type:alipay, Oid:' . $out_trade_no . ', Money:' . ($total_fee) . ', Error:验证成功进行充值');
                try {
                    $result = $this->Pay_Order($out_trade_no, $trade_no,$total_fee);
                    if ($result['error'] == '0') {
                        return true;
                    } else {
                        Logs::Write('alipay', 'Type:alipay, Oid:' . $out_trade_no . ', Money:' . ($total_fee) . ', Error:' . $result['message']);
                        return false;
                    }
                } catch (Exception $exc) {
                    return false;
                }
            } else {
                //Logs::Write('alipay', 'Type:alipay, Oid:' . $_POST['trade_no'] . $_POST['trade_status'].',Error:支付宝状态错误');
                Logs::Write('alipay', 'Type:alipay, Oid:' . $_POST['out_trade_no'] . ', Money:' . ($_POST['trade_status']) .', responesTxt:'.$responseTxt. ', Error:支付宝状态错误');
                return false;
            }
        }
    }

    /**
     * 获取返回时的签名验证结果
     * @param $para_temp 通知返回来的参数数组
     * @param $sign 返回的签名结果
     * @return 签名验证结果
     */
    function getSignVeryfy($para_temp, $sign, $type) {
        //构建签名
        $prestr = $this->buildSign($para_temp,$type);
        //var_dump($prestr);
        $issign = false;
        switch ($type) {
            case "RSA" :
                $issign = $this->rsaVerify($prestr, trim($this->_pay_config['ali_public_key_path']), $sign);
                break;
            case 'MD5':
                $issign = $this->md5Verify($prestr['sign'], $sign);
                break;
            default :
                $issign = false;
                break;
        }
        return $issign;
    }

    /**
     * 验证签名
     */
    function md5Verify($mysign, $sign) {
        Logs::Write('alipay', 'Type:alipay,验证签名' . $mysgin . $sign);
        if ($mysign == $sign) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 获取远程服务器ATN结果,验证返回URL
     * @param $notify_id 通知校验ID
     * @return 服务器ATN结果
     * 验证结果集：
     * invalid命令参数不对 出现这个错误，请检测返回处理中partner和key是否为空 
     * true 返回正确信息
     * false 请检查防火墙或者是服务器阻止端口问题以及验证时间是否超过一分钟
     */
    function getResponse($notify_id) {
        $partner = trim($this->_pay_config['account']);
        $veryfy_url = $this->http_verify_url;

        $veryfy_url = $veryfy_url . "partner=" . $partner . "&notify_id=" . $notify_id;
        $responseTxt = $this->getHttpResponseGET($veryfy_url, $this->_pay_config['cacert']);
        return $responseTxt;
    }

    /**
     * 远程获取数据，GET模式
     * 注意：
     * 1.使用Crul需要修改服务器中php.ini文件的设置，找到php_curl.dll去掉前面的";"就行了
     * 2.文件夹中cacert.pem是SSL证书请保证其路径有效，目前默认路径是：getcwd().'\\cacert.pem'
     * @param $url 指定URL完整路径地址
     * @param $cacert_url 指定当前工作目录绝对路径
     * return 远程输出的数据
     */
    function getHttpResponseGET($url, $cacert_url) {
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_HEADER, 0); // 过滤HTTP头
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // 显示输出结果
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true); //SSL证书认证
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2); //严格认证
        curl_setopt($curl, CURLOPT_CAINFO, $cacert_url); //证书地址
        $responseText = curl_exec($curl);
        //var_dump(curl_error($curl)); //如果执行curl过程中出现异常，可打开此开关，以便查看异常内容
        curl_close($curl);

        return $responseText;
    }

    /**
     * RSA验签
     * @param $data 待签名数据
     * @param $ali_public_key_path 支付宝的公钥文件路径
     * @param $sign 要校对的的签名结果
     * return 验证结果
     */
    function rsaVerify($data, $ali_public_key_path, $sign) {
        $pubKey = file_get_contents($ali_public_key_path);
        $pubKey = str_replace("-----BEGIN PUBLIC KEY-----", "", $pubKey);
        $pubKey = str_replace("-----END PUBLIC KEY-----", "", $pubKey);
        $pubKey = str_replace("\n", "", $pubKey);
        $pubKey = '-----BEGIN PUBLIC KEY-----' . PHP_EOL . wordwrap($pubKey, 64, "\n", true) . PHP_EOL . '-----END PUBLIC KEY-----';
        $res = openssl_get_publickey($pubKey);
        $result = (bool) openssl_verify($data, base64_decode($sign), $res);
        openssl_free_key($res);
        return $result;
    }

}
