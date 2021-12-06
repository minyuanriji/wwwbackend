<?php

namespace app\plugins\taolijin\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\taolijin\models\TaolijinCats;

class TaoLiJinCatEditForm extends BaseModel{

    public $parent_id;
    public $name;
    public $sort;
    public $pic_url;
    public $id;
    public $ali_type;
    public $ali_cat_id;
    public $ali_custom_data;

    public function rules(){
        return [
            [['name', 'sort', 'ali_type'], 'required'],
            [['parent_id', 'id'], 'integer'],
            [['pic_url'], 'string'],
            [['ali_custom_data', 'ali_cat_id'], 'safe']
        ];
    }

    public function attributeLabels(){
        return [
            'parent_id' => '父级分类ID',
            'name'      => '分类名称',
            'sort'      => '排序',
            'pic_url'   => '分类图标',
        ];
    }

    public function save() {

        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        try {

            if ($this->id) {
                $cat = TaolijinCats::findOne([
                    'mall_id' => \Yii::$app->mall->id,
                    'id'      => $this->id
                ]);
                if (!$cat) {
                    throw new \Exception("数据异常,该条数据不存在");
                }
            } else {
                $cat = new TaolijinCats();
                $cat->mall_id   = \Yii::$app->mall->id;
                $cat->parent_id = $this->parent_id ? $this->parent_id : 0;
            }

            $cat->name     = $this->name;
            $cat->sort     = $this->sort ? $this->sort : 100;
            $cat->pic_url  = $this->pic_url;
            $cat->ali_type = $this->ali_type;

            $aliCustomData = [];
            if($this->ali_custom_data && is_array($this->ali_custom_data)){
                $nameArr = [];
                foreach($this->ali_custom_data as &$customData){
                    $name = trim($customData['name']);
                    if(empty($name) || in_array($name, $nameArr))
                        continue;
                    $customData['name'] = $name;
                    $customData['value'] = trim($customData['value']);
                    $nameArr[] = $name;
                    $aliCustomData[] = $customData;
                }
            }
            $cat->ali_custom_data = json_encode($aliCustomData);

            if(!$cat->save()){
                throw new \Exception($this->responseErrorMsg($cat));
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功',
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $e->getMessage(),
            ];
        }
    }

}