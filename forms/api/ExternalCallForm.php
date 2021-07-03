<?php
/**
 * Created by PhpStorm.
 * User: 阿源
 * Date: 2020/11/3
 * Time: 10:40
 */
namespace app\forms\api;

use app\core\ApiCode;
use app\models\BaseModel;
use app\models\Discuz;
use app\models\Notice;
use app\models\Option;

class ExternalCallForm extends BaseModel
{

    //获取论坛给外部调用
    public function getForum(){
        //论坛列表
        try {

            $discuz = new Discuz();

            $list = $discuz::find()->select(['subject as title','dateline'])->limit(6)->orderBy('dateline desc')->asArray()->all();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '获取成功',
                'data'=> $list
            ];
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => '获取失败',
            ];
        }
    }

    //获取公告外部调用
    public function getNotice()
    {
        $list = Notice::find()->where(['is_delete' => 0])->select(['id', 'title', 'content'])->orderBy('id desc')->limit(3)->asArray()->all();
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '获取成功',
            'data' => $list
        ];
    }

    //获取客服联系电话地址等
    public function getService(){
        $list = Option::find()->where(['name'=>'ind_setting','mall_id'=>0])->select(['value'])->asArray()->one();

        $list = json_decode($list['value']);

        $data = [
            ['key' => 'web_service_url','value'=>isset($list->web_service_url)?$list->web_service_url:null],
            ['key' => 'contact_tel','value'=>isset($list->contact_tel)?$list->contact_tel:null],
        ];

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '获取成功',
            'data' => $data
        ];

    }
}