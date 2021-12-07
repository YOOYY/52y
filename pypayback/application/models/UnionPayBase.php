<?php
require_once APPLICATION_ROOT_PATH . '/models/Base.php';

/**
 * 支付宝工具类
 * 
 * @package payment
 * @author pigyjump@yahoo.com.cn
 */
class UnionPayBase extends Base {

    function buildSign($para_temp,$type){
        //除去待签名参数数组中的空值和签名参数
        $para_filter = $this->paraFilter($para_temp);
        
        //对待签名参数数组排序
        $para_sort = $this->argSort($para_filter);

        //把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
        $prestr = $this->createLinkstring($para_sort);
        
        $payment = $this->Get_Config('unionpay');
        
        $mysign = "";

        $prestr = $prestr . $payment['key'];
        if($type === "sha256"){
            $mysign = hash("sha256", $prestr);
        }else{
            $mysign = strtoupper(md5($prestr));
        }

        return $mysign;
    }

    /**
     * 把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
     * @param $para 需要拼接的数组
     * return 拼接完成以后的字符串
     */
    function createLinkstring($para) {
        $arg = "";
        while (list ($key, $val) = each($para)) {
            $arg.=$key . "=" . $val . "&";
        }
        //去掉最后一个&字符
        $arg = substr($arg, 0, count($arg) - 2);

        //如果存在转义字符，那么去掉转义
        if (get_magic_quotes_gpc()) {
            $arg = stripslashes($arg);
        }

        return $arg;
    }

    /**
     * 除去数组中的空值和签名参数
     * @param $para 签名参数组
     * return 去掉空值与签名参数后的新签名参数组
     */
    function paraFilter($para) {
        $para_filter = array();
        while (list ($key, $val) = each($para)) {
            if ($key == "sign" || !$this->myTrim($val))
                continue;
            else
                $para_filter[$key] = $para[$key];
        }
        return $para_filter;
    }

    /**
     * 对数组排序
     * @param $para 排序前的数组
     * return 排序后的数组
     */
    function argSort($para) {
        ksort($para);
        reset($para);
        return $para;
    }

    function myTrim($val){
        if(is_string($val)){
            $search = array(" ","　","\n","\r","\t");
            $replace = array("","","","","");
            return str_replace($search, $replace, $val) !== "";
        }else{
            return true;
        }
    }
}

?>
