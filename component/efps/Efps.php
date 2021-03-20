<?php
namespace app\component\efps;


use yii\base\Component;

class Efps extends Component{

    public $on_dev = true;
    public $charset = "UTF-8";      //编码格式
    public $signType = "RSA2";      //签名方式

    public $main_config = [
        /**
         * 测试环境接口路径
         */
        "gateway_dev" => "https://test-efps.epaylinks.cn",

        /**
         * 生产环境接口路径
         */
        "gateway" => "https://efps.epaylinks.cn",

        "rsaPrivateKeyFilePath" => "", //私钥文件路径
        "publicKeyFilePath"     => "", //易票联公钥
        "sign_no"               => "", //证书序列号
        "password"              => "", //证书密码
        "acq_sp_id"             => ""  //平台商户号
    ];

    public $notify_url;
    public $return_url;

    private $apiName;

    /**
     * 收单且开户
     * @param $params
     * @throws \Exception
     */
    private function acceptAndOpenCheck($params){
        if($params['acceptOrder'] == 1 && $params['openAccount'] == 1){
            //个体、企业必须设置营业执照照片
            //http 开头时，当 URL 处理，其他当编码的图片内容处理
            if(in_array($params['merchantType'], [1, 2]) && empty($params['businessLicensePhoto'])){
                throw new \Exception("营业执照照片[businessLicensePhoto]未设置");
            }
            //必须设置经营地址
            if(empty($params['businessAddress'])){
                throw new \Exception("经营地址[businessAddress]未设置");
            }
            //必须设置省编码
            if(empty($params['province'])){
                throw new \Exception("省编码[province]未设置");
            }
            //必须设置市编码
            if(empty($params['city'])){
                throw new \Exception("市编码[city]未设置");
            }
            //必须设置MCC码
            if(empty($params['mcc'])){
                throw new \Exception("MCC码[mcc]未设置");
            }
            //个体或企业
            if(in_array($params['merchantType'], [1, 2])){
                if(empty($params['storeHeadPhoto'])){
                    throw new \Exception("门店门头照[storeHeadPhoto]未设置");
                }
                if(empty($params['storeHallPhoto'])){
                    throw new \Exception("门店内景照[storeHallPhoto]未设置");
                }
            }
            //证件正面照
            if(!isset($params['lawyerCertPhotoFront']) || empty($params['lawyerCertPhotoFront'])){
                throw new \Exception("证件正面照[lawyerCertPhotoFront]未设置");
            }
            //证件背面照
            if(!isset($params['lawyerCertPhotoBack']) || empty($params['lawyerCertPhotoBack'])){
                throw new \Exception("证件背面照[lawyerCertPhotoBack]未设置");
            }
            //邮箱地址
            if(!isset($params['email']) || empty($params['email'])){
                throw new \Exception("邮箱地址[email]未设置");
            }
            //类型为企业
            if($params['merchantType'] == 2){
                //账户名
                if(!isset($params['licenceAccount']) || empty($params['licenceAccount'])){
                    throw new \Exception("账户名[licenceAccount]未设置");
                }
                //账号
                if(!isset($params['licenceAccountNo']) || empty($params['licenceAccountNo'])){
                    throw new \Exception("账号[licenceAccountNo]未设置");
                }
                //开户银行
                if(!isset($params['licenceOpenBank']) || empty($params['licenceOpenBank'])){
                    throw new \Exception("开户银行[licenceOpenBank]未设置");
                }
                //开户支行
                if(!isset($params['licenceOpenSubBank']) || empty($params['licenceOpenSubBank'])){
                    throw new \Exception("开户支行[licenceOpenSubBank]未设置");
                }
                //证明文件（照片）
                if(!isset($params['openingLicenseAccountPhoto']) || empty($params['openingLicenseAccountPhoto'])){
                    throw new \Exception("证明文件（照片）[openingLicenseAccountPhoto]未设置");
                }
            }
        }
    }

