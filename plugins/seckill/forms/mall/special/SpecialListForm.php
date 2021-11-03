<?php

namespace app\plugins\seckill\forms\mall\special;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\seckill\models\Seckill;

class SpecialListForm extends BaseModel
{

    public $page;
    public $keyword;
    public $start_time;
    public $end_time;


    public function rules()
    {
        return [
            [['page'], 'integer'],
            [['page'], 'default', 'value' => 1],
            [['keyword', 'start_time', 'end_time'], 'string'],
        ];
    }

    /**
     * @Note: 获取秒杀专题，可带查询
     * @return array
     */

    public function search()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }
        try {
            $query = Seckill::find()->where([
                'mall_id' => \Yii::$app->mall->id,
                'is_delete' => 0
            ]);

            if ($this->keyword) {
                $query->andWhere(['like', 'name', $this->keyword]);
            }

            if ($this->end_time && $this->start_time) {
                $startTime = strtotime($this->start_time);
                $endTime = strtotime($this->end_time);
                $query->andWhere([
                    'and',
                    ['>=', 'start_time', $startTime],
                    ['<=', 'end_time', $endTime],
                ]);
            }

            $list = $query->page($pagination)
                ->orderBy(['id' => SORT_ASC])
                ->asArray()->all();
            if ($list) {
                foreach ($list as &$item) {
                    $item['start_time'] = date("Y-m-d H:i:s", $item['start_time']);
                    $item['end_time'] = date("Y-m-d H:i:s", $item['end_time']);
                    $item['created_at'] = date("Y-m-d H:i:s", $item['created_at']);
                }
            }
            return $this->returnApiResultData(ApiCode::CODE_SUCCESS, '', [
                'list' => $list,
                'pagination' => $pagination
            ]);
        } catch (\Exception $e) {
            return $this->returnApiResultData(ApiCode::CODE_FAIL, $e->getMessage());
        }
    }
}