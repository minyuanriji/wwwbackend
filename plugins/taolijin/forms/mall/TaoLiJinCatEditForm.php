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

    public function rules(){
        return [
            [['name', 'sort'], 'required'],
            [['parent_id', 'id'], 'integer'],
            [['pic_url'], 'string'],
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

            $cat->name    = $this->name;
            $cat->sort    = $this->sort ? $this->sort : 100;
            $cat->pic_url = $this->pic_url;

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