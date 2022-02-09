<?php
/**
 *   加密工具类
 *
 * User: jiehua
 * Date: 16/3/30
 * Time: 下午3:25
 */


/**
 * 加密方法
 * @param string $str
 * @return string
 */
function encrypt($str,$screct_key){
    //AES, 128 模式加密数据 CBC
    $screct_key = base64_decode($screct_key);
    $str = trim($str);

    // 这里获取对应加密方式的iv长度
    $iv_size = openssl_cipher_iv_length('AES-128-CBC');
    $iv = str_repeat("\0", $iv_size);
    // 加密
    $encrypt_str = openssl_encrypt($str,'AES-128-CBC',$screct_key,OPENSSL_RAW_DATA,$iv);
    return base64_encode($encrypt_str);
}

/**
 * 解密方法
 * @param string $str
 * @return string
 */
function decrypt($str,$screct_key){
    //AES, 128 模式加密数据 CBC
    $str = base64_decode($str);
    $screct_key = base64_decode($screct_key);

    $iv_size = openssl_cipher_iv_length('AES-128-CBC');
    $iv = str_repeat("\0", $iv_size);
    $decrypt_str = openssl_decrypt ($str, 'AES-128-CBC', $screct_key, OPENSSL_RAW_DATA,$iv);


    return $decrypt_str;
}

/**
 * 填充算法
 * @param string $source
 * @return string
 */
function addPKCS7Padding($source)
{
    $source = trim($source);
    $block = mcrypt_get_block_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);

    $pad = $block - (strlen($source) % $block);
    if ($pad <= $block) {
        $char = chr($pad);
        $source .= str_repeat($char, $pad);
    }
    return $source;
}

/**
 * 移去填充算法
 * @param string $source
 * @return string
 */
function stripPKSC7Padding($source)
{
    $char = substr($source, -1);
    $num = ord($char);
    if ($num == 62) return $source;
    $source = substr($source, 0, -$num);
    return $source;
}