<?php

namespace app\plugins\smart_shop\components;

trait SmartShopOrderTrait
{
    /**
     * 获取智慧门店订单详情
     * @param $table
     * @param $record_id
     * @return array
     */
    public function getOrderDetail($table, $record_id){
        $detail = [];
        if($table == "cyorder"){
            $detail = $this->getCyorderDetail($record_id);
        }elseif($table == "czorder"){
            $detail = $this->getCzorderDetail($record_id);
        }
        return $detail;
    }

    public function getCyorders($selects = ["o.*"], $wheres = [], $limit = 10, $orderBy = null){
        $sql = "SELECT " .implode(",", $selects) . " FROM {{%cyorder}} o " .
            "INNER JOIN {{%store}} s ON s.id=o.store_id " .
            "INNER JOIN {{%users}} u ON u.id=o.user_id " .
            "INNER JOIN {{%merchant}} m ON s.admin_id=m.admin_id " .
            "LEFT JOIN {{%merchant_entry}} me ON me.merchant_id=m.id AND me.pay_way_id=1 " .
            "LEFT JOIN {{%merchant_entry}} me_ali ON me_ali.merchant_id=m.id AND me_ali.pay_way_id=2 " .
            "LEFT JOIN {{%citys}} pv ON pv.cityid=s.province_code ".
            "LEFT JOIN {{%citys}} ct ON ct.cityid=s.city_code " .
            "LEFT JOIN {{%attachment}} s_at ON s_at.id=s.thumb " .
            "WHERE " . (!empty($wheres) ? implode(" AND ", $wheres) : "1") . " " .
            "ORDER BY " . (!empty($orderBy) ? $orderBy : " o.id DESC") . " " .
            "LIMIT 0,{$limit}";
        $rows = $this->getDB()->createCommand($sql)->queryAll();
        return $rows ? $rows : [];
    }

    /**
     * 通过订单ID获取详情列表
     * @param $orderId
     * @param string[] $selects
     * @return array|\yii\db\DataReader
     * @throws \yii\db\Exception
     */
    public function getCyorderDetailByOrderId($orderId, $selects = ["od.*"]){
        $sql = "SELECT " .implode(",", $selects) . " FROM {{%cyorder_detail}} od WHERE od.order_id='{$orderId}' ORDER BY od.id DESC";
        $rows = $this->getDB()->createCommand($sql)->queryAll();
        return $rows ? $rows : [];
    }

    /**
     * 获取商品订单
     * @param string[] $selects
     * @param array $wheres
     * @param int $limit
     * @param null $orderBy
     * @return array|\yii\db\DataReader
     * @throws \yii\db\Exception
     */
    public function getCzorders($selects = ["o.*"], $wheres = [], $limit = 10, $orderBy = null){
        $sql = "SELECT " .implode(",", $selects) . " FROM {{%czorder}} o " .
            "INNER JOIN {{%store}} s ON s.id=o.store_id " .
            "INNER JOIN {{%users}} u ON u.id=o.user_id " .
            "INNER JOIN {{%merchant}} m ON s.admin_id=m.admin_id " .
            "LEFT JOIN {{%merchant_entry}} me ON me.merchant_id=m.id AND me.pay_way_id=1 " .
            "LEFT JOIN {{%merchant_entry}} me_ali ON me_ali.merchant_id=m.id AND me_ali.pay_way_id=2 " .
            "LEFT JOIN {{%citys}} pv ON pv.cityid=s.province_code ".
            "LEFT JOIN {{%citys}} ct ON ct.cityid=s.city_code " .
            "LEFT JOIN {{%attachment}} s_at ON s_at.id=s.thumb " .
            "WHERE " . (!empty($wheres) ? implode(" AND ", $wheres) : "1") . " " .
            "ORDER BY " . (!empty($orderBy) ? $orderBy : " o.id DESC") . " " .
            "LIMIT 0,{$limit}";
        $rows = $this->getDB()->createCommand($sql)->queryAll();
        return $rows ? $rows : [];
    }

    /**
     * 批量设置订单分账状态
     * @param $orderIds
     * @param $status
     */
    public function batchSetCyorderSplitStatus($orderIds, $status){
        $sql = "UPDATE {{%cyorder}} SET split_status='{$status}' WHERE id IN(".implode(",", $orderIds).")";
        $this->getDB()->createCommand($sql)->execute();
    }

