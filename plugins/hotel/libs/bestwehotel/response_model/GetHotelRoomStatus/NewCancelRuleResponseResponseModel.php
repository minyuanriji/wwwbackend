<?php
namespace app\plugins\hotel\libs\bestwehotel\response_model\GetHotelRoomStatus;


use app\plugins\hotel\libs\bestwehotel\response_model\BaseReponseModel;

/**
 * Class NewCancelRuleResponseResponseModel
 * @package app\plugins\hotel\libs\bestwehotel\response_model\GetHotelRoomStatus
 * @property string $cancelType 取消类型：0免费取消 1有偿取消
 * @property string $type 罚金类型 0 房晚 1 百分数 2固定罚金
 * @property string $value 罚款值
 * @property int $minCount 最小房间数范围（部分有）
 * @property int $maxCount 最大房间数范围（部分有）
 * @property int $startHour 开始时间 小时 从当前入住时间24点开始减
 * @property int $endHour 结束时间 小时 从当前入住时间24点开始减
 */
class NewCancelRuleResponseResponseModel extends BaseReponseModel{
    public $cancelType;
    public $type;
    public $value;
    public $minCount;
    public $maxCount;
    public $startHour;
    public $endHour;
}