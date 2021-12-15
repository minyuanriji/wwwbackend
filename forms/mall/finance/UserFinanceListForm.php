<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-05-13
 * Time: 14:51
 */

namespace app\forms\mall\finance;


use app\core\ApiCode;
use app\helpers\SerializeHelper;
use app\models\BaseModel;
use app\models\User;
use app\models\UserCoupon;

class UserFinanceListForm extends BaseModel
{

    public $page;
    public $limit;
    public $start_date;
    public $end_date;
    public $keyword;
    public $kw_type;
    public $user_id;

    public function rules()
    {
        return [
            [['page', 'limit', 'user_id'], 'integer'],
            [['keyword', 'start_date', 'end_date', 'kw_type'], 'trim'],
        ];
    }

    public function getList()
    {
        if (!$this->validate()) return $this->responseErrorInfo();

        $query = User::find()
            ->select('id,nickname,avatar_url,balance,total_balance,income,total_income,total_score,score')
            ->andWhere(['and', ['mall_id' => \Yii::$app->mall->id], ['!=', 'mobile', ''], ['IS NOT', 'mobile', NULL], ['is_delete' => 0]]);

        if ($this->keyword && $this->kw_type) {
            switch ($this->kw_type)
            {
                case "mobile":
                    $query->andWhere(['mobile' => $this->keyword]);
                    break;
                case "user_id":
                    $query->andWhere(['id' => $this->keyword]);
                    break;
                case "nickname":
                    $query->andWhere(['like', 'nickname', $this->keyword]);
                    break;
                default:
            }
        }
        if ($this->user_id)
            $query->andWhere(['user_id' => $this->user_id]);

        if ($this->start_date && $this->end_date)
            $query->andWhere(['<', 'created_at', strtotime($this->end_date)])
                ->andWhere(['>', 'created_at', strtotime($this->start_date)]);

        $list = $query->orderBy('id desc')->page($pagination, $this->limit)->asArray()->all();
        /*foreach ($list as &$item) {
                $couponList=UserCoupon::getList(['user_id'=>$item['id']]);
                $item['coupon_count']=count($couponList);
        }*/

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'list' => $list,
                'pagination' => $pagination
            ]
        ];
    }
}