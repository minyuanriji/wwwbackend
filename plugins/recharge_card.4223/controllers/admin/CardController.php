<?php
namespace app\plugins\recharge_card\controllers\admin;

use app\core\CsvExport;
use app\models\User;
use app\plugins\recharge_card\controllers\BaseController;
use app\plugins\recharge_card\models\Card;
use app\plugins\recharge_card\models\CardDetail;
use app\plugins\recharge_card\models\ProfitAgent;
use app\plugins\recharge_card\models\ProfitCard;
use app\plugins\recharge_card\models\ProfitCardDetail;
use Da\QrCode\Contracts\ErrorCorrectionLevelInterface;
use Da\QrCode\QrCode;
use Yii;

/**
 * 经销商控制器
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Author: bing
 */
class CardController extends BaseController{

    public $pageSize = 10;
    public function actionIndex(){
        if (\Yii::$app->request->isAjax) {
            $request = Yii::$app->request;
            $keyword = $request->get('keyword', '');
            $where = array(['=', 'c.mall_id', Yii::$app->mall->id]);
            // $keyword && $where[] = array('like', 'user.nickname',$keyword.'%', false);
            $params = array(
                'alias' => 'c',
                'where' => $where,
                'joinWith' => [
                    'user' => function ($query) use ($keyword) {
                        $keyword && $query->alias('u')->orWhere(['or',['like', 'u.nickname', $keyword . '%', false], ['like', 'u.username', $keyword . '%', false]]);
                    },
                ],
                'limit' => $this->pageSize,
                'order' => 'id ASC'
            );
            //获取数据
            $data = Card::listPage($params,false,true);
            foreach($data['list'] as $key => $card_info){
                $data['list'][$key]['integral_setting'] = json_decode($card_info['integral_setting']);
            }
            return $this->success('请求成功', $data);
        } else {
            return $this->render('index');
        }
    }


