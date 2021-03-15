<?php


namespace app\forms\api\poster;

use app\core\ApiCode;
use app\forms\common\grafika\GrafikaOption;
use app\logic\AppConfigLogic;
use app\logic\CommonLogic;
use app\models\Goods;
use app\models\User;
use app\forms\common\grafika\CustomizeFunction;
class GoodsPosterForm extends GrafikaOption implements BasePoster
{
   use CustomizeFunction;
    public $goods_id;

    public function rules()
    {
        return [
            [['goods_id'], 'integer'],
        ];
    }

    public function get()
    {
        $default = (new \app\forms\mall\poster\PosterForm())->getDefault()['goods'];
        $options = AppConfigLogic::getPosterConfig();
        $option = $options["goods"];
        $option = $this->optionDiff($option, $default);

        $goods = Goods::find()->where([
            'is_delete' => 0,
            'id' => $this->goods_id,
        ])->with(['goodsWarehouse', 'attr', 'mallGoods'])->one();
        if (!$goods) {
            throw new \Exception('商品不存在');
        }
        isset($option['pic']) && $option['pic']['file_path'] = $goods->goodsWarehouse->cover_pic;
        isset($option['desc']) && $option['desc']['text'] = self::autowrap($option['desc']['font'], 0, $this->font_path, $option['desc']['text'], $option['desc']['width']);
        isset($option['name']) && $option['name']['text'] = self::autowrap($option['name']['font'], 0, $this->font_path, $goods->goodsWarehouse->name, 750 - (float)$option['name']['left'] - 40, 2);
        isset($option['nickname']) && $option['nickname']['text'] = \Yii::$app->user->identity->nickname;

        if (isset($option['price'])) {
            $price = array_column($goods->attr, 'price');
            $price_str = $goods->mallGoods['is_negotiable'] ? '价格面议' : (max($price) > min($price) ? '￥' . min($price) . '~' . max($price) : '￥' . min($price));
            $option['price']['text'] = $price_str;
        }

        if (isset($option['price']) && isset($option['name'])) {
            //自适应
            $nameSize = imagettfbbox($option['name']['font'], 0, $this->font_path, $option['name']['text']);
            $nameHeight = $option['name']['top'] + $nameSize[1] - $nameSize[7];

            $priceSize = imagettfbbox($option['price']['font'], 0, $this->font_path, $option['price']['text']);
            $priceHeight = $option['price']['top'] + $priceSize[1] - $priceSize[7];

            //compare
            if ($nameHeight > $option['price']['top'] && $priceHeight > $option['name']['top']) {
                $option['price']['top'] = $nameHeight + 25;
            }
        }

        $cache = $this->getCache($option);
        if ($cache) {
            return $this->returnApiResultData(ApiCode::CODE_SUCCESS,'请求成功',['pic_url' => $cache . '?v=' . time()]);
        }

        $user_id = \Yii::$app->user->id;

        if(\Yii::$app->appPlatform == User::PLATFORM_MP_WX){
            $file = $this->qrcode($option, [
                ['proId' =>  $this->goods_id, 'pid' => \Yii::$app->user->id,'source'=>User::SOURCE_SHARE_GOODS,'mall_id'=>\Yii::$app->mall->id],
                280,
                'pages/goods/detail'
            ], $this);
            isset($option['qr_code']) && $option['qr_code']['file_path'] = $file;
            isset($option['head']) && $option['head']['file_path'] = self::head($this);
        }else{
            $path = "/h5/#/pages/goods/detail?mall_id=".\Yii::$app->mall->id."&proId=".$this->goods_id."&pid=".$user_id."&source=".User::SOURCE_SHARE_GOODS;;
            $dir = 'goods/' . $this->goods_id . time() . uniqid() . '.jpg';
            $file = CommonLogic::createQrcode($option,$this,$path,$dir);
            isset($option['qr_code']) && $option['qr_code']['file_path'] = $file;
            isset($option['head']) && $option['head']['file_path'] = self::head($this,"goods/");
        }
        $editor = $this->getPoster($option);
        return $this->returnApiResultData(ApiCode::CODE_SUCCESS,'请求成功',['pic_url' => $editor->qrcode_url . '?v=' . time()]);
    }
}