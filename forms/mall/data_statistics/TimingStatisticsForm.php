<?php
/**
 * Created by PhpStorm.
 * User: 阿源
 * Date: 2020/10/23
 * Time: 18:24
 */

namespace app\forms\mall\data_statistics;

use app\models\BaseModel;
use app\models\DistrictArr;
use app\models\Mall;
use app\models\MemberLevel;
use app\models\Order;
use app\models\StatisticsBrowseLog;
use app\models\StatisticsRecord;
use app\models\User;
use jianyan\easywechat\Wechat;

class TimingStatisticsForm extends BaseModel
{
    public $config;
    public $yesterday;
    public $today;

    const VISITOR_NUM = 'visitor_num';//访客量
    const BROWSE_NUM = 'browse_num';//浏览量
    const MEMBER_LEVEL = 'member_level';//用户等级
    const TOTAL_TRANSACTIONS = 'total_transactions';//交易总额设置（元）
    const TODAY_EARNINGS = 'today_earnings';//今日收益（元）
    const USER_SUM = 'user_sum';//用户总数（人）
    const PROVINCE_DATA = 'province_data';//省份数据
    const BROWSE_SUM_NUM = 'conversion_browse_num';//浏览量(总）
    const CONVERSION_VISITOR_NUM = 'conversion_visitor_num';//转化访客量
    const FOLLOW_NUM = 'follow_num';//关注量
    const ORDER_NUM = 'order_num';//下单数量
    const PAY_NUM = 'pay_num';//支付人数
    const ORDER_VISIT_NUM = 'order_visit_num';//下单处的访问量
    const ADD_USER = 'add_user';//新增用户
    const PURCHASING_POWER = 'purchasing_power';//购买力
    const USER_SOURCE = 'user_source';//用户来源
    const ONE_DAY = 1;//每天一次
    const ONE_HOUR = 0;//每小时一次


    public function updateDay(){
        $list = Mall::find()->where(['is_delete'=>0])->asArray()->all();
        foreach ($list as $k=>$v){
            $this->provinceData($v['id']);//城市
            $this->purchasingPower($v['id']);//购买力
            $this->userSource($v['id']);//用户来源
            $this->conversionBrowseNum($v['id']);//浏览量总
            $this->conversionVisitorNum($v['id']);//访客量总
            $this->orderNum($v['id']);//下单量
            $this->payNum($v['id']);//支付数
            $this->levelDay($v['id']);//等级单日
            $this->visitorDay($v['id']);//访客单日
            $this->browseDay($v['id']);//浏览量单日
            $this->followNum($v['id']);//关注量
        }
    }

    public function updateHour(){
        $list = Mall::find()->where(['is_delete'=>0])->asArray()->all();
        foreach ($list as $k=>$v){
            $this->levelData($v['id']);//统计等级
            $this->visitorNum($v['id']);//访客数
            $this->browseNum($v['id']);//浏览量
            $this->totalTransactions($v['id']);//交易总额
            $this->todayEarnings($v['id']);//今日收益（元）
            $this->addUser($v['id']);//新增用户
            $this->userSum($v['id']);//总用户
        }
    }

    //关注量
    public function followNum($mallId){
        $begTime = strtotime(date("Y-m-d", strtotime("-1 day")));
        $access_token = '';
        $num = 0;
        try{
            /** @var Wechat $wechatModel */
            $wechatModel = \Yii::$app->wechat;
            $access_token = $wechatModel->app->access_token->getToken();
        }catch (\Exception $exception) {
            \Yii::warning('统计数据时，更新关注量数据失败，失败原因，获取access_token失败'.$exception->getMessage());
        }

        if (empty($access_token)){
            \Yii::warning('统计数据时，更新关注量数据失败，失败原因，获取access_token失败');
        }else {


            $url = 'https://api.weixin.qq.com/cgi-bin/user/get?access_token=' . $access_token['access_token'];

            $res = $this->post_data($url);

            if (isset($res['errcode'])) \Yii::warning('统计数据时，更新关注量数据失败，微信返回错误码：' . $res['errcode'] . '，错误描述：' . $res['errmsg']);

            if (!isset($res['total'])) \Yii::warning('统计数据时，更新关注量数据失败，微信返回错误：' . json_encode($res));
            $num = $res['total'];
        }

        if (!$this->updateData($mallId,self::FOLLOW_NUM,$begTime,$num,[],self::ONE_DAY))\Yii::warning('统计数据时，更新单小时访客数总数据失败');
    }

