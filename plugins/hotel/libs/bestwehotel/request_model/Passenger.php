<?php
namespace app\plugins\hotel\libs\bestwehotel\request_model;


/***
 * Class PassengerModel
 * @package app\plugins\hotel\libs\bestwehotel\request_model
 * @property string $guestName 入住人姓名
 * @property string $guestMobile 入住人手机
 * @property string $guestIDCard 可选-入住人身份证号
 */
class Passenger extends BaseRequest {

    public $guestName;
    public $guestMobile;
    public $guestIDCard;

}