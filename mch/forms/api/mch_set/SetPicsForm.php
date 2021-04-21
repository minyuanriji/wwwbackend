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
            [['act'], 'string']
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
            /*if(isset($picUrls[0]) && empty($picUrls[0])){
                unset($picUrls[0]);
            }*/
            $new_picUrls = [];
            array_values($picUrls);
            if(strtoupper($this->act) == "ADD"){ //添加图片
                foreach ($picUrls as $key => $value) {
                    if (isset($value['id'])) {
                        $new_picUrls[$key] = $value;
                        unset($picUrls[$key]);
                    }
                }
                $picUrls[] = $this->pic_url;
            }else{ //删除图片
                foreach($picUrls as $key => $pic){
                    if (is_array($this->pic_url)) {
                        if (is_array($pic)) {
                            if(strtoupper($pic['pic_url']) == strtoupper($this->pic_url['pic_url'])){
                                unset($picUrls[$key]); 
                                break;
                            }
                        }
                        continue;
                    } else {
                        if(strtoupper($pic) == strtoupper($this->pic_url)){
                            unset($picUrls[$key]);
                            break;
                        }
                    }
                }
            }

            $picUrls = array_unique($picUrls);
            sort($picUrls);
            if ($new_picUrls) {
                foreach (array_values($new_picUrls) as $pic_key => $pic_val) {
                    array_push($picUrls,$pic_val);
                }
            }

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