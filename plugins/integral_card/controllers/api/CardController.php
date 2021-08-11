<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-06-29
 * Time: 16:51
 */

namespace app\plugins\integral_card\controllers\api;

use app\controllers\api\filters\LoginFilter;
use app\models\Integral;
use app\models\IntegralDeduct;
use app\models\IntegralRecord;
use app\models\User;
use app\plugins\integral_card\controllers\ApiBaseController;
use app\plugins\integral_card\models\CardDetail;
use Yii;

class CardController extends ApiBaseController
{
    public $pageSize = 10;
    //poster $page;

    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'login' => [
                'class' => LoginFilter::class,
            ],
        ]);
    }

    /**
     * 积分券充值
     * @Author bing
     * @DateTime 2020-10-10 19:48:51
     * @return \yii\web\Response
     */
    public function actionRecharge(){
        $params = $this->requestData;
        $serialize_no = $params['serialize_no'] ?? '';
        $use_code = $params['use_code'] ?? '';
        $user_id = Yii::$app->user->id;
        $card = CardDetail::recharge($serialize_no,$use_code,$user_id);
        if($card === false)  return $this->error(CardDetail::getError());
        $unit_list = Integral::$unit_list;
        $integral_setting= json_decode($card->integral_setting,true);
        return $this->success('充值成功',compact('integral_setting','unit_list'));
    }

    /**
     * 积分券充值列表
     * @Author bing
     * @DateTime 2020-10-10 19:48:51
     * @copyright: Copyright (c) 2020 广东七件事集团
     * @return \yii\web\Response
     */
    public function actionRechargeRecord(){
        $params = $this->requestData;
        $page = $params['page'] ?? 1;
        $user_id = Yii::$app->user->id;
        $where = array(
            ['=','picker_id',$user_id],
            ['=','mall_id',Yii::$app->mall->id ?? 0],
            ['=','is_delete',0]

        );
        $params = array(
            'select'=>'id,card_id,serialize_no,status,integral_setting,updated_at,created_at',
            'with'=>['card'],
            'where' => $where,
            'page'=>$page,
            'limit' => $this->pageSize,
            'order' => 'id DESC'
        );
        $data = CardDetail::listPage($params,false,true);
        if(!empty($data)){
            foreach($data['list'] as $key => $card){
                $data['list'][$key]['created_at'] = !empty($card['updated_at']) ? $card['updated_at'] : $card['created_at'];
                $data['list'][$key]['integral_setting'] = json_decode($card['integral_setting'],true);
            }
        }
        return $this->success('success',$data);
    }

    /**
     * 积分券列表
     * @Author bing
     * @DateTime 2020-10-10 19:48:51
     * @copyright: Copyright (c) 2020 广东七件事集团
     * @return \yii\web\Response
     */
    public function actionMyIntegral(){
        $params = $this->requestData;
        $type = $params['type'] ?? 0;
        $page = $params['page'] ?? 1;
        $status = $params['status'] ?? 0;
        $user_id = Yii::$app->user->id;
        $where = array(
            ['=','cd.user_id',$user_id],
            ['=','cd.mall_id',Yii::$app->mall->id ?? 0]
        );
        if($type == 1){
            $where[] = ['not like','cd.integral_setting','"expire":"-1"'];
        }else{
            $where[] = ['like','cd.integral_setting','"expire":"-1"'];

        }

        if($status == 1){
            //$status = 0; //未充值
            $where[] = ['=','cd.status',0];
        }else if($status == 2){
            //$status = 1; //已充值
            $where[] = ['=','cd.status',1];
        }else if($status == 3){
            //$status = 2; //已过期
            $where[] = ['=','cd.status',2];
        }else if($status == 4){
            //$status = -1; //禁用
            $where[] = ['=','cd.status',-1];
        }

        $params = array(
            'alias' => 'cd',
            'select'=>'cd.id,cd.card_id,cd.serialize_no,cd.use_code,cd.status,cd.integral_setting,cd.expire_time,cd.created_at,cd.updated_at,cd.picker_id',
            'with'=>['card','picker'],
            'where' => $where,
            'page'=>$page,
            'limit' => $this->pageSize,
            'order' => 'updated_at DESC'
        );
        $data = CardDetail::listPage($params,false,true);
        if(!empty($data)){
            foreach($data['list'] as $key => $card){
                $data['list'][$key]['integral_setting'] = json_decode($card['integral_setting'],true);
            }
        }
        return $this->success('success',$data);
    }
}