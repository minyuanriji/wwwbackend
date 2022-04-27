<?php

namespace app\plugins\smart_shop\components;

use app\core\BasePagination;

trait SmartShopShopTrait
{
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
     * 获取智慧门店详情
     * @param $id
     * @return array
     */
    public function getStoreDetail($id){
        $whereStr = "WHERE s.id='{$id}'";
        $fromTable = "FROM {{%store}} s";
        $innerArr = [
            "INNER JOIN {{%merchant}} m ON m.admin_id=s.admin_id",
            "LEFT JOIN {{%storeset}} sst ON sst.store_id=s.id"
        ];
        $selects = "s.id as ss_store_id, s.title as store_name, m.id as merchant_id";

        //获取记录数
        $row = $this->getDB()->createCommand("SELECT {$selects} {$fromTable} " . implode(" ", $innerArr) . " {$whereStr}")->queryOne();

        return $row ? $row : null;
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
            "LEFT JOIN {{%merchant_entry}} wx_me ON wx_me.merchant_id=m.id AND wx_me.pay_way_id=1",
            "LEFT JOIN {{%merchant_entry}} ali_me ON ali_me.merchant_id=m.id AND ali_me.pay_way_id=2",
            "LEFT JOIN {{%citys}} pv ON pv.cityid=s.province_code",
            "LEFT JOIN {{%citys}} ct ON ct.cityid=s.city_code",
            "LEFT JOIN {{%attachment}} s_at ON s_at.id=s.thumb",
            "LEFT JOIN {{%storeset}} sst ON sst.store_id=s.id"
        ];

        //获取记录数
        $row = $this->getDB()->createCommand("SELECT COUNT(*) as count {$fromTable} " . implode(" ", $innerArr) . " {$whereStr}")->queryOne();
        $totalCount = (int)$row['count'];

        $pagination = new BasePagination(['totalCount' => $totalCount, 'pageSize' => $limit, 'page' => $page]);

        $page = max(1, $page);

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