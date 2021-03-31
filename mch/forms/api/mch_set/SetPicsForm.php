<?php
namespace app\mch\forms\api\mch_set;

use app\core\ApiCode;
use app\models\BaseModel;
use app\models\Store;

class SetPicsForm extends BaseModel{

    public $mch_id;
    public $act;
    public $pic_url;

    public function rules(){
        return array_merge(parent::rules(), [
            [['mch_id', 'act', 'pic_url'], 'required'],
            [['act', 'pic_url'], 'string']
        ]);
    }

    public function save(){
        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {
            $mchStore = Store::findOne(["mch_id" => $this->mch_id]);
            if(!$mchStore || $mchStore->is_delete){
                throw new \Exception("无法获取店铺信息");
            }

            $picUrls = (array)@json_decode($mchStore->pic_url, true);
            if(isset($picUrls[0]) && empty($picUrls[0])){
                unset($picUrls[0]);
            }

            if(strtoupper($this->act) == "ADD"){ //添加图片
                $picUrls[] = $this->pic_url;
            }else{ //删除图片
                foreach($picUrls as $key => $pic){
                    if(strtoupper($pic) == strtoupper($this->pic_url)){
                        unset($picUrls[$key]);
                    }
                }
            }

            $picUrls = array_unique($picUrls);
            sort($picUrls);

            $mchStore->pic_url = json_encode($picUrls);
            if(!$mchStore->save()){
                throw new \Exception($this->responseErrorMsg($mchStore));
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg'  => '请求成功',
                'data' => [
                    'pic_urls' => $picUrls,
                ]
            ];

        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $e->getMessage(),
            ];
        }
    }

}