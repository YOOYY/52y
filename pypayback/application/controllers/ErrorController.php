<?php

/**
 * ErrorController 统一的异常处理
 * 
 * @uses Fucai
 * @uses _Controller_Action
 * @package 
 * @version 0.1 2008-02-28 15:16:28
 * @copyright 
 * @author RD
 * @license 
 */
class ErrorController extends Front_Controller_Action {

    /**
     * writeExceptionLog 写异常的log
     * 
     * @param Exception $e 
     * @static
     * @access private
     * @return void
     * @author RD
     * @throws bool
     */
    private static function writeExceptionLog($e) /* {{{ */ {
        //zf抛出这样的异常:arrayobject=>(exception=>(),type=>(),request=>())
        //其它异常直接引用
        if ($e instanceof ArrayObject)
            $e = $e->exception;
        //记录日志
        $content = date('Y-m-d H:i:s') . ":\t";
        $content.= $e->getMessage() . "\t";
        $content.= $e->getFile() . " on line " . $e->getLine() . "\t";
        $content.= $e->getTraceAsString() . "\t";
        require_once SHARE_ROOT_PATH . 'models/Logs.php';
        return Logs::write('exception', $content);
    }

    /* }}} */
}

?>
