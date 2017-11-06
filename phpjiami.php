<?php
    include 'File.class.php';

    //By Wfox
    //用法：将需要解密的文件拷进encode目录，执行本文件，解密结果在decode目录

    //内置解密字符串函数
    function de($var1, $var2 = '')
    {
        global $hash, $rand;
        $md5 = md5(pack("H*", $hash));//随机字符串1
        $var2 = !$var2?ord(pack("H*", $rand)):$var2;//随机字符串2

        $str = '';
        for($i=0; $i<strlen($var1); $i++)
        {
            $str .= ord($var1{$i}) < ord(pack("H*",'F5')) ? ((ord($var1{$i}) > $var2 && ord($var1{$i}) < ord(pack("H*",'F5'))) ? chr(ord($var1{$i}) / 2) : $var1{$i}) : '';
        }
        $de1 =  base64_decode($str);
        $len = $len2 = strlen($md5);
        $str2 = '';
        for($i=0; $i<strlen($de1); $i++)
        {
            $len = $len ? $len : $len2;
            $len--;
            $str2 .= $de1[$i] ^ $md5[$len];
        }
        return $str2;
    }
    function decode($filename){
        global $hash, $rand;
        $code = file_get_contents($filename);

        $bin = bin2hex($code);//将源码转成16进制再进行匹配
        preg_match('/6257513127293b24[a-z0-9]{2,30}3d24[a-z0-9]{2,30}2827([a-z0-9]{2,30})27293b/', $bin, $hash);//匹配随机字符串1
        preg_match('/2827([a-z0-9]{2})27293a24/', $bin, $rand);//匹配随机字符串1
        
        if(!isset($hash[1]) && !isset($rand[1]))
        {
            echo "can't match\r\n";
        }
        $hash = $hash[1];
        $rand = $rand[1];
        
        $a = explode('?>', $code);
        $decode = str_rot13(@gzuncompress(de($a[1])) ? @gzuncompress(de($a[1])) : de($a[1]));//核心解密
        $decode = substr($decode, 2);
        
        $compress = false;
        //phpjiami加密默认压缩代码，解密不会多出乱码。如果代码底部出现多余乱码请设置$compress = true;
         if($compress)
        {
            $decode = substr($decode, 0, strripos($decode, '<?php'));
        }
        if(stripos($decode, 'Encode by  phpjiami.com') !== false)
        {
            $decode = substr($decode, strpos($decode, '?>')+2);
        }
            
        return $decode;
    }

    //批量解密，将需要解密的文件放进encode文件夹
    $op=new fileDirUtil();
	$fileArr = array();
	foreach($op->dirList('./encode') as $f)
	{
		$info = pathinfo($f);
		$dirName = str_replace('encode','decode',$info['dirname']);
		if(!is_dir($dirName))
		{
			mkdir($dirName);
		}
		if(@$info['extension']=='php')
		{
			if(stripos(file_get_contents($f),"PHPJiaMi") !== false)
			{
				$content = decode($f);
				$fileName = str_replace('encode','decode',$f);
				file_put_contents($fileName,$content);
				echo $f.'<br>';
			}
		}
	}
    

?>