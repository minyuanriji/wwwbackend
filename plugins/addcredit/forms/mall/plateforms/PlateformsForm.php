<?php

namespace app\plugins\addcredit\forms\mall\plateforms;

use app\core\ApiCode;
use app\core\BasePagination;
use app\models\BaseModel;
use app\models\GoodsService;
use app\models\User;
use app\plugins\addcredit\models\AddcreditPlateforms;

class PlateformsForm extends BaseModel
{

    public $id;
    public $page;
    public $is_default;
    public $keyword;
    public $mch_id;


    public function rules()
    {
        return [
            [['id', 'is_default', 'mch_id'], 'integer'],
            [['page'], 'default', 'value' => 1],
            [['keyword'], 'string'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => '角色ID',
        ];
    }

    /**
     * @Note: 获取平台列表，可带查询
     * @return array
     */

    public function search()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }
        $query = AddcreditPlateforms::find()->where([
            'mall_id' => \Yii::$app->mall->id
        ]);

        if ($this->keyword) {
            $query->andWhere(['like', 'name', $this->keyword]);
        }
        $list = $query->page($pagination)
            ->orderBy(['id' => SORT_ASC])
            ->asArray()->all();
        if ($list) {
            foreach ($list as &$item) {
                $item['created_at'] = date("Y-m-d H:i:s", $item['created_at']);
                $item['json_param'] = json_decode($item['json_param'],true);
            }
        }
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'list' => $list,
                'pagination' => $pagination
            ]
        ];
    }

    public function getDetail()
    {
        $detail = AddcreditPlateforms::find()->where([
            'id' => $this->id,
        ])->asArray()->one();
        if ($detail) {
            $json_param = json_decode($detail['json_param'],true);
            $detail['cyd_id'] = $json_param['id'];
            $detail['secret_key'] = $json_param['secret_key'];
            $user = User::findOne($detail['parent_id']);
            if (!$user) {
                throw new \Exception('用户不存在', ApiCode::CODE_FAIL);
            }
            $detail['parent_name'] = $user->nickname;
            if (!$user) {
                return [
                    'code' => ApiCode::CODE_FAIL,
                    'msg' => '数据为空',
                ];
            }
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请求成功',
                'data' => $detail
            ];
        }
        return [
            'code' => ApiCode::CODE_FAIL,
            'msg' => '请求失败',
        ];
    }

    public function delete()
    {
        $services = GoodsService::find()->where([
            'id' => $this->id,
            'mall_id' => \Yii::$app->mall->id,
            'mch_id' => \Yii::$app->admin->identity->mch_id,
        ])->one();

        if (!$services) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => '数据异常,该条数据不存在',
            ];
        }

        try {
            $services->is_delete = 1;
            $res = $services->save();

            if (!$res) {
                return [
                    'code' => ApiCode::CODE_FAIL,
                    'msg' => $this->responseErrorMsg($services),
                ];
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '删除成功',
            ];

        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $e->getMessage(),
                'error' => [
                    'line' => $e->getLine()
                ]
            ];
        }
    }
}