    //访客数
    public function visitorNum($mallId){
        //今天的时间
        $begTime = strtotime(date("Y-m-d"));
        $endTime = time();
        $idSum = StatisticsBrowseLog::find()->where(['mall_id'=>$mallId,'type'=>0])->andWhere(['between','created_at',$begTime,$endTime])->groupBy('user_id')->count();  //更新数据
        $ipSum = StatisticsBrowseLog::find()->where(['mall_id'=>$mallId,'type'=>1])->groupBy('user_ip')->count();  //更新数据

        $sum = $idSum+$ipSum;
        if (!$this->updateData($mallId,self::VISITOR_NUM,$begTime,$sum,[],self::ONE_HOUR))\Yii::warning('统计数据时，更新单小时访客数总数据失败');
    }

    //统计等级
    public function levelData($mallId){
        $level = MemberLevel::find()->where(['mall_id'=> $mallId,'is_delete'=>0,'status'=>1])->select(['id'])->orderBy('level asc')->asArray()->all();

        $begTime = strtotime(date("Y-m-d"));
        $endTime = time();
        foreach ($level as $k => $v) {
            //查询昨天新增的数据
            $level[$k]['num'] = User::find()->where(['between', 'upgrade_time', $begTime, $endTime])->andWhere(['mall_id' => $mallId, 'level' => $v['id']])->count();
        }
        
        $level = array_values($level);

        //更新数据
        if (!$this->updateData($mallId,self::MEMBER_LEVEL,$begTime,0,$level,self::ONE_HOUR))\Yii::warning('统计数据时，更新等级数据失败');
    }

    //浏览量
    public function browseNum($mallId){
        //昨天的时间
        $begTime = strtotime(date("Y-m-d"));
        $endTime = time();
        $sum = StatisticsBrowseLog::find()->where(['mall_id'=>$mallId])->andWhere(['between', 'created_at', $begTime, $endTime])->count();  //更新数据
        if (!$this->updateData($mallId,self::BROWSE_NUM,$begTime,$sum,[],self::ONE_HOUR))\Yii::warning('统计数据时，更新浏览量数据失败');
    }

    //交易总额
    public function totalTransactions($mallId){
        $begTime = strtotime(date("Y-m-d"));
        $sum = Order::find()->where(['mall_id'=>$mallId,'is_pay'=>1])->select(['sum(total_pay_price) as money'])->asArray()->one();  //更新数据
        $sum = empty($sum['money'])?0:$sum['money'];
        if (!$this->updateData($mallId,self::TOTAL_TRANSACTIONS,$begTime,$sum,[],self::ONE_DAY))\Yii::warning('统计数据时，更新支付数据失败');
    }

    //今日收益（元）
    public function todayEarnings($mallId){
        $begTime = strtotime(date("Y-m-d"));
        $endTime = time();
        $sum = Order::find()->where(['mall_id'=>$mallId,'is_pay'=>1])->andWhere(['between', 'pay_at', $begTime, $endTime])->select(['sum(total_pay_price) as money'])->asArray()->one();  //更新数据
        $sum = empty($sum['money'])?0:$sum['money'];
        if (!$this->updateData($mallId,self::TODAY_EARNINGS,$begTime,$sum,[],self::ONE_DAY))\Yii::warning('统计数据时，更新今日收益数据失败');
    }

