<?php
require_once APPLICATION_ROOT_PATH . 'models/Base.php';

/**
 * 在线支付通用类
 * 
 * @package payment
 * @author pigyjump@yahoo.com.cn
 */
class Queryorder extends Base {

    /*
     * 执行订单查询
     */

    public function Query_Order($type, $sn) {
        $status = '';
        $result = array();
        $return = array();
        switch ($type) {
            case 'alipay':
                require_once APPLICATION_ROOT_PATH . 'models/payquery/Aliquery.php';
                $aliquery = new Aliquery();
                try {
                    $result = $aliquery->srchpay($sn);
                } catch (Exception $e) {
                    $return['error'] = $e->getMessage();
                    $return['message'] = $this->errormsg($return['error']);
                }
                break;
            case'wechat':
                require_once APPLICATION_ROOT_PATH . 'models/payquery/Wechatquery.php';
                $wechatquery = new Wechatquery();
                try {
                    $result = $wechatquery->srchpay($sn);
                } catch (Exception $e) {
                    $return['error'] = $e->getMessage();
                    $return['message'] = $this->errormsg($return['error']);
                }
                break;
            default :
                $return['error'] = '102';
                $return['message'] = $this->errormsg($return['error']);
                break;
        }
        if(isset($return)){
            var_dump($return);
            die;
        }
        $result = $this->Pay_Order($result['order_id'], $result['sn']);
        if ($result['error'] == '0') {
            $return['error'] = '0';
            $return['message'] = '补单完成';
        } else {
            $return['error'] = '104';
            $return['message'] = $this->errormsg($result['error']);
        }
        return json_encode($return);
    }

    /*
     * 错误码
     */

    private function errormsg($id) {
        $errorarr = array('100' => '系统错误', '101' => '订单号不存在', '102' => '支付方式错误', '103' => '订单未支付，或已经关闭', '104' => '补单失败');
        return $errorarr[$id];
    }

}

?>
