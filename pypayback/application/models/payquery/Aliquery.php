<?php
require_once APPLICATION_ROOT_PATH . 'models/AlipayBase.php';

/**
 * 支付宝在线支付类
 * 
 * @package alipay
 */
class Aliquery extends AlipayBase {

    var $_alipay_gateway_new = 'https://mapi.alipay.com/gateway.do?';
    var $_pay_config;

    /*
     * 支付宝订单查询操作
     */

    public function srchpay($sn) {
        $this->_pay_config = $this->Get_Config('alipay');
        $this->_pay_config['cacert'] = getcwd() . '/cacert.pem';
        $parameter = array(
            "service" => "single_trade_query",
            "partner" => trim($this->_pay_config['account']),
            "trade_no" => $sn,
            "out_trade_no" => '',
            "_input_charset" => trim(strtolower('utf-8'))
        );
        $html_text = $this->buildRequestHttp($parameter);
        $xml = simplexml_load_string($html_text); //创建 SimpleXML对象

        if ($xml->is_success == 'T') {
            if ($xml->response->trade->trade_status == 'TRADE_SUCCESS' || $xml->response->trade->trade_status == 'TRADE_FINISHED') {
                $result = array(
                    'order_id' => (string) $xml->response->trade->out_trade_no,
                    'sn' => (string) $billid = $xml->response->trade->trade_no,
                    'status' => (string) $xml->response->trade->trade_status,
                );
            } else {
                throw new Exception('103');
            }
            unset($xml);
            return $result;
        } else {
            throw new Exception('101');
        }
    }

    /**
     * 建立请求，以模拟远程HTTP的POST请求方式构造并获取支付宝的处理结果
     * @param $para_temp 请求参数数组
     * @return 支付宝处理结果
     */
    function buildRequestHttp($para_temp) {
        $sResult = '';

        //待请求参数数组字符串
        $request_data = $this->buildSign($para_temp,'MD5');
      
	 //远程获取数据
        $sResult = $this->getHttpResponsePOST($this->_alipay_gateway_new, $this->_pay_config['cacert'], $request_data, trim(strtolower('utf-8')));
        return $sResult;
    }

    /**
     * 远程获取数据，POST模式
     * 注意：
     * 1.使用Crul需要修改服务器中php.ini文件的设置，找到php_curl.dll去掉前面的";"就行了
     * 2.文件夹中cacert.pem是SSL证书请保证其路径有效，目前默认路径是：getcwd().'\\cacert.pem'
     * @param $url 指定URL完整路径地址
     * @param $cacert_url 指定当前工作目录绝对路径
     * @param $para 请求的数据
     * @param $input_charset 编码格式。默认值：空值
     * return 远程输出的数据
     */
    function getHttpResponsePOST($url, $cacert_url, $para, $input_charset = '') {

        if (trim($input_charset) != '') {
            $url = $url . "_input_charset=" . $input_charset;
        }
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true); //SSL证书认证
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2); //严格认证
        curl_setopt($curl, CURLOPT_CAINFO, $cacert_url); //证书地址
        curl_setopt($curl, CURLOPT_HEADER, 0); // 过滤HTTP头
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // 显示输出结果
        curl_setopt($curl, CURLOPT_POST, true); // post传输数据
        curl_setopt($curl, CURLOPT_POSTFIELDS, $para); // post传输数据
        $responseText = curl_exec($curl);
        //var_dump( curl_error($curl) );//如果执行curl过程中出现异常，可打开此开关，以便查看异常内容
        curl_close($curl);

        return $responseText;
    }

}
