<?php
namespace app\plugins\hotel\libs\bestwehotel\request_model;

use app\models\BaseModel;
use yii\base\BaseObject;

/***
 * Class BookingPostOrder
 * @package app\plugins\hotel\libs\bestwehotel\request_model
 * @property string $innId          酒店ID
 * @property string $roomTypeId     房型编号
 * @property int    $roomCount      房间数量
 * @property string $dtArrorig      入住营业日，格式为yyyy-MM-dd。限制：不能早于当天营业日。当天营业日入住最晚在次日03:00前下单（这是理论最晚时间，实际允许最晚下单时间按产品规则）。
 * @property string $dtDeporig      离店营业日，格式yyyy-MM-dd。限制：必须大于入住营业日。可预订的最大入住天数为15天
 * @property int    $payType        支付方式。0：到付，1：企业储值预付 2 月结预付（预付totalRate必传）
 * @property float  $totalRate      订单价格，校验订单价格
 * @property string $externalId     第三方渠道的订单号
 * @property string $productCode    商品编号
 * @property string $lastArrTim     可选-到店时间，格式yyyy-MM-dd HH:mm:ss。现付无担保订单到店时间理论取值范围（T为入住营业日）：T 13:00:00 到 T+1 04:00:00。预付担保单订单到店时间无限制。18:00后预订当天入住的正价房必需传值。
 * @property string $remarks        可选-订单备注，内容不能超过200个字，限制：不含json特殊字符
 * @property string $bookMobile     可选-预订人手机号，必须为该公司员工，如果未绑定过公司自动绑定
 * @property string $bookName       可选-预订人姓名。如若不传预订姓名手机号，默认预订人为企业管理员
 * @property float  $feeRate        可选-订单其他费用，如果需要额外收费这个字段必填
 * @property array<Passenger> $passengers 入住人信息。入住人信息数量与房间数量原则上一致
 */
class BookingPostOrder extends BaseRequest {

    public $innId;
    public $roomTypeId;
    public $roomCount;
    public $dtArrorig;
    public $dtDeporig;
    public $payType;
    public $externalId;
    public $productCode;

    public $passengers = [];

    //以下是可选内容
    public $lastArrTim;
    public $remarks;
    public $totalRate;
    public $bookMobile;
    public $bookName;
    public $feeRate;



    public function buildPassengers($models){
        $passengers = [];
        if(is_array($models)){
            foreach($models as $model){
                $passengers[] = $model->getAttributes();
            }
        }
        return $passengers;
    }

    public function getUri(){
        return "/booking/postOrder";
    }
}