    //新增用户
    public function addUser($mallId){
        $begTime = strtotime(date("Y-m-d"));
        $endTime = time();
        $sum = User::find()->where(['mall_id'=>$mallId])->andWhere(['between', 'created_at', $begTime, $endTime])->count();  //更新数据
        if (!$this->updateData($mallId,self::ADD_USER,$begTime,$sum,[],self::ONE_DAY))\Yii::warning('统计数据时，更新新增用户数据失败');
    }

    //总用户
    public function userSum($mallId){
        $begTime = strtotime(date("Y-m-d"));
        $sum = User::find()->where(['mall_id'=>$mallId])->count();  //更新数据
        if (!$this->updateData($mallId,self::USER_SUM,$begTime,$sum,[],self::ONE_DAY))\Yii::warning('统计数据时，更新总用户数据失败');
    }




    //浏览量单日
    public function browseDay($mallId){
        //昨天的时间
        $begTime = strtotime(date("Y-m-d", strtotime("-1 day")));
        $endTime = strtotime(date("Y-m-d")) - 1;
        $sum = StatisticsBrowseLog::find()->where(['mall_id'=>$mallId])->andWhere(['between', 'created_at', $begTime, $endTime])->count();  //更新数据
        if (!$this->updateData($mallId,self::BROWSE_NUM,$begTime,$sum,[],self::ONE_DAY))\Yii::warning('统计数据时，更新单日浏览量数据失败');
    }

    //访客数
    public function visitorDay($mallId)
    {
        //昨天的时间
        $begTime = strtotime(date("Y-m-d", strtotime("-1 day")));
        $endTime = strtotime(date("Y-m-d")) - 1;
        $idSum = StatisticsBrowseLog::find()->where(['mall_id' => $mallId, 'type' => 0])->andWhere(['between', 'created_at', $begTime, $endTime])->groupBy('user_id')->count();  //更新数据
        $ipSum = StatisticsBrowseLog::find()->where(['mall_id' => $mallId, 'type' => 1])->groupBy('user_ip')->count();  //更新数据

        $sum = $idSum + $ipSum;
        if (!$this->updateData($mallId, self::VISITOR_NUM, $begTime, $sum, [], self::ONE_DAY)) \Yii::warning('统计数据时，更新每日访客数总数据失败');

    }


    //等级单日
    public function levelDay($mallId){
        $level = MemberLevel::find()->where(['mall_id'=> $mallId,'is_delete'=>0,'status'=>1])->select(['id'])->orderBy('level asc')->asArray()->all();

        //昨天的时间
        $begTime = strtotime(date("Y-m-d",strtotime("-1 day")));
        $endTime = strtotime(date("Y-m-d"))-1;
        foreach ($level as $k => $v) {
            //查询昨天新增的数据
            $level[$k]['num'] = User::find()->where(['between', 'upgrade_time', $begTime, $endTime])->andWhere(['mall_id' => $mallId, 'level' => $v['id']])->count();
        }
        //更新数据
        if (!$this->updateData($mallId,self::MEMBER_LEVEL,$begTime,0,$level,self::ONE_HOUR))\Yii::warning('统计数据时，更新昨天的等级数据失败');

    }

    //浏览量总
    public function conversionBrowseNum($mallId){
        $begTime = strtotime(date ( "Y-m-d" , strtotime ( "-1 day" )));
        $sum = StatisticsBrowseLog::find()->where(['mall_id'=>$mallId])->count();  //更新数据
        if (!$this->updateData($mallId,self::BROWSE_SUM_NUM,$begTime,$sum,[],self::ONE_DAY))\Yii::warning('统计数据时，更新浏览量总数据失败');

    }

