<?php
namespace app\plugins\hotel\libs\bestwehotel\response_model\GetHotelRoomStatus;


use app\plugins\hotel\libs\bestwehotel\response_model\BaseReponseModel;

/***
 * Class CancelRuleResponseResponseModel
 * @package app\plugins\hotel\libs\bestwehotel\response_model\GetHotelRoomStatus
 * @property int $supportCancel 取消方式{0: 不可取消, 1: 免费取消，2: 限时取消}
 * @property array<CancelPenaltyResponseResponseModel> $cancelPenaltyList 取消时间段规则。当cancelType=0时,表示在当前时间段免费取消（start-end） 当cancelType=1时,表示在当前时间段有偿取消（start-end）,根据type罚金类型匹配惩罚值value
 */
class CancelRuleResponseResponseModel extends BaseReponseModel{

    public $supportCancel;
    public $cancelPenaltyList;

}