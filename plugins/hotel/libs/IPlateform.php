<?php
namespace app\plugins\hotel\libs;


use app\plugins\hotel\libs\plateform\OrderRefundResult;
use app\plugins\hotel\models\HotelOrder;
use app\plugins\hotel\models\HotelPlateforms;
use app\plugins\hotel\models\Hotels;

interface IPlateform
{

    /**
     * 订单退款
     * @param HotelOrder $order
     * @throws HotelException
     * @return OrderRefundResult
     */
    public function orderRefund(HotelOrder $order);

    /**
     * 提交订单
     * @param HotelOrder $order
     * @throws HotelException
     * @return SubmitOrderResult
     */
    public function submitOrder(HotelOrder $order);

    /**
     * 查询订单
     * @param HotelOrder $order
     * @throws HotelException
     * @return QueryOrderResult
     */
    public function queryOrder(HotelOrder $order);

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