    /**
     * 收单或开户
     * @param $params
     * @throws \Exception
     */
    private function acceptOrOpenCheck($params){
        if(($params['acceptOrder'] == 1 || $params['openAccount'] == 1)){
            //商户类型为个体或企业
            if(in_array($params['merchantType'], [1, 2])){
                //必须设置营业执照号
                //长度：13—18
                if(empty($params['businessLicenseCode'])){
                    throw new \Exception("营业执照号[businessLicenseCode]未设置");
                }
                //必须设置商户经营名称
                //与营业执照上登记注册的名称一致
                if(empty($params['businessLicenseName'])){
                    throw new \Exception("商户经营名称[businessLicenseName]未设置");
                }
                //必须设置营业执照有效期（截止）
                //格式：yyyymmdd，无限期填写“长期”。
                if(empty($params['businessLicenseTo'])){
                    throw new \Exception("营业执照有效期[businessLicenseTo]未设置");
                }
                //必须设置营业执照类型
                //1：已3证合一；0：未3证合一
                if(!isset($params['isCc'])){
                    throw new \Exception("营业执照类型[isCc]未设置");
                }
                //必须设置法人姓名
                if(empty($params['lawyerName'])){
                    throw new \Exception("法人姓名[lawyerName]未设置");
                }
                //必须设置经营范围
                //按营业执照内容填写
                if(empty($params['businessScope'])){
                    throw new \Exception("经营范围[businessScope]未设置");
                }
                //必须设置注册地址
                if(empty($params['registerAddress'])){
                    throw new \Exception("注册地址[registerAddress]未设置");
                }

            }
        }
    }

    /**
     * 三证未合一
     * @param $params
     * @throws \Exception
     */
    private function noIsCcCheck($params){
        if(in_array($params['merchantType'], [1, 2]) && isset($params['isCc']) && $params['isCc'] == 0){
            if(empty($params['organizationCode'])){
                throw new \Exception("组织机构代码[organizationCode]未设置");
            }
            //组织机构代码照片
            //http开头时，当URL处理，其他当编码的图片内容处理
            if(empty($params['organizationCodePhoto'])){
                throw new \Exception("组织机构代码照片[organizationCodePhoto]未设置");
            }
            if(empty($params['organizationCodeFrom'])){
                throw new \Exception("组织机构代码有效期（起始）[organizationCodeFrom]未设置");
            }
            if(empty($params['organizationCodeTo'])){
                throw new \Exception("组织机构代码有效期（截止）[organizationCodeTo]未设置");
            }
        }
    }

    /**
     * 结算账号信息
     * @param $params
     * @throws \Exception
     */
    private function settleCheck($params){
        if($params['openAccount'] == 1 || in_array($params['merchantType'], [1, 2])){
            //结算账户类型
            if(!isset($params['settleAccountType']) || empty($params['settleAccountType'])){
                throw new \Exception("结算账户类型[settleAccountType]未设置");
            }
            //结算账户号
            if(!isset($params['settleAccountNo']) || empty($params['settleAccountNo'])){
                throw new \Exception("结算账户号[settleAccountNo]未设置");
            }
            //结算账户名
            if(!isset($params['settleAccount']) || empty($params['settleAccount'])){
                throw new \Exception("结算账户名[settleAccount]未设置");
            }
            //开户银行
            if(!isset($params['openBank']) || empty($params['openBank'])){
                throw new \Exception("开户银行[openBank]未设置");
            }
        }
        if($params['openAccount'] == 1){
            //提现方式
            if(!isset($params['settleTarget']) || empty($params['settleTarget'])){
                throw new \Exception("提现方式[settleTarget]未设置");
            }
        }
        if(isset($params['settleAccountType']) && in_array($params['settleAccountType'], [3, 4])){
            //结算账户附件
            if(!isset($params['settleAttachment']) || empty($params['settleAttachment'])){
                throw new \Exception("提现方式[settleAttachment]未设置");
            }
        }
        if(isset($params['settleAccountType']) && $params['settleAccountType'] == 1){
            //开户支行
            if(!isset($params['openSubBank']) || empty($params['openSubBank'])){
                throw new \Exception("开户支行[openSubBank]未设置");
            }
            //开户行联行号
            if(!isset($params['openBankCode']) || empty($params['openBankCode'])){
                throw new \Exception("开户行联行号[openBankCode]未设置");
            }
        }
    }

