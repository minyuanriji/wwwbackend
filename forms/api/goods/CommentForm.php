<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-04-28
 * Time: 11:55
 */

namespace app\forms\api\goods;


use app\core\ApiCode;
use app\models\BaseModel;
use app\models\Goods;
use app\models\Mall;
use app\models\OrderComments;

/**
 * @property Mall $mall
 */
class CommentForm extends BaseModel
{
    public $mall;

    public $goods_id;
    public $page;
    public $limit;
    public $status;

    public function rules()
    {
        return [
            ['goods_id', 'required'],
            [['goods_id', 'page', 'limit', 'status'], 'integer'],
            ['page', 'default', 'value' => 1],
            ['limit', 'default', 'value' => 20],
            ['status', 'default', 'value' => 0]
        ];
    }

    public function search()
    {
        if (!$this->validate()) {
            return $this->returnApiResultData();
        }
        $setting = $this->mall->getMallSetting(['is_comment']);
        if ($setting['is_comment'] == 0) {
            return $this->returnApiResultData(ApiCode::CODE_FAIL, ApiCode::CODE_FAIL, ['comments' => [], 'comment_count' => [],]);
        }
        $goods = Goods::findOne($this->goods_id);

        $query = OrderComments::find()
            ->where([
                'goods_warehouse_id' => $goods->goods_warehouse_id, 'mall_id' => $this->mall->id, 'is_delete' => 0,
                'is_show' => 1
            ]);

        if ($this->status) {
            if ($this->status >= 3) {
                $query->andWhere(['>=', 'score', 3]);
            } else {
                $query->andWhere(['score' => $this->status]);
            }
        }

        $list = $query->select(['*', 'time' => 'case when `is_virtual` = 1 then `virtual_at` else `created_at` end'])
            ->with('user')->apiPage($this->limit, $this->page)
            ->orderBy(['is_top' => SORT_DESC, 'created_at' => SORT_DESC])
            ->all();

        $newList = [];
        /* @var OrderComments[] $list */
        $good_num = $total = 0;
        $good_rate=0;

        foreach ($list as $item) {
            $newItem = [
                'content' => $item->content,
                'pic_url' => \Yii::$app->serializer->decode($item->pic_url),
                'reply_content' => $item->reply_content,
                'score'=>$item->score
            ];
            if($item->score >= 4){
                $good_num++;
            }
            if ($item->is_virtual == 1) {
                $newItem['avatar'] = $item->virtual_avatar;
                $newItem['time'] = $item->virtual_at > 0 ? date('Y-m-d', $item->virtual_at) : "";
                $newItem['nickname'] = $this->substrCut($item->virtual_user);
            } else {
                $newItem['avatar'] = isset($item->user->avatar_url) ? $item->user->avatar_url : \Yii::$app->request->hostInfo .
                    \Yii::$app->request->baseUrl . '/statics/img/common/default-avatar.png';
                $newItem['time'] = $item->created_at > 0 ? date('Y-m-d', $item->created_at) : "";
                $newItem['nickname'] = isset($item->user->nickname) ? $this->substrCut($item->user->nickname) : "匿名用户";
            }

            if ($item->is_anonymous == 1) {
                $newItem['avatar'] = \Yii::$app->request->hostInfo .
                    \Yii::$app->request->baseUrl . '/statics/img/common/default-avatar.png';
                $newItem['nickname'] = '匿名用户';
            }
            $total++;
            $newList[] = $newItem;
        }
        if($total){
            $good_rate = number_format($good_num / $total,2) * 100;
        }



        return $this->returnApiResultData(ApiCode::CODE_SUCCESS, '', ['comments' => $newList, 'comment_count' => $this->countData($goods),'good_rate'=>$good_rate]);
    }

    private function countData($goods)
    {
        $list = OrderComments::find()
            ->where([
                'goods_warehouse_id' => $goods->goods_warehouse_id, 'mall_id' => $this->mall->id, 'is_delete' => 0,
                'is_show' => 1
            ])
            ->select([
                'count(1) score_all', 'SUM(IF( score >=4 ,1,0)) score_3',
                'SUM(IF ( score < 4 and score >1,1,0)) score_2',
                'SUM( IF ( score >= 0 and score <2,1,0) ) AS `score_1`',
            ])->asArray()->one();
        $list = array_map(function ($v) {
            if (!$v) {
                $v = 0;
            }
            return $v;
        }, $list);
        $newList = [];
        foreach ($list as $key => $value) {
            switch ($key) {
                case 'score_all':
                    $name = '全部';
                    $index = 0;
                    break;
                case 'score_3':
                    $name = '好评';
                    $index = 3;
                    break;
                case 'score_2':
                    $name = '中评';
                    $index = 2;
                    break;
                case 'score_1':
                    $name = '差评';
                    $index = 1;
                    break;
                default:
                    $name = $key;
                    $index = 0;
            }
            $newList[] = [
                'name' => $name,
                'count' => $value,
                'index' => $index,
            ];
        }
        return $newList;
    }

    // 将用户名 做隐藏
    private function substrCut($user_name)
    {
        $strlen = mb_strlen($user_name, 'utf-8');
        $firstStr = mb_substr($user_name, 0, 1, 'utf-8');
        $lastStr = mb_substr($user_name, -1, 1, 'utf-8');
        return $strlen <= 2 ? $firstStr . '*' : $firstStr . str_repeat("*", 2) . $lastStr;
    }
}
