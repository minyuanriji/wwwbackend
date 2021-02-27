<?php
namespace app\controllers\business;
use app\models\mysql\Goods;
use yii;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use app\models\mysql\{PostageRules,PluginDistributionGoods};
set_time_limit(0);
ini_set("memory_limit", "1024M");
class ExportData{
    public function ExportData($start_id,$end_id){
        $Spreadsheet = new Spreadsheet();
        $sheet = $Spreadsheet -> getActiveSheet();
        $sheet -> setCellValue('A1','ID');
        $sheet -> setCellValue('B1','卡片正面序号');
        $sheet -> getColumnDimension('B') -> setWidth(15);
        $sheet -> setCellValue('C1','卡盒子编号');
        $sheet -> getColumnDimension('C') -> setWidth(15);
        $sheet -> setCellValue('D1','发卡人');
        $sheet -> setCellValue('E1','序列号');
        $sheet -> getColumnDimension('E') -> setWidth(10);
        $sheet -> setCellValue('F1','密码');
        $sheet -> getColumnDimension('F') -> setWidth(10);
        $sheet -> setCellValue('G1','积分劵数额');
        $sheet -> getColumnDimension('G') -> setWidth(15);
        $sheet -> setCellValue('H1','积分劵月份');
        $sheet -> getColumnDimension('H') -> setWidth(15);
        $sheet -> setCellValue('I1','积分劵有效时间');
        $sheet -> getColumnDimension('I') -> setWidth(15);
        $sheet -> setCellValue('J1','过期时间');
        $sheet -> getColumnDimension('J') -> setWidth(20);
        $sheet -> setCellValue('K1','二维码收卡人');
        $sheet -> getColumnDimension('K') -> setWidth(50);
        $db = yii::$app->db;

        $sql2 = "SELECT de.id as ID,ca.name as 卡片面序号,ca.name as 卡盒子编号,u.nickname as 发卡人,de.serialize_no as 序列号,de.use_code as 密码,
json_unquote(JSON_EXTRACT(de.integral_setting,'$.integral_num')) as 积分劵,
json_unquote(JSON_EXTRACT(de.integral_setting,'$.period')) as 积分劵月份,
json_unquote(JSON_EXTRACT(de.integral_setting,'$.expire')) as 积分劵有效时间,
FROM_UNIXTIME(de.expire_time,'%Y-%m-%d %H:%i:%s') as 过期时间,
de.qr_url as 二维码收卡人
from jxmall_plugin_integral_card as ca,jxmall_plugin_integral_card_detail as de,jxmall_user as u
WHERE ca.name >= {$start_id} AND ca.name <= {$end_id} AND ca.id = de.card_id AND u.id = de.user_id ORDER BY ca.name asc";
        $integralData = $db -> createCommand($sql2) -> queryAll();
        $sheet -> fromArray($integralData,null,'A2');
        header("Content-type:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
        //1.8 MIME协议扩展
        header('Content-Disposition:attachment;filename=积分卡劵数据.xlsx');
        //1.9 缓存控制
        header('Cache-Control:max-age=0');
        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($Spreadsheet,'Xlsx');
        $writer -> save('php://output');
        exit();
    }

    public function getGoodsData($params){
        $db = \Yii::$app->db;
        $data = [];
        if($params != '*'){
            $params = explode(',',$params);
        }
        if($params != '*'){
            foreach ($params as $key => $val){
//                $sql = "SELECT * FROM jxmall_goods as g WHERE id = {$val}";
                $sql = "SELECT id,goods_warehouse_id,status,price,use_attr,attr_groups,goods_stock,virtual_sales,confine_count,pieces,forehead,freight_id,give_score_type,forehead_score,forehead_score_type,accumulative,individual_share,attr_setting_type,share_type,is_default_services,use_score,max_deduct_integral,enable_integral,integral_setting,enable_score,score_setting,is_order_paid,order_paid,cannotrefund,is_show_sales,use_virtual_sales FROM jxmall_goods WHERE id = {$val}";
                $res = $db -> createCommand($sql) -> queryOne();
                $res['attr_groups'] = json_decode($res['attr_groups'],true);
                array_push($data,$res);
            }
        }else{
            $total_sql = "SELECT count(id) as total FROM jxmall_goods";
            $total = $db -> createCommand($total_sql) -> queryOne();
            $pageSize = 100;
            $page_total = $total['total'] / $pageSize;
            for ($i = 0; $i <= ceil($page_total); $i++){
                $pageNow = $i * $pageSize;
                $sql = "SELECT id,goods_warehouse_id,status,price,use_attr,attr_groups,goods_stock,virtual_sales,confine_count,pieces,forehead,freight_id,give_score_type,forehead_score,forehead_score_type,accumulative,individual_share,attr_setting_type,share_type,is_default_services,use_score,max_deduct_integral,enable_integral,integral_setting,enable_score,score_setting,is_order_paid,order_paid,cannotrefund,is_show_sales,use_virtual_sales FROM jxmall_goods WHERE mch_id = 0 limit {$pageNow},{$pageSize}";
                $res = $db -> createCommand($sql) -> queryAll();
                foreach ($res as $key => $val){
                    $val['attr_groups'] = json_decode($val['attr_groups'],true);
                    array_push($data,$val);
                }
            }
        }
        $goods_data = [];
        foreach ($data as $key => $val){
            $val['status'] = $val['status'] == 1 ? '上架' : '下架';
            $val['use_attr'] = $val['use_attr'] == 1 ? '使用' : '不使用';
            $val['give_score_type'] = $val['give_score_type'] == 1 ? '固定值' : '百分比';
            $val['forehead_score_type'] = $val['forehead_score_type'] == 1 ? '固定值' : '百分比';
//            $val['individual_share'] = $val['individual_share'] == 1 ? '是' : '否';
            $val['attr_setting_type'] = $val['attr_setting_type'] == 1 ? '详细设置' : '普通设置';
            $val['share_type'] = $val['share_type'] == 1 ? '百分比' : '固定金额';
            $val['is_default_services'] = $val['is_default_services'] == 1 ? '是' : '否';
            $val['use_score'] = $val['use_score'] == 1 ? '不使用' : '使用';
            $val['enable_integral'] = $val['enable_integral'] == 1 ? '赠送' : '不赠送';
            $val['enable_score'] = $val['enable_score'] == 1 ? '赠送' : '不赠送';
            $val['is_show_sales'] = $val['is_show_sales'] == 1 ? '销量：开启' : '销量：关闭';
            $val['use_virtual_sales'] = $val['use_virtual_sales'] == 1 ? '虚拟销量：开启' : '虚拟销量：关闭';
            if(!empty($val['attr_groups'])){
                $attr_groups_msg = '规格组：';
                foreach ($val['attr_groups'][0]['attr_list'] as $key2 => $val2){
                    $attr_groups_msg .= $val2['attr_name'] . '、';
                }
                $val['attr_groups'] = $attr_groups_msg;
            }
            if(!empty($val['integral_setting'])){
                $integral_setting_num = '赠送:' . json_decode($val['integral_setting'],true)['integral_num'] . ',' . json_decode($val['integral_setting'],true)['period'] . '月';
                $val['integral_setting'] = $integral_setting_num;
            }else{
                $val['integral_setting'] = '无';
            }

            if($val['freight_id'] == 0){
                $PostageRules = (new PostageRules()) -> getExpressPrice();
            }else{
                $PostageRules = (new PostageRules()) -> getGoodsExpressPrice($val['freight_id']);
            }
            $val['freight_id'] = '运费规则：' . (!empty($PostageRules -> name) ? $PostageRules -> name : '暂无数据');

            if(!empty($val['score_setting'])){
                $integral_num = '赠送:' . json_decode($val['score_setting'],true)['integral_num'] . ',' . json_decode($val['score_setting'],true)['period'] . '月' . '，积分类型：' . (json_decode($val['score_setting'],true)['expire'] > 0 ? '限时有效。' : '永久有效。');
                $val['score_setting'] = $integral_num;
            }else{
                $val['score_setting'] = '无';
            }

            if(!empty($val['cannotrefund'])){
                $cannotrefund = json_decode($val['cannotrefund'],true);
                $cannotrefund_msg = '';
                $cannotrefund_msg .= array_search(1,$cannotrefund) === 0 ? '退货：支持，' : '退货：不支持，';
                $cannotrefund_msg .= array_search(2,$cannotrefund) !== false ? '退货退款：支持，' : '退货退款：不支持，';
                $cannotrefund_msg .= array_search(3,$cannotrefund) !== false ? '换货：支持。' : '换货：不支持。';
                $val['cannotrefund'] = $cannotrefund_msg;
            }else{
                $val['cannotrefund'] = '暂无数据';
            }
            $goods_warehouse_sql = "SELECT `name`,original_price FROM jxmall_goods_warehouse WHERE id = {$val['goods_warehouse_id']}";
            $goods_warehouse = $db -> createCommand($goods_warehouse_sql) -> queryOne();
            $val['goods_warehouse_id'] = $goods_warehouse['name'];
            $val['original_price'] = $goods_warehouse['original_price'];

            if(!empty($val['is_order_paid'])){
                $val['is_order_paid'] = '订单支付设置：开启';
                $order_paid = json_decode($val['order_paid'],true);
                $order_paid_msg = '积分：' . (empty($order_paid['is_score']) ? '关闭，' : '开启，') . '积分劵：' . (empty($order_paid['is_score_card']) ? '关闭，' : '开启，') . '购物券：' . (empty($order_paid['is_integral_card']) ? '关闭。' : '开启。');
                $val['order_paid'] = $order_paid_msg;
            }else{
                $val['order_paid'] = '订单支付参数：无' ;
                $val['is_order_paid'] = '订单支付设置：关闭';
            }
//            echo '<pre>';
            $DistributionGoods = (new PluginDistributionGoods()) -> getDistributionData($val['id']);
            if(!empty($DistributionGoods)){
                $val['individual_share'] = $DistributionGoods['is_alone'] == 1 ? '独立分销：开启' : '独立分销：关闭';
                $val['attr_setting_type'] = $DistributionGoods['attr_setting_type'] == 0 ? '分销佣金设置：商品属性设置' : '规格设置';
                $val['share_type'] = $DistributionGoods['share_type'] == 0 ? '分销佣金类型：固定值' : '分销佣金类型：百分比';
            }else{
                $val['individual_share'] = '暂无数据';
                $val['attr_setting_type'] = '暂无数据';
                $val['share_type'] = '暂无数据';
            }
            $goods_distribution_sql = "SELECT commission_first,commission_second,`level` FROM jxmall_plugin_distribution_goods_detail WHERE goods_id = {$val['id']} AND `level` BETWEEN 5 AND 7";
            $goods_distribution = $db -> createCommand($goods_distribution_sql) -> queryAll();
            $val['level_5'] = '暂无数据';
            $val['level_6'] = '暂无数据';
            $val['level_7'] = '暂无数据';
            if(!empty($goods_distribution)){
                $val['level_5'] = '一级分销：'. (!empty($goods_distribution[0]['commission_first']) ? $goods_distribution[0]['commission_first'] : '无') . '，二级分销：' . (!empty($goods_distribution[0]['commission_second']) ? $goods_distribution[0]['commission_second'] : '无');
                $val['level_6'] = '一级分销：'. (!empty($goods_distribution[1]['commission_first']) ? $goods_distribution[1]['commission_first'] : '无') . '，二级分销：' . (!empty($goods_distribution[1]['commission_second']) ? $goods_distribution[1]['commission_second'] : '无');
                $val['level_7'] = '一级分销：'. (!empty($goods_distribution[2]['commission_first']) ? $goods_distribution[2]['commission_first'] : '无') . '，二级分销：' . (!empty($goods_distribution[2]['commission_second']) ? $goods_distribution[2]['commission_second'] : '无');
            }

            array_push($goods_data,$val);
        }
//        echo '<pre>';
//        var_dump($goods_data);exit();

        $this -> ExportGoodsData($goods_data);
    }

    public function ExportGoodsData($arrData){
        $ascii = 90;
        $ascii_arr = [];
        $add_ascii = ['AA','AB','AC','AD','AE','AF','AG','AH','AI'];
        $title = ['编号', '商品名称', '是否上架', '售价', '使用规格组', '规格组', '商品库存', '已出售量', '购物数量限制', '单品满件包邮', '单口满额包邮', '运费模板ID', '赠送积分类型', '可抵扣积分', '可抵扣积分类型', '允许多件累计折扣', '是否单独分销设置', '分销设置类型', '分类佣金类型', '默认服务', '使用积分', '购物券可抵扣分', '是否启用购物券赠送', '购物券赠送设置', '是否启用积分券赠送', '积分券赠送设置','订单支付设置','订单支付参数','是否支持退换货','销量是否开启','虚拟销量是否开启','原价', '合伙人','联合创始人','分公司'
        ];
        $widthArr = ['B' => 30,'F' => 120,'Z' => 28,'AA' => 20,'AB' => 40,'AC' => 40,'AG' => 40,'AH' => 40,'AI' => 40,'X' => 20,'O' => 20,'V' => 20,'Y' => 20,'AD' => 18,'AE' => 18,'L' => 25,'Q' => 20,'R' => 25,'S' => 25];
        $num = 0;
        for ($k = 65; $k <= $ascii; $k++){
            $ascii_arr[$num] = chr($k);
            $num++;
            if($k == 90){
                for ($j = 0; $j < count($add_ascii); $j++){
                    $ascii_arr[$num] = $add_ascii[$j];
                    $num++;
                }
            }
        }
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet -> getActiveSheet();
        foreach ($ascii_arr as $key => $val){
            $sheet -> setCellValue($val . 1,$title[$key]);
        }
        foreach ($widthArr as $key => $val){
            $sheet->getColumnDimension($key)->setWidth($val);
        }
        $sheet->fromArray($arrData,null,'A2');
        header('Content-Description: File Transfer');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename=商品信息.xlsx');
        header('Cache-Control: max-age=0');
        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output');
        exit();
    }

}



