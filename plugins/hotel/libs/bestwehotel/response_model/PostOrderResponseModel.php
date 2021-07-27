<?php
namespace app\plugins\hotel\libs\bestwehotel\response_model;

/***
 * Class PostOrder
 * @package app\plugins\hotel\libs\bestwehotel\response_model
 * @property string $orderCode 订单号
 * @property int $orderState 订单状态，取值：0：待确认 1：预订成功 2：已取消 3：预订未到 4：已入住 5：已完成 6：确认失败（注： 用户暂时不会看到0和6这两个状态）
 */
class PostOrderResponseModel extends BaseReponseModel{

    public $orderCode;
    public $orderState;

}