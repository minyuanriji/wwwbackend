<?php

namespace app\plugins\smart_shop\components;

use app\core\BasePagination;
use app\plugins\smart_shop\forms\mall\SettingDetailForm;
use yii\base\Component;
use yii\db\Connection;

class SmartShop extends Component
{
    public $db = null;
    public $setting = [];

    public function init()
    {
        parent::init();
        $this->initSetting();
    }

    public function getDB($force = false){
        if($force || !$this->db || !$this->db->getIsActive()){
            $this->db = new Connection([
                'dsn'         => 'mysql:host=' . $this->setting['db_host'] . ';port=' . $this->setting['db_port'] . ';dbname=' . $this->setting['db_name'],
                'username'    => $this->setting['db_user'],
                'password'    => $this->setting['db_pass'],
                'charset'     => $this->setting['db_charset'],
                'tablePrefix' => $this->setting['db_tb_prefix']
            ]);
            //echo "database connected\n";
        }
        return $this->db;
    }

    public function initSetting(){
        $this->setting = SettingDetailForm::getSetting();
    }

    /**
     * 验证token
     * @param $token
     * @return boolean
     */
    public function validateToken($token){
        $row = null;
        if(!empty($token)){
            $sql = "SELECT id FROM {{%admin}} WHERE token='{$token}'";
            $row = $this->getDB()->createCommand($sql)->queryOne();
        }
        return $row && isset($row['id']) ? true : false;
    }

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
     * 批量设置订单分账状态
     * @param $orderIds
     * @param $status
     */
    public function batchSetCyorderSplitStatus($orderIds, $status){
        $sql = "UPDATE {{%cyorder}} SET split_status='{$status}' WHERE id IN(".implode(",", $orderIds).")";
        $this->getDB()->createCommand($sql)->execute();
    }

    /**
     * 获取订单详情
     * @param $record_id
     * @return array
     */
    public function getCyorderDetail($record_id){

        $selects = ["o.order_no", "o.order_status", "o.is_pay", "o.total_price", "o.pay_price", "o.is_confirm", "o.apply_refund",
            "o.cancel_status", "o.is_cancel", "o.pay_type", "o.out_trade_no as transaction_id", "u.mobile as u_mobile", "s.title as store_name",
            "o.address as store_address", "pv.city_name as province",
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
     * 获取订单详情
     * @param $record_id
     * @return array
     */
    public function getCzorderDetail($record_id){
        return [];
    }

    /**
     * 批量设置智慧门店开启分账功能
     * @param $storeIds
     * @param $startAt
     * @param $setting
     */
    public function batchSetStoreSplitEnable($storeIds, $startAt, $setting = []){
        $sql = "UPDATE {{%store}} SET split_setting='".json_encode($setting)."',split_enable=1,split_start_at='{$startAt}' WHERE id IN(".implode(",", $storeIds).")";
        $this->getDB()->createCommand($sql)->execute();
    }

    /**
     * 批量设置智慧门店关闭分账功能
     * @param $storeIds
     */
    public function batchSetStoreSplitDisable($storeIds){
        $sql = "UPDATE {{%store}} SET split_enable=0 WHERE id IN(".implode(",", $storeIds).")";
        $this->getDB()->createCommand($sql)->execute();
    }

    /**
     * 获取智慧门店数据
     * @param $pagination
     * @param array $selects
     * @param array $where
     * @param int $page
     * @param int $limit
     * @return array
     */
    public function getStoreList(&$pagination, $selects = [], $where = [], $page = 1, $limit = 10){

        //条件
        $whereStr = "WHERE " . implode(" AND ", $where);

        //排序
        $orderStr = "ORDER BY s.id DESC";

        $fromTable = "FROM {{%store}} s";
        $innerArr = [
            "INNER JOIN {{%merchant}} m ON m.admin_id=s.admin_id",
            "LEFT JOIN {{%citys}} pv ON pv.cityid=s.province_code",
            "LEFT JOIN {{%citys}} ct ON ct.cityid=s.city_code",
            "LEFT JOIN {{%attachment}} s_at ON s_at.id=s.thumb",
        ];

        //获取记录数
        $row = $this->getDB()->createCommand("SELECT COUNT(*) as count {$fromTable} " . implode(" ", $innerArr) . " {$whereStr}")->queryOne();
        $totalCount = (int)$row['count'];

        $pagination = new BasePagination(['totalCount' => $totalCount, 'pageSize' => $limit, 'page' => $page]);

        $page = max(1, min($page, $pagination->page_count));

        //显示字段
        $selects = implode(",", $selects);

        //获取数据
        $offset = ($page - 1) * $limit;
        $sql = "SELECT {$selects} {$fromTable} " . implode(" ", $innerArr) . " {$whereStr} {$orderStr} limit $offset,{$limit}";
        $rows = $this->getDB()->createCommand($sql)->queryAll();

        $pagination = (array)$pagination;

        return $rows ? $rows : [];
    }
}