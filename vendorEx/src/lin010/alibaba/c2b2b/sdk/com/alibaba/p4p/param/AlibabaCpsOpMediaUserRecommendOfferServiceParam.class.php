<?php

include_once ('com/alibaba/openapi/client/entity/SDKDomain.class.php');
include_once ('com/alibaba/openapi/client/entity/ByteArray.class.php');

class AlibabaCpsOpMediaUserRecommendOfferServiceParam {

        
        /**
    * @return 设备id求md5(32位小写)(手机号与设备号至少一个)
    */
        public function getDeviceIdMd5() {
        $tempResult = $this->sdkStdResult["deviceIdMd5"];
        return $tempResult;
    }
    
    /**
     * 设置设备id求md5(32位小写)(手机号与设备号至少一个)     
     * @param String $deviceIdMd5     
     * 参数示例：<pre>xxxxxxx</pre>     
     * 此参数必填     */
    public function setDeviceIdMd5( $deviceIdMd5) {
        $this->sdkStdResult["deviceIdMd5"] = $deviceIdMd5;
    }
    
        
        /**
    * @return 手机号求md5(32位小写)(手机号与设备号至少一个)
    */
        public function getPhoneMd5() {
        $tempResult = $this->sdkStdResult["phoneMd5"];
        return $tempResult;
    }
    
    /**
     * 设置手机号求md5(32位小写)(手机号与设备号至少一个)     
     * @param String $phoneMd5     
     * 参数示例：<pre>xxxxxxx</pre>     
     * 此参数必填     */
    public function setPhoneMd5( $phoneMd5) {
        $this->sdkStdResult["phoneMd5"] = $phoneMd5;
    }
    
        
        /**
    * @return 页码
    */
        public function getPageNo() {
        $tempResult = $this->sdkStdResult["pageNo"];
        return $tempResult;
    }
    
    /**
     * 设置页码     
     * @param Integer $pageNo     
     * 参数示例：<pre>1</pre>     
     * 此参数必填     */
    public function setPageNo( $pageNo) {
        $this->sdkStdResult["pageNo"] = $pageNo;
    }
    
        
        /**
    * @return 每页数量
    */
        public function getPageSize() {
        $tempResult = $this->sdkStdResult["pageSize"];
        return $tempResult;
    }
    
    /**
     * 设置每页数量     
     * @param Integer $pageSize     
     * 参数示例：<pre>20</pre>     
     * 此参数必填     */
    public function setPageSize( $pageSize) {
        $this->sdkStdResult["pageSize"] = $pageSize;
    }
    
        
    private $sdkStdResult=array();
    
    public function getSdkStdResult(){
    	return $this->sdkStdResult;
    }

}
?>