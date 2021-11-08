<?php

namespace app\plugins\seckill\forms\mall\special;

use app\core\ApiCode;
use app\core\BasePagination;
use app\models\BaseModel;
use app\models\GoodsService;
use app\models\User;
use app\plugins\addcredit\models\AddcreditPlateforms;
use app\plugins\seckill\models\Seckill;
use app\plugins\seckill\models\SeckillGoods;
use app\plugins\seckill\models\SeckillGoodsPrice;
use function Webmozart\Assert\Tests\StaticAnalysis\float;
use function Webmozart\Assert\Tests\StaticAnalysis\string;

class SpecialDestroyForm extends BaseModel
{

    public $id;

    public function rules()
    {
        return [
            [['id'], 'integer'],
        ];
    }

    public function delete()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }
        $t = \Yii::$app->db->beginTransaction();
        try {
            $seckill = Seckill::findOne($this->id);
            if (!$seckill)
                throw new \Exception('数据异常,该条数据不存在');

            $seckill->is_delete = 1;

            if (!$seckill->save())
                throw new \Exception($seckill->getErrorMessage());

            $seckillGoodsModels = SeckillGoods::find()->where('seckill_id = ' . $seckill->id)->all();
            foreach ($seckillGoodsModels as $model) {
                $model->is_delete = 1;
                $model->update(false);

                SeckillGoodsPrice::deleteAll('seckill_goods_id = ' . $model->id);
            }

            $t->commit();
            return $this->returnApiResultData(ApiCode::CODE_SUCCESS, '删除成功');
        } catch (\Exception $e) {
            $t->rollBack();
            return $this->returnApiResultData(ApiCode::CODE_FAIL, $e->getMessage(), [
                'line' => $e->getLine()
            ]);
        }
    }

}