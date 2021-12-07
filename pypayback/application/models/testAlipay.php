<?php

require_once APPLICATION_ROOT_PATH . '/models/Payment.php';
require_once SHARE_ROOT_PATH . 'models/Logs.php';

/**
 * 易宝付在线支付类
 * 
 * @package yeepay
 */
class Alipay extends Payment {

    var $alipay_gateway_new = 'https://mapi.alipay.com/gateway.do?';

    /**
     * 生成支付代码
     * 
     * @access public
     * @param array $order 订单信息
     * @param string $baseurl 网站url
     * 
     * @return string
     */
    public function Pay_Code($order, $baseurl) {
        $payment = $this->Get_Config('alipay');

        //业务类型
        $payment_type = '1';
        //必填，不能修改
        //服务器异步通知页面路径

        $notify_url = $this->Notify_Url('alipay', $baseurl);
        //需http://格式的完整路径，不能加?id=123这类自定义参数
        //页面跳转同步通知页面路径
        $return_url = $this->Return_Url('alipay', $baseurl);
        //卖家支付宝帐户
        $seller_email = $payment['email'];
        //必填
        //商户订单号
        $out_trade_no = $order['order_id'];

        //商户网站订单系统中唯一订单号，必填
        //订单名称
        $subject = '捕鱼世界宝石';
        //必填
        //付款金额
        $total_fee = $order['order_amount'];
        //必填
        //订单描述
        $body = '捕鱼世界宝石';
        //商品展示地址
        $show_url = 'http://www.by5918.com/';
        //需以http://开头的完整路径，例如：http://www.xxx.com/myorder.html
        //防钓鱼时间戳
//        $anti_phishing_key = $this->query_timestamp();
        $anti_phishing_key = '';
        //若要使用请调用类文件submit中的query_timestamp函数
        //客户端的IP地址
        $exter_invoke_ip = util::getip();
        //非局域网的外网IP地址，如：221.0.0.1
//构造要请求的参数数组，无需改动
        $parameter = array(
            "service" => "create_direct_pay_by_user",
            "partner" => trim($payment['account']),
            "seller_email" => $seller_email,
            "payment_type" => $payment_type,
            "notify_url" => $notify_url,
            "return_url" => $return_url,
            "out_trade_no" => $out_trade_no,
            "subject" => $subject,
            "total_fee" => $total_fee,
            "body" => $body,
            "show_url" => $show_url,
            "anti_phishing_key" => $anti_phishing_key,
            "exter_invoke_ip" => $exter_invoke_ip,
            "_input_charset" => trim(strtolower('utf-8'))
        );
        // 签名
        $html_text = $this->buildRequestForm($parameter, "post", $out_trade_no);
        return $html_text;
    }

    /**
     * 建立请求，以表单HTML形式构造（默认）
     * @param $para_temp 请求参数数组
     * @param $method 提交方式。两个值可选：post、get
     * @param $button_name 确认按钮显示文字
     * @return 提交表单HTML文本
     */
    function buildRequestForm($para_temp, $method, $out_trade_no) {
        //待请求参数数组
        $para = $this->buildRequestPara($para_temp);
        $sHtml = "<form target='_blank' id='alipaysubmit' name='alipaysubmit' action='https://mapi.alipay.com/gateway.do?_input_charset=" . trim(strtolower('utf-8')) . "' method='" . $method . "'>";
        while (list ($key, $val) = each($para)) {
            $sHtml.= "<input type='hidden' name='" . $key . "' value='" . $val . "'/>";
        }

        //submit按钮控件请不要含有name属性
        $sHtml = $sHtml . "<div class='item item-btns'><input type='submit' class='button2 btn-login' value='点击支付' id='quickpay' class='alipaySub' onclick='topay(" . $out_trade_no . ",\"alipay\")'></div></form>";

//        $sHtml = $sHtml . "<script>document.forms['alipaysubmit'].submit();</script>";

        return $sHtml;
    }

    /**
     * 生成要请求给支付宝的参数数组
     * @param $para_temp 请求前的参数数组
     * @return 要请求的参数数组
     */
    function buildRequestPara($para_temp) {
        //除去待签名参数数组中的空值和签名参数
        $para_filter = $this->paraFilter($para_temp);

        //对待签名参数数组排序
        $para_sort = $this->argSort($para_filter);

        //生成签名结果
        $mysign = $this->buildRequestMysign($para_sort);

        //签名结果与签名方式加入请求提交参数组中
        $para_sort['sign'] = $mysign;
        $para_sort['sign_type'] = strtoupper(trim(strtoupper('MD5')));

        return $para_sort;
    }

    /**
     * 生成签名结果
     * @param $para_sort 已排序要签名的数组
     * return 签名结果字符串
     */
    function buildRequestMysign($para_sort) {
        $payment = $this->Get_Config('alipay');
        //把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
        $prestr = $this->createLinkstring($para_sort);

        $mysign = "";
//        switch (strtoupper(trim(strtoupper('MD5')))) {
//            case "MD5" :
        $mysign = $this->md5Sign($prestr, $payment['key']);
//                break;
//            default :
//                $mysign = "";
//        }

        return $mysign;
    }

