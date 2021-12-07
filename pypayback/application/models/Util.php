<?php
/**
 * 工具类文件，在这里写一些公用的函数
 * @package util
 */


 class Util {
 
 /**
  * 分页函数,风格为首页，上一页，下一页，最后一页
  *
  * @param Int totalNum 总的记录数
  * @param Int per_page 每页显示的数量
  * @param Int nowPage 当前页
  * @return  Array 关联数组
   array(
         'page'     => Array(
                             'totalnum'  => $totalNum,//总的数量
                             'totalpage' => $PageNum,//总的页数
                             'perpage'   => $PerPage,//每页显示多少数据
                             'first'     => $FirstNum,//第一页
                             'end'       => $EndNum,//最后一页
                             'previous'  => $previousNum,//前一页
                             'nowp'      => $nowPage,//当前页
                             'next'      => $nextNum//下一页
                             ),
         'start'    => $limitStart
                  );
  * @author RD
  */
public static function page ($totalNum = 0, $per_page = 10, $nowPage = 1)
 {
     /**总的页数**/
     $PageNum = ceil ($totalNum / $per_page);
     
     $nowPage = $nowPage > $PageNum ? $PageNum : $nowPage;
     $nowPage = $nowPage < 1 ? 1 : $nowPage;
     
     /**开始结束页数**/
     $FirstNum      = 1;
     $EndNum        = $PageNum;
     
     /**前一页，下一页**/
     $previousNum   = $nowPage - 1;
     $nextNum       = $nowPage + 1;
     
     $previousNum   = $previousNum < 1 ? 1 : $previousNum;
     $nextNum       = $nextNum > $PageNum ? $PageNum : $nextNum;
     
     $limitStart    = ($nowPage - 1) * $per_page;
     
     return Array(
                  'start'     => $limitStart,
                  'end'       =>$per_page * $nowPage,
                  'page' => Array(
                                  'totalnum'  => $totalNum,
                                  'totalpage' => $PageNum,
                                  'perpage'   => $per_page,
                                  'first'     => $FirstNum,
                                  'end'       => $EndNum,
                                  'previous'  => $previousNum,
                                  'nowp'      => $nowPage,
                                  'next'      => $nextNum
                                  )
            );
 }
 
 
/**
 * 分页辅助函数，风格类似baidu,google那种 以数字显示
 * 
 * @param Array page page 函数返回的结果
 * @param Int PageNum     显示的页数数量
 * @return Array 页数列表
 */
public static function pageNumStyle (Array $page, $PageNum = 15) {
    //存放结果
    $result = Array();
    $HalfPageNum = ceil ($PageNum / 2);
    //若总的页数小于 firstPageNum 则全部显示
    if ($page['totalpage'] <= $PageNum ) {
        for ($i = $page['first']; $i <= $page['end']; $i++)
            $result[] = $i;
    } else if ($page['end'] - $page['nowp'] < $HalfPageNum) {//最后的PageNum页 
            
            for ($i = $page['end'], $j = 0; $j < $PageNum; $j++, $i--)
                $result[] = $i;
            sort ($result);
    } else {

        for ($i = $page['nowp'] - 1, $j=1; $i > 0 && $j < $HalfPageNum; $i--) {
            $result[] = $i;
            $j++;
        }
        
        $HalfPageNum = $PageNum - $j;
        for ($i = $page['nowp'] + 1, $j=0; $i<= $page['end'] && $j < $HalfPageNum; $i++) {
            $result[] = $i;
            $j++;
        }
        
        $result[] = $page['nowp'];
        
        sort ($result);
    }
    
    return $result;
}


/**
 * 输出分页文本
 */
public static function pageString (array $page, $url='', $pageVar = 'page', $txt='个结果', $HalfPageNum = 15) {
    $pageNumStyle = self::pageNumStyle ($page, $HalfPageNum);
    if (strpos ($url, '?') === false) {
        $url .= '?';
    }else{
        $url .= '&';
    }
    $str ="共有{$page['totalnum']}{$txt}，共{$page['totalpage']}页<a href='{$url}{$pageVar}=1'>&nbsp;&nbsp;首页</a> <a href='{$url}{$pageVar}={$page['previous']}'>前一页</a>";
foreach ($pageNumStyle as $v){
    if ($page['nowp'] == $v)
        $str.="&nbsp;<font color='red'>{$v}</font>&nbsp;";
    else
        $str .="&nbsp;<a href='{$url}{$pageVar}={$v}'>{$v}</a>&nbsp;";
}

$str .="<a href='{$url}{$pageVar}={$page['next']}'>下一页</a> <a href='{$url}{$pageVar}={$page['totalpage']}'>末页</a>";

    return $str;
}


/**
 * createJpeg 根据提供的文本（四位），创建一个jpeg 图片
 * 示例：
 *  <?php
 *  @header("Expires: -1");
 *  @header("Cache-Control: no-store, private, post-check=0, pre-check=0, max-age=0");
 *  @header("Pragma: no-cache");
 *  header('Content-type: image/jpeg');
 *  $str = mt_rand (0,9) . mt_rand (0,9) . mt_rand (0,9) . mt_rand (0,9);
 *  $im  = createJpeg ($str);
 *  imageJpeg ($im);
 *  imagedestroy ($im);

 * @param String text 文本
 * @param String font 字体文件路径，若为空，则从配置文件里读取
 * @access public
 * @return imageresource
 * @author RD
 */
function createJpeg ($text, $font= '') {
    //定义的背景图片，使用base64_decode 解码
    $BACKGROUND[] = '/9j/4AAQSkZJRgABAQAAAQABAAD/2wBDAAsICAoIBwsKCQoNDAsNERwSEQ8PESIZGhQcKSQrKigkJyctMkA3LTA9MCcnOEw5PUNFSElIKzZPVU5GVEBHSEX/2wBDAQwNDREPESESEiFFLicuRUVFRUVFRUVFRUVFRUVFRUVFRUVFRUVFRUVFRUVFRUVFRUVFRUVFRUVFRUVFRUVFRUX/wAARCAAeAFADASIAAhEBAxEB/8QAHwAAAQUBAQEBAQEAAAAAAAAAAAECAwQFBgcICQoL/8QAtRAAAgEDAwIEAwUFBAQAAAF9AQIDAAQRBRIhMUEGE1FhByJxFDKBkaEII0KxwRVS0fAkM2JyggkKFhcYGRolJicoKSo0NTY3ODk6Q0RFRkdISUpTVFVWV1hZWmNkZWZnaGlqc3R1dnd4eXqDhIWGh4iJipKTlJWWl5iZmqKjpKWmp6ipqrKztLW2t7i5usLDxMXGx8jJytLT1NXW19jZ2uHi4+Tl5ufo6erx8vP09fb3+Pn6/8QAHwEAAwEBAQEBAQEBAQAAAAAAAAECAwQFBgcICQoL/8QAtREAAgECBAQDBAcFBAQAAQJ3AAECAxEEBSExBhJBUQdhcRMiMoEIFEKRobHBCSMzUvAVYnLRChYkNOEl8RcYGRomJygpKjU2Nzg5OkNERUZHSElKU1RVVldYWVpjZGVmZ2hpanN0dXZ3eHl6goOEhYaHiImKkpOUlZaXmJmaoqOkpaanqKmqsrO0tba3uLm6wsPExcbHyMnK0tPU1dbX2Nna4uPk5ebn6Onq8vP09fb3+Pn6/9oADAMBAAIRAxEAPwD1OjNJmjP514HMUL/Oim5ozRzAOzR/Okz+dJmjmAdRmm5pc8+9HMAufzopM0Zo5gGbufek3VH3x+dGeM9hWAyTeKXdz71FnHPej2/OgCTdRvqPPHsKMkDPc0AS7ufejcKi74/OjP5DtQBJuHel3fnUWT17mjvj86AP/9k=';
    $BACKGROUND[] = '/9j/4AAQSkZJRgABAQAAAQABAAD/2wBDAAsICAoIBwsKCQoNDAsNERwSEQ8PESIZGhQcKSQrKigkJyctMkA3LTA9MCcnOEw5PUNFSElIKzZPVU5GVEBHSEX/2wBDAQwNDREPESESEiFFLicuRUVFRUVFRUVFRUVFRUVFRUVFRUVFRUVFRUVFRUVFRUVFRUVFRUVFRUVFRUVFRUVFRUX/wAARCAAeAFADASIAAhEBAxEB/8QAHwAAAQUBAQEBAQEAAAAAAAAAAAECAwQFBgcICQoL/8QAtRAAAgEDAwIEAwUFBAQAAAF9AQIDAAQRBRIhMUEGE1FhByJxFDKBkaEII0KxwRVS0fAkM2JyggkKFhcYGRolJicoKSo0NTY3ODk6Q0RFRkdISUpTVFVWV1hZWmNkZWZnaGlqc3R1dnd4eXqDhIWGh4iJipKTlJWWl5iZmqKjpKWmp6ipqrKztLW2t7i5usLDxMXGx8jJytLT1NXW19jZ2uHi4+Tl5ufo6erx8vP09fb3+Pn6/8QAHwEAAwEBAQEBAQEBAQAAAAAAAAECAwQFBgcICQoL/8QAtREAAgECBAQDBAcFBAQAAQJ3AAECAxEEBSExBhJBUQdhcRMiMoEIFEKRobHBCSMzUvAVYnLRChYkNOEl8RcYGRomJygpKjU2Nzg5OkNERUZHSElKU1RVVldYWVpjZGVmZ2hpanN0dXZ3eHl6goOEhYaHiImKkpOUlZaXmJmaoqOkpaanqKmqsrO0tba3uLm6wsPExcbHyMnK0tPU1dbX2Nna4uPk5ebn6Onq8vP09fb3+Pn6/9oADAMBAAIRAxEAPwD1HPvx3NG78uwqLd+XYUbuff8AlQBLnn3/AJUZ7Z47mot3vx3NG78uwoAl3dM9Owozz7/yqLdz7/yo3e/Hc0AS59+O5o3fl2FRbvy7Cjd+f8qAJd3Pv/KjPvx3NRbvfjuaN35dhQBFu/Ojd7/U1FntRu49hQBJu/8ArCl3fnUWfzoz2oAk3e/1NG78uwqPPHsKM/nQBLu/P+VJu/8Armo89qTdx7CgCXd+XYUu786iz+dGe1AH/9k=';
    $BACKGROUND[] = '/9j/4AAQSkZJRgABAQAAAQABAAD/2wBDAAsICAoIBwsKCQoNDAsNERwSEQ8PESIZGhQcKSQrKigkJyctMkA3LTA9MCcnOEw5PUNFSElIKzZPVU5GVEBHSEX/2wBDAQwNDREPESESEiFFLicuRUVFRUVFRUVFRUVFRUVFRUVFRUVFRUVFRUVFRUVFRUVFRUVFRUVFRUVFRUVFRUVFRUX/wAARCAAeAFADASIAAhEBAxEB/8QAHwAAAQUBAQEBAQEAAAAAAAAAAAECAwQFBgcICQoL/8QAtRAAAgEDAwIEAwUFBAQAAAF9AQIDAAQRBRIhMUEGE1FhByJxFDKBkaEII0KxwRVS0fAkM2JyggkKFhcYGRolJicoKSo0NTY3ODk6Q0RFRkdISUpTVFVWV1hZWmNkZWZnaGlqc3R1dnd4eXqDhIWGh4iJipKTlJWWl5iZmqKjpKWmp6ipqrKztLW2t7i5usLDxMXGx8jJytLT1NXW19jZ2uHi4+Tl5ufo6erx8vP09fb3+Pn6/8QAHwEAAwEBAQEBAQEBAQAAAAAAAAECAwQFBgcICQoL/8QAtREAAgECBAQDBAcFBAQAAQJ3AAECAxEEBSExBhJBUQdhcRMiMoEIFEKRobHBCSMzUvAVYnLRChYkNOEl8RcYGRomJygpKjU2Nzg5OkNERUZHSElKU1RVVldYWVpjZGVmZ2hpanN0dXZ3eHl6goOEhYaHiImKkpOUlZaXmJmaoqOkpaanqKmqsrO0tba3uLm6wsPExcbHyMnK0tPU1dbX2Nna4uPk5ebn6Onq8vP09fb3+Pn6/9oADAMBAAIRAxEAPwD0Hf6HjuaN/c9Owqvv4yenYUu85/2v5VB4XtCfec/7X8qN/oeO5qvv7A8dzRv4yenYUB7Qsb+Mnp2FG85/2v5VBvOf9r+VJv7A8dzQHtCxv9Dx3NG/jJ6dhVffxk9Owpd5z/tfyoD2hPvOf9r+VG/0PHc1X39geO5o38ZPTsPWgPaEO8g/7f8AKk3DBAPHc1DnDFBxjO4+uKN2QTj5V4ApHFzk28YBPTsPWl3nP+3/ACqAuQAx+8entRnDFBxjO4+uKA5ybdwQDx3NG8YBI47D1qHdkE4+VeAKC5ADH7x6e1Aucn3nP+3/ACpN3BAPHc+tQ5wxQcYzuPrijdkE4+VeAKB85NvGAT07D1pd5z/t/wAqgLkAMfvHp7UZwxQcYzuPrigOc//Z';

    //字体文件路径
    if (empty ($font)){
        $config       = Zend_Registry::get ('config');
        $font         = APPLICATION_ROOT_PATH.'configs/'.$config['font_path_name'];
    }
    //背景图片
    $BACKGROUND = $BACKGROUND[array_rand ($BACKGROUND, 1)];
    //创建图片资源
    $im = imagecreatefromstring ( base64_decode ($BACKGROUND));
    //图片宽带和长度
    $tx = imagesx ($im);
    $ty = imagesy ($im);
    $ss = $tx / 4;
    // 颜色
    $black = imagecolorallocate($im, 0, 0, 0);
    $red   = imagecolorallocate($im, 255, 0, 0);
    $blue  = imagecolorallocate($im, 0, 0, 255);
    $yellow= imagecolorallocate($im, 153, 0, 255);
    $green = imagecolorallocate($im, 0, 255, 0);

    //显示字符
    //array imagettftext ( resource $image , float $size , float $angle , int $x , int $y , int $color , string $fontfile , string $text )
    $color = array_rand (array ('black'=>$black, 'red'=>$red, 'blue'=>$blue, 'yellow'=>$yellow));
    imagettftext ( $im , mt_rand (12, 18) , mt_rand (-15, 30) , 5       , mt_rand (19, $ty - 3) , $$color , $font , $text{0});

    $color = array_rand (array ('black'=>$black, 'red'=>$red, 'blue'=>$blue, 'yellow'=>$yellow));
    imagettftext ( $im , mt_rand (12, 18) , mt_rand (-15, 30) , 5+$ss   , mt_rand (19, $ty - 3) , $$color , $font , $text{1});

    $color = array_rand (array ('black'=>$black, 'red'=>$red, 'blue'=>$blue, 'yellow'=>$yellow));
    imagettftext ( $im , mt_rand (12, 18) , mt_rand (-15, 30) , 5+2*$ss , mt_rand (19, $ty - 3) , $$color , $font , $text{2});

    $color = array_rand (array ('black'=>$black, 'red'=>$red, 'blue'=>$blue, 'yellow'=>$yellow));
    imagettftext ( $im , mt_rand (12, 18) , mt_rand (-15, 30) , 5+3*$ss , mt_rand (19, $ty - 3) , $$color , $font , $text{3});
    
    //画线干扰
    $color = array_rand (array ('black'=>$black, 'red'=>$red, 'blue'=>$blue, 'yellow'=>$yellow));
    imageline ( $im , mt_rand (0, $tx) , $ty/8 , $tx , $ty , $$color );
    
    $color = array_rand (array ('black'=>$black, 'red'=>$red, 'blue'=>$blue, 'yellow'=>$yellow));
    imageline ( $im , 0 , $ty , mt_rand(0,$tx) , $ty / 8 , $$color );

    //画点
    for ($x = 0; $x < $tx; ) {
    for ($y = 0; $y < $ty; ) {
         $color = array_rand (array ('black'=>$black, 'red'=>$red, 'blue'=>$blue, 'yellow'=>$yellow));
         imagesetpixel ( $im , $x , $y , $$color );
        $y += mt_rand (1, 10);
    }
    $x += mt_rand (5,15);
    }
    return $im;
}


/**
 * get_ip 获得用户的IP,未知ip返回0.0.0.0
 * 
 * @access public
 * @param $islong 是否返回浮点型数字
 *
 * @return string
 * @author RD
 */
public static function getip( $islong = false)
{
    if(getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), '0.0.0.0')) 
        $onlineip = getenv('HTTP_CLIENT_IP');
    elseif(getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), '0.0.0.0'))
        $onlineip = getenv('HTTP_X_FORWARDED_FOR');
    elseif(getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), '0.0.0.0'))
        $onlineip = getenv('REMOTE_ADDR');
    elseif(isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], '0.0.0.0'))
        $onlineip = $_SERVER['REMOTE_ADDR'];

    preg_match("/[\d\.]{7,15}/", $onlineip, $onlineipmatches);
    $onlineip = !empty($onlineipmatches[0]) ? $onlineipmatches[0] : '0.0.0.0';
    
    if ($islong) $onlineip = sprintf ('%u', ip2long ($onlineip));

    return $onlineip;
}

