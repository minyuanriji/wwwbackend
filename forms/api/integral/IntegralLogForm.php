<?php

namespace app\forms\api\integral;

use app\core\ApiCode;
use app\models\BaseModel;
use app\models\IntegralLog;

class IntegralLogForm extends BaseModel
{
    public $page;
    public $limit;

    public $source_type;

    public $created_at;

    public function rules()
    {
        return [
            [['page', 'limit'], 'integer'],
            [['source_type', 'created_at'], 'string'],
            ['page', 'default', 'value' => 1],
            ['limit', 'default', 'value' => 20],
        ];
    }

    /**
     * @Note:收益记录
     * @return array
     */
    public function getList()
    {
        $query = IntegralLog::find()->where(['user_id' => \Yii::$app->user->identity->id]);

        if ($this->created_at) {
            $query->andWhere('FROM_UNIXTIME(created_at,"%Y年%m月")="' . $this->created_at . '"');
        }

        if ($this->source_type) {
            $query->andWhere(['source_type' => $this->source_type]);
        }
        $list = $query->page($pagination, 10, $this->page)->orderBy('id DESC')->asArray()->all();

        foreach ($list as &$item) {
            $item['created_at'] = date('m月d日 H:i', $item['created_at']);
            $item['money'] = sprintf("%.2f", $item['money']);
            $item['income'] = sprintf("%.2f", $item['income']);
        }

        return $this->returnApiResultData(ApiCode::CODE_SUCCESS, null, ['list' => $list, 'pagination' => $pagination]);
    }
}
