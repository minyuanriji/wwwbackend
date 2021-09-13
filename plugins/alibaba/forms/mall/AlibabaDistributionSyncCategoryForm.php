<?php

namespace app\plugins\alibaba\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\alibaba\models\AlibabaApp;
use app\plugins\alibaba\models\AlibabaDistributionGoodsCategory;
use lin010\alibaba\c2b2b\api\GetCategoryList;
use lin010\alibaba\c2b2b\api\GetCategoryListResponse;
use lin010\alibaba\c2b2b\Distribution;

class AlibabaDistributionSyncCategoryForm extends BaseModel{

    public $app_id;
    public $parent_id;
    public $wait_first_parents;

    public function rules(){
        return [
            [['app_id'], 'required'],
            [['parent_id', 'wait_first_parents'], 'safe']
        ];
    }

    public function sync(){

        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {
            $app = AlibabaApp::findOne($this->app_id);
            if(!$app || $app->is_delete){
                throw new \Exception("应用不存在");
            }

            $distribution = new Distribution($app->app_key, $app->secret);
            $res = $distribution->request(new GetCategoryList([
                "categoryID" => $this->parent_id ? $this->parent_id : 0
            ]));
            if(!$res instanceof GetCategoryListResponse){
                throw new \Exception("返回结果异常");
            }

            if($res->list){
                foreach($res->list as $item){
                    $cat = AlibabaDistributionGoodsCategory::findOne([
                        "ali_cat_id" => $item['id'],
                        "mall_id"    => \Yii::$app->mall->id
                    ]);
                    if(!$cat){
                        $cat = new AlibabaDistributionGoodsCategory([
                            "ali_cat_id" => $item['id'],
                            "mall_id"    => \Yii::$app->mall->id,
                            "created_at" => time()
                        ]);
                    }
                    $cat->name          = $item['name'];
                    $cat->ali_parent_id = $this->parent_id ? $this->parent_id : 0;
                    $cat->updated_at    = time();
                    $cat->orgin_data    = json_encode($item);
                    $cat->is_delete     = 0;
                    if(!$cat->save()){
                        throw new \Exception($this->responseErrorMsg($cat));
                    }
                }
            }


            $isFinished = 0;
            $content = "";
            if(!$this->parent_id){
                $waitFirstParents = [];
                if($res->list){
                    foreach($res->list as $item){
                        $waitFirstParents[] = $item['id'];
                    }
                    $this->parent_id = array_shift($waitFirstParents);
                    $content = "获得".count($res->list)."个主类别，开始同步{$this->parent_id}的子类别";
                }
            }else{
                if(empty($this->wait_first_parents)){
                    $isFinished = 1;
                    $waitFirstParents = [];
                    $content = "同步完成";
                }else{
                    $this->parent_id = array_shift($this->wait_first_parents);
                    $waitFirstParents = $this->wait_first_parents;
                    $content = "获得".count($res->list)."子类别，开始同步{$this->parent_id}的子类别，剩余" . count($this->wait_first_parents) . "个主类别";
                }
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    "parent_id" => $this->parent_id,
                    "is_finished" => $isFinished,
                    "content" => $content,
                    "wait_first_parents" => $waitFirstParents ? $waitFirstParents : []
                ]
            ];
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }

    }

}