/**
 * get_ip 获得用户的IP,未知ip返回0.0.0.0
 * 
 * @access public
 * @param $islong 是否返回浮点型数字
 *
 * @return string
 * @author RD
 */
public static function getregip( $islong = false)
{
    $headers = array();
    foreach ($_SERVER as $key => $value) {
    if ('HTTP_' == substr($key, 0, 5)) {
        $headers[str_replace('_', '-', substr($key, 5))] = $value;
        }
    }
    
    return $headers['X-CLIENT-ADDRESS'];
}
/**
 * 截取字符串长度
 * @param String string 要截取的字符
 * @param Int length 要控制的长度
 * @param String dot 截取后跟的字符
 * @param String charset 该字符串的编码
 * @return String strcut 截取后字符串
 * @author ydl
 */
public static function cutstr($string, $length, $dot = '...', $charset) {

	if(strlen($string) <= $length) {
		return $string;
	}

	$string = str_replace(array('&amp;', '&quot;', '&lt;', '&gt;'), array('&', '"', '<', '>'), $string);

	$strcut = '';
	if(strtolower($charset) == 'utf-8') {

		$n = $tn = $noc = 0;
		while($n < strlen($string)) {

			$t = ord($string[$n]);
			if($t == 9 || $t == 10 || (32 <= $t && $t <= 126)) {
				$tn = 1; $n++; $noc++;
			} elseif(194 <= $t && $t <= 223) {
				$tn = 2; $n += 2; $noc += 2;
			} elseif(224 <= $t && $t <= 239) {
				$tn = 3; $n += 3; $noc += 2;
			} elseif(240 <= $t && $t <= 247) {
				$tn = 4; $n += 4; $noc += 2;
			} elseif(248 <= $t && $t <= 251) {
				$tn = 5; $n += 5; $noc += 2;
			} elseif($t == 252 || $t == 253) {
				$tn = 6; $n += 6; $noc += 2;
			} else {
				$n++;
			}

			if($noc >= $length) {
				break;
			}

		}
		if($noc > $length) {
			$n -= $tn;
		}

		$strcut = substr($string, 0, $n);

	} else {
		for($i = 0; $i < $length; $i++) {
			$strcut .= ord($string[$i]) > 127 ? $string[$i].$string[++$i] : $string[$i];
		}
	}

	$strcut = str_replace(array('&', '"', '<', '>'), array('&amp;', '&quot;', '&lt;', '&gt;'), $strcut);

	return $strcut.$dot;
}

 /**
 * 邮件发送
 *
 * @param string $mail_content 邮件内容
 * @param string $mail_title 邮件标题
 * @param string $charset 邮件字符编码
 * @param string $mail_to 收件人email
 * @param string $mail_to_nick 收件人昵称
 * @return boole 邮件是否发送成功
 */
 public function sentmail ($mail_content = '' , $mail_title = '', $mail_to = '', $mail_to_nick = '', $charset = 'gbk')
{
	$error = '';
	require_once 'Zend/Mail.php';
	require_once 'Zend/Mail/Transport/Smtp.php';
	try{
		if($mail_content == '' || $mail_to == '' || $mail_to_nick == '' || $mail_title == '') {
			throw new Exception ('发送邮件需要的信息不完整!');
		}
		$mail = new Zend_Mail($charset);
		$config = array(
			'auth'=>'login',
			'username'=>"binbinact",
			'password'=>"g_binbinact1",
			'ssl'=>"ssl"
		);
		$transport = new Zend_Mail_Transport_Smtp('smtp.gmail.com',$config);
		$mail->setDefaultTransport($transport);
		$mail->setBodyHtml($mail_content);
		$mail->setFrom('binbinact@gmail.com', 'binbinact');
		$mail->addTo($mail_to, $mail_to_nick);
		$mail->setSubject("=?{$charset}?B?".base64_encode($mail_title)."?=");
		$mail->send();
	} catch (Exception $e) { $error = $e->getMessage(); }
	    
	if($error != '') {
		return false;//邮件发送失败,请重试!
	} else {
		return true;//发送成功
	}
}

