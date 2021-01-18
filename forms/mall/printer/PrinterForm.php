<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-04-17
 * Time: 15:54
 */

namespace app\forms\mall\printer;


use app\core\ApiCode;
use app\core\BasePagination;
use app\models\BaseModel;
use app\models\Printer;
use app\models\PrinterSetting;

class PrinterForm extends BaseModel
{


    public $id;
    public $page_size;
    public $keyword;

    const SELECT = [
        ['value' => Printer::P_360_KDT2, 'label' => '365云打印(编号kdt2)'],
        ['value' => Printer::P_YILIANYUN_K4, 'label' => '易联云-k4'],
        ['value' => Printer::P_FEIE, 'label' => '飞鹅打印机'],
        ['value' => Printer::P_GAINSCHA_GP, 'label' => '佳博云打印(GP-5890XIII/GP-5890XIV)'],
    ];

    public function rules()
    {
        return [
            [['id', 'page_size'], 'integer'],
            [['page_size'], 'default', 'value' => 10],
            [['keyword'], 'default', 'value' => ''],
            [['keyword'], 'string'],
        ];
    }

    //GET
    public function search()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        $query = Printer::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'mch_id' => \Yii::$app->admin->identity->mch_id,
            'is_delete' => 0
        ]);
        /**
         * @var BasePagination $pagination
         */
        $list = $query->orderBy('id desc')
            ->keyword($this->keyword, ['like', 'name', $this->keyword])
            ->page($pagination, $this->page_size)
            ->asArray()
            ->all();
        $select = PrinterForm::SELECT;
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'list' => $list,
                'select' => $select,
                'pagination' => $pagination,
            ]
        ];
    }

    public function getDetail()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }
        $list = Printer::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'mch_id' => \Yii::$app->admin->identity->mch_id,
            'is_delete' => 0,
            'id' => $this->id,
        ])
            ->asArray()
            ->one();
        if ($list) {
            $list['setting'] = json_decode($list['setting'], true);
        }

        $select = PrinterForm::SELECT;
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'list' => $list,
                'select' => $select,
            ]
        ];
    }

    //DELETE
    public function delete()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $model = Printer::findOne([
                'id' => $this->id,
                'is_delete' => 0,
                'mall_id' => \Yii::$app->mall->id,
                'mch_id' => \Yii::$app->admin->identity->mch_id,
            ]);
            if (!$model) {
                throw new \Exception('数据不存在或已经删除');
            }

            $model->is_delete = 1;
            $model->save();

            $res = PrinterSetting::updateAll(['is_delete' => 1], [
                'printer_id' => $model->id,
                'mall_id' => \Yii::$app->mall->id
            ]);
            $transaction->commit();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '删除成功'
            ];

        } catch (\Exception $exception) {
            $transaction->rollBack();
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $exception->getMessage(),
                'error' => [
                    'line' => $exception->getLine()
                ]
            ];
        }
    }
}