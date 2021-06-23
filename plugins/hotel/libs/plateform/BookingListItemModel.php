<?php
namespace app\plugins\hotel\libs\plateform;

use yii\base\Model;

/**
 * Class BookingListItemModel
 * @package app\plugins\hotel\libs\plateform
 * @property string $product_thumb 产品图片
 * @property string $product_code  房型唯一编码
 * @property string $product_name  名称
 * @property int    $product_num   数量
 * @property float  $product_price 价格
 * @property int    $is_breakfast  是否有早餐
 * @property int    $ban_smoking   是否禁烟
 * @property string $bed_type      床型：single单床，double双床，big大床
 * @property int    $window        窗户 no:无窗 out:外窗 part_no:部分无窗 inner:内窗 part_inner:部分内窗
 *
 */
class BookingListItemModel extends Model
{
    public $product_thumb;
    public $product_code;
    public $product_name;
    public $product_num;
    public $product_price;
    public $is_breakfast;
    public $ban_smoking;
    public $bed_type;
    public $window;

}