    /**
     * 参数检查
     * @param $params
     * @throws \Exception
     */
    private function paramCheck($params){

        if(empty($params['acqSpId'])){
            throw new \Exception("平台商户编号[acqSpId]不能为空");
        }

        if(empty($params['merchantName'])){
            throw new \Exception("商户名称[merchantName]不能为空");
        }

        $params['acceptOrder'] = isset($params['acceptOrder']) ? $params['acceptOrder'] : 0;
        $params['openAccount'] = isset($params['openAccount']) ? $params['openAccount'] : 0;


        //商户类型：1个体工商户 2企业 3个人(小微)
        if(empty($params['merchantType']) || !in_array($params['merchantType'], [1,2,3])){
            throw new \Exception("商户类型[merchantType]未设置");
        }

        $this->acceptAndOpenCheck($params);
        $this->acceptOrOpenCheck($params);

        //收单必须设置商户简称
        //最长 20 个汉字
        if($params['acceptOrder'] == 1 && empty($params['shortName'])){
            throw new \Exception("商户简称[shortName]未设置");
        }

        //三证未合一条件检查
        $this->noIsCcCheck($params);


        //证件类型
        if(!isset($params['lawyerCertType'])){
            throw new \Exception("证件类型[lawyerCertType]未设置");
        }

        //证件号码
        if(!isset($params['lawyerCertNo']) || empty($params['lawyerCertNo'])){
            throw new \Exception("证件号码[lawyerCertNo]未设置");
        }

        //证件人姓名
        if(!isset($params['certificateName']) || empty($params['certificateName'])){
            throw new \Exception("证件人姓名[certificateName]未设置");
        }

        //证件有效期(截止)
        if($params['acceptOrder'] == 1){
            if($params['openAccount'] == 1 || in_array($params['merchantType'], [1,2])){
                if(!isset($params['certificateTo']) || empty($params['certificateTo'])){
                    throw new \Exception("证件有效期(截止)[certificateTo]未设置");
                }
            }
        }

        if($params['acceptOrder'] == 1){
            if(!isset($params['contactPerson']) || empty($params['contactPerson'])){
                throw new \Exception("联系人姓名[contactPerson]未设置");
            }
            if(!isset($params['serviceTel']) || empty($params['serviceTel'])){
                throw new \Exception("客服电话[serviceTel]未设置");
            }
        }

        //联系人手机号码
        if($params['openAccount'] == 1){
            if(!isset($params['contactPhone']) || empty($params['contactPhone'])){
                throw new \Exception("联系人手机号码[contactPhone]未设置");
            }
        }

        $this->settleCheck($params);

        //业务代码
        if(!isset($params['businessCode']) || empty($params['businessCode'])){
            throw new \Exception("业务代码[businessCode]未设置");
        }
    }

    public function apiName($apiName){
        $this->apiName = $apiName;
        return $this;

    }

