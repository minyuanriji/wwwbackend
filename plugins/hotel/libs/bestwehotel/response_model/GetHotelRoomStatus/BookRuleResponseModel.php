<?php
namespace app\plugins\hotel\libs\bestwehotel\response_model\GetHotelRoomStatus;


use app\plugins\hotel\libs\bestwehotel\response_model\BaseReponseModel;

/**
 * Class BookRuleResponseModel
 * @package app\plugins\hotel\libs\bestwehotel\response_model\GetHotelRoomStatus
 * @property float $backCash 返现(单位元，最多保留两位小数点)
 * @property int $maxAmount 最大入住人数
 * @property string $startDateTime 预定范围开始日期(yyyy-MM-dd HH:mm)
 * @property string $endDateTime 预定范围结束日期(yyyy-MM-dd HH:mm)
 * @property string $checkStartDateTime 入住范围开始日期(yyyy-MM-dd)
 * @property string $checkEndDateTime 入住范围结束日期(yyyy-MM-dd)
 * @property string $checkWeeks "1,2,3,4,5,6,7", //可入住的星期几
 * @property int $checkMinDay 最小入住天数(可等于)
 * @property int $checkMaxDay 最大入住天数(可等于)
 * @property string $weeks "1,2,3,4,5,6,7", //可预定的星期几
 * @property int $minStayThrough 表示若预订区间内，某一天这个属性有值，则整个连住天数，必须以这些天的最小minStayThrough，最大的maxStayThrough范围内
 * @property int $maxStayThrough 表示若预订区间内，某一天这个属性有值，则整个连住天数，必须以这些天的最小minStayThrough，最大的maxStayThrough范围内。
 * @property string $bminDay 最少提前预定天数
 * @property string $bmaxDay 最大提前预定天数
 * @property int $roomMaxAmount 最大预订房间数
 * @property int $roomMinAmount 最小预订房间数
 * @property array $bookConstranit 预定限制
 * @property int $bookCheck 预订限制类型 {0 提前预定 1 不可取消 2 不可预订 3 当天预订当天入住 4 本人预订本人入住 5 必须连住N晚以上 6 仅限预订当天 7 限制预订当天入住并允许连住N晚 8 是否仅开放指定天数 9 本人入住})
 * @property int $bookCheckV 预订限制的值
 */
class BookRuleResponseModel extends BaseReponseModel{
    public $backCash;
    public $maxAmount;
    public $startDateTime;
    public $endDateTime;
    public $checkStartDateTime;
    public $checkEndDateTime;
    public $checkWeeks;
    public $checkMinDay;
    public $checkMaxDay;
    public $weeks;
    public $minStayThrough;
    public $maxStayThrough;
    public $bminDay;
    public $bmaxDay;
    public $roomMaxAmount;
    public $roomMinAmount;
    public $bookConstranit;
    public $bookCheck;
    public $bookCheckV;
}