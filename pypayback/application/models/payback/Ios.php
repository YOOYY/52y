<?php

require_once APPLICATION_ROOT_PATH . '/models/Base.php';
require_once APPLICATION_ROOT_PATH . 'models/Logs.php';

/**
 * 易宝付在线支付类
 * 
 * @package yeepay
 */
class Ios extends Base {

    public $data; //接收到的数据，类型为关联数组
    var $returnParameters; //返回参数，类型为关联数组

    /**
     * 苹果服务器验证
     */

    public function getReceiptData($receipt, $isSandbox = false, $orderId) {
        $this->keyword_score = 'score-ios';
        Logs::Write('ios', 'Type:ios,启动ios验证');
        if ($isSandbox) {
            $endpoint = 'https://sandbox.itunes.apple.com/verifyReceipt';
        } else {
            $endpoint = 'https://buy.itunes.apple.com/verifyReceipt';
        }

        $postData = json_encode(
                array('receipt-data' => $receipt)
        );

        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);  //这两行一定要加，不加会报SSL 错误  
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        $response = curl_exec($ch);
        $errno = curl_errno($ch);
        $errmsg = curl_error($ch);
        curl_close($ch);
        //判断时候出错，抛出异常  
        if ($errno != 0) {
            throw new Exception($errmsg, $errno);
        }

        $data = json_decode($response);

        //var_dump($data);
        //判断返回的数据是否是对象  
        if (!is_object($data)) {
            throw new Exception('Invalid response data');
            return false;
        }

        //判断购买时候成功  
        if (!isset($data->status) || $data->status != 0) {
            Logs::Write('ios', 'Type:ios, oid:' . $orderId . ', Error:ios1验证错误' . $data->status);
            $status = 'fail';
            throw new Exception('错误状态' . $data->status);
        } else {
            $status = 'success';
        }

        //返回产品的信息             
        $tmpData = array(
            'quantity' => $data->receipt->quantity, //数量
            'product_id' => $data->receipt->product_id, //产品ID
            'transaction_id' => $data->receipt->transaction_id, //交易标识
            'purchase_date' => $data->receipt->purchase_date, //交易日期
//            'app_item_id' => $data->receipt->app_item_id, //AppStore程序标识（sandbox没有）
            'bid' => $data->receipt->bid, //bundle标识
            'bvrs' => $data->receipt->bvrs,//版本
        );
        foreach ($tmpData as $k => $v) {
            Logs::Write('ios', 'Type:ios, ' . $k . ':' . ($v) . ', Error:参数');
        }

//       if ($tmpData['bid'] != 'com.paiyuan.newpygame') {
//           Logs::Write('ios', 'Type:ios, oid:' . $orderId . ', Error:bid错误' . $tmpData['bid']);
//           throw new Exception('bid错误' . $tmpData['bid']);
//            return false;
//       }

        //支付ID
        $trade_no = $tmpData['transaction_id'];
//        require_once APPLICATION_ROOT_PATH . '/models/payment/iosconfig.php';
//        // 检查宝石数量是否正确
//        if ($tmpData['quantity'] != $product[$tmpData['product_id']]['quantity']) {
//            Logs::Write('payment', 'Type:ios, gem:' . $tmpData['quantity'] . ', Error:宝石数量错误');
//            return false;
//        }
        $total_fee = substr($tmpData['product_id'],strrpos($tmpData['product_id'],"shop")+4);
	//$total_fee = (int)$tmpData['product_id'];
	Logs::Write('ios', 'Type:ios, 金额:' . $total_fee);

        //获得宝石
//        $score = $product[$tmpData['product_id']]['quantity'];

        //require_once 'Zend/Config/Ini.php';
        //$pay_config = new Zend_Config_Ini(APPLICATION_ROOT_PATH . 'configs/payment.ini', 'ios');
        //$payment = $pay_config->ios->toArray();
//        var_dump($payment);
       // if (!in_array($tmpData['quantity'], $payment)) {
        //    Logs::Write('payment', 'Type:ios, gem:' . $tmpData['quantity'] . ', Error:宝石数量错误');
         //   return false;
       // }
       // if (!in_array(substr($tmpData['product_id'],strcspn($tmpData['product_id'],'coin')+4), $payment)) {
         //   Logs::Write('payment', 'Type:ios, gem:' . $tmpData['product_id'] . ', Error:金额错误');
         //   return false;
       // }

            Logs::Write('ios', 'Type:ios, 状态:' . $status);
        if ($status == 'success') {
            // 处理订单
            Logs::Write('ios', 'Type:ios, 状态:' . $status);
            try {
                $result = $this->Pay_Order($orderId, $trade_no,$total_fee);
                if ($result['error'] == '0') {
                    return true;
                } else {
                    Logs::Write('ios', 'Type:alipay, Oid:' . $orderId . ', Error:' . $result['message']);
                    throw new Exception('充值错误' . $result['message']);
                }
            } catch (Exception $e) {
                Logs::Write('ios', 'Type:ios, oid:' . $orderId . 'Tid' . $trade_no . ', Error:处理订单-' . $e->getMessage());
            }
        }
        else {
            Logs::Write('payment', 'Type:ios,Tid:' . $trade_no . ', Money:' . ($total_fee) . ', Error:支付状态-' . $note);
            return false;
        }
    }

}
