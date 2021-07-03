<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: zal
 * Date: 2020-04-11
 * Time: 15:16
 */

namespace app\forms\mall\shop;

use app\core\ApiCode;
use app\forms\common\PickLinkForm;
use app\models\BaseModel;
use app\models\NavIcon;

class NavIconEditForm extends BaseModel
{
    public $name;
    public $sort;
    public $url;
    public $icon_url;
    public $open_type;
    public $status;
    public $id;
    public $params;

    public function rules()
    {
        return [
            [['name', 'icon_url', 'status', 'sort', 'url'], 'required'],
            [['icon_url', 'name', 'url', 'sort', 'open_type'], 'string'],
            [['id', 'status'], 'integer'],
            [['params'], 'safe'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'name' => '名称',
            'sort' => '排序',
            'url' => '导航链接',
            'icon_url' => '导航图标',
            'status' => '是否显示状态',
            'open_type' => '打开方式类型',
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        try {
            if ($this->id) {
                $homeNav = NavIcon::findOne(['mall_id' => \Yii::$app->mall->id, 'id' => $this->id]);

                if (!$homeNav) {
                    return [
                        'code' => ApiCode::CODE_FAIL,
                        'msg' => '该条数据不存在',
                    ];
                }
            } else {
                $homeNav = new NavIcon();
            }

            $homeNav->name = $this->name;
            $homeNav->mall_id = \Yii::$app->mall->id;
            $homeNav->icon_url = $this->icon_url;
            $homeNav->status = $this->status;
            $homeNav->open_type = $this->open_type ? $this->open_type : PickLinkForm::OPEN_TYPE_NAVIGATE;
            $homeNav->sort = $this->sort;
            $homeNav->params = \Yii::$app->serializer->encode($this->params ? $this->params : []);
            $homeNav->url = $this->url;
            $res = $homeNav->save();

            if (!$res) {
                throw new \Exception('保存失败');
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
