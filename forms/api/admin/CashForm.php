<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 提现
 * Author: zal
 * Date: 2020-04-08
 * Time: 15:16
 */

namespace app\forms\api\admin;

use app\core\ApiCode;
use app\core\exceptions\ClassNotFoundException;
use app\forms\mall\distribution\CashApplyForm;
use app\forms\mall\distribution\CashListForm;
use app\models\BaseModel;
use app\models\DistributionCash;
use app\models\DistributionSetting;

class CashForm extends BaseModel
{
    public $id;
    public $page;
    public $type;
    public $status;
    public $keyword;
    public $content;
    public $transfer_type;

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
            [['content','transfer_type'], 'safe'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'type' => '提现类型',
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
            $this->getCashForm();
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
                $mch = $this->getCashForm();
                $mch->keyword_nickname = $this->keyword;
                $this->keyword = null;
                $mch->attributes = $this->attributes;
                $mch->transfer_status = 0;
                return $mch->getList();
            } catch (ClassNotFoundException | \Exception $exception) {
                return [
                    'code' => ApiCode::CODE_FAIL,
                    'msg' => $exception->getMessage()
                ];
            }
        } else {
            $form = new CashListForm();
            $form->attributes = $this->attributes;
            return $form->search();
        }
    }

    public function verify()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        if ($this->type == 1) {
            try {
                $mch = $this->getCashEditForm();
                $mch->attributes = $this->attributes;
                return $mch->save();
            } catch (ClassNotFoundException | \Exception $exception) {
                return [
                    'code' => ApiCode::CODE_FAIL,
                    'msg' => $exception->getMessage()
                ];
            }
        } else {
            $form = new CashApplyForm();
            $form->attributes = $this->attributes;
            return $form->save();
        }
    }

    /**
     * 打款
     */
    public function cash()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        if ($this->type == 1) {
            try {
                $mch = $this->getCashEditForm();
                $mch->attributes = $this->attributes;
                return $mch->transfer();
            } catch (ClassNotFoundException | \Exception $exception) {
                return [
                    'code' => ApiCode::CODE_FAIL,
                    'msg' => $exception->getMessage()
                ];
            }
        } else {
            $form = new CashApplyForm();
            $form->attributes = $this->attributes;
            return $form->save();
        }
    }

    public function getCount()
    {
        try {
            if ($this->mch_per) {
                $mch = $this->getCashForm();
                $mchCashCount = $mch->getCount();
            } else {
                $mchCashCount = 0;
            }
        } catch (\Exception $exception) {
            $mchCashCount = 0;
        }

        $shareCashCount = 0;

        $shareInfo = DistributionSetting::findOne(['mall_id' => \Yii::$app->mall->id, 'key' => 'level', 'is_delete' => 0]);
        if (!empty($shareInfo) && $shareInfo['value'] >= 1) {

            $shareCashCount = DistributionCash::find()->where([
                'mall_id' => \Yii::$app->mall->id,
                'is_delete' => 0,
                'status' => [0,1],
            ])->count();
        }

        $allCount = $mchCashCount + $shareCashCount;
        return  $allCount;
    }

    private function getCashForm()
    {
        $plugin = \Yii::$app->plugin->getPlugin('mch');
        $form = $plugin->getCashForm();
        return $form;
    }

    private function getCashEditForm()
    {
        $plugin = \Yii::$app->plugin->getPlugin('mch');
        $form = $plugin->getCashEditForm();
        return $form;
    }
}