    //访客量总
    public function conversionVisitorNum($mallId){
        $begTime = strtotime(date ( "Y-m-d" , strtotime ( "-1 day" )));
        $idSum = StatisticsBrowseLog::find()->where(['mall_id'=>$mallId,'type'=>0])->groupBy('user_id')->count();  //更新数据
        $ipSum = StatisticsBrowseLog::find()->where(['mall_id'=>$mallId,'type'=>1])->groupBy('user_ip')->count();  //更新数据

        $sum = $idSum+$ipSum;
        if (!$this->updateData($mallId,self::CONVERSION_VISITOR_NUM,$begTime,$sum,[],self::ONE_DAY))\Yii::warning('统计数据时，更新访客量总数据失败');
    }

    //下单量
    public function orderNum($mallId){
        $begTime = strtotime(date ( "Y-m-d" , strtotime ( "-1 day" )));
        $sum = Order::find()->where(['mall_id'=>$mallId])->count();  //更新数据
        if (!$this->updateData($mallId,self::ORDER_NUM,$begTime,$sum,[],self::ONE_DAY))\Yii::warning('统计数据时，更新下单量数据失败');
    }

    //支付量
    public function payNum($mallId){
        $begTime = strtotime(date ( "Y-m-d" , strtotime ( "-1 day" )));
        $sum = Order::find()->where(['mall_id'=>$mallId,'is_pay'=>1])->count();  //更新数据
        if (!$this->updateData($mallId,self::PAY_NUM,$begTime,$sum,[],self::ONE_DAY))\Yii::warning('统计数据时，更新支付数据失败');
    }

    //统计城市
    public function provinceData($mallId){
        $districtArr = new DistrictArr();
        $districtArr = $districtArr::getArr();


        $data = [];
        foreach ($districtArr as $k=>$v){
            if ($v['level'] == 'province'){
                $data[] = [
                    'id' => $v['id'],
                    'num'=>0
                ];
            }
        }

        $list = $this->arrayUnderReset($data,'id');

        $order = Order::find()->where(['mall_id'=>$mallId])->groupBy('user_id')->addGroupBy('province_id')->select(['id','user_id','province_id','count(1) as num'])->asArray()->all();

        foreach ($order as $v){
            if (isset($list[$v['province_id']])){
                $list[$v['province_id']]['num']+=$v['num'];
            }
        }
        $list = array_values($list);

        //昨天的时间
        $begTime = strtotime(date("Y-m-d",strtotime("-1 day")));
        //更新数据
        if (!$this->updateData($mallId,self::PROVINCE_DATA,$begTime,0,$list,self::ONE_DAY))\Yii::warning('统计数据时，更新省份数据数据失败');
    }


    //购买力
    public function purchasingPower($mallId){
        $data = [
            ['name' => '0-200','num'=>0],
            ['name' => '200-400','num'=>0],
            ['name' => '400-600','num'=>0],
            ['name' => '600-800','num'=>0],
            ['name' => '800-1000','num'=>0],
            ['name' => '1000+','num'=>0],
        ];
        //昨天的时间
        $begTime = strtotime(date("Y-m-d",strtotime("-1 day")));

        //分组统计订单
        $order = Order::find()->where(['mall_id'=>$mallId])->groupBy('user_id')->select(['user_id','sum(total_pay_price) as money','count(1) as num'])->asArray()->all();

        foreach ($order as $v){
            //客单价
            $averageMoney = round($v['money']/$v['num'],2);
            switch ($averageMoney){
                case $averageMoney > 0 && $averageMoney <= 200://大于0小于两百
                    $data[0]['num']+=1;
                    break;
                case $averageMoney > 200 && $averageMoney <= 400:
                    $data[1]['num']+=1;
                    break;
                case $averageMoney > 400 && $averageMoney <= 600:
                    $data[2]['num']+=1;
                    break;
                case $averageMoney > 600 && $averageMoney <= 800:
                    $data[3]['num']+=1;
                    break;
                case $averageMoney > 800 && $averageMoney <= 1000:
                    $data[4]['num']+=1;
                    break;
                case $averageMoney > 1000:
                    $data[5]['num']+=1;
                    break;
                default:
                    break;
            }

        }

        //更新数据
        if (!$this->updateData($mallId,self::PURCHASING_POWER,$begTime,0,$data,self::ONE_DAY))\Yii::warning('统计数据时，更新用户购买力数据失败');

    }


