<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: zal
 * Date: 2020-04-11
 * Time: 15:16
 */

namespace app\forms\mall\shop;


use app\core\ApiCode;
use app\models\Banner;
use app\models\BaseModel;
use app\models\BannerRelation;
use yii\helpers\ArrayHelper;

class MallBannerForm extends BaseModel
{
    public $page_size;
    public $ids;

    public function rules()
    {
        return [
            [['ids'], 'safe'],
            [['page_size'], 'default', 'value' => 10],
            [['ids'], 'default', "value" => []]
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'mall_id' => 'Mall ID',
            'is_delete' => '删除',
        ];
    }

    /**
     * 获取列表数据
     * @Author: 广东七件事 zal
     * @Date: 2020-04-11
     * @Time: 11:49
     * @param bool $isPluginMenus
     * @return array
     */
    public function getList()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }
        $bannerIds = Banner::find()->where(['is_delete' => 0, 'mall_id' => \Yii::$app->mall->id])->select('id');
        $query = BannerRelation::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0,
        ]);

        $list = $query
            ->andWhere(['banner_id' => $bannerIds])
            ->with('banner')
            ->orderBy('id ASC')
            ->all();

        $list = array_map(function ($item) {
            $newItem = ArrayHelper::toArray($item->banner);
            try {
                $newItem['params'] = \Yii::$app->serializer->decode($item->banner->params);
                if (!$newItem['params']) {
                    $newItem['params'] = [];
                }
            } catch (\Exception $exception) {
                $newItem['params'] = [];
            }
            return $newItem;
        }, $list);

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'list' => $list,
                'pagination' => []
            ]
        ];
    }

    /**
     * 保存
     * @Author: 广东七件事 zal
     * @Date: 2020-04-11
     * @Time: 11:49
     * @return array|string
     * @throws \yii\db\Exception
     */
    public function save()
    {
        if (!$this->validate()) {
            return $this->responseErrorMsg();
        };

        $t = \Yii::$app->db->beginTransaction();

        BannerRelation::updateAll(['is_delete' => 1, 'deleted_at' => time()], [
            'is_delete' => 0,
            'mall_id' => \Yii::$app->mall->id,
        ]);
        if(!empty($this->ids)){
            foreach ($this->ids as $id) {
                $form = new BannerRelation();
                $form->banner_id = $id;
                $form->mall_id = \Yii::$app->mall->id;
                $form->is_delete = 0;
                if (!$form->save()) {
                    $t->rollBack();
                    return $this->responseErrorMsg($form->getErrors());
                }
            };
        }

        $t->commit();
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '保存成功',
        ];
    }
}
