<?php

/**
 * 网站日志类
 * 
 * @package logs
 * @author RD
 */
class Logs {

    /**
     * 写入日志
     * 
     * @access public
     * @param string $logtype 日志类型
     * @param mix $content 日志内容
     * @author RD
     * 
     * @return void
     */
    public static function Write($logtype, $content) {
        $logfile = SITEDATA_ROOT_PATH . 'log/' . date('Y-m-d') . $logtype . '.log';
        $content = is_array($content) ? $content : array($content);

        // 写入日志的内容
        $writes = '';
        $t_array = & $content;
        reset($t_array);
        while (list(, $v) = each($t_array)) {
            //[Time]\t[who_am_i]\t[Keyword]\t[Content]\n
            $writes .= "[" . date('Y-m-d H:i:s') . "]\t[" . WHO_AM_I . "]\t[" . $logtype . "]\t[" . $v . "]EOR\n";
        }
        unset($t_array);
        file_put_contents($logfile, $writes, FILE_APPEND);
    }

}

?>
