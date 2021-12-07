<?php

require_once APPLICATION_ROOT_PATH . 'models/Logs.php';
require_once APPLICATION_ROOT_PATH . 'models/Base.php';

// +----------------------------------------------------------------------
// | JuhePHP [ NO ZUO NO DIE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2010-2015 http://juhe.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: Juhedata <info@juhe.cn-->
// +----------------------------------------------------------------------
//----------------------------------
// 聚合数据-手机话费充值API调用类
//----------------------------------
class Telrecharge {

    private $uid = 'hzpykj';
    private $appkey = '54058df2501e4cbaa8134dcaba08b8bc';
    private $orderurl = 'http://118.178.154.180:8899/bc/order/apply';
    private $re_url = 'http://pay.52y.com/telrecharge/telback';
    // private $submitUrl = 'http://op.juhe.cn/ofpay/mobile/onlineorder';
    // private $staUrl = 'http://op.juhe.cn/ofpay/mobile/ordersta';
    private $orderid;
    private $business_id = 'hf';
    private $_perarr = ['1', '5', '10', '20', '50', '100'];
    private $_db;

    public function __construct($db) {
        $this->_db = $db;
    }

    public function getorderid() {
        return $this->orderid;
    }

//     /**
//      * 根据手机号码及面额查询是否支持充值
//      * @param  string $mobile   [手机号码]
//      * @param  int $pervalue [充值金额]
//      * @return  boolean
//      */
//     public function telcheck($mobile, $pervalue) {
// //        $this->datacheck($mobile, $pervalue);
//         $params = 'key=' . $this->appkey . '&phoneno=' . $mobile . '&cardnum=' . $pervalue;
//         $content = $this->juhecurl($this->telCheckUrl, $params);
//         $result = $this->_returnArray($content);
//         if ($result['error_code'] == '0') {
//             return true;
//         } else {
//             throw new Exception('102');
//         }
//     }

    // /**
    //  * 根据手机号码和面额获取商品信息
    //  * @param  string $mobile   [手机号码]
    //  * @param  int $pervalue [充值金额]
    //  * @return  array
    //  */
    // public function telquery($mobile, $pervalue) {
    //     $this->telcheck($mobile, $pervalue);
    //     $params = 'key=' . $this->appkey . '&phoneno=' . $mobile . '&cardnum=' . $pervalue;
    //     $content = $this->juhecurl($this->telQueryUrl, $params);
    //     return $this->_returnArray($content);
    // }

    /**
     * 提交话费充值
     * @param  [string] $mobile   [手机号码]
     * @param  [int] $pervalue [充值面额]
     * @param  [string] $orderid  [自定义单号]
     * @return  [array]
     */
    public function telcz($phonenum, $item_id, $account_id) {
        $pervalue = $item_id % 10000;
        // $this->telcheck($phonenum, $pervalue);
        $order_info = ['phonenum' => $phonenum, 'pervalue' => $pervalue, 'item_id' => $item_id, 'account_id' => $account_id];
        //数据写入数据库
        if (!$this->add_order($order_info)) {
            throw new Exception('201');
        }
        Logs::Write('telrecharge', 'orderid:' . $this->orderid . ', Error:提交订单');
        $sign = md5($this->business_id . $phonenum . $pervalue . $this->orderid . $this->appkey); //校验值计算
        $params = array(
            'sign' => $sign,
            're_url' => $this->re_url,
            'business_id' => $this->business_id,
            'recharge_no' => $phonenum,
            'user_order_no' => $this->orderid,
            'amount' => $pervalue,
            'uid' => $this->uid,
            'recharge_type' => 201
        );
        $content = $this->juhecurl($this->orderurl, $params);
//        $content = $this->telcheck($phonenum, $pervalue);
        $contentarr = $this->_returnArray($content);
        if ($contentarr['code'] == 10000) {
            $order_up_info = ['cash' => $contentarr['result']['price'], 'sporder_id' => $contentarr['result']['order_no'], 'status' => 1];
            $this->up_order($order_up_info, $this->orderid);
            return $this->orderid;
        } else {
            $order_up_info = ['status' => $contentarr['code']];
            $this->up_order($order_up_info, $this->orderid);
            Logs::Write('telrecharge', 'orderid:' . $this->orderid . ', code:' . $contentarr['message'] . ', Error:提交订单出错');
            throw new Exception('201');
        }
    }

