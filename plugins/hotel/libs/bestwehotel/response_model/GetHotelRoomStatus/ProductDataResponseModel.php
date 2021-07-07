<?php
namespace app\plugins\hotel\libs\bestwehotel\response_model\GetHotelRoomStatus;


use app\plugins\hotel\libs\bestwehotel\response_model\BaseReponseModel;

/***
 * Class ProductDataResponseModel
 * @package app\plugins\hotel\libs\bestwehotel\response_model\GetHotelRoomStatus
 * @property int     $quota                 可售房量
 * @property int     $endOfDay              营业日
 * @property float   $rackRate              门市价
 * @property float   $spotRate              到付价
 * @property float   $advanceRate           预付价
 * @property int     $spotBreakfastCount    到付早餐null/0-无 1-单早 2-双早
 * @property int     $advanceBreakfastCount 预付早餐null/0-无 1-单早 2-双早
 * @property string  $productName           商品名称
 * @property string  $productCode           商品编号
 * @property int     $productType           商品类型 0本店协议价 1批量分店品牌协议价 2组合品牌协议价 3平台协议价 4企业活动价
 * @property int     $protocolType          协议类型 0分店协议 1品牌批量分店协议 2品牌组合协议 3自营销售分店协议 4自营销售品牌批量分店协议 5自营销售品牌组合协议
 * @property int     $freeCancelTime        免费取消时间(这个时间前预付的订单可免费取消时间)
 * @property array<> $supportPay            是否支持到付和预付0,1
 * @property boolean $hasEffiveTime         是否含有生效时间,false为永久有效
 * @property int     $startTime             协议价格生效开始时间
 * @property string  $endTime               协议价格生效结束时间
 * @property float   $advanceFeeRate        预付其他费用
 * @property float   $spotFeeRate           到付其他费用
 * @property int     $guests                丽笙单人价/双人价
 * @property CancelRuleResponseResponseModel $cancelRule 取消规则（锦江品牌需要用到）,取消规则优先看cancelRule，没有再看cancelTime
 * @property NewCancelRuleResponseResponseModel $newCancelPenaltyList  新取消规则（维也纳品牌需要用到）,取消规则优先看cancelRule，没有再看cancelTime
 * @property array<> $bookRule              预定规则,目前只针对于活动价
 */
class ProductDataResponseModel extends BaseReponseModel{
    public $quota;
    public $endOfDay;
    public $rackRate;
    public $spotRate;
    public $advanceRate;
    public $spotBreakfastCount;
    public $advanceBreakfastCount;
    public $productName;
    public $productCode;
    public $productType;
    public $protocolType;
    public $freeCancelTime;
    public $supportPay;
    public $hasEffiveTime;
    public $startTime;
    public $endTime;
    public $cancelRule;
    public $newCancelPenaltyList;
    public $advanceFeeRate;
    public $spotFeeRate;
    public $guests;
    public $bookRule;
}