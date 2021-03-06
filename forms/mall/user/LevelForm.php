<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 用户余额
 * Author: zal
 * Date: 2020-04-15
 * Time: 16:45
 */

namespace app\forms\mall\user;

use app\core\ApiCode;
use app\forms\mall\export\MemberLogExport;
use app\models\BaseModel;
use app\models\MemberOrders;

class LevelForm extends BaseModel
{
    public $page_size;
    public $keyword;
    public $date;
    public $start_date;
    public $end_date;
    public $user_id;

    public $fields;
    public $flag;

    public function rules()
    {
        return [
            [['user_id'], 'integer'],
            [['date', 'flag'], 'trim'],
            [['start_date', 'end_date',], 'string'],
            [['page_size'], 'default', 'value' => 10],
            [['keyword'], 'string', 'max' => 255],
            [['fields'], 'safe'],
        ];
    }

    //会员购买记录
    public function search()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        };

        $query = MemberOrders::find()->alias('i')->where([
            'i.mall_id' => \Yii::$app->mall->id,
            'i.is_pay' => 1,
            'i.is_delete' => 0,
        ])->joinwith(['user' => function ($query) {
            if ($this->keyword) {
                $query->where(['like', 'nickname', $this->keyword]);
            }
        }]);

        if ($this->user_id) {
            $query->andWhere(['i.user_id' => $this->user_id]);
        }

        if ($this->start_date && $this->end_date) {
            $query->andWhere(['<', 'i.created_at', $this->end_date])
                ->andWhere(['>', 'i.created_at', $this->start_date]);
        }

        if ($this->flag == "EXPORT") {
            $new_query = clone $query;
            $exp = new MemberLogExport();
            $exp->fieldsKeyList = $this->fields;
            $exp->export($new_query);
            return false;
        }

        $list = $query
            ->orderBy(['created_at' => SORT_DESC])
            ->page($pagination, $this->page_size)->asArray()->all();
        foreach ($list as $k => $v) {
            $detail = json_decode($v['detail'], true);

            if (array_key_exists('before_update', $detail)) {
                $info = $detail['before_update']['name'] . '->';
            } else {
                $info = '';
            }
            $info .= end($detail['after_update'])['name'];
            $list[$k]['pay_info'] = $info;
            $list[$k]['pay_price'] = $v['pay_price'] . '元';
        }
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'list' => $list,
                'export_list' => (new MemberLogExport())->fieldsList(),
                'pagination' => $pagination,
            ]
        ];
    }
}
