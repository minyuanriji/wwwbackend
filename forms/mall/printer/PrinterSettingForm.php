<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-04-17
 * Time: 16:20
 */

namespace app\forms\mall\printer;


use app\core\ApiCode;
use app\core\BasePagination;
use app\forms\mall\shop\StoreForm;
use app\models\BaseModel;
use app\models\Printer;
use app\models\PrinterSetting;
use yii\helpers\ArrayHelper;

class PrinterSettingForm extends BaseModel
{

    public $id;
    public $page_size;

    public function rules()
    {
        return [
            [['id', 'page_size'], 'integer'],
            [['page_size'], 'default', 'value' => 10],
        ];
    }

    //GET
    public function search()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        $query = PrinterSetting::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'mch_id' => \Yii::$app->admin->identity->mch_id,
            'is_delete' => 0
        ])->with('printer', 'store');

        /**
         * @var BasePagination $pagination
         */
        $list = $query->orderBy('id desc')->page($pagination, $this->page_size)->all();
        $newList = [];
        if ($list) {
            /**
             * @var  $k
             * @var PrinterSetting $v
             */
            foreach ($list as $k => $v) {
                $printerItem = ArrayHelper::toArray($v);
                $printerItem['printer'] = $v->printer ? ArrayHelper::toArray($v->printer) : [];
                $printerItem['store_name'] = $v->store ? $v->store->name : '全门店通用';
                $printerItem['type'] = json_decode($v['type'], true);
                $newList[] = $printerItem;
            };
        }
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'list' => $newList,
                'pagination' => $pagination,
            ]
        ];
    }

    public function getDetail()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }
        $list = PrinterSetting::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'mch_id' => \Yii::$app->admin->identity->mch_id,
            'is_delete' => 0,
            'id' => $this->id,
        ])->one();
        if ($list) {
            $list = ArrayHelper::toArray($list);
            $list['type'] = json_decode($list['type'], true);
        }
        $select = Printer::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'mch_id' => \Yii::$app->admin->identity->mch_id,
            'is_delete' => 0,
        ])->all();


        $storeForm = new StoreForm();
        $stores = $storeForm->getAllStore();
        $stores = $stores ? ArrayHelper::toArray($stores) : [];
        array_unshift($stores, [
            'id' => 0,
            'name' => '全门店通用'
        ]);

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'list' => $list,
                'select' => $select,
                'stores' => $stores
            ]
        ];
    }

    //DELETE
    public function delete()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }
        $model = PrinterSetting::findOne([
            'id' => $this->id,
            'is_delete' => 0,
            'mall_id' => \Yii::$app->mall->id,
            'mch_id' => \Yii::$app->admin->identity->mch_id,
        ]);
        if (!$model) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => '数据不存在或已经删除',
            ];
        }
        $model->is_delete = 1;
        $model->save();
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '删除成功'
        ];
    }
}