    /**
     * 签名字符串
     * @param $prestr 需要签名的字符串
     * @param $key 私钥
     * return 签名结果
     */
    function md5Sign($prestr, $key) {
        $prestr = $prestr . $key;
        return md5($prestr);
    }

    /**
     * 把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
     * @param $para 需要拼接的数组
     * return 拼接完成以后的字符串
     */
    function createLinkstring($para) {
	$arg  = "";
	while (list ($key, $val) = each ($para)) {
		$arg.=$key."=".$val."&";
	}
	//去掉最后一个&字符
	$arg = substr($arg,0,count($arg)-2);
	
	//如果存在转义字符，那么去掉转义
	if(get_magic_quotes_gpc()){$arg = stripslashes($arg);}
	
	return $arg;
    }

    /**
     * 除去数组中的空值和签名参数
     * @param $para 签名参数组
     * return 去掉空值与签名参数后的新签名参数组
     */
    function paraFilter($para) {
        $para_filter = array();
        while (list ($key, $val) = each($para)) {
            if ($key == "sign" || $key == "sign_type" || $val == "")
                continue;
            else
                $para_filter[$key] = $para[$key];
        }
        return $para_filter;
    }

    /**
     * 对数组排序
     * @param $para 排序前的数组
     * return 排序后的数组
     */
    function argSort($para) {
        ksort($para);
        reset($para);
        return $para;
    }

    /**
     * 支付宝支付响应操作
     * 
     * @access public
     * @param
     * 
     * @return bool
     */
    public function Respond() {

        $this->keyword_score = 'pc-alipay';

        $payment = $this->Get_Config('alipay');
        //生成签名结果

        $isSign = $this->getSignVeryfy($_GET, $_GET["sign"]);

        //获取支付宝远程服务器ATN结果（验证是否是支付宝发来的消息）
//        $responseTxt = 'true';
//        if (!empty($_GET["notify_id"])) {
//            $responseTxt = $this->getResponse($_GET["notify_id"]);
//        }
        //验证
        //$responsetTxt的结果不是true，与服务器设置问题、合作身份者ID、notify_id一分钟失效有关
        //isSign的结果不是true，与安全校验码、请求时的参数格式（如：带自定义参数等）、编码格式有关
//        if (preg_match("/true$/i", $responseTxt) && $isSign) {
        //商户订单号

        $order_id = $_GET['out_trade_no'];

        //支付宝交易号

        $trade_no = $_GET['trade_no'];

        //交易状态
        $trade_status = $_GET['trade_status'];

        //支付金额
        $total_fee = $_GET['total_fee'];

        if ($_GET['trade_status'] == 'TRADE_FINISHED' || $_GET['trade_status'] == 'TRADE_SUCCESS') {

            //数字签名错误，写入日志

            if ($isSign == false) {
                Logs::Write('payment', 'Type:alipay, Oid:' . $order_id . ', Money:' . ($total_fee) . ', Error:数字签名错误');

                return false;
            }
//
//            // 检查商户号是否正确
//            if ($order_id != $payment['account']) {
//                Logs::Write('payment', 'Type:alipay, Oid:' . $order_id . ', Money:' . ($total_fee) . ', Error:商户号错误-' . $order_id);
//
//                return false;
//            }
            // 检查支付的金额是否相符
            if (!$this->Check_Money($order_id, $total_fee)) {
                Logs::Write('payment', 'Type:alipay, Oid:' . $order_id . ', Money:' . ($total_fee) . ', Error:金额错误');

                return false;
            }


            // 支付状态处理
            if ($isSign == true) {
                $status = 'success';
                $note = '支付成功';
            } else {
                $status = 'fail';
                $note = '支付失败';
            }
        } else {
            $status = 'fail';
            $note = '支付失败';
        }
        if ($status == 'success') {
            // 处理订单
            try {
                $this->Order_Paid($order_id, $trade_no, $status, $note);
                return true;
            } catch (Exception $e) {
                Logs::Write('payment', 'Type:alipay, Oid:' . $order_id . ', Money:' . ($total_fee) . ', Error:处理订单-' . $e->getMessage());
                return false;
            }
        } else {
            Logs::Write('payment', 'Type:alipay, Oid:' . $order_id . ', Money:' . ($total_fee) . ', Error:支付状态-' . $note);
            return false;
        }
//        } else {
//            return false;
//        }
    }

