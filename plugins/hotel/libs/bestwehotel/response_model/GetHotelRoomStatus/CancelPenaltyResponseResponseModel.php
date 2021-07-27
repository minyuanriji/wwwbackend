<?php
namespace app\plugins\hotel\libs\bestwehotel\response_model\GetHotelRoomStatus;

use app\plugins\hotel\libs\bestwehotel\response_model\BaseReponseModel;

/**
 * Class CancelPenaltyResponseResponseModel
 * @package app\plugins\hotel\libs\bestwehotel\response_model\GetHotelRoomStatus
 * @property string $start 开始时间 yyyy-MM-dd HH:mm:ss
 * @property string $end 结束时间  yyyy-MM-dd HH:mm:ss
 * @property string $cancelType 取消类型：0免费取消 1有偿取消
 * @property string $type 罚金类型 0 房晚 1 百分数 2固定罚金
 * @property string $value 罚款值
 */
class CancelPenaltyResponseResponseModel extends BaseReponseModel{

    public $start;
    public $end;
    public $cancelType;
    public $type;
    public $value;

}