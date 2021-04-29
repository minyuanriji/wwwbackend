<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 商品会员
 * Author: zal
 * Date: 2020-04-13
 * Time: 16:16
 */

namespace app\forms\common\goods;

use app\forms\common\CommonMallMember;
use app\models\BaseModel;
use app\models\Goods;
use app\models\GoodsAttr;
use app\models\GoodsMemberPrice;
use app\models\MemberLevel;

use app\models\User;

class GoodsMember extends BaseModel
{
    private static $instance;
    public static $memberLevel = null;

    public static function getCommon()
    {
        if (!self::$instance instanceof self) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public $priceList;
    public $is_level;
    public $is_level_alone;
    public $level;



    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-05-05
     * @Time: 14:45
     * @Note:获取商城商品会员价
     * @param GoodsCommon $goods
     * @return float|int|mixed|null
     *
     */

    public function getGoodsMemberPrice($goods)
    {
        $this->setLevel();
        $this->priceList = $this->getPriceList($goods);
        $this->is_level = $goods->is_level;
        $this->is_level_alone = $goods->is_level_alone;
        return $this->getGoodsMember();
    }

    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-05-05
     * @Time: 14:46
     * @Note:设置等级
     */
    public function setLevel()
    {
        if (!\Yii::$app->user->isGuest) {
            /* @var User $user */
            $user = \Yii::$app->user->identity;
            $level = $user->level;
            if ($level <= 0) {
                $level = $this->getMinMember();
            } else {
                $level = \Yii::$app->mall->getMallSettingOne('is_member_user_member_price') == 1 ? $level : 0;
            }
        } else {
            $level = $this->getMinMember();
        }
        $this->level = $level;
    }


    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-05-05
     * @Time: 14:42
     * @Note:获取最小会员等级
     * @return int|mixed|null
     */
    private function getMinMember()
    {
        if (\Yii::$app->mall->getMallSettingOne('is_common_user_member_price') == 0) {
            return 0;
        }
        if (self::$memberLevel !== null) {
            return self::$memberLevel;
        }
        $memberLevel = MemberLevel::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0,
            'status' => 1,
        ])->orderBy(['level' => SORT_ASC])->one();
        if (!$memberLevel) {
            self::$memberLevel = 0;
            return 0;
        }
        self::$memberLevel = $memberLevel->level;
        return $memberLevel->level;
    }

    private $list;


    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-05-05
     * @Time: 14:44
     * @Note:获取价格列表
     * @param GoodsCommon $goods
     * @return array|bool
     */
    public function getPriceList($goods)
    {
        if (isset($this->list[$goods->id])) {
            return $this->list[$goods->id];
        }
        $list = [];
        if ($this->level > 0) {
            if ($goods->is_level_alone == 0) {
                $list = array_column($goods->attr, 'price');
            } else {
                $list = GoodsMemberPrice::find()
                    ->where(['is_delete' => 0, 'goods_id' => $goods->id, 'level' => $this->level])
                    ->select('price')->column();
            }
        }
        $this->list[$goods->id] = $list;
        return $list;
    }


    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-05-05
     * @Time: 14:43
     * @Note:从价格列表中取出最小会员价
     * @return float|int|mixed|null
     *
     */
    public function getGoodsMember()
    {
        $levelPrice = null;
        if ($this->level > 0 && $this->is_level == 1) {
            foreach ($this->priceList as $item) {
                if (!$levelPrice) {
                    $levelPrice = $item;
                } else {
                    $levelPrice = min($levelPrice, $item);
                }
            }
            if ($this->is_level_alone == 0) {
                $member = CommonMallMember::getMemberOne($this->level);
                $levelPrice *= $member->discount / 10;
            }
            $levelPrice = round($levelPrice, 2);
        } else {
            $levelPrice = -1;
        }

        return $levelPrice;
    }
}
