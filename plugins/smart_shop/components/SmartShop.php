<?php

namespace app\plugins\smart_shop\components;

use app\core\BasePagination;
use app\plugins\smart_shop\forms\mall\SettingDetailForm;
use yii\base\Component;
use yii\db\Connection;

class SmartShop extends Component
{
    public $db;
    public $setting = [];

    public function init()
    {
        parent::init();

        $this->setting = SettingDetailForm::getSetting();

        $this->db = new Connection([
            'dsn'         => 'mysql:host=' . $this->setting['db_host'] . ';port=' . $this->setting['db_port'] . ';dbname=' . $this->setting['db_name'],
            'username'    => $this->setting['db_user'],
            'password'    => $this->setting['db_pass'],
            'charset'     => $this->setting['db_charset'],
            'tablePrefix' => $this->setting['db_tb_prefix']
        ]);
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

    /**
     * 获取订单详情
     * @param $record_id
     * @return array
     */
    public function getCyorderDetail($record_id){

        $selects = ["o.order_no", "o.order_status", "o.total_price", "o.pay_price", "o.is_confirm", "o.apply_refund",
            "o.cancel_status", "o.is_cancel", "o.pay_type", "u.mobile as u_mobile", "s.title as store_name",
            "o.address as store_address", "pv.city_name as province",
            "ct.city_name as city", "s_at.filepath as store_logo", "m.id as merchant_id", "m.name as merchant_name",
            "m.mobile as merchant_mobile"];
        $sql = "SELECT " .implode(",", $selects) . " FROM {{%cyorder}} o " .
               "INNER JOIN {{%store}} s ON s.id=o.store_id " .
               "INNER JOIN {{%users}} u ON u.id=o.user_id " .
               "INNER JOIN {{%merchant}} m ON s.admin_id=m.admin_id " .
               "LEFT JOIN {{%citys}} pv ON pv.cityid=s.province_code ".
               "LEFT JOIN {{%citys}} ct ON ct.cityid=s.city_code " .
               "LEFT JOIN {{%attachment}} s_at ON s_at.id=s.thumb " .
               "WHERE o.id='{$record_id}'";

        $row = $this->db->createCommand($sql)->queryOne();
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
        $row = $this->db->createCommand("SELECT COUNT(*) as count {$fromTable} " . implode(" ", $innerArr) . " {$whereStr}")->queryOne();
        $totalCount = (int)$row['count'];

        $pagination = new BasePagination(['totalCount' => $totalCount, 'pageSize' => $limit, 'page' => $page]);

        $page = max(1, min($page, $pagination->page_count));

        //显示字段
        $selects = implode(",", $selects);

        //获取数据
        $offset = ($page - 1) * $limit;
        $sql = "SELECT {$selects} {$fromTable} " . implode(" ", $innerArr) . " {$whereStr} {$orderStr} limit $offset,{$limit}";
        $rows = $this->db->createCommand($sql)->queryAll();

        $pagination = (array)$pagination;

        return $rows ? $rows : [];
    }
}