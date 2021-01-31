<?php

namespace app\plugins\mch\forms\api\poster;

use app\core\ApiCode;
use app\forms\api\poster\BasePoster;
use app\forms\api\poster\common\StyleGrafika;
use app\models\BaseModel;
use app\plugins\mch\models\Goods;


class PosterNewForm extends BaseModel implements BasePoster
{
    public $style;
    public $typesetting;
    public $type;
    public $goods_id;
    public $color;

    public function rules()
    {
        return [
            [['style', 'typesetting', 'goods_id'], 'required'],
            [['style', 'typesetting', 'type'], 'integer'],
            [['color'], 'string'],
        ];
    }

    public function poster()
    {
        try {
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => $this->get()
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $e->getMessage(),
                'line' => $e->getLine(),
            ];
        }
    }

    public function get()
    {
        if (!$this->validate()) {
            return $this->responseErrorMsg();
        }

        $class = $this->getClass($this->style);

        $goods = Goods::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'id' => $this->goods_id,
        ])->one();
        if (empty($goods)) {
            throw new \Exception('海报-商品不存在');
        }

        $class->typesetting = $this->typesetting;
        $class->type = $this->type;
        $class->color = $this->color;
        $class->goods = $goods;

        $class->extraModel = 'app\plugins\mch\forms\api\poster\PosterCustomize';
        return $class->build();
    }


    /**
     * @param int $key
     * @return StyleGrafika
     * @throws \Exception
     */
    private function getClass(int $key): StyleGrafika
    {
        $map = [
            1 => 'app\forms\api\poster\style\StyleOne',
            2 => 'app\forms\api\poster\style\StyleTwo',
            3 => 'app\forms\api\poster\style\StyleThree',
            4 => 'app\forms\api\poster\style\StyleFour',
        ];
        if (isset($map[$key]) && class_exists($map[$key])) {
            return new $map[$key];
        }
        throw new \Exception('调用错误');
    }
}