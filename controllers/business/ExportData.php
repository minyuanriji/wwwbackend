<?php
namespace app\controllers\business;
use yii;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
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
        // $sql = "SELECT COUNT(de.id) as total from jxmall_plugin_integral_card as ca,jxmall_plugin_integral_card_detail as de,jxmall_user as u WHERE ca.name >= {$start_id} AND ca.name <= {$end_id} AND ca.id = de.card_id AND u.id = de.user_id ORDER BY ca.name asc;";

        // $total = $db -> createCommand($sql) -> queryOne();
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
}



