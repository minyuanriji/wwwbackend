<?php

namespace app\forms\mall\data_statistics;

use app\core\ApiCode;
use app\models\BaseModel;
use app\models\DistrictArr;
use app\models\MemberLevel;
use app\models\StatisticsVirtualConfig;

class StatisticsForm extends BaseModel
{

    public $set_type;
    public $user_sum = 0;
    public $visitor_num = 0;
    public $browse_num = 0;
    public $conversion_browse_num = 0;
    public $conversion_visitor_num = 0;
    public $order_visit_num = 0;
    public $order_num = 0;
    public $pay_num = 0;
    public $total_transactions = 0;
    public $today_earnings = 0;
    public $province_data;
    public $member_level;
    public $add_user;
    public $follow_num;

    public function rules()
    {
        return [
            [['set_type'],'required'],
            [[ 'set_type', 'user_sum','visitor_num','browse_num','conversion_browse_num','conversion_visitor_num','follow_num','order_visit_num','order_num','pay_num'], 'integer'],
            [['total_transactions','today_earnings'], 'double'],
            [['province_data','member_level'], 'string'],
            [['user_sum','visitor_num','browse_num','conversion_browse_num','conversion_visitor_num','follow_num','order_visit_num','order_num','pay_num','add_user'] ,'integer', 'min' => 0],
            [['total_transactions','today_earnings'] ,'double', 'min' => 0]
        ];
    }

    /**
     * 获取配置详情
     */
    public function search(){
        try{

            $list = StatisticsVirtualConfig::find()->where(['mall_id'=>\Yii::$app->mall->id,'is_delete'=>0])->select(['set_type', 'user_sum','visitor_num','browse_num','conversion_browse_num','conversion_visitor_num','order_visit_num','order_num','pay_num','total_transactions','today_earnings','add_user','province_data','member_level','follow_num'])->asArray()->one();

            //城市
            if (empty($list['province_data'])){
                $list['province_data'] = [];
            }else{
                $provinceData = json_decode($list['province_data']);
                $list['province_data'] = $this->getProvinceList($provinceData);
            }

            //等级
            if (empty($list['member_level'])){
                $list['member_level'] = $this->getLevelList();
            }else{
                
                $memberLevel = json_decode($list['member_level']);
                $list['member_level'] = $this->getLevelList($memberLevel);
            }

            
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '操作成功',
                'data'=> $list
            ];


        }catch (\Exception $exception) {
            return $this->returnApiResultData(ApiCode::CODE_FAIL,$exception->getMessage());
        }

    }

    //重组等级配置
    public function getLevelList($data=[]){
        $level = MemberLevel::find()->where(['mall_id'=> \Yii::$app->mall->id,'is_delete'=>0,'status'=>1])->select(['id','name'])->orderBy('level asc')->asArray()->all();

        foreach ($data as $k=>$v){
            $data[$k] = (array)$v;
        }
        $data = $this->arrayUnderReset($data,'id');

        foreach ($level as $k=>$v){
            if (isset($data[$v['id']])){
                $level[$k]['num'] = $data[$v['id']]['num'];
            }else{
                $level[$k]['num'] = 0;
            }
        }

        return $level;
    }

    //重组省份配置
    public function getProvinceList($data){
        $provinces = $this->getProvince();

        $province = $this->arrayUnderReset($provinces['data'],'id');

        $list = [];
        foreach ($data as $v){
            if (isset($province[$v->id])){
                $list[] = [
                    'id'    => $v->id,
                    'name'  => $province[$v->id]['name'],
                    'num'   => $v->num,
                ];
            }
        }

        return $list;
    }


    /**
     * 编辑配置
     */
    public function save(){
       if (!$this->validate()) {
           return $this->responseErrorInfo();
       }
        try {
            $config = StatisticsVirtualConfig::findOne(['mall_id'=> \Yii::$app->mall->id]);
            if (!$config) {
                $config = new StatisticsVirtualConfig();
                $config->mall_id = \Yii::$app->mall->id;
                $config->created_at = time();
            }
            $config->attributes = $this->attributes;
            $config->is_delete = 0;
            $config->updated_at = time();

//
//            $this->province_data = [
//                [
//                    'id'=>2,
//                    'num'=>5
//                ],
//                [
//                    'id'=>20,
//                    'num'=>50
//                ],
//                [
//                    'id'=>2302,
//                    'num'=>20
//                ],
//
//            ];
//            $this->member_level = [
//                [
//                    'id'=>2,
//                    'num'=>5
//                ],
//                [
//                    'id'=>3,
//                    'num'=>1
//                ],
//                [
//                    'id'=>1,
//                    'num'=>20
//                ],
//
//            ];

            if (!empty($this->province_data)){//省份
                //验证数据是否有不存在的
                $provinceStatus = $this->getProvinceStatus($this->province_data);
                if (!$provinceStatus['status'])throw  new \Exception('请检查省份参数设置');
                $config->province_data = json_encode($provinceStatus['data']);
            }
            
            if (!empty($this->member_level)){//等级
                //验证数据是否有不存在的
                $levelStatus = $this->getLevelStatus($this->member_level);
                if (!$levelStatus['status'])throw  new \Exception('请检查等级参数设置');
                $config->member_level = json_encode($levelStatus['data']);
            }

            if (!$config->save()) {
                throw new \Exception($this->responseErrorMsg($config));
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '操作成功',
            ];

        }catch (\Exception $exception) {
            return $this->returnApiResultData(ApiCode::CODE_FAIL,$exception->getMessage());
        }
    }

    /**
     * 获取省份
     * @return array
     */
    public function getProvince(){

        $districtArr = new DistrictArr();
        $districtArr = $districtArr::getArr();


        $data = [];
        foreach ($districtArr as $v){
            if ($v['level'] == 'province'){
                $data[] = $v;
            }
        }
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '',
            'data' => $data
        ];
    }


    /**
     * @param $data
     * @return bool
     */
    public function getProvinceStatus($data){
        $data = json_decode($data);
        $list = $this->getProvince();

        $list = $this->arrayUnderReset($list['data'],'id');
        $array = [];
        foreach ($data as $k=>$v){
            $v = (array)$v;
            if (!isset($v['num']) || $v['num'] < 0) return ['status'=>false];
            if (!isset($v['id'])  || !isset($list[$v['id']])) return ['status'=>false];

            $array[] = [
                'id' => $v['id'],
                'num'=> $v['num']
            ];
        }
        return ['status'=>true,'data'=>$array];

    }


    //获取等级状态
    public function getLevelStatus($data){
        $data = json_decode($data);
        $level = MemberLevel::find()->where(['mall_id'=> \Yii::$app->mall->id,'is_delete'=>0,'status'=>1])->select(['id','name'])->orderBy('level asc')->asArray()->all();

        $level = $this->arrayUnderReset($level,'id');

        $array = [];
        foreach ($data as $k=>$v){
            $v = (array)$v;
            if (!isset($v['num']) || $v['num'] < 0)  return ['status'=>false];
            if (!isset($v['id'])  || !isset($level[$v['id']])) return ['status'=>false];

            $array[] = [
                'id' => $v['id'],
                'num'=> $v['num']
            ];
        }
        return ['status'=>true,'data'=>$array];
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