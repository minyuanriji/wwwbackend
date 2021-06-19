<?php
namespace app\plugins\hotel\libs\bestwehotel\client;


use app\plugins\hotel\libs\bestwehotel\request_model\BaseRequest;
use app\plugins\hotel\libs\bestwehotel\response_model\BaseReponseModel;
use app\plugins\hotel\libs\HotelException;

abstract class BaseClient{

    protected $requestModel;

    public function __construct(BaseRequest $requestModel){
        $this->requestModel = $requestModel;
    }

    public function getDataJSONString(){
        if(!$this->requestModel->build()){
            throw new \Exception($this->requestModel->getError());
        }
        return $this->requestModel->getJsonString();
    }

    /**
     * 解析数据
     * @param $content
     */
    public function parseResult($content){
        $parseArray = @json_decode($content, true);
        if(!isset($parseArray['msgCode'])){
            throw new HotelException("[BaseClient::parseResult]解析数据错误");
        }

        if($parseArray['msgCode'] != BaseReponseModel::MSG_CODE_SUCC){
            if(isset($parseArray['message'])){
                throw new HotelException($parseArray['message'] . " " . $parseArray['msgCode']);
            }else{
                throw new HotelException("未知错误 " . $parseArray['msgCode']);
            }
        }

        $responseModel = $this->parseResponseModel($parseArray);
        $responseModel->msgCode = BaseReponseModel::MSG_CODE_SUCC;

        return $responseModel;
    }

    /**
     * 解析客户端响应模型
     * @param $parseArray
     * @return BaseReponseModel
     */
    abstract public function parseResponseModel($parseArray);
}