<?php
namespace app\plugins\hotel\libs\bestwehotel\response_model\HotelGetHotelInfo;


use app\plugins\hotel\libs\bestwehotel\response_model\BaseReponseModel;

/***
 * Class HotelGetHotelInfoResponseModel
 * @package app\plugins\hotel\libs\bestwehotel\response_model\HotelGetHotelInfo
 * @property string $innId 酒店唯一编号
 * @property string $innName 酒店名称
 * @property string $innShortName 酒店短名称
 * @property string $innNamePinYin 酒店名称全拼
 * @property string $address 酒店地址
 * @property int $businessType 经营类别(0 自营店 1 管理店 2 加盟店 3 标准店  4 合作店)
 * @property int $status 酒店状态(-1 开发 0 筹建中 1开业 2开业后退筹建 3 开业后解约 5 下线整改 6 售出未下线)
 * @property int $innType 酒店类别(100经济型酒店 101 精品商务酒店102 景区度假酒店 103 主题特色酒店 104 民族风情酒店)
 * @property int $starType 星级类别(0 一星 1 二星 2 三星 3 四星  4五星 -1无星)（已废弃）
 * @property string $cityCode 城市编号
 * @property string $cityName 城市名称
 * @property string $brandCode 品牌编号
 * @property string $innPhone 酒店电话
 * @property string $innEmail 酒店邮箱
 * @property int $bookFlag 是否支持预订(1 是 0 否)
 * @property int $valid 是否有效(1 是 0 否)
 * @property int $openDate 开业时间
 * @property int $closeDate 停业时间（Null为没有停业，尚营业中
 * @property string $description 描述信息(富文本)
 * @property string $sourceType 酒店来源
 * @property int $supportForeignGuest 1、可接待外宾 0 不可接待外宾 可以为空
 * @property int $restaurant 1有餐厅 0 无餐厅
 * @property array $invoiceType 发票类型列表，1 普通发票,0 无,2 增值税普通发票,3 增值税专用发票,4 电子增值税普通发票
 * @property array $mapInfo 酒店地图坐标信息
 *
 */
class HotelGetHotelInfoResponseModel extends BaseReponseModel{

    public $innId;
    public $innName;
    public $innShortName;
    public $innNamePinYin;
    public $address;
    public $businessType;
    public $status;
    public $innType;
    public $starType;
    public $cityCode;
    public $cityName;
    public $brandCode;
    public $innPhone;
    public $innEmail;
    public $bookFlag;
    public $valid;
    public $openDate;
    public $closeDate;
    public $description;
    public $sourceType;
    public $supportForeignGuest;
    public $restaurant;
    public $invoiceType;

    private $mapInfo = [];

    public function addMapInfo(HotelGetHotelInfoMapInfoItemModel $item){
        $this->mapInfo[] = $item;
    }

    public function getMapInfo(){
        return $this->mapInfo;
    }
}