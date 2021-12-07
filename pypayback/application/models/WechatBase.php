<?php
require_once APPLICATION_ROOT_PATH . '/models/Base.php';

/**
 * 微信工具类
 * 
 * @package payment
 * @author pigyjump@yahoo.com.cn
 */
class WechatBase extends Base {

    /**
     * 生成签名
     * @return 签名，本函数不覆盖sign成员变量，如要设置签名需要调用SetSign方法赋值
     */
    public function MakeSign($tmpdata,$key) {
        foreach ($tmpdata as $k => $v) {
            $Parameters[strtolower($k)] = $v;
        }
        //签名步骤一：按字典序排序参数
        ksort($Parameters);
        $string = $this->ToUrlParams($Parameters, false);
        //签名步骤二：在string后加入KEY
        $string = $string . "&key=" . $key;
        //签名步骤三：MD5加密
        $string = md5($string);
        //签名步骤四：所有字符转为大写
        $result = strtoupper($string);
        return $result;
    }

    /**
     * 输出xml字符
     * @throws WxPayException
     * */
    public function ToXml($data) {
        if (!is_array($data) || count($data) <= 0) {
            throw new WxPayException("数组数据异常！");
        }

        $xml = "<xml>";
        foreach ($data as $key => $val) {
            if (is_numeric($val)) {
                $xml.="<" . $key . ">" . $val . "</" . $key . ">";
            } else {
                $xml.="<" . $key . "><![CDATA[" . $val . "]]></" . $key . ">";
            }
        }
        $xml.="</xml>";
        return $xml;
    }

    public function FromXml($xml) {
        if (!$xml) {
            throw new Exception("xml数据异常！");
        }
        //将XML转为array
        //禁止引用外部xml实体
        libxml_disable_entity_loader(true);
        $arr = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);

        return $arr;
    }
    //发送过来的数据
    public function CheckSign($data,$key) {
        $tmpData = $data;
        unset($tmpData['sign']);
        $sign = $this->MakeSign($tmpData,$key);

        if ($data['sign'] == $sign) {
            return true;
        }
        throw new Exception("签名错误！");
    }

    
    /**
     * 格式化参数格式化成url参数
     */
    public function ToUrlParams($input) {
        $buff = "";
        foreach ($input as $k => $v) {
            if ($k != "sign" && $v != "" && !is_array($v)) {
                $buff .= $k . "=" . $v . "&";
            }
        }
        $buff = trim($buff, "&");
        return $buff;
    }

    
    /**
     * 
     * 产生随机字符串，不长于32位
     * @param int $length
     * @return 产生的随机字符串
     */
    public function getNonceStr($length = 32) {
        $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }

    
    /**
     * 以post方式提交xml到对应的接口url
     * 
     * @param string $xml  需要post的xml数据
     * @param string $url  url
     * @param bool $useCert 是否需要证书，默认不需要
     * @param int $second   url执行超时时间，默认30s
     * @throws Exception
     */
    public function postXmlCurl($xml, $url, $useCert = false, $second = 30) {
        $ch = curl_init();
        //设置超时
        curl_setopt($ch, CURLOPT_TIMEOUT, $second);

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE); //严格校验
        //设置header
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

        //post提交方式
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        //运行curl
        $data = curl_exec($ch);

        //返回结果
        if ($data) {
            curl_close($ch);
            return $data;
        } else {
            $error = curl_errno($ch);
            curl_close($ch);
            throw new Exception("curl出错，错误码:$error");
        }
    }
}

?>
