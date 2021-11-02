<?php

namespace app\plugins\addcredit\forms\mall\plateforms;

use app\core\ApiCode;
use app\core\BasePagination;
use app\models\BaseModel;
use app\models\GoodsService;
use app\models\User;
use app\plugins\addcredit\models\AddcreditPlateforms;
use function Webmozart\Assert\Tests\StaticAnalysis\float;
use function Webmozart\Assert\Tests\StaticAnalysis\string;

class PlateformsForm extends BaseModel
{

    public $id;
    public $product_id;
    public $page;
    public $is_default;
    public $keyword;
    public $mch_id;
    public $product_price;
    public $product_type;


    public function rules()
    {
        return [
            [['id', 'is_default', 'mch_id', 'product_id', 'product_type'], 'integer'],
            [['page'], 'default', 'value' => 1],
            [['keyword'], 'string'],
            [['product_price'], 'number'],
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
                $item['region_deny'] = !empty($item['region_deny']) ? @json_decode($item['region_deny'], true) : [];
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
            $detail['cyd_id'] = isset($json_param['id']) ? $json_param['id'] : "";
            $detail['secret_key'] = isset($json_param['secret_key']) ? $json_param['secret_key'] : "";
            $user = User::findOne($detail['parent_id']);
            $detail['parent_name'] = $user ? $user->nickname : "";
            $detail['product_json_data'] = json_decode($detail['product_json_data'], true);
            $detail['enable_fast'] = (string)$detail['enable_fast'];
            $detail['enable_slow'] = (string)$detail['enable_slow'];
            $detail['region_deny'] = !empty($detail['region_deny']) ? json_decode($detail['region_deny'], true) : [];
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

    //弃用
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

    public function isEnable()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $plateforms = AddcreditPlateforms::findOne($this->id);

            if (!$plateforms)
                throw new \Exception('数据异常,该条数据不存在');

            $plateforms::updateAll(['is_enabled' => 0], ['is_enabled' => 1]);

            $plateforms->is_enabled = 1;
            $plateforms->save();

            $transaction->commit();
            return $this->returnApiResultData(ApiCode::CODE_SUCCESS, '启用成功');
        } catch (\Exception $e) {
            $transaction->rollBack();
            return $this->returnApiResultData(ApiCode::CODE_FAIL,$e->getMessage(),['error' => ['line' => $e->getLine()]]);
        }
    }

    public function delProduct()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        try {
            $plateforms = AddcreditPlateforms::findOne($this->id);

            if (!$plateforms)
                throw new \Exception('数据异常,该条数据不存在');

            $product = json_decode($plateforms->product_json_data, true);
            foreach ($product as $key => $item) {
                if ($item['product_id'] == $this->product_id) {
                    unset($product[$key]);
                }
            }

            $plateforms->product_json_data = json_encode(array_values($product));
            if (!$plateforms->save())
               throw new \Exception($plateforms->getErrorMessage());

            return $this->returnApiResultData(ApiCode::CODE_SUCCESS, '删除成功');
        } catch (\Exception $e) {
            return $this->returnApiResultData(ApiCode::CODE_FAIL,$e->getMessage(),['error' => ['line' => $e->getLine()]]);
        }
    }

    public function addProduct()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        try {
            $plateforms = AddcreditPlateforms::findOne($this->id);

            if (!$plateforms)
                throw new \Exception('数据异常,该条数据不存在');

            $product = $plateforms->product_json_data;
            if ($product) {
                $product = json_decode($product, true);
            }
            $addProduct = [
                "product_id" => $this->product_id,
                "price" => $this->product_price,
                "type" => ($this->product_type == 1) ? 'fast' : 'slow',
            ];
            if ($product) {
                array_push($product,  $addProduct);
            } else {
                $product = [$addProduct];
            }

            $plateforms->product_json_data = json_encode($product);
            if (!$plateforms->save())
                throw new \Exception($plateforms->getErrorMessage());

            return $this->returnApiResultData(ApiCode::CODE_SUCCESS, '添加成功');
        } catch (\Exception $e) {
            return $this->returnApiResultData(ApiCode::CODE_FAIL,$e->getMessage(),['error' => ['line' => $e->getLine()]]);
        }
    }
}