    /**
     * 针对notify_url验证消息是否是支付宝发出的合法消息
     * @return 验证结果
     */
    function verifyNotify() {
        $this->keyword_score = 'pc-alipay';
        Logs::Write('payment', 'Type:alipay,接受异步通知数据');
//        if (empty($_POST)) {//判断POST来的数组是否为空
//            return false;
//        } else {
        //生成签名结果
        $isSign = $this->getSignVeryfy($_POST, $_POST["sign"]);

        //获取支付宝远程服务器ATN结果（验证是否是支付宝发来的消息）
        //获取支付宝远程服务器ATN结果（验证是否是支付宝发来的消息）
//
//            $responseTxt = 'true';
//            if (!empty($_GET["notify_id"])) {
//                $responseTxt123 = $this->getResponse($_GET["notify_id"]);
//            }
//            Logs::Write('payment', 'Type:alipay,验证来源' . $responseTxt123);
        //验证
        //$responsetTxt的结果不是true，与服务器设置问题、合作身份者ID、notify_id一分钟失效有关
        //isSign的结果不是true，与安全校验码、请求时的参数格式（如：带自定义参数等）、编码格式有关
//        if (preg_match("/true$/i", $responseTxt) && $isSign) {
        //商户订单号

        $order_id = $_POST['out_trade_no'];

        //支付宝交易号

        $trade_no = $_POST['trade_no'];

        //交易状态
        $trade_status = $_POST['trade_status'];

        //支付金额
        $total_fee = $_POST['total_fee'];

        if ($_POST['trade_status'] == 'TRADE_FINISHED' || $_POST['trade_status'] == 'TRADE_SUCCESS') {

            //数字签名错误，写入日志

            if ($isSign == false) {
                Logs::Write('payment', 'Type:alipay, Oid:' . $order_id . ', Money:' . ($total_fee) . ', Error:数字签名错误');

                return false;
            }
//
//            // 检查商户号是否正确
//            if ($order_id != $payment['account']) {
//                Logs::Write('payment', 'Type:yeepay, Oid:' . $order_id . ', Money:' . ($total_fee) . ', Error:商户号错误-' . $order_id);
//
//                return false;
//            }
            // 检查支付的金额是否相符
            if (!$this->Check_Money($order_id, $total_fee)) {
                Logs::Write('payment', 'Type:alipay, Oid:' . $order_id . ', Money:' . ($total_fee) . ', Error:金额错误');

                return false;
            }


            // 支付状态处理
            if ($isSign == true) {
                $status = 'success';
                $note = '支付成功';
            } else {
                $status = 'fail';
                $note = '支付失败';
            }
        } else {
            $status = 'fail';
            $note = '支付失败';
        }
        if ($status == 'success') {
            // 处理订单
            try {
                $this->Order_Paid($order_id, $trade_no, $status, $note);
                return true;
            } catch (Exception $e) {
                Logs::Write('payment', 'Type:alipay, Oid:' . $order_id . ', Money:' . ($total_fee) . ', Error:处理订单-' . $e->getMessage());
                return false;
            }
        } else {
            Logs::Write('payment', 'Type:alipay, Oid:' . $order_id . ', Money:' . ($total_fee) . ', Error:支付状态-' . $note);
            return false;
        }
//        } else {
//            return false;
//        }
//        }
    }

    /**
     * 获取返回时的签名验证结果
     * @param $para_temp 通知返回来的参数数组
     * @param $sign 返回的签名结果
     * @return 签名验证结果
     */
    function getSignVeryfy($para_temp, $sign) {
        //除去待签名参数数组中的空值和签名参数
        $para_filter = $this->paraFilter($para_temp);

        //对待签名参数数组排序
        $para_sort = $this->argSort($para_filter);

        //把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
        $prestr = $this->createLinkstring($para_sort);

        $payment = $this->Get_Config('alipay');

        $isSgin = false;

        $isSgin = $this->md5Verify($prestr, $sign, $payment['key']);


        return $isSgin;
    }

    /**
     * 验证签名
     * @param $prestr 需要签名的字符串
     * @param $sign 签名结果
     * @param $key 私钥
     * return 签名结果
     */
    function md5Verify($prestr, $sign, $key) {
        $prestr = $prestr . $key;
        $mysgin = md5($prestr);
        Logs::Write('payment', 'Type:alipay,验证签名' . $mysgin . $sign);
        if ($mysgin == $sign) {
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
        $payment = $this->Get_Config('alipay');
        $partner = trim($payment['key']);
        $veryfy_url = '';
        $veryfy_url = 'http://notify.alipay.com/trade/notify_query.do?';
        $veryfy_url = $veryfy_url . "partner=" . $partner . "notify_id=" . $notify_id;
        $responseTxt = $this->getHttpResponseGET($veryfy_url, getcwd() . '\\cacert.pem');
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

    public function writelog($content, $result) {
        Logs::Write('payment', 'result:' . $result . ', content:' . $content);
    }

    function query_timestamp() {
        $payment = $this->Get_Config('alipay');
        $url = $this->alipay_gateway_new . "service=query_timestamp&partner=" . trim(strtolower($payment['account'])) . "&_input_charset=" . trim(strtolower('utf-8'));
        $encrypt_key = "";

        $doc = new DOMDocument();
        $doc->load($url);
        $itemEncrypt_key = $doc->getElementsByTagName("encrypt_key");
        $encrypt_key = $itemEncrypt_key->item(0)->nodeValue;
        return $encrypt_key;
    }

}
