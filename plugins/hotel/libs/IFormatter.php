<?php
namespace app\plugins\hotel\libs;

interface IFormatter
{
    /**
     * 酒店基本信息
     * @return array
     */
    public static function hotelInfo(HotelResponse $response);

    /**
     * 酒店图片信息
     * @return array
     */
    public static function picsInfo(HotelResponse $response);

    /**
     * 房间信息
     * @return array
     */
    public static function roomInfo(HotelResponse $response);

}