    /**
     * 商户信息进件
     */
    public function merchantApply($params){
        try {

            $this->paramCheck($params);

            $stageParams = [];

            $paperParams = [
                'merchantType'               => $params['merchantType'],
                'businessLicenseCode'        => isset($params['businessLicenseCode']) ? $params['businessLicenseCode'] : "",
                'businessLicenseName'        => isset($params['businessLicenseName']) ? $params['businessLicenseName'] : "",
                'businessLicensePhoto'       => isset($params['businessLicensePhoto']) ? $params['businessLicensePhoto'] : "",
                'businessLicenseFrom'        => isset($params['businessLicenseFrom']) ? $params['businessLicenseFrom'] : "",
                'businessLicenseTo'          => isset($params['businessLicenseTo']) ? $params['businessLicenseTo'] : "",
                'shortName'                  => isset($params['shortName']) ? $params['shortName'] : "",
                'isCc'                       => isset($params['isCc']) ? $params['isCc'] : "",
                'lawyerName'                 => isset($params['lawyerName']) ? $params['lawyerName'] : "",
                'businessScope'              => isset($params['businessScope']) ? $params['businessScope'] : "",
                'registerAddress'            => isset($params['registerAddress']) ? $params['registerAddress'] : "",
                'organizationCode'           => isset($params['organizationCode']) ? $params['organizationCode'] : "",
                'organizationCodePhoto'      => isset($params['organizationCodePhoto']) ? $params['organizationCodePhoto'] : "",
                'organizationCodeFrom'       => isset($params['organizationCodeFrom']) ? $params['organizationCodeFrom'] : "",
                'organizationCodeTo'         => isset($params['organizationCodeTo']) ? $params['organizationCodeTo'] : "",
                'businessAddress'            => isset($params['businessAddress']) ? $params['businessAddress'] : "",
                'province'                   => isset($params['province']) ? $params['province'] : "",
                'city'                       => isset($params['city']) ? $params['city'] : "",
                'mcc'                        => isset($params['mcc']) ? $params['mcc'] : "",
                'unionShortName'             => isset($params['unionShortName']) ? $params['unionShortName'] : "",
                'storeHeadPhoto'             => isset($params['storeHeadPhoto']) ? $params['storeHeadPhoto'] : "",
                'storeHallPhoto'             => isset($params['storeHallPhoto']) ? $params['storeHallPhoto'] : "",
                'lawyerCertType'             => isset($params['lawyerCertType']) ? $params['lawyerCertType'] : "",
                'lawyerCertNo'               => isset($params['lawyerCertNo']) ? $params['lawyerCertNo'] : "",
                'lawyerCertPhotoFront'       => isset($params['lawyerCertPhotoFront']) ? $params['lawyerCertPhotoFront'] : "",
                'lawyerCertPhotoBack'        => isset($params['lawyerCertPhotoBack']) ? $params['lawyerCertPhotoBack'] : "",
                'certificateName'            => isset($params['certificateName']) ? $params['certificateName'] : "",
                'certificateTo'              => isset($params['certificateTo']) ? $params['certificateTo'] : "",
                'contactPerson'              => isset($params['contactPerson']) ? $params['contactPerson'] : "",
                'contactPhone'               => isset($params['contactPhone']) ? $params['contactPhone'] : "",
                'serviceTel'                 => isset($params['serviceTel']) ? $params['serviceTel'] : "",
                'email'                      => isset($params['email']) ? $params['email'] : "",
                'licenceAccount'             => isset($params['licenceAccount']) ? $params['licenceAccount'] : "",
                'licenceAccountNo'           => isset($params['licenceAccountNo']) ? $params['licenceAccountNo'] : "",
                'licenceOpenBank'            => isset($params['licenceOpenBank']) ? $params['licenceOpenBank'] : "",
                'licenceOpenSubBank'         => isset($params['licenceOpenSubBank']) ? $params['licenceOpenSubBank'] : "",
                'openingLicenseAccountPhoto' => isset($params['openingLicenseAccountPhoto']) ? $params['openingLicenseAccountPhoto'] : "",
                'settleAccountType'          => isset($params['settleAccountType']) ? $params['settleAccountType'] : "",
                'settleAccountNo'            => isset($params['settleAccountNo']) ? $params['settleAccountNo'] : "",
                'settleAccount'              => isset($params['settleAccount']) ? $params['settleAccount'] : "",
                'settleTarget'               => isset($params['settleTarget']) ? $params['settleTarget'] : "",
                'settleAttachment'           => isset($params['settleAttachment']) ? $params['settleAttachment'] : "",
                'openBank'                   => isset($params['openBank']) ? $params['openBank'] : "",
                'openSubBank'                => isset($params['openSubBank']) ? $params['openSubBank'] : "",
                'openBankCode'               => isset($params['openBankCode']) ? $params['openBankCode'] : "",
                'businessCode'               => isset($params['businessCode']) ? $params['businessCode'] : "",
                'settleCycle'                => isset($params['settleCycle']) ? $params['settleCycle'] : "",
                'stage'                      => json_encode($stageParams)
            ];

            //主要参数
            $needParams = [
                'acqSpId'      => $this->main_config['acq_sp_id'],
                'merchantName' => $params['merchantName'],
                'acceptOrder'  => (int)$params['acceptOrder'],
                'openAccount'  => (int)$params['openAccount'],
                'paper'        => json_encode($paperParams)
            ];

            $this->apiName("/api/cust/SP/Merchant/apply")
                 ->request($needParams);
        }catch (\Exception $e){
            echo $e->getMessage();
            exit;
        }

    }


    protected function sign($data) {
        $certs = [];
        openssl_pkcs12_read(file_get_contents($this->main_config['rsaPrivateKeyFilePath']), $certs, $this->main_config['password']); //其中password为你的证书密码

        if(empty($certs)){
            throw new \Exception("您使用的私钥格式错误，请检查RSA私钥配置");
        }

        openssl_sign($data, $sign, $certs['pkey'],OPENSSL_ALGO_SHA256);

        $sign = base64_encode($sign);
        return $sign;
    }

    protected function request($params){

        $jsonStr = json_encode($params);
        $sign = $this->sign($jsonStr);

        $headers = array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($jsonStr),
            'x-efps-sign-no:' . $this->main_config['sign_no'],
            'x-efps-sign-type:SHA256withRSA',
            'x-efps-sign:'.$sign,
            'x-efps-timestamp:'.date('YmdHis'),
        );
        $url = ($this->on_dev ? $this->main_config['gateway_dev'] : $this->main_config['gateway']) . $this->apiName;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonStr);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);  // 跳过检查
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);  // 跳过检查
        //curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_VERBOSE, true);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        $errno = curl_errno($ch);
        curl_close($ch);

        print_r($response);
        exit;

        return [$httpCode, $response];
    }
}