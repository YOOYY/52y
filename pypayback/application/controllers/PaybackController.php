<?php

/**
 * 用户充值响应
 * 
 * @package payback
 * @author ydl <hehaiyishu@live.cn>
 */
class PaybackController extends Front_Controller_Action
{

    public function indexAction()
    {
        echo '充值返回';
    }

    /**
     * 支付宝支付响应控制器
     * paycode: alipay 
     */
    public function alipayrespondAction()
    {
        require_once APPLICATION_ROOT_PATH . '/models/payback/Alipay.php';
        $payment = new Alipay();
        if ($payment->Respond()) {
            $url = '#';
        } else {
            $url = '#';
        }
        Header("Location:$url");
    }

    /*
     * 支付宝支付响应操作 (服务器通知)
     * web专用
     */

    public function alipaynotifyAction()
    {
        require_once APPLICATION_ROOT_PATH . '/models/payback/Alipay.php';
        $payment = new Alipay();
        $result = 'fail';
        if ($payment->verifyNotify()) {
            $result = 'success';
        } else {
            $result = 'fail';
        }
        echo $result;
        exit;
    }

    //微信支付响应操作（web服务器通知）
    public function wechatnotifyAction()
    {
        require_once APPLICATION_ROOT_PATH . 'models/payback/Wechat.php';
        $payment = new Wechat();

        if ($payment->Notify()) {
            $payment->setReturnParameter("return_code", "SUCCESS");
            $payment->setReturnParameter("return_msg", "OK");
        } else {
            $payment->setReturnParameter("return_code", "FAIL");
            $payment->setReturnParameter("return_msg", "支付失败");
        }

        $returnXml = $payment->returnXml();
        echo $returnXml;
    }


    /*
     * 支付宝支付响应操作 (服务器通知)
     * app专用
     */

    public function alinotifyAction()
    {
        require_once APPLICATION_ROOT_PATH . '/models/payback/Alipay.php';
        $payment = new Alipay();
        $result = 'fail';
        if ($payment->appNotify()) {
            $result = 'success';
        } else {
            $result = 'fail';
        }
        echo $result;
        exit;
    }

    //微信支付响应操作（服务器通知）
    public function wcnotifyAction()
    {
        require_once APPLICATION_ROOT_PATH . 'models/payback/Wechat.php';
        $payment = new Wechat();

        if ($payment->Notify()) {
            $payment->setReturnParameter("return_code", "SUCCESS");
            $payment->setReturnParameter("return_msg", "OK");
        } else {
            $payment->setReturnParameter("return_code", "FAIL");
            $payment->setReturnParameter("return_msg", "支付失败");
        }

        $returnXml = $payment->returnXml();
        echo $returnXml;
    }

    public function unionpayrespondAction()
    {
        require_once APPLICATION_ROOT_PATH . '/models/payback/Unionpay.php';
        $payment = new Unionpay();
        $result = 'FAILED';
        if ($payment->Respond("web")) {
            $result = 'SUCCESS';
        } else {
            $result = 'FAILED';
        }
        echo $result;
        exit;
    }

    public function unionpaynotifyAction()
    {
        require_once APPLICATION_ROOT_PATH . '/models/payback/Unionpay.php';
        $payment = new Unionpay();
        $result = 'FAILED';
        if ($payment->Respond("app")) {
            $result = 'SUCCESS';
        } else {
            $result = 'FAILED';
        }
        echo $result;
        exit;
    }

    public function debugAction()
    {
        require_once APPLICATION_ROOT_PATH . 'models/Logs.php';
        $key = $_POST['key'];
        $data = $_POST['data'];
        //if($key && $data){
        Logs::Write('debug', 'Type:key=' . $key . chr(10) . 'Type:data,' . $data);
        //}
    }

    public function gamelogAction()
    {
        require_once APPLICATION_ROOT_PATH . 'models/Logs.php';
        $key = $_POST['key'];
        $data = $_POST['data'];
        if ($key && $data) {
            //$group_id_array = array('newfish'=>1004673047);
            // $group_id = 1004673047;
            // if($group_id){
            //$param = 'group_id='.$group_id.'&message='.time().'key:'.$key.'\ndata:'.$data;
            //	    $param = array('group_id'=>$group_id,'message'=>time().'key:'.$key.'\ndata:'.$data);
            //           Util::send_post('http://115.231.24.98:5700/send_group_msg_rate_limited',$param);

            //     }
            Logs::Write('gamelog', 'Type:key=' . $key . chr(10) . 'Type:data,' . $data);
        } else {
            echo 1;
        }
    }


