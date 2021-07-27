<?php
set_time_limit(0);
class Integral{
    public function getUserInfo(){
        $link = "mysql:host=127.0.0.1;dbname=myrj;port=3306;charset=utf8";
        $user = 'myrj';
        $pass = 'MdFzYh63WKEppzs4';
        $pdo = new PDO($link,$user,$pass);
        $pdo -> beginTransaction();
        $sql = "SELECT count(id) as total FROM jxmall_integral WHERE controller_type = 0 AND period_unit = 'month' AND finish_period < 12 AND status = 1 || status = 0";
        $row = $pdo -> query($sql);
        $total = $row -> fetch(PDO::FETCH_ASSOC);
        $page = 100;
        $total = $total['total'] / $page;
        for ($i = 0; $i < ceil($total); $i++){
            $pageNow = $i * $page;
            $sql = "SELECT id,user_id,integral_num,period,finish_period,effective_days,next_publish_time FROM jxmall_integral WHERE period_unit = 'month' AND finish_period < 12 AND status = 1 || status = 0 LIMIT {$pageNow},{$page}";
            $row = $pdo -> query($sql);
            $rows = $row -> fetchAll(PDO::FETCH_ASSOC);
            foreach ($rows as $key => $val){
//                if(time() < $val['next_publish_time']){
//                    continue;
//                }
                $date_days = 0;
                if(date('m') == 02){
                    $date_days = 3;
                }
                $expire_time = strtotime('+ '. ($val['effective_days'] - $date_days) .'days',strtotime(date('Y-m-01')));
                $next_publish_time = strtotime(date('Y-m-01',strtotime('+ 1 month'))) + 30;
                $sql2 = "SELECT id,dynamic_score,score FROM jxmall_user WHERE id = {$val['user_id']}";
                $user = $pdo -> query($sql2);
                if(empty($user)){
                    echo '没有找到用户';
                    continue;
                }
                $userinfo = $user -> fetch(PDO::FETCH_ASSOC);
                $dynamic_score = $userinfo['dynamic_score'] + $val['integral_num'];
                $score = $userinfo['score'] + $val['integral_num'];
                try{
                    $sql2 = "UPDATE jxmall_user SET dynamic_score = {$dynamic_score},score = {$score} WHERE id = {$val['user_id']}";
                    $user = $pdo -> exec($sql2);
                    if($val['finish_period'] == 11){
                        $status = 2;
                    }else{
                        $status = 1;
                    }
                    $sql3 = "UPDATE jxmall_integral SET finish_period = finish_period + 1,status = {$status},next_publish_time = {$next_publish_time} WHERE id = {$val['id']}";
                    $integral = $pdo -> exec($sql3);
                    $finish_period = $val['finish_period'] + 1;
                    $mag = "用户充值积分券 发放进度({$finish_period}/12)";
                    $sql4 = "UPDATE jxmall_integral_record SET before_money = '{$userinfo['dynamic_score']}',`desc` = '$mag',expire_time = {$expire_time} where source_id = {$val['id']}";
                    $integral_record = $pdo -> exec($sql4);
                    $pdo -> commit();
                }catch (\Exception $e){
                    $pdo -> rollBack();
                    var_dump($e -> getMessage());
                }

            }
        }

    }
}
$Integral = new Integral();
$Integral -> getUserInfo();




