<?php

require_once APPLICATION_ROOT_PATH . 'models/Logs.php';
require_once 'Zend/Config/Ini.php';
require_once APPLICATION_ROOT_PATH . 'models/paysend/Payment.php';
/**
 * PaymentController 充值入口
 * @package payment
 * @author RD
 * @version 0.1
 */
class PaymentController extends Front_Controller_Action {
    
    /*
     * 非实卡充值参数接收
     * $account   账号  
     * $accountID 账号ID
     * $index    商品类型
     * $type      支付类型（string）
     * $isExchange  是否转化为金币（string）
     */
    
    public function dopayAction() {
//        if (isset($_SERVER['HTTP_QIUTAO']) && $_SERVER['HTTP_QIUTAO'] == 'leilei' && (Util::getip() == '47.102.131.125' || Util::getip() == '47.98.134.190')) {
            //接受参数
            $account = $this->getRequest()->getPost('account');
            $accountID = $this->getRequest()->getPost('accountID');
            $index = $this->getRequest()->getPost('index');
            $type = $this->getRequest()->getPost('type');
            $isExchange = $this->getRequest()->getPost('isExchange');
            //默认返回参数
            $result['ret'] = 'fail';
            $pay_config = new Zend_Config_Ini(APPLICATION_ROOT_PATH . 'configs/payment.ini', 'base');
            $payment = $pay_config->paytype->toArray();
            if (!in_array($type, $payment)) {
                $result['msg'] = '支付方式错误';
                echo json_encode($result);
                exit();
            }
            if (!in_array($index, explode(',', $pay_config->denomination->$type))) {
                $result['msg'] = '支付金额错误';
                $result['msg2'] = $index;
                echo json_encode($result);
                exit();
            }
            $pd_FrpId = '';
            $result['type'] = $type;
            $paycode = $type;
            $paycode = ucfirst($paycode);
            require_once APPLICATION_ROOT_PATH . 'models/paysend/' . $paycode . '.php';

            $payment = new $paycode();
            //创建订单
            $order = $payment->Create_Order($account, $accountID, $index, $type, $isExchange);
            Logs::write('Create_Order', $order);
            $order_arr = json_decode($order, true);
            //error 为 0
            if ($order_arr['error'] == '0') {
                //组装支付@param
                $param = array(
                    'amount' => $order_arr['data']['order']['amount'], //金额（单位分）
                    'order_id' => $order_arr['data']['order']['id'], //平台订单号 
                    'pd_FrpId' => $pd_FrpId, //支付方式
                    'playerid' => $account
                );
                //发送支付请求
                try {
                    $result['data'] = $payment->Pay_Code($param);
                    $result['ret'] = 'success';
                    $result['param'] = array('account' => $account, 'accountid' => $accountID, 'amount' => $index);
                } catch (Exception $e) {
                    Logs::write('Create_Order', '[reemsg]：' . $e->getMessage());
                    $result['message'] = '生成失败!请重试或联系客服处理！';
                }
            } else {
                switch ($order_arr['error']) {
                    case '10004':
                        $result['message'] = '该账户不存在';
                        break;
                    default :
                        $result['message'] = $order_arr['error'];
                }
            }
            echo json_encode($result);
  //      }else{
//		    echo json_encode("error");
//	}
    }