    //用户来源
    public function userSource($mallId){
        $data = [
            ['name' => '分享首页','num'=>0],
            ['name' => '分享海报','num'=>0],
            ['name' => '分享商品','num'=>0],
            ['name' => '分享内容','num'=>0],
            ['name' => '分享视频','num'=>0],
            ['name' => '分享资讯','num'=>0],
            ['name' => '分享名片','num'=>0],
        ];
        //昨天的时间
        $begTime = strtotime(date("Y-m-d",strtotime("-1 day")));

        $user = User::find()->where(['mall_id'=>$mallId])->select(['source','count(1) as num'])->groupBy('source')->asArray()->all();
        foreach ($user as $v){
            if ($v['source'] != 0){
                $data[$v['source']-1]['num'] = $v['num'];
            }
        }

        //更新数据
        if (!$this->updateData($mallId,self::USER_SOURCE,$begTime,0,$data,self::ONE_DAY))\Yii::warning('统计数据时，更新用户来源数据失败');
    }





    public function updateData($mallId,$type,$begTime,$num=0,$data=[],$updateType){
        $log = StatisticsRecord::find()->where(['mall_id'=>$mallId,'type'=>$type])->andWhere(['=','date',$begTime])->one();
        if (!$log) {
            $log = new StatisticsRecord();
        }
        $log->type = $type;
        $log->mall_id = $mallId;
        $log->num = round($num,2);
        $log->update_type = $updateType;
        $log->remark = json_encode($data);
        $log->date = $begTime;
        if (!$log->save())return false;

    }




    /**
     * 返回以原数组某个值为下标的新数据
     *
     * @param array $array
     * @param string $key
     * @param int $type 1一维数组2二维数组
     * @return array
     */
    public function arrayUnderReset($array, $key, $type = 1)
    {
        if (is_array($array)) {
            $tmp = array();
            foreach ($array as $v) {
                if ($type === 1) {
                    $tmp[$v[$key]] = $v;
                }
                elseif ($type === 2) {
                    $tmp[$v[$key]][] = $v;
                }
            }
            return $tmp;
        }
        else {
            return $array;
        }
    }

    /**
     * 以POST方式提交数据
     * @param $url                      地址
     * @param $param                    数组参数
     * @param bool $is_file             是否文件
     * @param bool $return_array        是否返回数组
     * @param bool $is_wf_json          是否进行微信模式的格式化：false：普通json格式
     * @return mixed
     */
    function post_data($url, $param=[], $is_file = false, $return_array = true, $is_wf_json = true)
    {
        set_time_limit(0);
        if ($is_file) {
            $header [] = "content-type: multipart/form-data; charset=UTF-8";
        } else {
            $header [] = "content-type: application/json; charset=UTF-8";
        }
        $ch = curl_init();

        if (class_exists('\CURLFile')) { // php5.5跟php5.6中的CURLOPT_SAFE_UPLOAD的默认值不同
            curl_setopt($ch, CURLOPT_SAFE_UPLOAD, true);
            if (!empty($param['media']) && !is_object($param['media'])) {
                $param['media'] = new CURLFile(ltrim($param['media'], '@'));//5.5以上去除@
            }
        } else {
            if (defined('CURLOPT_SAFE_UPLOAD')) {
                curl_setopt($ch, CURLOPT_SAFE_UPLOAD, false);
            }
        }

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)');
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $param);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $res = curl_exec($ch);
        $flat = curl_errno($ch);
        if ($flat) {
            $data = curl_error($ch);
        }

        curl_close($ch);
        // if($is_file)var_dump($res);
        $return_array && $res = json_decode($res, true);

        // var_dump($res);exit;
        return $res;
    }

}