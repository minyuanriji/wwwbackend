<?php
namespace app\plugins\hotel\libs\bestwehotel\response_model\QueryOrder;

use app\plugins\hotel\libs\bestwehotel\response_model\BaseReponseModel;

/**
 * Class QueryOrderResponseModel
 * @package app\plugins\hotel\libs\bestwehotel\response_model\QueryOrder
 * @property string $orderCode 订单编号
 * @property string $externalId 第三方渠道的订单号，这是在下单时第三方渠道传过来的订单号
 * @property string $innId 酒店ID
 * @property string $innName 酒店名称
 * @property string $roomTypeId 房型编号
 * @property string $roomTypeName 房间类型名称
 * @property int $roomQty 房间数量
 * @property int $orderState 订单状态，取值：0：待确认1：预订成功2：已取消3：预订未到4：已入住5：已完成6：确认失败（注： 用户暂时不会看到0和6这两个状态）
 * @property int $payType 支付方式（0 到付；1 线上预付）
 * @property int $payState 支付状态0：未付款 1：已付款 2：退款处理中 3：已退款
 * @property float $payRate 订单金额
 * @property string $assureType 担保类型 0：无担保 2：全程担保
 * @property string $guestsName 入住人姓名（逗号隔开）
 * @property string $contactName 联系人姓名
 * @property string $contactPhone 联系人电话
 * @property string $contactEmail 联系人邮箱
 * @property string $origArrDate 预计抵店时间
 * @property string $origDepDate 预计离店时间
 * @property string $arrDate 实际入住时间
 * @property string $depDate 实际离店时间
 * @property string $remarks 订单备注
 * @property float $returnRate 订单提前离店返回储值
 * @property float $realPayRate 订单实际支付价格
 * @property array<int> $invoiceType 发票类型列表发票类型 1 普通发票,0 无,2 增值税普通发票,3 增值税专用发票,4 电子增值税普通发票
 * @property float $feeRate 其他费用
 */
class QueryOrderResponseModel extends BaseReponseModel{

    public $orderCode;
    public $externalId;
    public $innId;
    public $innName;
    public $roomTypeId;
    public $roomTypeName;
    public $roomQty;
    public $orderState;
    public $payType;
    public $payState;
    public $payRate;
    public $assureType;
    public $guestsName;
    public $contactName;
    public $contactPhone;
    public $contactEmail;
    public $origArrDate;
    public $origDepDate;
    public $arrDate;
    public $depDate;
    public $remarks;
    public $returnRate;
    public $realPayRate;
    public $invoiceType;
    public $feeRate;

}