    public function doaliiosAction() {
        //if (isset($_SERVER['HTTP_QIUTAO']) && $_SERVER['HTTP_QIUTAO'] == 'leilei' && Util::getip() == '47.102.131.125') {

            $infullAccount = $this->getRequest()->getPost('infullAccount');
            $infullAccountID = $this->getRequest()->getPost('infullAccountID');
            $infullAmount = $this->getRequest()->getPost('infullAmount');
            $isExchange = $this->getRequest()->getPost('isExchange');
            require_once 'Zend/Config/Ini.php';
            $pay_config = new Zend_Config_Ini(APPLICATION_ROOT_PATH . 'configs/payment.ini', 'base');
            if (!in_array($infullAmount, explode(',', $pay_config->denomination->alipay))) {
                $result['message'] = '支付金额错误';
                echo json_encode($result);
                return;
            }
            require_once APPLICATION_ROOT_PATH . 'models/paysend/Alipay.php';
            $payment = new Alipay();
            //创建订单
            $order = $payment->Create_Order($infullAccount, $infullAccountID, $infullAmount, 'alipay', $isExchange);

            $order_arr = json_decode($order, true);
            //error 为 0
            if ($order_arr['error'] == '0') {
                //组装支付@param
                $param = array(
                    'amount' => $order_arr['data']['order']['amount'], //金额（单位分）
                    //微信   订单号重复（记得删）
                    'order_id' => $order_arr['data']['order']['id'], //平台订单号 
                    'pd_FrpId' => '', //支付方式
                    'playerid' => $infullAccount,
                    'infullAmount'=>$infullAmount
                );
                //发送支付请求
                try {
                    $result['data'] = '<p style="font-size:.45rem;width:100%;text-align:center;color:#fff;">' . $infullAmount . '</p>';
                    $result['data'] .= $payment->Pay_Code($param, 'wap');
                    $result['data'] .="<script></script>";
                    $result['ret'] = 'success';
                    $result['param'] = array('account' => $infullAccount, 'accountid' => $infullAccountID, 'amount' => $infullAmount);
                } catch (Exception $e) {
                    Logs::write('Create_Order', $order . '[reemsg]：' . $e->getMessage());
                    $result['message'] = '生成失败!请重试或联系客服处理！';
                }
            } else {
                switch ($order_arr['error']) {
                    case '10004':
                        $result['message'] = '该账户不存在';
                        break;
                    default :
                        $result['message'] = $order_arr['message'];
                        break;
                }
            }
            //var_dump($result);die;
            echo json_encode($result);
        //}
    }

    public function dowechatpayAction() {
        $account = $this->getRequest()->getPost('account');
        $accountID = $this->getRequest()->getPost('accountID');
        $index = $this->getRequest()->getPost('index');
        $isExchange = $this->getRequest()->getPost('isExchange',false);
        $type = 'wechat';
        $paytype = true; //true为JSAPI,false为NATIVE
        $payment = new Payment();
        $result = $payment->pay($account,$accountID,$index,$type,$isExchange,$paytype);
        echo json_encode($result);
    }

    public function dounionpayAction() {
        $account = $this->getRequest()->getPost('account');
        $accountID = $this->getRequest()->getPost('accountID');
        $index = $this->getRequest()->getPost('index');
        $isExchange = $this->getRequest()->getPost('isExchange',false);
        $type = $this->getRequest()->getPost('type');
        Logs::write('payment', 'Type: '.$type. 'accountID:'.$accountID);
        $result['ret'] = 'fail';
        $result['type'] = $type;

        $pay_config = new Zend_Config_Ini(APPLICATION_ROOT_PATH . 'configs/payment.ini', 'base');
        $payment_type = $pay_config->paytype->toArray();
        if (!in_array($type, $payment_type)) {
            $result['msg'] = '支付方式错误';
            echo json_encode($result);
            exit();
        }
        if (!in_array($index, explode(',', $pay_config->denomination->$type))) {
            $result['msg'] = '支付金额错误';
            echo json_encode($result);
            exit();
        }

        require_once APPLICATION_ROOT_PATH . 'models/UnionPayBase.php';
        $payment = new UnionPayBase();
        $order = $payment->Create_Order($account, $accountID, $index, $type, $isExchange);
        Logs::write('Create_Order', 'Type: '.$type. '订单返回:'.$order);
        $order_arr = json_decode($order, true);
        if ($order_arr['error'] == '0') {
            $params = $order_arr['data']['payresponse'];
            // print_r($order_arr);
            $url = $payment->Get_Config('unionpay')['orderformurl'];
            $result['ret'] = 'success';
            $result['data'] = $url.'?'.$payment->createLinkstringUrlencode($params);
        } else {
            switch ($order_arr['error']) {
                case '10004':
                    $result['message'] = '该账户不存在';
                    break;
                default :
                    $result['message'] = $order_arr['error'];
            }
        }
        echo json_encode($result);
    }
}
