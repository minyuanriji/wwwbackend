<?php

namespace app\models;

use app\services\MallSetting\JsonService;
use app\services\ReturnData;
use Yii;

/**
 * This is the model class for table "{{%user_setting}}".
 *
 * @property int $id
 * @property int $user_id 用户id
 * @property string $setting_key 设置键
 * @property string $data 设置数据
 * @property int $mall_id 商城id
 * @property int $created_at 新增时间
 * @property int $update_at 更新时间
 * @property int $deleted_at 删除时间
 */
class UserSetting extends \yii\db\ActiveRecord
{
    use ReturnData;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%user_setting}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'mall_id', 'setting_key'], 'required', "on" => 'get_one'],
            [['user_id', 'mall_id', 'setting_key', 'data'], 'required', "on" => 'store'],
            [['user_id', 'mall_id', 'created_at', 'updated_at', 'deleted_at'], 'integer'],
            [['data'], 'safe'],
            [['setting_key'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'          => 'ID',
            'user_id'     => '用户id',
            'setting_key' => '设置键',
            'data'        => '设置数据',
            'mall_id'     => '商城id',
            'created_at'  => '新增时间',
            'updated_at'  => '更新时间',
            'deleted_at'  => '删除时间',
        ];
    }

    /**
     * 更新
     * @param array $params
     */
    public function store($params = [])
    {
        $this->attributes = $params;

        $this->scenario = 'store';

        if (!$this->validate()) {
            return $this->returnApiResultData(99, $this->responseErrorMsg($this));
        }

        $query = $this->query();

        $model = $query->one();

        //如是数组,转为json写入;
        if (is_array($params['data'])) {
            $params['data'] = json_encode($params['data']);
        }

        if (!$model) {
            $model             = new UserSetting();
            $model->user_id    = $this->user_id;
            $model->mall_id    = $this->mall_id;
            $model->created_at = time();
        } else {
            $model->updated_at = time();
        }

        $model->setting_key = $params['setting_key'];
        $model->data        = $params['data'];

        $model->save();

        if (!$model->save()) {
            return $this->returnApiResultData(98, $this->responseErrorMsg($model));
        }

        if (JsonService::is_json($model['data'])) {
            $model['data'] = json_decode($model['data'], true);
        }

        return $this->returnApiResultData(0, "更新成功", $model);
    }

    private function query()
    {
        return UserSetting::find()->where(['deleted_at' => 0, 'user_id' => $this->user_id, 'mall_id' => $this->mall_id]);
    }

    /**
     * 获取一条数据
     * @param array $params
     * @param bool $as_array
     * @return array
     */
    public function getOne($params = [], $as_array = true)
    {
        $this->attributes = $params;

        $this->scenario = 'get_one';

        if (!$this->validate()) {
            return $this->returnApiResultData(99, $this->responseErrorMsg($this));
        }

        $query = $this->query();

        if (isset($params['setting_key'])) {
            $query->andWhere(['setting_key' => $params['setting_key']]);
        }

        $model = $query->asArray($as_array)->one();

        if (!$model) {
            return $this->returnApiResultData(98, "数据为空");
        }

        if (JsonService::is_json($model['data'])) {
            $model['data'] = json_decode($model['data'], true);
        }

        return $this->returnApiResultData(0, "成功", $model);
    }
}