    /**
     * 编辑
     * @Author bing
     * @DateTime 2020-10-08 19:27:55
     * @copyright: Copyright (c) 2020 广东七件事集团
     * @return void
     */
    public function actionEdit(){
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $post = Yii::$app->request->post('form');
                $res = Card::setData($post);
                if($res === false) return $this->error(Card::getError());
                return $this->success('success');
            } else {
                $id =  Yii::$app->request->get('id');
                $info = Card::getData($id);
                return $this->success('success',compact('info'));
            }
        } else {
            return $this->render('edit');
        }
    }


    /**
     * 模糊查找代理
     * @Author bing
     * @DateTime 2020-10-10 16:57:40
     * @copyright: Copyright (c) 2020 广东七件事集团
     * @return void
     */
    public function actionFindAgent(){
        $request = Yii::$app->request;
        $keyword = $request->get('keyword', '');
        $where = array(
            ['=', 'mall_id', Yii::$app->mall->id],
            ['or',['like', 'username', $keyword],['like', 'nickname', $keyword],['like', 'mobile', $keyword]]
        );
        $params = array(
            'select' => 'id,nickname,username',
            'where' => $where,
            'limit' => 100,
            'order' => 'id ASC'
        );
        $pageData = User::listPage($params,false,true);
        return $this->success('success',$pageData);
    }

    /**
     * 查找卡券
     * @Author bing
     * @DateTime 2020-10-10 16:57:40
     * @copyright: Copyright (c) 2020 广东七件事集团
     * @return void
     */
    public function actionFindCard(){
        $request = Yii::$app->request;
        $post = $request->post();
        $integral_setting = $post['integral_setting'];
        $fee = $post['fee'];
        $expire_time = $post['expire_time'];
        $id = $post['id'];

        $is_integral_num = $integral_setting['integral_num'] ? $integral_setting['integral_num'] : '';
        $is_period = $integral_setting['period'] ? $integral_setting['period'] : '';
        $is_period_unit = $integral_setting['period_unit'] ? $integral_setting['period_unit'] : '';
        $is_expire = $integral_setting['expire'] ? $integral_setting['expire'] : '';

       
        $list = Card::find()
        ->where("
            integral_setting->'$.integral_num' = '".$is_integral_num."' and 
            integral_setting->'$.period' = '".$is_period."' and 
            integral_setting->'$.period_unit' = '".$is_period_unit."' and 
            integral_setting->'$.expire' = '".$is_expire."' and 
            expire_time = '".$expire_time."' and 
            fee = '".$fee."' and 
            mall_id = ".Yii::$app->mall->id." and 
            id <> ".$id
            )
        ->with(['user'])
        ->asArray()
        ->all();
        return $this->success('success',$list);
    }

    /**
     * 批量变更归属人
     * @Author bing
     * @DateTime 2020-10-12 10:45:18
     * @copyright: Copyright (c) 2020 广东七件事集团
     * @return void
     */
    public function actionChangeAgent(){
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $card_name = \Yii::$app->request->post('card_name');
                $user_id = \Yii::$app->request->post('user_id');
                $new_user_id = \Yii::$app->request->post('new_user_id');
                $card_id = \Yii::$app->request->post('card_id');
                $current_batch = \Yii::$app->request->post('current_batch');
                $batch_ids = \Yii::$app->request->post('batch_ids');
                $mall_id = \Yii::$app->mall->id;
                $count_batch_ids = -count($batch_ids);


                $card = Card::find()->where(['id' => $card_id, 'mall_id'=>$mall_id])->asArray()->one();
                if($current_batch == 'copy'){
                    $agent = User::findOne(['id' => $new_user_id, 'mall_id'=>$mall_id, 'is_delete' => 0]);
                    if (!$agent) {
                        return $this->error('该代理商不存在或者已被删除！');
                    }
                    $data = [
                        'mall_id' => $mall_id,
                        'user_id' => $new_user_id,
                        'name' => $card_name,
                        'integral_setting' => json_decode($card['integral_setting']),
                        'generate_num' => count($batch_ids),
                        'use_num' => $card['use_num'],
                        'fee' => $card['fee'],
                        'expire_time' => date('Y-m-d H:i:s', $card['expire_time']),
                        'status' => intval($card['status']),
                        'generate_time' => date('Y-m-d H:i:s', $card['generate_time']),
                    ];
                    $res = Card::setData($data);
                    if($res) {
                        $resx = CardDetail::updateAll(['card_id'=>$res,'user_id'=>$new_user_id],['id'=>$batch_ids,'mall_id'=>$mall_id]);
                        if($resx === false) return $this->error(CardDetail::getError());
                        return $this->success('success');
                    }else{
                        return $this->error(Card::getError());
                    }
                }else{
                    //var_dump($card);exit;
                    $card_user_id = (integer)$card['user_id'];
                    $card_card_id = (integer)$card['id'];
                    $generate_num = (integer)$card['generate_num']+count($batch_ids);
                    $res = CardDetail::updateAll(['card_id'=>$card_card_id,'user_id'=>$card_user_id],['id'=>$batch_ids,'mall_id'=>$mall_id]);
                    if($res === false) return $this->error(CardDetail::getError());
                    Card::updateAll(['generate_num'=>$generate_num],['id'=>$card_card_id,'mall_id'=>$mall_id]);
                    return $this->success('success');
                }
                
                exit;


                
                
            }
        } else {
            return $this->render('edit');
        }
    }

    /**
     * 生成卡片
     * @Author bing
     * @DateTime 2020-10-10 16:58:34
     * @copyright: Copyright (c) 2020 广东七件事集团
     * @return void
     */
    public function actionGenerateCard(){
        $request = Yii::$app->request;
        //卡模板ID
        $card_id =  $request->post('card_id', 0);
        if($card_id < 1) return $this->error('card_id参数错误');
        $res =  Card::generateCard($card_id);
        if($res === false) return $this->error(Card::getError());
        return $this->success('卡生成成功');
    }
    
    /**
     * 卡券列表
     * @Author bing
     * @DateTime 2020-10-12 10:45:18
     * @copyright: Copyright (c) 2020 广东七件事集团
     * @return void
     */
    public function actionCardList(){
        $request = Yii::$app->request;
        if ($request->isAjax) {
            $keyword = $request->get('keyword', '');
            $card_id = $request->get('card_id', 0);
            $status = $request->get('status', 999);
            if($card_id < 1) return $this->error('card_id参数错误');
            $where = array(
                ['=', 'c.mall_id', Yii::$app->mall->id],
                ['=','c.is_delete',0],
                ['=','c.card_id',$card_id]
            );
            if($status != 999)  $where[] = array('=','c.status',$status);
            // $keyword && $where[] = array('like', 'user.nickname',$keyword.'%', false);
            $params = array(
                'alias' => 'c',
                'where' => $where,
                'joinWith' => [
                    'user' => function ($query) use ($keyword) {
                        $keyword && $query->orWhere(['or',array('like', 'nickname', $keyword . '%', false), array('like', 'username', $keyword . '%', false)]);
                    },
                    'picker'
                ],
                'limit' => $this->pageSize,
                'order' => 'id ASC'
            );
            //获取数据
            $data = CardDetail::listPage($params,false,true);
            foreach($data['list'] as $key => $card){
                $data['list'][$key]['integral_setting'] = json_decode($card['integral_setting']);
                $qrCode = (new QrCode($card['qr_url'], ErrorCorrectionLevelInterface::HIGH))
                ->useEncoding('UTF-8')->setLogoWidth(60)->setSize(300)->setMargin(5);
                $qr_code_url = $qrCode->writeDataUri();
                $data['list'][$key]['qr_code_base64'] = $qr_code_url;
            }
            return $this->success('请求成功', $data);
        } else {
            return $this->render('card-list');
        }
    }


    /**
     * 禁用或删除卡券
     * @Author bing
     * @DateTime 2020-10-12 10:45:18
     * @copyright: Copyright (c) 2020 广东七件事集团
     * @return void
     */
    public function actionCardStatus(){
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $post = Yii::$app->request->post('form');
                $card_id = $post['card_detail_id'] ?? 0;
                $option = $post['option'] ?? '';
                $res = CardDetail::operator($card_id,$option);
                if($res === false) return $this->error(CardDetail::getError());
                return $this->success('success');
            } else {
                $id =  Yii::$app->request->get('id');
                $info = Card::getData($id);
                return $this->success('success',compact('info'));
            }
        } else {
            return $this->render('edit');
        }
    }
    
    /**
     * 导出充值卡列表
     * @Author bing
     * @DateTime 2020-10-12 19:08:22
     * @copyright: Copyright (c) 2020 广东七件事集团
     * @return void
     */
    public function actionExportCardInfo(){
        $request = Yii::$app->request;
        $card_id = $request->get('card_id', 1);
        if($card_id < 1) return $this->error('card_id参数错误');
        $card_one = Card::find()->select('name')->where(['id' => $card_id])->asArray()->one();
        $where = array(
            ['=', 'mall_id', Yii::$app->mall->id],
            ['=','is_delete',0]
        );
        // $keyword && $where[] = array('like', 'user.nickname',$keyword.'%', false);
        $params = array(
            'where' => $where,
            'with' => ['user','picker'],
            'limit' => $this->pageSize,
            'order' => 'id ASC'
        );
        //获取数据
        $list = CardDetail::lists($params,true);
        $export_data = array();
        foreach($list as $key => $card){
            $integral_setting = json_decode($card['integral_setting'],true);
            $integral_type = $integral_setting['expire'] == -1 ? '永久有效' : '限时有效';
            $integral_num = $integral_setting['integral_num'] * 1;
            $period = $integral_setting['period'];
            $period_unit = $integral_setting['period_unit'] == 'week' ? '周' : '月';
            $expire = $integral_setting['expire'] == -1 ? '--' : $integral_setting['expire'] * 1;
            $export_data[]= array(
                $card['id'],
                $card_one['name'],
                $card['user']['nickname'] ??  $card['user']['username'],
                $card['serialize_no'],
                $card['use_code'],
                $integral_type,
                $integral_num,
                $period.$period_unit,
                $expire,
                date('Y-m-d H:i:s',$card['expire_time']),
                $card['qr_url']
            );
            $qrCode = (new QrCode($card['qr_url'], ErrorCorrectionLevelInterface::HIGH))
            ->useEncoding('UTF-8')->setLogoWidth(60)->setSize(300)->setMargin(5);
            $qr_code_url = $qrCode->writeDataUri();
            $data['list'][$key]['qr_code_base64'] = $qr_code_url;
        }
        
        $csv = new CsvExport();
        $fileName = "购物卡券-".date('YmdHis', time());
        $headlist = ['ID','购物卡','发卡人','序列号','密码','红包券类型','红包券面值','红包券发放周期','红包券有效期（天）','过期时间','二维码链接'];
        return $csv->export($export_data, $headlist, $fileName);
    }

}