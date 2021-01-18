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
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'login' => [
                'class' => LoginFilter::class,
            ],
        ]);
    }

    /**
     * 购物券充值
     * @Author bing
     * @DateTime 2020-10-10 19:48:51
     * @copyright: Copyright (c) 2020 广东七件事集团
     * @return void
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
     * 购物券充值列表
     * @Author bing
     * @DateTime 2020-10-10 19:48:51
     * @copyright: Copyright (c) 2020 广东七件事集团
     * @return void
     */
    public function actionRechargeRecord(){
        $user_id = Yii::$app->user->id;
        $where = array(
            ['=','picker_id',$user_id],
            ['=','mall_id',Yii::$app->mall->id ?? 0],
            ['=','is_delete',0]

        );
        $params = array(
            'select'=>'id,card_id,serialize_no,status,integral_setting,created_at',
            'with'=>['card'],
            'where' => $where,
            'limit' => $this->pageSize,
            'order' => 'id DESC'
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