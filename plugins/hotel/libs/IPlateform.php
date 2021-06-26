<?php
namespace app\plugins\hotel\libs;


use app\plugins\hotel\models\HotelOrder;
use app\plugins\hotel\models\HotelPlateforms;
use app\plugins\hotel\models\Hotels;

interface IPlateform
{
    /**
     * TODO 提交订单
     * @param HotelOrder $order
     * @throws HotelException
     * @return
     */
    public function submitOrder(HotelOrder $order);

    /**
     * 判断订单是否可以退款
     * @param HotelOrder $order
     * @throws HotelException
     * @return boolean
     */
    public function refundable(HotelOrder $order);

    /**
     * 导入第三方数据到平台
     * @param $page
     * @param $size
     * @throws HotelException
     * @return ImportResult
     */
    public function import($page, $size);

    /**
     * 获取酒店可预订的房间列表
     * @param Hotels $hotel
     * @param $startDate
     * @param $days
     * @throws HotelException
     * @return BookingListResult
     */
    public function getBookingList(Hotels $hotel, HotelPlateforms $hotelPlateform, $startDate, $days);
}