/**
 * 格式化金币输出
 *
 * @param int $score
 * @return int
 */
public static function format_score ($score)
{
	if(!is_numeric($score)){
		return $score;}else{
        $patten = '/(?<=\d)(?=(?:\d\d\d\d)+(?!\d))/';    
return preg_replace($patten,',',$score);        
        }

}

public static function _Post($url,$data){
    $headers = array('qiutao:leilei');
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $return = curl_exec($ch);
    // 检查是否有错误发生
    if (curl_errno($ch)) {
        echo 'Curl error: ' . curl_error($ch);
        die;
    }
    curl_close($ch);
    return $return;
}


public static function send_post($url, $post_data) {
  

  $postdata = http_build_query($post_data);

  $options = array(

    'http' => array(

      'method' => 'POST',

      'header' => 'Content-type:application/x-www-form-urlencoded',

      'content' => $postdata,

      'timeout' => 15 * 60 // 超时时间（单位:s）

    )

  );

  $context = stream_context_create($options);

  $result = file_get_contents($url, false, $context);

  

  return $result;

}

/**
 * curl访问url
 *
 * @param str $url_str
 * @return int
 */
public function call_remote_by_curl($url_str)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url_str ); 
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $remote_result = curl_exec($ch);

    if (curl_errno($ch)) {
        $remote_result = 0;
    }
    curl_close($ch);

    return $remote_result;
}
}
