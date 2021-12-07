<?php

require_once APPLICATION_ROOT_PATH . '/models/Base.php';
require_once APPLICATION_ROOT_PATH . 'models/Logs.php';

/**
 * 易宝付在线支付类
 * 
 * @package yeepay
 */
class Huawei extends Base {

    public $data; //接收到的数据，类型为关联数组
    var $returnParameters; //返回参数，类型为关联数组

    /**
     * 苹果服务器验证
     */

    // public function productPublicFile() {
    //     header("Content-Type: text/html; charset=utf-8");
    //     $filename = APPLICATION_ROOT_PATH . "models/payback/key/payPublicKey.pem";
    //     //if(file_exists($filename)){
    //         //chmod($filename, 0777);
    //         //unlink($filename);
    //     //}
    //     //MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAlqD2oOH6Z9Ky4jJ7WIQWXSpz0ri9ZXhVOo9wd2mKtI2uTQBsInjxJ50JzzGf49blrXlxQ+JVU3zm0V7gLDBg6PDMEH+lLz8dlVNN8KfKoNVf3gOSdJpijwYCQeshKBacgj7SScWrH9XRHI+fpYXjS87knKwAPcr+eLNv+O1TVC8huwd1yG0OfQD45iC/tBXwVBX1Jiy0khQnO6+jgHrEiygr0p/4RqB19JvaxJPT6Y17udwo4IrJ2sH0qYwXcu3rD9wO4HOkyElyVAQHFmlAAN1WlCOXRV06sRoxTq7v79MRkypX71NUzPOIfmAZKdT6L6M/Gx/IF1YJKdcgR8GN1QIDAQAB
    //     $devPubKey = "MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAlqD2oOH6Z9Ky4jJ7WIQWXSpz0ri9ZXhVOo9wd2mKtI2uTQBsInjxJ50JzzGf49blrXlxQ+JVU3zm0V7gLDBg6PDMEH+lLz8dlVNN8KfKoNVf3gOSdJpijwYCQeshKBacgj7SScWrH9XRHI+fpYXjS87knKwAPcr+eLNv+O1TVC8huwd1yG0OfQD45iC/tBXwVBX1Jiy0khQnO6+jgHrEiygr0p/4RqB19JvaxJPT6Y17udwo4IrJ2sH0qYwXcu3rD9wO4HOkyElyVAQHFmlAAN1WlCOXRV06sRoxTq7v79MRkypX71NUzPOIfmAZKdT6L6M/Gx/IF1YJKdcgR8GN1QIDAQAB";
    //     $begin_public_key = "-----BEGIN PUBLIC KEY-----\r\n";
    //     $end_public_key = "-----END PUBLIC KEY-----\r\n";
        
    //     $fp = fopen($filename,'ab');
    //     fwrite($fp,$begin_public_key,strlen($begin_public_key)); 
        
    //     $raw = strlen($devPubKey)/64;
    //     $index = 0;
    //     while($index <= $raw )
    //     {
    //         $line = substr($devPubKey,$index*64,64)."\r\n";
    //         if(strlen(trim($line)) > 0)
    //         fwrite($fp,$line,strlen($line)); 
    //         $index++;
    //     }
    //     fwrite($fp,$end_public_key,strlen($end_public_key)); 
    //     fclose($fp);
    //     echo '1';
    // }

    public function sha256withrsa() {
        ksort($_POST);
        $sign = $_POST['sign'];
        $form = $_POST;
        $orderId = $_POST['orderId'];
        $requestId = $_POST['requestId'];
        $total_fee = ceil($_POST['amount']);
        foreach ($form as $k => $v) {
            Logs::Write('Huawei', 'Type:Huawei, ' . $k . ':' . ($v) . ', Error:参数');
        }

        unset($form['sign']);
        unset($form['signType']);
        if(empty($sign))
        {
            Logs::Write('Huawei', 'error:签名为空');
            echo "{\"result\":1}";
            return;
        }
        
        $content = "";
        $i = 0;
        foreach($form as $key=>$value)
        {
           if($key != "sign" && $key != "signType")
            {
               $content .= ($i == 0 ? '' : '&').$key.'='.$value;
            }
           $i++;
        }
        $filename = APPLICATION_ROOT_PATH . "models/payback/key/payPublicKey.pem";
        if(!file_exists($filename))
        {
            echo "{\"result\" : 1 }";
            Logs::Write('Huawei', 'Type:Huawei,公钥文件不存在!');
            return;
        }

        $pubKey = file_get_contents($filename);
        $openssl_public_key = openssl_get_publickey($pubKey);
        $ok = openssl_verify($content,base64_decode($sign), $openssl_public_key, 'SHA256');

        Logs::Write('Huawei', 'Type:Huawei,'.$ok);
        openssl_free_key($openssl_public_key);
        
        $res = false;
        
        if($ok)
        {
            $res = true;
                // 处理订单
                Logs::Write('Huawei', 'Type:Huawei, 状态:' . $res);
                try {
                    $result = $this->Pay_Order($requestId,$orderId,$total_fee);
                    if ($result['error'] == '0') {
                        $res = true;
                    } else {
                        Logs::Write('Huawei', 'Type:alipay, Oid:' . $requestId . ', Error:' . $result['message']);
                        throw new Exception('充值错误' . $result['message']);
                    }
                } catch (Exception $e) {
                    Logs::Write('Huawei', 'Type:Huawei, oid:' . $orderId . 'Tid' . $requestId . ', Error:处理订单-' . $e->getMessage());
                    $res = false;
                }
        }else
        {
            $res = false;
        }
        return $res;
    }
}