    /**
     * 批量设置KPI新订单状态
     * @param $orderIds
     * @param $status
     */
    public function batchSetCyorderKpiNewStatus($orderIds, $status){
        $sql = "UPDATE {{%cyorder}} SET `kpi_new_status`='{$status}' WHERE `id` IN(".implode(",", $orderIds).")";
        $this->getDB()->createCommand($sql)->execute();
    }

    /**
     * 获取订单详情
     * @param $record_id
     * @return array
     */
    public function getCyorderDetail($record_id){

        $selects = ["o.order_no", "o.order_status", "o.state", "o.type", "o.is_pay", "o.total_price", "o.pay_price", "o.is_confirm", "o.apply_refund",
            "o.cancel_status", "o.is_cancel", "o.pay_type", "o.out_trade_no as transaction_id", "u.mobile as u_mobile", "s.title as store_name",
            "o.address as store_address", "pv.city_name as province", "o.create_time", "o.store_id", "u.nickname", "o.refund_price", "o.apply_time", "o.refund_reason",
            "ct.city_name as city", "s_at.filepath as store_logo", "m.id as merchant_id", "m.name as merchant_name",
            "m.mobile as merchant_mobile", "me.mno", "me_ali.mno as mno_ali"];
        $sql = "SELECT " .implode(",", $selects) . " FROM {{%cyorder}} o " .
            "INNER JOIN {{%store}} s ON s.id=o.store_id " .
            "INNER JOIN {{%users}} u ON u.id=o.user_id " .
            "INNER JOIN {{%merchant}} m ON s.admin_id=m.admin_id " .
            "LEFT JOIN {{%merchant_entry}} me ON me.merchant_id=m.id AND me.pay_way_id=1 " .
            "LEFT JOIN {{%merchant_entry}} me_ali ON me_ali.merchant_id=m.id AND me_ali.pay_way_id=2 " .
            "LEFT JOIN {{%citys}} pv ON pv.cityid=s.province_code ".
            "LEFT JOIN {{%citys}} ct ON ct.cityid=s.city_code " .
            "LEFT JOIN {{%attachment}} s_at ON s_at.id=s.thumb " .
            "WHERE o.id='{$record_id}'";

        $row = $this->getDB()->createCommand($sql)->queryOne();
        if($row){
            $row['store_logo'] = rtrim($this->setting['host_url'], "/") . "/" . ltrim(str_replace("\\", "/", $row['store_logo']), "/");
        }

        return $row ? $row : [];
    }

    /**
     * 批量设置KPI新订单状态
     * @param $orderIds
     * @param $status
     */
    public function batchSetCzorderKpiNewStatus($orderIds, $status){
        $sql = "UPDATE {{%czorder}} SET `kpi_new_status`='{$status}' WHERE `id` IN(".implode(",", $orderIds).")";
        $this->getDB()->createCommand($sql)->execute();
    }

    /**
     * 获取订单详情
     * @param $record_id
     * @return array
     */
    public function getCzorderDetail($record_id){
        $selects = ["o.state", "o.code as order_no", "o.store_id", "o.pay_price", "o.send_price", "u.mobile as u_mobile", "u.nickname",
            "s.title as store_name", "o.create_time", "u.balance"];
        $sql = "SELECT " .implode(",", $selects) . " FROM {{%czorder}} o " .
            "INNER JOIN {{%store}} s ON s.id=o.store_id " .
            "INNER JOIN {{%users}} u ON u.id=o.user_id " .
            "INNER JOIN {{%merchant}} m ON s.admin_id=m.admin_id " .
            "LEFT JOIN {{%merchant_entry}} me ON me.merchant_id=m.id AND me.pay_way_id=1 " .
            "LEFT JOIN {{%merchant_entry}} me_ali ON me_ali.merchant_id=m.id AND me_ali.pay_way_id=2 " .
            "LEFT JOIN {{%citys}} pv ON pv.cityid=s.province_code ".
            "LEFT JOIN {{%citys}} ct ON ct.cityid=s.city_code " .
            "LEFT JOIN {{%attachment}} s_at ON s_at.id=s.thumb " .
            "WHERE o.id='{$record_id}'";

        $row = $this->getDB()->createCommand($sql)->queryOne();
        if($row){

        }

        return $row ? $row : [];
    }
}