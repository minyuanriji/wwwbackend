<?php
namespace app\plugins\hotel\libs\bestwehotel\response_model\HotelGetHotelRoomType;


use app\plugins\hotel\libs\bestwehotel\response_model\BaseReponseModel;
use yii\base\Model;

/***
 * Class HotelGetHotelRoomTypeDataModel
 * @package app\plugins\hotel\libs\bestwehotel\response_model\HotelGetHotelRoomType
 * @property string $roomTypeCode 房型编号(兼容第三方)
 * @property string $roomTypeName 房型名称
 * @property string $sCode 房型唯一编号
 * @property int $maxRoom 最大房间数量
 * @property int $maxCheckIn 最多入住人数
 * @property string $floor 楼层
 * @property int $bedType 床型（1双床 2单床 0大床 101其他床型）
 * @property string $bedWidth 床宽(14 床宽:2.6米;13床宽:2.4米;12床宽:2.3米;11床宽2.2米; 10 床宽:2米;9床宽1.8米 ;8 床宽:1.65米;7 床宽1.6米; 6 床宽1.5米;5 床宽1.4米 ;4床宽1.35米 ;3床宽1.3米 ;2 床宽1.2 米 ;1 床宽1.1米;0 床宽1米;101 其他宽度);
 * @property int $addBed 是否允许加床（0：否 1：是 ）
 * @property int $window 0:无窗1:外窗 2:部分无窗 3:内窗 4:部分内窗
 *
 */
class HotelGetHotelRoomTypeDataModel extends BaseReponseModel {

    public $roomTypeCode;
    public $roomTypeName;
    public $sCode;
    public $maxRoom;
    public $maxCheckIn;
    public $floor;
    public $bedType;
    public $bedWidth;
    public $addBed;
    public $window;
}