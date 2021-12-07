<?php

require_once APPLICATION_ROOT_PATH . 'models/Logs.php';

/**
 * 从平台获取数据
 */
class Getdata {

    private $_url; //接口服务器

    /**
     * 初始化
     */

    public function __construct() {
        $this->_url = Zend_Registry::get('config')['url'];
    }

    /*
     * 从接口获取数据
     */

    public function Get($url, $data) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        curl_setopt($ch, CURLOPT_URL, $this->_url . $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $return = curl_exec($ch);
        if (curl_errno($ch)) {
            Logs::Write('getdata', 'curl_errno:' . curl_error($ch));
        }
        curl_close($ch);
        return $return;
    }

}
