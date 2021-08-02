<?php


namespace app\plugins\group_buy\forms\api\poster;

use app\core\ApiCode;
use app\forms\common\grafika\GrafikaOption;
use app\logic\AppConfigLogic;
use app\logic\CommonLogic;
use app\models\Goods;
use app\models\User;
use app\forms\api\poster\BasePoster;
use app\plugins\group_buy\services\GroupBuyGoodsServices;
use app\plugins\group_buy\models\PluginGroupBuyGoods;

class GoodsPosterForm extends GrafikaOption implements BasePoster
{
    public $goods_id;

    public function rules()
    {
        return [
            [['goods_id', 'detail_id'], 'integer'],
        ];
    }

    public function get()
    {
        $default                      = (new \app\forms\mall\poster\PosterForm())->getDefault()['goods'];
        $options                      = AppConfigLogic::getPosterConfig();
        $option                       = $options['goods'];
        $option['is_group_buy_goods'] = 1;
        $option                       = $this->optionDiff($option, $default);

        $goods = Goods::find()->where([
            'is_delete' => 0,
            'is_recycle' => 0,
            'id'        => $this->goods_id,
        ])->with(['goodsWarehouse', 'attr', 'mallGoods'])->one();

        if (!$goods) {
            throw new \Exception('商品不存在');
        }
        $PluginGroupBuyGoods = new PluginGroupBuyGoods();
        $group_buy_goods     = $PluginGroupBuyGoods->getGroupBuyGoodsOne($this->goods_id,\Yii::$app->mall->id);
        if (!$group_buy_goods) {
            throw new \Exception('拼团商品不存在');
        }

        isset($option['pic']) && $option['pic']['file_path'] = $goods->goodsWarehouse->cover_pic;
        isset($option['desc']) && $option['desc']['text'] = self::autowrap($option['desc']['font'], 0, $this->font_path, $option['desc']['text'], $option['desc']['width']);
        isset($option['name']) && $option['name']['text'] = self::autowrap($option['name']['font'], 0, $this->font_path, $goods->goodsWarehouse->name, 750 - (float)$option['name']['left'] - 40, 2);
        isset($option['nickname']) && $option['nickname']['text'] = \Yii::$app->user->identity->nickname;

        if (isset($option['price'])) {
            $price                   = array_column($goods->attr, 'price');
            $getDispalyGroupBuyPrice = new GroupBuyGoodsServices();
            $return                  = $getDispalyGroupBuyPrice->getDispalyGroupBuyPrice($this->goods_id);
            $price                   = $return['data']['min_group_buy_price'];
            $price_str               = "拼团价低至:￥" . $price;
            $option['price']['text'] = $price_str;
        }

        if (isset($option['price']) && isset($option['name'])) {
            //自适应
            $nameSize   = imagettfbbox($option['name']['font'], 0, $this->font_path, $option['name']['text']);
            $nameHeight = $option['name']['top'] + $nameSize[1] - $nameSize[7];

            $priceSize   = imagettfbbox($option['price']['font'], 0, $this->font_path, $option['price']['text']);
            $priceHeight = $option['price']['top'] + $priceSize[1] - $priceSize[7];

            //compare
            if ($nameHeight > $option['price']['top'] && $priceHeight > $option['name']['top']) {
                $option['price']['top'] = $nameHeight + 25;
            }
        }

        $cache = $this->getCache($option);

        if ($cache) {
            return $this->returnApiResultData(ApiCode::CODE_SUCCESS, '请求成功', ['pic_url' => $cache . '?v=' . time()]);
        }

        $user_id = \Yii::$app->user->id;

        if(\Yii::$app->appPlatform == User::PLATFORM_MP_WX){
            $file = $this->qrcode($option, [
                ['proId' =>  $this->goods_id, 'pid' => \Yii::$app->user->id,'source'=>User::SOURCE_SHARE_GOODS,'mall_id'=>\Yii::$app->mall->id],
                240,
                'pages/goods/detail'
            ], $this);
            isset($option['qr_code']) && $option['qr_code']['file_path'] = $file;
            isset($option['head']) && $option['head']['file_path'] = self::head($this);
        }else{
            $path = "/h5/#/mch/group-buy/good?proId=" . $this->goods_id . "&mall_id=" . \Yii::$app->mall->id . "&pid=" . $user_id . "&source=" . User::SOURCE_SHARE_GOODS;
            $dir = 'group_buy/goods/' . $this->goods_id . '.jpg';
            $file = CommonLogic::createQrcode($option, $this, $path, $dir);
            isset($option['qr_code']) && $option['qr_code']['file_path'] = $file;
            isset($option['head']) && $option['head']['file_path'] = self::head($this, "group_buy/goods/");
        }

        $editor = $this->getPoster($option);
        return $this->returnApiResultData(ApiCode::CODE_SUCCESS, '请求成功', ['pic_url' => $editor->qrcode_url . '?v=' . time()]);
    }

    public function setFile(array $option)
    {
        $key = $option;
        if (array_key_exists('qr_code', $option)) {
            unset($key['qr_code']['file_path']);
        }
        if (array_key_exists('head', $option)) {
            unset($key['head']['file_path']);
        }
        $keys = array_merge(
            $key,
            [
                'mall_id'         => \Yii::$app->mall->id,
                'user_id'         => \Yii::$app->admin->id,
                'group_buy_goods' => 1,
                'goods_id'        => $this->goods_id,
            ]
        );
        $this->poster_file_name = sha1(serialize($keys)) . '.jpg';
        $file_url               = str_replace('http://', 'https://', \Yii::$app->request->hostInfo . \Yii::$app->request->baseUrl . '/temp/' . $this->poster_file_name);
        if (file_exists($this->temp_path . $this->poster_file_name)) {
            return $file_url;
        }
        return false;
    }
}