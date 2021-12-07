<?php

require_once APPLICATION_ROOT_PATH . 'models/Telrecharge.php';

/**
 * TelcheckController 手机充值入口
 * @package payment
 * @author RD
 * @version 0.1
 */
class TelrechargeController extends Front_Controller_Action
{

    public function preDispatch()
    {
        $this->_db = Zend_Registry::get("db");
        $this->_telrecharge = new Telrecharge($this->_db);
        $this->_getdata = new Base();
    }

    /**
     * 充值主页
     *
     */
    public function indexAction()
    {
        echo (Util::getip());
    }

    /*
     * 接收手机充值请求
     */

    public function createtelorderAction()
    {
        Logs::Write('telrecharge', 'item_id:account_id操作：发送错误消息123' . @$_SERVER['HTTP_QIUTAO']);
        $phonenum = $this->getRequest()->getParam('phonenum', '');
        $item_id = $this->getRequest()->getParam('item_id', '');
        $account_id = $this->getRequest()->getParam('account_id', '');
        if ($_SERVER['HTTP_QIUTAO'] == 'leilei') {
            $result = '';
            try {
                $this->_telrecharge->telcz($phonenum, $item_id, $account_id);
                $result = '0';
            } catch (Exception $e) {
                $result = $e->getMessage();
            }
            Logs::Write('telrecharge', 'item_id:' . $item_id . 'account_id:' . $account_id . '操作：充值错误：' . $result);
        } else {
            $result = '201';
            Logs::Write('telrecharge', 'item_id:' . $item_id . 'account_id:' . $account_id . '操作：非官方请求：' . $result);
        }
        if ($result != '0') {
            $url = 'account/telephonefare_error';
            Logs::Write('telrecharge', 'item_id:' . $item_id . 'account_id:' . $account_id . '操作：上报错误');
            $data = ['itemid' => $item_id, 'id' => $account_id, 'error_code' => $result];
            $return['error'] = '';
            $return = $this->_getdata->Get($url, $data);
            $return = json_decode($return, true);
            if ($return['error'] != '0') {
                $order_up_info = ['send_status' => 0];
                $this->_telrecharge->up_order($order_up_info, $this->_telrecharge->getorderid());
                $msg = 'fail';
            }
        }
        echo 'success';
        die;
    }

    /*
     * 手机充值状态查询 
     */

    public function statusqueryAction()
    {
        $orderid = $this->getRequest()->getParam('orderid');
        $result = array();
        try {
            $result = $this->_telrecharge->telsta($orderid);
        } catch (Exception $e) {
            $result['error'] = $e->getMessage();
        }
    }

    /*
     * 手机充值回调
     */

    public function telbackAction()
    {
        $sporder_id = $this->getRequest()->getParam('sporder_id');
        $orderid = $this->getRequest()->getParam('orderid');
        $status = $this->getRequest()->getParam('sta');
        $sign = $this->getRequest()->getParam('sign');
        try {
            $result = $this->_telrecharge->telback($sporder_id, $orderid, $status, $sign);
            $msg = 'sucess';
        } catch (Exception $e) {
            $result['error'] = $e->getMessage();
            $msg = 'fail';
        }
        if ($result['error'] == '0') {
            $url = 'account/telephonefare_error';
            Logs::Write('telrecharge', 'item_id:' . $result['data']['itemid'] . 'account_id' . $result['data']['id'] . '操作：发送错误消息');
            $return = $this->_getdata->Get($url, $result['data']);
            $return = json_decode($return, true);
            if ($return['error'] != '0') {
                $order_up_info = ['send_status' => 0];
                $this->_telrecharge->up_order($order_up_info, $orderid);
                $msg = 'fail';
            }
        }
        echo $msg;
    }

    //    /*
    //     * 手机充值测试
    //     */
    //
    public function teltestAction()
    {
        echo Util::getip();
        //        $url = 'http://#/telrecharge/telback';
        //        $sign = md5('e5e93eb690fb13112d6de5c6682ae288123123123123201612150000000013');
        //        $data = ['sporder_id' => '123123123123', 'orderid' => '201612150000000013', 'sta' => '9', 'sign' => $sign];
        //        $ch = curl_init();
        //        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        //        curl_setopt($ch, CURLOPT_URL, $url);
        //        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        //        curl_setopt($ch, CURLOPT_HEADER, 0);
        ////        curl_setopt($ch, CURLOPT_HTTPHEADER, array('qiutao: leilei'));
        //        curl_setopt($ch, CURLOPT_POST, 1);
        //        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        //        $return = curl_exec($ch);
        //        // 检查是否有错误发生
        //        if (curl_errno($ch)) {
        //            echo 'Curl error: ' . curl_error($ch);
        //            die;
        //        }
        //        curl_close($ch);
        //        var_dump($return);
        //        die;
    }
}
