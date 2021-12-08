<?php

namespace app\plugins\mch\forms\common;

use app\models\BaseModel;
use app\models\Store;
use app\models\User;
use app\plugins\mch\models\Mch;
use app\plugins\mch\Plugin;
use phpDocumentor\Reflection\Types\This;

class CommonMchForm extends BaseModel
{
    public $keyword;
    public $keyword1;
    public $page;
    public $id;
    public $is_review_status;
    public $sort_prop;
    public $sort_type;

    public function getList()
    {

        $query = Mch::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0,
            'review_status' => 1,
        ]);

        if(!empty($this->keyword1) && !empty($this->keyword)){
            switch ($this->keyword1){
                case 'store_name':
                    $mchIds = Store::find()->andWhere(['like', 'name', $this->keyword])->select('mch_id');
                    $query->andWhere(['id' => $mchIds]);
                    break;
                case 'user_name':
                    $userIds = User::find()->andWhere(['like', 'nickname', $this->keyword])->andWhere(['mall_id' => \Yii::$app->mall->id])->select('id');
                    $query->andWhere(['user_id' => $userIds]);
                    break;
                case 'mch_id':
                    $query->andWhere(['id' => $this->keyword]);
                    break;
                case 'mobile':
                    $query->andWhere(['mobile' => $this->keyword]);
                    break;
                default;
            }
        }


        if ($this->sort_prop && $this->sort_type) {
            $orderBy = $this->sort_prop . ' ' . $this->sort_type;
        } else {
            $orderBy = 'id DESC';
        }

        $query->andWhere(['!=', 'mobile', '']);

        $list = $query->orderBy($orderBy)
            ->with('user', 'store', 'category', 'mchAdmin')
            ->page($pagination)->asArray()->all();

        if($list){
            foreach($list as &$item){
                $item['name'] = $item['store']['name'];
                if (empty($item['store']['cover_url'])) {
                    $item['store']['cover_url'] = 'https://dev.mingyuanriji.cn/web/static/header-logo.png';
                }
            }
        }

        return [
            'list' => $list,
            'pagination' => $pagination
        ];
    }

    /**
     * @param string $type mall--后台数据|api--小程序端接口数据
     * @return array
     * @throws \Exception
     * 获取首页布局的数据
     */
    public function getHomePage($type)
    {
        if ($type == 'mall') {
            $baseUrl = \Yii::$app->request->hostInfo . \Yii::$app->request->baseUrl;
            $plugin = new Plugin();
            return [
                'list' => [
                    [
                        'key' => $plugin->getName(),
                        'name' => '好店推荐',
                        'relation_id' => 0,
                        'is_edit' => 0
                    ]
                ],
                'bgUrl' => [
                    $plugin->getName() => [
                        'bg_url' => $baseUrl . '/statics/img/mall/home_block/yuyue-bg.png',
                    ]
                ],
                'key' => $plugin->getName()
            ];
        } elseif ($type == 'api') {
            /* @var Mch[] $list*/
            $list = Mch::find()->with('store')->where([
                'mall_id' => \Yii::$app->mall->id,
                'is_delete' => 0,
                'review_status' => 1,
                'status' => 1,
                'is_recommend' => 1
            ])->orderBy(['sort' => SORT_ASC, 'created_at' => SORT_DESC])
                ->limit(20)
                ->all();
            $newList = [];
            foreach ($list as $item) {
                $newList[] = [
                    'name' => $item->store->name,
                    'cover_url' => $item->store->cover_url,
                    'mch_id' => $item->id,
                    'id' => $item->id,
                    'picUrl' => $item->store->cover_url,
                ];
            }
            return $newList;
        } else {
            throw new \Exception('无效的数据');
        }
    }

    public function getDetail()
    {
        $query = Mch::find()->where([
            'id' => $this->id,
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0,
        ]);
        if (!$this->is_review_status) {
            $query->andWhere(['review_status' => 1]);
        }

        /** @var Mch $detail */
        $detail = $query->with('user.userInfo', 'mchUser', 'store', 'category')->one();
        if (!$detail) {
            throw new \Exception('商户不存在');
        }

        $detail->form_data = !$detail->form_data ?: \Yii::$app->serializer->decode($detail->form_data);
        $detail->store->pic_url = !$detail->store->pic_url ?: \Yii::$app->serializer->decode($detail->store->pic_url);

        return $detail;
    }
}
