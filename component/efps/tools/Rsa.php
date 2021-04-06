<?php
namespace app\component\efps\tools;

class Rsa{

    private $publicKey; //公钥资源
    private $privateKey; //私钥资源

    /**
     * 构造函数
     * @param [string] $public_key  [公钥数据字符串]
     * @param [string] $private_key [私钥数据字符串]
     */
    public function __construct($public_key, $private_key) {
        $this->publicKey = !empty($public_key) ? openssl_pkey_get_public($this->getPublicKey($public_key)) : false;
        $this->privateKey = !empty($private_key) ? openssl_pkey_get_private($this->getPrivateKey($private_key)) : false;
    }

    /**
     * 获取私有key字符串 重新格式化  为保证任何key都可以识别
     * @param $private_key
     * @return string
     */
    public function getPrivateKey($private_key){
        $search = [
            "-----BEGIN RSA PRIVATE KEY-----",
            "-----END RSA PRIVATE KEY-----",
            "\n",
            "\r",
            "\r\n"
        ];
        $private_key = str_replace($search,"",$private_key);
        return $search[0] . PHP_EOL . wordwrap($private_key, 64, "\n", true) . PHP_EOL . $search[1];
    }


    /**
     * 获取公共key字符串  重新格式化 为保证任何key都可以识别
     * @param $public_key
     * @return string
     */
    public function getPublicKey($public_key){
        $search = [
            "-----BEGIN CERTIFICATE-----",
            "-----END CERTIFICATE-----",
            "-----BEGIN PUBLIC KEY-----",
            "-----END PUBLIC KEY-----",
            "\n",
            "\r",
            "\r\n"
        ];
        $public_key = str_replace($search,"", $public_key);
        return $search[0] . PHP_EOL . wordwrap($public_key, 64, "\n", true) . PHP_EOL . $search[1];
    }

    /**
     * 生成一对公私钥 成功返回 公私钥数组 失败 返回 false
     * @return array|false
     */
    public function createPairs() {
        $res = openssl_pkey_new();
        if($res == false) return false;
        openssl_pkey_export($res, $private_key);
        $public_key = openssl_pkey_get_details($res);
        return [
            'public_key'  => $public_key["key"],
            'private_key' => $private_key
        ];
    }

    /**
     * 用私钥加密
     * @param $input
     * @return string
     */
    public function encryptByPrivate($input) {
        openssl_private_encrypt($input,$output, $this->privateKey);
        return base64_encode($output);
    }

    /**
     * 解密 私钥加密后的密文
     * @param $input
     * @return mixed
     */
    public function decryptByPublic($input) {
        openssl_public_decrypt(base64_decode($input),$output, $this->publicKey);
        return $output;
    }

    /**
     * 用公钥加密
     * @param $input
     * @return string
     */
    public function encryptByPublic($input) {
        openssl_public_encrypt($input,$output, $this->publicKey,OPENSSL_PKCS1_OAEP_PADDING);
        return base64_encode($output);
    }

    /**
     * 解密 公钥加密后的密文
     * @param $input
     * @return mixed
     */
    public function decryptByPrivate($input) {
        openssl_private_decrypt(base64_decode($input),$output, $this->privateKey,OPENSSL_PKCS1_OAEP_PADDING);
        return $output;
    }

}