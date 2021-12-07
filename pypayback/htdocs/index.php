<?php
//echo('1');die;
/* 定义常量,每个系统都必须有这4个常量 */
define('APPLICATION_ROOT_PATH', dirname(dirname(__FILE__)) . '/application/');
define('SITEDATA_ROOT_PATH', dirname(dirname(__FILE__)) . '/sitedata/');
define('HTDOCS_ROOT_PATH', dirname(dirname(__FILE__)) . '/htdocs/');
define('WHO_AM_I', 'payback');

require_once 'Zend/Controller/Front.php';
require_once 'Zend/Registry.php';
require_once 'Zend/Db.php';
require_once APPLICATION_ROOT_PATH . 'controllers/Front_Controller_Action.php';
//帮助函数
require_once APPLICATION_ROOT_PATH . 'models/Util.php';
//错误处理
require_once APPLICATION_ROOT_PATH . 'controllers/ErrorController.php';

//配置信息
require_once APPLICATION_ROOT_PATH . 'configs/application.php';
require_once APPLICATION_ROOT_PATH . 'configs/dbmm.php';
$config = array_merge($dbmm, $application_config);
//$config = $application_config;
unset($dbmm, $application_config);

//数据库
$dbParams = array('host' => $config['db']['host'],
    'username' => $config['db']['username'],
    'password' => $config['db']['password'],
    'dbname' => $config['db']['dbname'],
    'port' => $config['db']['port'],
);

//try {
    $db = Zend_Db::factory($config['db']['type'], $dbParams);
//} catch (Exception $e) {
//    echo $e->getMessage();
//}
$db->query("set names 'utf8'");
unset($dbParams);
//扩展数据库
//foreach ($config['db_second_keys'] as $dbkey) {
//	$dbParams =  array( 'host'		=> $config['db_second'.$dbkey]['host'],
//	                    'username'	=> $config['db_second'.$dbkey]['username'],
//	                    'password'	=> $config['db_second'.$dbkey]['password'],
//	                    'dbname'	=> $config['db_second'.$dbkey]['dbname'],
//	                    'port'		=> $config['db_second'.$dbkey]['port'],
//	                   );
//	$db_second = Zend_Db::factory($config['db_second'.$dbkey]['type'],$dbParams);
//	Zend_Registry::set('db_second'.$dbkey, $db_second);
//	unset($dbParams,$db_second);
//}
//缓存对象
//require_once SHARE_ROOT_PATH . 'models/MM.php';
//$mm = new MM($config ['mm']);
//用户对象
//require_once SHARE_ROOT_PATH . 'models/User.php';
//$userObj = new User($db, $mm);
//Zend_Registry::set('user', $userObj);
Zend_Registry::set('db', $db);
//Zend_Registry::set('mm', $mm);
Zend_Registry::set('config', $config);


//front controller
$fc = Zend_Controller_Front::getInstance(); //front controller
$fc->setControllerDirectory(APPLICATION_ROOT_PATH . 'controllers/');
$router = $fc->getRouter();
//自定义路由
//$router->addRoute('notice', new Zend_Controller_Router_Route('notice/:show',
//                array(
//                    'controller' => 'index',
//                    'action' => 'notice'
//                )
//        )
//);
//不同开发模式的PHP环境设置
switch ($config['system_run_level']) {
    case 'develop'://开发环境
        error_reporting(E_ALL);
        ini_set('display_errors', 'on');
        $fc->throwExceptions(true);
        break;

    case 'product'://生产环境
    default:
        error_reporting(E_ALL & ~E_NOTICE);
        ini_set('display_errors', 'off');
        $fc->throwExceptions(false);
        set_exception_handler("ErrorController::showException"); //未被ZF捕获的异常处理
        break;
}

// $xml = $GLOBALS['HTTP_RAW_POST_DATA'];
// Logs::Write('wechat', 'Type:wechat, ' . $xml . ', Error:http参数');
// if(!isset($xml)){
//     $xml = file_get_contents('php://input');
// }
// Logs::Write('wechat', 'Type:wechat, ' . $xml . ', Error:input参数');

$fc->dispatch();
//
$db->closeConnection();
//$mm->close();
