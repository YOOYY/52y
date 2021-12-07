<?php
require_once APPLICATION_ROOT_PATH . '/models/UnionPayBase.php';

class Unionpay extends UnionPayBase {

    public function Respond($paytype) {
        foreach ($_POST as $k => $v) {
            Logs::Write('Unionpay', 'Type:Unionpay, ' . $k . ':' . ($v) . ', Error:参数');
        }

        // $_POST = array(
        //     'payTime' => '2020-05-13 11:04:18',
        //     'connectSys' => 'UNIONPAY',
        //     'sign' => '341DB97EB34F5FB84289BD9B63753555',
        //     'merName' => '牌缘（湖州）网络科技有限公司',
        //     'mid' => '898310148163372',
        //     'invoiceAmount' => '500',
        //     'settleDate' => '2020-05-13',
        //     'billFunds'=>'现金:500',
        //     'buyerId' => 'otdJ_uEfR-XmnBr0KksuvnoRYpUc',
        //         'mchntUuid'=>'2d9081bd7182be6b01719594352f2f97',
        //     'tid' => '20125810',
        //     'instMid' => 'MINIDEFAULT',
        //     'couponAmount' => '0',
        //     'targetOrderId' => '4200000553202005137206165240',
        //     'subBuyerId' => 'o45PI5Uleh0SOtkffU7DkhDsnES0',
        //     'billFundsDesc' => '现金支付5.00元。',
        //     'orderDesc' => '牌缘（湖州）网络科技有限公司',
        //     'seqId' => '14438254826N',
        //     'merOrderId' => '736240003788',
        //     'targetSys' => 'WXPay',
        //     'bankInfo' => 'OTHERS',
        //     'ZE' => 'bDHA',
        //     'totalAmount' => '500',
        //     'createTime'=>'2020-05-13 11:04:07',
        //     'buyerPayAmount'=>'500',
        //     'notifyId' => '126e7ee5-9a61-46e9-bdf8-f108710ad535',
        //     'subInst' => '104200',
        //     'status' => 'TRADE_SUCCESS',
        // );

        //生成签名结果
        if($paytype === "web"){
            if(isset($_POST["signType"]) && $_POST["signType"] === "sha256"){
                $isSign = $this->getSignVeryfy($_POST, $_POST["sign"], 'sha256');
            }else{
                $isSign = $this->getSignVeryfy($_POST, $_POST["sign"], 'MD5');
            }
        }else{
            $isSign = $this->getSignVeryfy($_POST, $_POST["sign"], 'MD5');
        }

        //商户订单号
        $prefix = substr($_POST['merOrderId'],0,4);
        $myPrefix = $this->Get_Config('unionpay')['prefix'];
        if($prefix == $myPrefix){
            $order_id = substr($_POST['merOrderId'],4);
        }else{
            Logs::Write('Unionpay', 'Type:Unionpay, merOrderId:' . $_POST['merOrderId'] . ', Error:订单前四位错误');
            return false;
        }

        //支付宝交易号
        $trade_no = $_POST['targetOrderId'];
        //支付金额
        //礼包不需要98折
        if((int)($_POST['totalAmount'])%98 == 0){
            $total_fee = $_POST['totalAmount']/98;
        }else{
            $total_fee = $_POST['totalAmount']/100;
        }

        if ($isSign === false) {
            Logs::Write('Unionpay', 'Type:Unionpay, merOrderId:' . $order_id . ', Money:' . ($total_fee) . ', Error:数字签名错误');
            return false;
        }

        if ($_POST['status'] == 'TRADE_SUCCESS') {
            Logs::Write('Unionpay', 'Type:Unionpay, Oid:' . $order_id . ', Money:' . ($total_fee) . ', Error:验证成功进行充值');
            try {
                $result = $this->Pay_Order($order_id, $trade_no,$total_fee);
                if ($result['error'] == '0') {
                    return true;
                } else {
                    Logs::Write('Unionpay', 'Type:Unionpay, Oid:' . $order_id . ', Money:' . ($total_fee) . ', Error:' . $result['message']);
                    return false;
                }
            } catch (Exception $exc) {
                return false;
            }
        }
        return false;
    }

    function getSignVeryfy($para_temp, $sign, $type) {
        //构建签名
        $mysign = $this->buildSign($para_temp,$type);

        $issign = false;

        Logs::Write('Unionpay', 'Type:Unionpay,验证签名' . $mysign . ' '.$sign);
        if ($mysign == $sign) {
            $issign = true;
        } else {
            $issign = false;
        }

        return $issign;
    }
}
