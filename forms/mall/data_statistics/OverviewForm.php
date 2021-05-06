<?php
/**
 * Created by PhpStorm.
 * User: 阿源
 * Date: 2020/10/23
 * Time: 14:35
 */
namespace app\forms\mall\data_statistics;


use app\core\ApiCode;
use app\models\BaseModel;
use app\models\DistrictArr;
use app\models\Goods;
use app\models\GoodsWarehouse;
use app\models\MemberLevel;
use app\models\OrderDetail;
use app\models\StatisticsRecord;
use app\models\StatisticsVirtualConfig;
use GuzzleHttp\Client;



class OverviewForm extends BaseModel
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


    //数字大屏
    public function search(){
        $config = $this->getConfig();
        $this->config = $config;
        $list = [];
        //获取昨日所有数据并以type为键
        $yesterday = StatisticsRecord::find()->where(['mall_id'=>\Yii::$app->mall->id,'date'=>strtotime(date ( "Y-m-d" , strtotime ( "-1 day" )))])->asArray()->all();
        $yesterday = $this->arrayUnderReset($yesterday,'type');
        $this->yesterday = $yesterday;

        //获取今日所有数据并以type为键
        $today = StatisticsRecord::find()->where(['mall_id'=>\Yii::$app->mall->id,'date'=>strtotime(date ( "Y-m-d"))])->asArray()->all();
        $today = $this->arrayUnderReset($today,'type');
        $this->today = $today;

        $status = $config['set_type'];//是否开启虚拟数据

        //访客量
        $visitor = $this->calculation($config,$today,$yesterday,self::VISITOR_NUM,self::VISITOR_NUM);
        $list['today_visitor_num']      = (int)$visitor['today_num'];
        $list['yesterday_visitor_num']  = (int)$visitor['yesterday_num'];
        $list['visitor_proportion']     = round($visitor['proportion'],2);

        //浏览量
        $browse = $this->calculation($config,$today,$yesterday,self::BROWSE_NUM,self::BROWSE_NUM);
        $list['today_browse_num']      = (int)$browse['today_num'];
        $list['yesterday_browse_num']  = (int)$browse['yesterday_num'];
        $list['browse_proportion']     = round($browse['proportion'],2);


        //等级
        $list['level_list'] = $this->levelAndProvince($config,$today,$yesterday,self::MEMBER_LEVEL,self::MEMBER_LEVEL);

        //交易总额
        $totalTransactions = isset($today[self::TOTAL_TRANSACTIONS]['num'])?$today[self::TOTAL_TRANSACTIONS]['num']:0;
        $totalTransactions = $status==1?$totalTransactions+$config[self::TOTAL_TRANSACTIONS]:$totalTransactions;
        $list['total_transactions'] = (int)$totalTransactions;
        //更新截止时间
        $list['update_time'] = isset($today[self::TOTAL_TRANSACTIONS]['updated_at'])?$today[self::TOTAL_TRANSACTIONS]['updated_at']:0;

        //今日收益
        $todayEarnings = isset($today[self::TODAY_EARNINGS]['num'])?$today[self::TODAY_EARNINGS]['num']:0;
        $todayEarnings = $status==1?$todayEarnings+$config[self::TODAY_EARNINGS]:$todayEarnings;
        $list['today_earnings'] = (int)$todayEarnings;

        //新增用户
        $addUser = isset($today[self::ADD_USER]['num'])?$today[self::ADD_USER]['num']:0;
        $list['add_user'] = (int)($status==1?$addUser+$config[self::ADD_USER]:$addUser);

        //总用户
        $userSum = isset($today[self::USER_SUM]['num'])?$today[self::USER_SUM]['num']:0;
        $list['user_sum'] = (int)($status==1?$userSum+$config[self::USER_SUM]:$userSum);

        //省份
        $list['province_list'] = $this->levelAndProvince($config,$today,$yesterday,self::PROVINCE_DATA,self::PROVINCE_DATA);

        //转化统计率
        //浏览量
        $list['conversion_browse_num'] = $this->getNum(self::BROWSE_SUM_NUM,self::BROWSE_SUM_NUM);
        //访客量
        $list['conversion_visitor_num'] = $this->getNum(self::CONVERSION_VISITOR_NUM,self::CONVERSION_VISITOR_NUM);
        //关注量
        $list['follow_num'] = $this->getNum(self::FOLLOW_NUM,self::FOLLOW_NUM);
        //浏览访客比
        $list['browse_visitor_proportion'] = 0;
        if ($list['conversion_browse_num'] != 0 && $list['conversion_visitor_num'] != 0){
            $list['browse_visitor_proportion'] = round($list['conversion_visitor_num']/$list['conversion_browse_num']*100,2);
        }
        //访客关注比
        $list['visitor_follow_proportion'] = 0;
        if ($list['conversion_visitor_num'] != 0){
            $list['visitor_follow_proportion'] = round($list['follow_num']/$list['conversion_visitor_num']*100,2);
        }
        //浏览关注比
        $list['browse_follow_proportion'] = 0;
        if ($list['conversion_browse_num'] != 0){
            $list['browse_follow_proportion'] = round($list['follow_num']/$list['conversion_browse_num']*100,2);
        }

        //用户购买力
        $purchasingPower = json_decode(isset($yesterday[self::PURCHASING_POWER]['remark'])?$yesterday[self::PURCHASING_POWER]['remark']:null);
        $list['purchasing_power'] = empty($purchasingPower)?$this->purchasingPower():$purchasingPower;
        //用户来源
        $userSource = json_decode(isset($yesterday[self::USER_SOURCE]['remark'])?$yesterday[self::USER_SOURCE]['remark']:null);
        $list['user_source'] = empty($userSource)?$this->userSource():$userSource;

        //下单统计
        //访问量
        $list['order_visit_num'] = $this->getNum(self::BROWSE_SUM_NUM,self::ORDER_VISIT_NUM);
        //下单量
        $list['order_num'] = $this->getNum(self::ORDER_NUM,self::ORDER_NUM);
        //支付量
        $list['pay_num'] = $this->getNum(self::PAY_NUM,self::PAY_NUM);
        //访问下单比
        $list['visit_order_proportion'] = 0;
        if ($list['order_visit_num'] != 0){
            $list['visit_order_proportion'] = round($list['order_num']/$list['order_visit_num']*100,2);
        }
        //访问下单支付比
        $list['order_pay_proportion'] = 0;
        if ($list['order_num'] != 0){
            $list['order_pay_proportion'] = round($list['pay_num']/$list['order_num']*100,2);
        }
        //访问支付支付比
        $list['visit_pay_proportion'] = 0;
        if ($list['order_visit_num'] != 0){
            $list['visit_pay_proportion'] = round($list['pay_num']/$list['order_visit_num']*100,2);
        }


        //获取热销商品
        $list['goods_list'] = $this->getHotSale();

        //消息通知
        $list['notice'] = $this->getExternalData('/web/index.php?r=api/external-call/get-notice','消息通知');


        //论坛
        $list['forum'] = $this->getExternalData('/web/index.php?r=api/external-call/get-forum','论坛');

        $list['services'] = $this->getExternalData('/web/index.php?r=api/external-call/get-services','服务');


        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '操作成功',
            'data'=> $list
        ];

    }


    //获取论坛给外部调用
    public function getForum(){
        //论坛列表
        try {
            $domainName = \Yii::$app->params['api_url'];
            $url = $domainName.'/web/index.php?r=api/external-call/get-forum';

            $res = $this->postData($url);

        } catch (\Exception $exception) {
            return [];
        }
    }
    //获取公告外部调用
    public function getExternalData($url,$remark){
        try {
            $domainName = \Yii::$app->params['api_url'];
            $url = $domainName.$url;
            $headers = ['content-type'=>'application/x-www-form-urlencoded','x-mall-id'=>'5'];

            $client = new Client();
            $response = $client->post($url, [
                'verify' => false,
                'headers' => $headers
            ]);

            $res = $response->getBody()->getContents();

            $res = json_decode($res);
            $res = (array)$res;

            if (!isset($res['code']) || $res['code'] != 0){
                throw new \Exception('获取失败');
            }

            $data = $res['data'];
            foreach ($data as $k=>$v){
                $data[$k] = (array)$v;
            }

            return $data;


        } catch (\Exception $exception) {
            \Yii::warning($remark.'获取失败'.$exception->getMessage());
            return [];
        }
    }


    public function purchasingPower(){
        return [
            ['name' => '分享首页','num'=>0],
            ['name' => '分享海报','num'=>0],
            ['name' => '分享商品','num'=>0],
            ['name' => '分享内容','num'=>0],
            ['name' => '分享视频','num'=>0],
            ['name' => '分享资讯','num'=>0],
            ['name' => '分享名片','num'=>0],
        ];
    }

    public function userSource(){

        return [
            ['name' => '0-200','num'=>0],
            ['name' => '200-400','num'=>0],
            ['name' => '400-600','num'=>0],
            ['name' => '600-800','num'=>0],
            ['name' => '800-1000','num'=>0],
            ['name' => '1000+','num'=>0],
        ];
    }

    //获取热销商品
    public function getHotSale(){
        $query = OrderDetail::find()
            ->alias('o')
            ->where(['o.is_refund' => 0,'g.is_delete' => 0, 'g.status' => 1, 'g.mall_id' => \Yii::$app->mall->id,])
            ->leftJoin(['g' => Goods::tableName()], 'o.id=g.id')
            ->leftJoin(['gw' => GoodsWarehouse::tableName()], 'gw.id=g.goods_warehouse_id');

        $list = $query->groupBy('o.goods_id')->orderBy(['sum(o.num)' => SORT_DESC])
            ->select(['g.id','gw.name','gw.cover_pic','g.virtual_sales','sum(o.num) as num','g.use_virtual_sales'])
            ->limit( 10)
            ->asArray()
            ->all();

        foreach ($list as $k => $v){
            $num = $v['num'];
            if ($v['use_virtual_sales'] == 1){
                $num = $num+$v['virtual_sales'];
            }
            $list[$k]['virtual_sales'] = $num;
        }

        return $list;

    }



    //获取数量
    public function getNum($type,$configType){

        $status = $this->config['set_type'];//是否开启虚拟数据
        $addUser = isset($this->yesterday[$type]['num'])?$this->yesterday[$type]['num']:0;
        return (int)($status==1?$addUser+$this->config[$configType]:$addUser);
    }

    //等级计算
    public function levelAndProvince($config,$today,$yesterday,$type,$field){

        $status = $this->config['set_type'];//是否开启虚拟数据

        $yesterday = isset($yesterday[$type]['remark'])?json_decode($yesterday[$type]['remark']):[];

        $today = isset($today[$type]['remark'])?json_decode($today[$type]['remark']):[];
        $today = (array)$today;

        $config = $status == 1?$config[$field]:[];
        $config = empty($config)?[]:json_decode($config);

        foreach ($yesterday as $k=>$v){
            $yesterday[$k] = (array)$v;
        }
        foreach ($today as $k=>$v){
            $today[$k] = (array)$v;
        }
        foreach ($config as $k=>$v){
            $config[$k] = (array)$v;
        }
        $config = $this->arrayUnderReset($config,'id');


        if ($type==self::MEMBER_LEVEL) {
            return $this->getLevel($config, $today, $yesterday);
        }else{
            return $this->getProvince($config, $yesterday);
        }
    }


    //计算今日明日比例省份
    public function getProvince($config,$yesterday){
        $districtArr = new DistrictArr();
        $districtArr = $districtArr::getArr();

        $status = $this->config['set_type'];//是否开启虚拟数据


        $province = [];
        foreach ($districtArr as $v){
            if ($v['level'] == 'province'){
                $province[] = $v;
            }
        }
        $yesterday = $this->arrayUnderReset($yesterday,'id');


        foreach ($province as $k=>$v){
            $yesNum = isset($yesterday[$v['id']]['num'])?$yesterday[$v['id']]['num']:0;
            $configNum = isset($config[$v['id']]['num'])?$config[$v['id']]['num']:0;
            $province[$k]['num'] = $status==1?$yesNum+$configNum:$yesNum;


        }

        return $province;
    }

    //计算今日明日比例等级
    public function getLevel($config,$today,$yesterday){
        $level = MemberLevel::find()->where(['mall_id'=> \Yii::$app->mall->id,'is_delete'=>0,'status'=>1])->select(['id','name'])->orderBy('level asc')->asArray()->all();

        $status = $this->config['set_type'];//是否开启虚拟数据

        $yesterday = $this->arrayUnderReset($yesterday,'id');
        $today = $this->arrayUnderReset($today,'id');

        foreach ($level as $k=>$v){
            $todayNum = (int)isset($today[$v['id']]['num'])?$today[$v['id']]['num']:0;
            $yesNum = (int)isset($yesterday[$v['id']]['num'])?$yesterday[$v['id']]['num']:0;
            $configNum = (int)isset($config[$v['id']]['num'])?$config[$v['id']]['num']:0;
            $todayNums =  $status==1?$todayNum+$configNum:$todayNum;
            $level[$k]['today_num'] = $todayNums;

            $level[$k]['yesterday_num'] =  $status==1?$yesNum+$configNum:$yesNum;

            $num = $todayNum-$yesNum;
            $level[$k]['proportion']     = round($yesNum==0?$todayNum:$num/$yesNum*100,2);
        }

        return $level;
    }


    //计算数量比例
    public function calculation($config,$today,$yesterday,$type,$field){
        $status = $config['set_type'];//是否开启虚拟数据

        $yesNum = isset($yesterday[$type]['num'])?$yesterday[$type]['num']:0;
        $configNum = isset($config[$field])?$config[$field]:0;
        $todayNum = isset($today[$type]['num'])?$today[$type]['num']:0;

        $list['today_num']      = $status==1?$todayNum+$configNum:$todayNum;

        $list['yesterday_num']  = $status==1?$yesNum+$configNum:$yesNum;

        $browse = $todayNum-$yesNum;
        $list['proportion']     = $yesNum==0?$todayNum:$browse/$yesNum*100;

        return $list;
    }

    //获取配置
    public function getConfig(){
        return StatisticsVirtualConfig::find()->where(['mall_id'=>\Yii::$app->mall->id,'is_delete'=>0])->asArray()->one();
    }



    function postData($url, $param=[], $return_array = true)
    {
        set_time_limit(0);
        $header [] = "content-type: application/x-www-form-urlencoded";
        $header [] = "x-mall-id: 5";
        $ch = curl_init();
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
        $return_array && $res = json_decode($res, true);
        return $res;
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


}