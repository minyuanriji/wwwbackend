<?php
namespace app\plugins\hotel\libs\bestwehotel\response_model\GetHotelImage;


use app\plugins\hotel\libs\bestwehotel\response_model\BaseReponseModel;

/***
 * Class GetHotelImageDataModel
 * @package app\plugins\hotel\libs\bestwehotel\response_model\GetHotelImage
 * @property int $imageType 图片类型:1 酒店图片 2 客房图片 3 酒店外观 4 酒店大堂 5 酒店娱乐设施 6酒店餐饮设施 7酒店服务设施 8酒店休闲设施 9其他 10公共区域 11周边景
 * @property string $uploadFileName 图片上传文件名称
 * @property string $imageUrlGetHotelImageDataModel 图片访问地址
 * @property int $sizeType 1小图120*120;2 640*480
 * @property int $master 1主图
 * @property string $roomTypeCode 房型
 * @property string $imageDes 图片说明
 */
class GetHotelImageDataModel extends BaseReponseModel
{
    public $imageType;
    public $uploadFileName;
    public $imageUrl;
    public $sizeType;
    public $master;
    public $roomTypeCode;
    public $imageDes;
}