    /*
     * 更新手机充值订单
     */

    public function up_order($order_up_info, $orderid) {
        $where = $this->_db->quoteInto('order_id=?', $orderid);
        $this->_db->update('telrecharge', $order_up_info, $where);
        return true;
    }

    /*
     * 添加手机充值订单
     */

    private function add_order($order_info) {
        if (!is_array($order_info) || count($order_info) == 0){
            return false;
        }
        if ($this->_db->insert('telrecharge', $order_info) != 1){
            return false;
        }
            
        // Logs::Write('telrecharge', '插入错误');
        $deposit_id = $this->_db->lastInsertId();

        // 生成订单号
        $this->orderid = date('Ymd') . str_pad($deposit_id, 10, '0', STR_PAD_LEFT);

        $where = $this->_db->quoteInto('id=?', $deposit_id);
        $this->_db->update('telrecharge', array('order_id' => $this->orderid), $where);

        return true;
    }

    /**
     * 查询订单的充值状态
     * @param  [string] $orderid [自定义单号]
     * @return  [array]
     */
    public function sta($orderid) {
        $params = 'key=' . $this->appkey . '&orderid=' . $orderid;
        $content = $this->juhecurl($this->staUrl, $params);
        return $this->_returnArray($content);
    }

    /*
     * 回调处理
     */

    public function telback($sporder_id, $orderid, $status, $sign, $memo) {
        $result = ['error' => ''];
        $local_sign = md5($this->uid . $sporder_id . $orderid . $this->appkey);
        if ($local_sign == $sign) {
            Logs::Write('telrecharge', 'orderid:' . $orderid . ', status:' . $status . ', Error:接收回调');
            if ($status == '202') {
                //订单取消处理
                $this->_db->query('BEGIN WORK');
                $orderinfo = $this->_db->fetchRow('select * from telrecharge where order_id=? for update', $orderid);
                Logs::Write('telrecharge', 'orderid:' . $orderid . ', status:' . $status . ', Error:接收回调1；失败原因'.$mome);
                if ($orderinfo['send_status'] == 0) {
                    Logs::Write('telrecharge', 'orderid:' . $orderid . ', status:' . $status . ', Error:接收回调2');
                    $order_up_info = ['send_status' => '1', 'status' => '9'];
                    $this->up_order($order_up_info, $orderid);
                    $result['error'] = '0';
                    $result['data'] = ['itemid' => $orderinfo['item_id'], 'id' => $orderinfo['account_id'], 'error_code' => '201'];
                }
                $this->_db->query('COMMIT WORK');
            } elseif ($status == '200') {
                Logs::Write('telrecharge', 'orderid:' . $orderid . ', status:' . $status . ', Error:接收回调3');
                $order_up_info = ['status' => '1'];
                $this->up_order($order_up_info, $orderid);
            }
        }
        return $result;
    }

    /**
     * 将JSON内容转为数据，并返回
     * @param string $content [内容]
     * @return array
     */
    public function _returnArray($content) {
        return json_decode($content, true);
    }

    /**
     * 请求接口返回内容
     * @param  string $url [请求的URL地址]
     * @param  string $params [请求的参数]
     * @return  string
     */
    public function juhecurl($url, $params) {

        $fields_string = "";
        foreach ($params as $key => $value) {
            $fields_string .= $key . '=' . $value . '&';
        }
        rtrim($fields_string, '&');

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
        $return = curl_exec($ch);
        // 检查是否有错误发生
        if (curl_errno($ch)) {
            echo 'Curl error: ' . curl_error($ch);
            die;
        }
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE); 
        curl_close($ch);
        if($httpCode != 200) return false;
        return "" . $return;
    }

    private function datacheck($mobile, $pervalue) {
        $search = '/^(1(([3578][0-9])|(47)))\d{8}$/';
        if (!in_array($pervalue, $this->_perarr) || !is_numeric($mobile) || strlen($mobile) != '11' || !preg_match($search, $mobile)) {
            throw new Exception('101');
        }
    }

}
