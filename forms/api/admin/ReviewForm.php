<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 审核
 * Author: zal
 * Date: 2020-04-16
 * Time: 15:16
 */

namespace app\forms\api\admin;

use app\core\ApiCode;
use app\core\exceptions\ClassNotFoundException;
use app\forms\mall\distribution\ApplyForm;
use app\forms\mall\distribution\IndexForm;
use app\models\BaseModel;
use app\models\Distribution;
use app\models\DistributionSetting;

class ReviewForm extends BaseModel
{
    public $id;
    public $page;
    public $type;
    public $status;
    public $keyword;

    public $mch_per = false;//多商户权限

    public $tabs = [
        ['typeid'=>1,'type'=>'mch'],
        ['typeid'=>2,'type'=>'share'],
    ];

    public function rules()
    {
        return [
            [['type', 'status', 'id'], 'integer'],
            [['page', 'type'], 'default', 'value' => 1],
            [['keyword'], 'string'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'type' => '审核消息类型',
            'statue' => '状态',
        ];
    }

    public function validate($attributeNames = null, $clearErrors = true)
    {
        $permission_arr = \Yii::$app->branch->childPermission(\Yii::$app->mall->user->adminInfo);//直接取商城所属账户权限，对应绑定管理员账户方法修改只给于app_admin权限
        if (!is_array($permission_arr) && $permission_arr) {
            $this->mch_per = true;
        } else {
            foreach ($permission_arr as $value) {
                if ($value == 'mch') {
                    $this->mch_per = true;
                    break;
                }
            }
        }
        return parent::validate($attributeNames, $clearErrors);
    }

    /**
     * 获取tab栏
     * @return array
     */
    public function getTabs()
    {
        try {
            $this->getMchReview();
        } catch (ClassNotFoundException $exception) {
            array_splice($this->tabs,0,1);
        }
        return [
            'code' => 0,
            'msg' => 'success',
            'data' => $this->tabs
        ];
    }

    public function getList()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        if ($this->type == 1) {
            try {
                $mch = $this->getMchReview();
                $mch->attributes = $this->attributes;
                $mch->review_status = $this->status;
                return $mch->getList();
            } catch (ClassNotFoundException | \Exception $exception) {
                return [
                    'code' => ApiCode::CODE_FAIL,
                    'msg' => $exception->getMessage()
                ];
            }
        } else {
            $form = new IndexForm();
            $form->attributes = $this->attributes;
            return $form->getList();
        }
    }

    /**
     * 获取入驻商详情
     * @return array
     */
    public function getDetail()
    {
        try {
            $mch = $this->getMch();
        } catch (ClassNotFoundException | \Exception $exception) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $exception->getMessage()
            ];
        }
        $mch->id = $this->id;
        return $mch->getDetail();
    }

    public function switchStatus()
    {
        if ($this->type == 1) {
            try {
                $mch = $this->getMchEdit();
                $data = json_decode(\Yii::$app->request->post('form'),true);
                $mch->attributes = $data;
                $mch->username = $data['mchUser']['username'];
                $mch->province_id = $data['district'][0];
                $mch->city_id = $data['district'][1];
                $mch->district_id = $data['district'][2];
                $mch->review_status = \Yii::$app->request->post('status');
                $mch->is_review = 1;
            } catch (ClassNotFoundException | \Exception $exception) {
                return [
                    'code' => ApiCode::CODE_FAIL,
                    'msg' => $exception->getMessage()
                ];
            }
            return $mch->save();
        } else {
            $form = new ApplyForm();
            $form->attributes = $this->attributes;
            $form->user_id = \Yii::$app->request->post('user_id');
            return $form->save();
        }
    }

    public function getCount()
    {
        try {
            if ($this->mch_per) {
                $mch = $this->getMch();
                $mchReviewCount = $mch->getCount();
            } else {
                $mchReviewCount = 0;
            }
        } catch (\Exception $exception) {
            $mchReviewCount = 0;
        }

        $shareCount = 0;

        $shareInfo = DistributionSetting::findOne(['mall_id' => \Yii::$app->mall->id, 'key' => 'level', 'is_delete' => 0]);
        if (!empty($shareInfo) && $shareInfo['value'] >= 1) {

            $shareCount = Distribution::find()->where([
                'mall_id' => \Yii::$app->mall->id,
                'is_delete' => 0,
                'status' => 0,
            ])->count();
        }
        $allCount = $mchReviewCount + $shareCount;
        return  $allCount;
    }

    private function getMchReview()
    {
        $plugin = \Yii::$app->plugin->getPlugin('mch');
        $form = $plugin->getMchReview();
        return $form;
    }

    private function getMch()
    {
        $plugin = \Yii::$app->plugin->getPlugin('mch');
        $form = $plugin->getMch();
        return $form;
    }

    private function getMchEdit()
    {
        $plugin = \Yii::$app->plugin->getPlugin('mch');
        $form = $plugin->getMchEdit();
        return $form;
    }
}