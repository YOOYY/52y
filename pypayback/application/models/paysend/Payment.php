<?php
require_once 'Zend/Config/Ini.php';

class Payment {

    public function pay($account,$accountID,$index,$type,$isExchange, $paytype = false) {
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

        $paycode = $type;
        $paycode = ucfirst($paycode);
        require_once APPLICATION_ROOT_PATH . 'models/paysend/' . $paycode . '.php';
        $payment = new $paycode();
        $order = $payment->Create_Order($account, $accountID, $index, 'wechatpay', $isExchange);
        Logs::write('Create_Order', 'Type: '.$type. '订单返回:'.$order);
        $order_arr = json_decode($order, true);
        if ($order_arr['error'] == '0') {
            //组装支付@param
            $param = array(
                'amount' => $order_arr['data']['order']['amount'], //金额（单位分）
                'order_id' => $order_arr['data']['order']['id'], //平台订单号 
                'playerid' => $account
            );
            //发送支付请求
            try {
                $result['data'] = $payment->Pay_Code($param,$paytype);
                $result['ret'] = 'success';
                $result['param'] = array('account' => $account, 'accountid' => $accountID, 'amount' => $index);
            } catch (Exception $e) {
                Logs::write('Create_Order', '[reemsg]：' . $e->getMessage().'[err]：' . $e);
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
        return $result;
    }
}