    public function gamelogtestAction()
    {
        require_once APPLICATION_ROOT_PATH . 'models/Logs.php';
        $key = $_GET['key'];
        $data = $_GET['data'];
        if ($key && $data) {
            $group_id_array = array('newfish' => 1004673047);
            $group_id = 1004673047;
            if ($group_id) {
                //            $param = 'group_id='.$group_id.'&message='.time().'key:'.$key.'\ndata:'.$data;
                //           Util::_Post('http://#/send_group_msg_rate_limited',$param);
                $param = array('group_id' => $group_id, 'message' => '12134');
                Util::send_post('http://#/send_group_msg_rate_limited', $param);
            }
            Logs::Write('gamelog', 'Type:key=' . $key . chr(10) . 'Type:data,' . $data);
        } else {
            echo 1;
        }
    }

    public function getlogAction()
    {
        $name = $_GET['name'];
        $date = $_GET['date'];
        if ($name == 'gamelog' || $name == 'debug') {
            $filename = SITEDATA_ROOT_PATH . 'log/' . $date . $name . '.log';
            header("Content-Type: application/force-download");
            header("Content-Disposition: attachment; filename=" . basename($filename));
            readfile($filename);
        }
        die('1');
    }
    //ios内置支付验证
    public function iosnotifyAction()
    {
        require_once APPLICATION_ROOT_PATH . 'models/Logs.php';
        Logs::Write('ios', 'Type:ios,transaction');
        $receipt = $_REQUEST['transaction'];
        Logs::Write('ios', 'Type:ios,transaction' . $receipt);

        $orderId = $_REQUEST['orderId'];
        //$orderId = 163;
        Logs::Write('ios', 'Type:ios,orderId' . $orderId);
        $packageVersion = $_REQUEST['packageVersion'];
        Logs::Write('ios', 'Type:ios,packageVersion' . $packageVersion);
        $MD5 = $_REQUEST['md5'];
        Logs::Write('ios', 'Type:ios,md5' . $MD5);
        $KEY = 'kcvXd6eHsdcvF35';
        //$receipt = 'ewoJInNpZ25hdHVyZSIgPSAiQTFXTks1R0VXRUt2NlVXWUNZaFEweXlBKzBGQ3JoSDlVOVl0VlNWbUlHOHplc1JhOUZUZDMyZHFPT21teXA2dUJxNTdPTVhWYmhhcm5hQ0ZrSStZQTBHa3k5L0V4ajhNUmoveDljRFZGblNZZ3htN3p6VkI4ZWdQblM1YiswUHBYcE43VUptdWoyb05vVnlMUGZSZUtpTXp4eDFQNGp4L3psa2J3Q0ltUFYyRWFkZWNGWS81SjVjUVVoREczVXlSWEdYRVhvN1pyY1cxYXFiUjRFMFBLWGprUExlZXlJck9Nb3hSS3hiL0d6UjRtMEd6UlVha0VMWkFhMTAzcXd1aHVzUmJXWXhXRUsyeGZPVGVWVFdZWm0wQkZxMUpoSHk2VlpBR0FrMVdWNG8vanlHVlBMZk9YM052azlOSEp1SEdIYVRHYnpRc09hVCsveUNrZVJ5ZXBuVUFBQVdBTUlJRmZEQ0NCR1NnQXdJQkFnSUlEdXRYaCtlZUNZMHdEUVlKS29aSWh2Y05BUUVGQlFBd2daWXhDekFKQmdOVkJBWVRBbFZUTVJNd0VRWURWUVFLREFwQmNIQnNaU0JKYm1NdU1Td3dLZ1lEVlFRTERDTkJjSEJzWlNCWGIzSnNaSGRwWkdVZ1JHVjJaV3h2Y0dWeUlGSmxiR0YwYVc5dWN6RkVNRUlHQTFVRUF3dzdRWEJ3YkdVZ1YyOXliR1IzYVdSbElFUmxkbVZzYjNCbGNpQlNaV3hoZEdsdmJuTWdRMlZ5ZEdsbWFXTmhkR2x2YmlCQmRYUm9iM0pwZEhrd0hoY05NVFV4TVRFek1ESXhOVEE1V2hjTk1qTXdNakEzTWpFME9EUTNXakNCaVRFM01EVUdBMVVFQXd3dVRXRmpJRUZ3Y0NCVGRHOXlaU0JoYm1RZ2FWUjFibVZ6SUZOMGIzSmxJRkpsWTJWcGNIUWdVMmxuYm1sdVp6RXNNQ29HQTFVRUN3d2pRWEJ3YkdVZ1YyOXliR1IzYVdSbElFUmxkbVZzYjNCbGNpQlNaV3hoZEdsdmJuTXhFekFSQmdOVkJBb01Da0Z3Y0d4bElFbHVZeTR4Q3pBSkJnTlZCQVlUQWxWVE1JSUJJakFOQmdrcWhraUc5dzBCQVFFRkFBT0NBUThBTUlJQkNnS0NBUUVBcGMrQi9TV2lnVnZXaCswajJqTWNqdUlqd0tYRUpzczl4cC9zU2cxVmh2K2tBdGVYeWpsVWJYMS9zbFFZbmNRc1VuR09aSHVDem9tNlNkWUk1YlNJY2M4L1cwWXV4c1FkdUFPcFdLSUVQaUY0MWR1MzBJNFNqWU5NV3lwb041UEM4cjBleE5LaERFcFlVcXNTNCszZEg1Z1ZrRFV0d3N3U3lvMUlnZmRZZUZScjZJd3hOaDlLQmd4SFZQTTNrTGl5a29sOVg2U0ZTdUhBbk9DNnBMdUNsMlAwSzVQQi9UNXZ5c0gxUEttUFVockFKUXAyRHQ3K21mNy93bXYxVzE2c2MxRkpDRmFKekVPUXpJNkJBdENnbDdaY3NhRnBhWWVRRUdnbUpqbTRIUkJ6c0FwZHhYUFEzM1k3MkMzWmlCN2o3QWZQNG83UTAvb21WWUh2NGdOSkl3SURBUUFCbzRJQjF6Q0NBZE13UHdZSUt3WUJCUVVIQVFFRU16QXhNQzhHQ0NzR0FRVUZCekFCaGlOb2RIUndPaTh2YjJOemNDNWhjSEJzWlM1amIyMHZiMk56Y0RBekxYZDNaSEl3TkRBZEJnTlZIUTRFRmdRVWthU2MvTVIydDUrZ2l2Uk45WTgyWGUwckJJVXdEQVlEVlIwVEFRSC9CQUl3QURBZkJnTlZIU01FR0RBV2dCU0lKeGNKcWJZWVlJdnM2N3IyUjFuRlVsU2p0ekNDQVI0R0ExVWRJQVNDQVJVd2dnRVJNSUlCRFFZS0tvWklodmRqWkFVR0FUQ0IvakNCd3dZSUt3WUJCUVVIQWdJd2diWU1nYk5TWld4cFlXNWpaU0J2YmlCMGFHbHpJR05sY25ScFptbGpZWFJsSUdKNUlHRnVlU0J3WVhKMGVTQmhjM04xYldWeklHRmpZMlZ3ZEdGdVkyVWdiMllnZEdobElIUm9aVzRnWVhCd2JHbGpZV0pzWlNCemRHRnVaR0Z5WkNCMFpYSnRjeUJoYm1RZ1kyOXVaR2wwYVc5dWN5QnZaaUIxYzJVc0lHTmxjblJwWm1sallYUmxJSEJ2YkdsamVTQmhibVFnWTJWeWRHbG1hV05oZEdsdmJpQndjbUZqZEdsalpTQnpkR0YwWlcxbGJuUnpMakEyQmdnckJnRUZCUWNDQVJZcWFIUjBjRG92TDNkM2R5NWhjSEJzWlM1amIyMHZZMlZ5ZEdsbWFXTmhkR1ZoZFhSb2IzSnBkSGt2TUE0R0ExVWREd0VCL3dRRUF3SUhnREFRQmdvcWhraUc5Mk5rQmdzQkJBSUZBREFOQmdrcWhraUc5dzBCQVFVRkFBT0NBUUVBRGFZYjB5NDk0MXNyQjI1Q2xtelQ2SXhETUlKZjRGelJqYjY5RDcwYS9DV1MyNHlGdzRCWjMrUGkxeTRGRkt3TjI3YTQvdncxTG56THJSZHJqbjhmNUhlNXNXZVZ0Qk5lcGhtR2R2aGFJSlhuWTR3UGMvem83Y1lmcnBuNFpVaGNvT0FvT3NBUU55MjVvQVE1SDNPNXlBWDk4dDUvR2lvcWJpc0IvS0FnWE5ucmZTZW1NL2oxbU9DK1JOdXhUR2Y4YmdwUHllSUdxTktYODZlT2ExR2lXb1IxWmRFV0JHTGp3Vi8xQ0tuUGFObVNBTW5CakxQNGpRQmt1bGhnd0h5dmozWEthYmxiS3RZZGFHNllRdlZNcHpjWm04dzdISG9aUS9PamJiOUlZQVlNTnBJcjdONFl0UkhhTFNQUWp2eWdhWndYRzU2QWV6bEhSVEJoTDhjVHFBPT0iOwoJInB1cmNoYXNlLWluZm8iID0gImV3b0pJbTl5YVdkcGJtRnNMWEIxY21Ob1lYTmxMV1JoZEdVdGNITjBJaUE5SUNJeU1ERTVMVEF4TFRFMUlESXlPak16T2pNeklFRnRaWEpwWTJFdlRHOXpYMEZ1WjJWc1pYTWlPd29KSW5WdWFYRjFaUzFwWkdWdWRHbG1hV1Z5SWlBOUlDSmlOek5tWVRRek5ETTJNbUUwTkdNek4yVTVaVEk0TUdNeU0yWTBaV1prWXpJeU9XWTVabVF4SWpzS0NTSnZjbWxuYVc1aGJDMTBjbUZ1YzJGamRHbHZiaTFwWkNJZ1BTQWlNVEF3TURBd01EUTVORGMzTVRZek1DSTdDZ2tpWW5aeWN5SWdQU0FpTVM0d0lqc0tDU0owY21GdWMyRmpkR2x2YmkxcFpDSWdQU0FpTVRBd01EQXdNRFE1TkRjM01UWXpNQ0k3Q2draWNYVmhiblJwZEhraUlEMGdJakVpT3dvSkltOXlhV2RwYm1Gc0xYQjFjbU5vWVhObExXUmhkR1V0YlhNaUlEMGdJakUxTkRjMk1qQTBNVE14TlRRaU93b0pJblZ1YVhGMVpTMTJaVzVrYjNJdGFXUmxiblJwWm1sbGNpSWdQU0FpTVRBeE5qRkVSVVF0UVVaRE1pMDBORFZFTFRneFJVRXRNemd5UVRFNU56QTJOMFpHSWpzS0NTSndjbTlrZFdOMExXbGtJaUE5SUNKamIyMHVaM1ZoYm5sMVkzVnNkSFZ5WlM1emFHOXdMbU52YVc0Mk5EZ2lPd29KSW1sMFpXMHRhV1FpSUQwZ0lqRTBORGc0TlRNd05EZ2lPd29KSW5abGNuTnBiMjR0WlhoMFpYSnVZV3d0YVdSbGJuUnBabWxsY2lJZ1BTQWlNQ0k3Q2draWFYTXRhVzR0YVc1MGNtOHRiMlptWlhJdGNHVnlhVzlrSWlBOUlDSm1ZV3h6WlNJN0Nna2ljSFZ5WTJoaGMyVXRaR0YwWlMxdGN5SWdQU0FpTVRVME56WXlNRFF4TXpFMU5DSTdDZ2tpY0hWeVkyaGhjMlV0WkdGMFpTSWdQU0FpTWpBeE9TMHdNUzB4TmlBd05qb3pNem96TXlCRmRHTXZSMDFVSWpzS0NTSnBjeTEwY21saGJDMXdaWEpwYjJRaUlEMGdJbVpoYkhObElqc0tDU0p2Y21sbmFXNWhiQzF3ZFhKamFHRnpaUzFrWVhSbElpQTlJQ0l5TURFNUxUQXhMVEUySURBMk9qTXpPak16SUVWMFl5OUhUVlFpT3dvSkltSnBaQ0lnUFNBaVkyOXRMbWQxWVc1NWRXTjFiSFIxY21VdVozVmhibVJoYmlJN0Nna2ljSFZ5WTJoaGMyVXRaR0YwWlMxd2MzUWlJRDBnSWpJd01Ua3RNREV0TVRVZ01qSTZNek02TXpNZ1FXMWxjbWxqWVM5TWIzTmZRVzVuWld4bGN5STdDbjA9IjsKCSJlbnZpcm9ubWVudCIgPSAiU2FuZGJveCI7CgkicG9kIiA9ICIxMDAiOwoJInNpZ25pbmctc3RhdHVzIiA9ICIwIjsKfQ==';
        //        $receipt = '{
        //            "signature" = "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";
        //            "purchase-info" = "ewoJIm9yaWdpbmFsLXB1cmNoYXNlLWRhdGUtcHN0IiA9ICIyMDE2LTA5LTI2IDAyOjUxOjAzIEFtZXJpY2EvTG9zX0FuZ2VsZXMiOwoJInVuaXF1ZS1pZGVudGlmaWVyIiA9ICIxNTM2MzA2M2Y4NzFjNTQxZDEzNTA1MTljYmY4Nzk2ZmVkZTNmZDg2IjsKCSJvcmlnaW5hbC10cmFuc2FjdGlvbi1pZCIgPSAiMTAwMDAwMDIzODM1NDU4NyI7CgkiYnZycyIgPSAiMS4wIjsKCSJ0cmFuc2FjdGlvbi1pZCIgPSAiMTAwMDAwMDIzODM1NDU4NyI7CgkicXVhbnRpdHkiID0gIjEiOwoJIm9yaWdpbmFsLXB1cmNoYXNlLWRhdGUtbXMiID0gIjE0NzQ4ODM0NjMzMTQiOwoJInVuaXF1ZS12ZW5kb3ItaWRlbnRpZmllciIgPSAiNDkzRTgxNUYtRjkxQi00QTFGLUI0MjgtOEQxQzhEQUVDQkE1IjsKCSJwcm9kdWN0LWlkIiA9ICJjb20uc3N5LmdhbWVtYXRlLml0ZW0xIjsKCSJpdGVtLWlkIiA9ICIxMTU2NzM3NzgwIjsKCSJiaWQiID0gImNvbS5zc3kuZ2FtZW1hdGUiOwoJInB1cmNoYXNlLWRhdGUtbXMiID0gIjE0NzQ4ODM0NjMzMTQiOwoJInB1cmNoYXNlLWRhdGUiID0gIjIwMTYtMDktMjYgMDk6NTE6MDMgRXRjL0dNVCI7CgkicHVyY2hhc2UtZGF0ZS1wc3QiID0gIjIwMTYtMDktMjYgMDI6NTE6MDMgQW1lcmljYS9Mb3NfQW5nZWxlcyI7Cgkib3JpZ2luYWwtcHVyY2hhc2UtZGF0ZSIgPSAiMjAxNi0wOS0yNiAwOTo1MTowMyBFdGMvR01UIjsKfQ==";
        //        }';
        //        $arr1 = explode(';', $receipt);
        //        $arr2 = explode('"', $arr1[1]);
        //        $receipt = $arr2[3];
        //        $receipt = base64_encode($receipt);
        //        Logs::Write('ios', 'Type:ios,启动ios验证' . $receipt);
        //        $arr3 = explode('"', $arr1[0]);
        ////        var_dump($arr3);die;
        //        $receipt = $arr3[3];
        //        $receipt = base64_encode();
        //        echo($receipt);die;
        Logs::Write('ios', 'Type:ios,启动ios验证' . $receipt . $orderId . $packageVersion . $KEY);
        // $orderId = '31';
        // $packageVersion = '0.5';
        //        $MD5 = '5d13168bcce2ee76f52b2c3ee33a9407';
        $reMD5 = md5($receipt . $orderId . $packageVersion . $KEY);
        //        $string = $MD5.$reMD5;
        //        echo($string);die;
        //Logs::Write('ios', 'Type:ios,reMD5' . $reMD5);

        if ($MD5 != $reMD5) {
            Logs::Write('ios', 'Type:ios,echo:1');
            echo '1';
            die;
        }
        //     if ($packageVersion != '0.5') {
        //       Logs::Write('ios', 'Type:ios,echo:2');
        //     echo '2';
        //     die;
        //  }

        $isSandbox = true;
        //        $isSandbox = false;
        $result = "null";
        $info = false;
        //开始执行验证 
        require_once APPLICATION_ROOT_PATH . '/models/payback/Ios.php';
        $payment = new Ios();
        try {
            $info = $payment->getReceiptData($receipt, $isSandbox, $orderId);
            echo '4';
            die;
            // 通过product_id 来判断是下载哪个资源  
        }
        //捕获异常  
        catch (Exception $e) {
            Logs::Write('payment', 'Type:ios, payback异常:' . $e->getMessage());
            echo '3';
            die;
        }
        //        return $info;
    }
}
