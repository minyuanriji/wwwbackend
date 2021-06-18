<?php

namespace app\forms\api\store;

use app\core\ApiCode;
use app\helpers\ArrayHelper;
use app\models\BaseModel;
use app\models\DistrictArr;
use app\models\EfpsMchReviewInfo;
use app\models\EfpsMerchantMcc;
use app\models\Store;
use app\models\User;
use app\models\UserRelationshipLink;
use app\plugins\mch\models\Mch;

class StoreForm extends BaseModel
{
    public $page;
    public $id;
    public $review_status;
    public $keyword;

    public function rules()
    {
        return [
            [['id', 'review_status'], 'integer'],
            [['keyword'], 'string'],
            [['page'], 'default', 'value' => 1],
        ];
    }

    public function getList()
    {
        $user_res = User::findOne(['id' => \Yii::$app->user->id, 'is_delete' => 0]);
        if (!$user_res) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => '账户不存在',
            ];
        }
        $user_link = UserRelationshipLink::findOne(['user_id' => \Yii::$app->user->id, 'is_delete' => 0]);
        if (!$user_link) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => '关系链异常，请联系客服！'
            ];
        }
        $user_ids = UserRelationshipLink::find()
            ->select('user_id')
            ->andWhere([
                "AND",
                "`left` > '{$user_link->left}' AND `right` < '{$user_link->right}'"
            ])
            ->asArray()
            ->all();
        $list = [];
        $pagination = null;
        if ($user_ids) {
            $new_user_ids = array_column($user_ids,'user_id');
            $query = Mch::find()->alias("m")->where([
                'm.mall_id' => \Yii::$app->mall->id,
                'm.is_delete' => 0,
                'm.review_status' => $this->review_status,
                'm.is_special' => 0
            ]);
            $query->leftJoin(["u" => User::tableName()], "u.id=m.user_id");
            $query->leftJoin(["p" => User::tableName()], "p.id=u.parent_id");
            $query->andWhere(['in','m.user_id',$new_user_ids]);

            if ($this->keyword) {
                $mchIds = Store::find()->where(['like', 'name', $this->keyword])->select('mch_id');
                $query->andWhere(['m.id' => $mchIds]);
            }

            $list = $query->select([
                        "m.id", "m.realname", "m.mobile", "m.created_at", "m.user_id",
                        "p.id as parent_id", "p.nickname as parent_nickname",
                        "p.mobile as parent_mobile", "p.role_type as parent_role_type"
                    ])
                    ->with([
                        'user' => function ($query) {
                            $query->select('id, nickname');
                        },
                        'store' => function ($query) {
                            $query->select('id, mch_id, cover_url, name');
                        }
                    ])
                    ->orderBy(['m.created_at' => SORT_DESC])
                    ->page($pagination)
                    ->asArray()
                    ->all();
        }
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'list' => $list,
                'pagination' => $pagination
            ]
        ];
    }

    /*public function getDetail()
    {
        try {
            $detail = Mch::find()->where([
                'id' => $this->id,
                'mall_id' => \Yii::$app->mall->id,
                'is_delete' => 0,
            ])->with('user.userInfo')->asArray()->one();
            if (!$detail) {
                throw new \Exception('商户不存在');
            }

            $detail['address'] = \Yii::$app->serializer->decode($detail['address']);

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请求成功',
                'data' => [
                    'detail' => $detail,
                ]
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $e->getMessage(),
            ];
        }
    }*/

    public function destroy()
    {
        try {
            $detail = Mch::find()->where([
                'id' => $this->id,
                'mall_id' => \Yii::$app->mall->id,
                'is_delete' => 0,
            ])->one();
            if (!$detail) {
                throw new \Exception('商户不存在');
            }

            $detail->is_delete = 1;
            $res = $detail->save();
            if (!$res) {
                throw new \Exception($this->responseErrorMsg($detail));
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '删除成功',
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $e->getMessage(),
            ];
        }
    }

    public function getDetail()
    {
        try {
            $detail = Mch::find()->where([
                        'id' => $this->id,
                        'mall_id' => \Yii::$app->mall->id,
                        'is_delete' => 0,
                     ])
                    ->with([
                        'user',
                        'mchAdmin',
                        'store',
                        'category'
                    ])
                    ->asArray()->one();
            if (!$detail) {
                throw new \Exception('商户不存在');
            }

            $detail['latitude_longitude'] = $detail['store']['longitude'] && $detail['store']['latitude'] ?
                $detail['store']['latitude'] . ',' . $detail['store']['longitude'] : '';
            $detail['address']          = $detail['store']['address'];
            $detail['logo']             = $detail['store']['cover_url'];
            $detail['service_mobile']   = $detail['store']['mobile'];
            $bgPicUrls                  = @json_decode($detail['store']['pic_url'], true);
            $detail['bg_pic_url']       = is_array($bgPicUrls) ? $bgPicUrls : [];
            $detail['name']             = $detail['store']['name'];
            $detail['description']      = $detail['store']['description'];
            $detail['scope']            = $detail['store']['scope'];
            $detail['district']         = [
                (int)$detail['store']['province_id'],
                (int)$detail['store']['city_id'],
                (int)$detail['store']['district_id']
            ];
            try {
                $detail['districts'] = DistrictArr::getDistrict((int)$detail['store']['province_id'])['name'] .
                    DistrictArr::getDistrict((int)$detail['store']['city_id'])['name'] .
                    DistrictArr::getDistrict((int)$detail['store']['district_id'])['name'];
            } catch (\Exception $e) {
                $detail['districts'] = '';
            }
            $detail['cat_name'] = $detail['category']['name'];
            $detail['form_data'] = $detail['form_data'] ? \Yii::$app->serializer->decode($detail['form_data']) : [];
            $detail['username'] = $detail['mchAdmin']['username'];
            $detail['password'] = $detail['mchAdmin']['password'];
            $detail['admin_id'] = $detail['mchAdmin']['id'];

            $detail['nickname'] = $detail['user']['nickname'];

            $relatEfps = EfpsMchReviewInfo::find()
                ->select("id,openAccount,paper_merchantType,paper_settleAccountType,paper_settleAccountNo,paper_settleAccount,paper_settleTarget,paper_openBank,paper_lawyerCertType,paper_lawyerCertNo,paper_certificateName")
                ->where(["mch_id" => $this->id])
                ->one();
            if(!$relatEfps){
                $relatEfps = new EfpsMchReviewInfo();
                $relatEfps->mch_id        = $this->id;
                $relatEfps->register_type = "separate_account";
                $relatEfps->created_at    = time();
                $relatEfps->updated_at    = time();
                if(!$relatEfps->save()){
                    throw new \Exception($this->responseErrorMsg($relatEfps));
                }
            }

            $reviewData = ArrayHelper::toArray($relatEfps);
            if(!empty($reviewData)){
                $reviewData['openAccount'] = $reviewData['openAccount'] ? "1" : "0";
                $reviewData['paper_merchantType'] = (string)$reviewData['paper_merchantType'];
                $reviewData['paper_settleAccountType'] = (string)$reviewData['paper_settleAccountType'];
                $reviewData['paper_settleTarget'] = (string)$reviewData['paper_settleTarget'];
            }
            unset(
                $detail['category'],
                $detail['district'],
                $detail['mchAdmin'],
                $detail['store'],
                $detail['user'],
            );

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请求成功',
                'data' => [
                    'detail' => $detail,
                    'review' => $reviewData
                ]
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $e->getMessage(),
            ];
        }
    }

    public function save($data)
    {
        if (!isset($data['id']) || $data['id'])
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => '请传入参数ID'
            ];

        $user_res = User::findOne(['id' => \Yii::$app->user->id, 'is_delete' => 0]);
        if (!$user_res)
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => '账户不存在',
            ];

        if (!$user_res->is_examine)
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => '账户没有审核功能,请联系客服！',
            ];

        $mch_exist = Mch::findOne($data['id']);
        if (!$mch_exist)
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => '该门店不存在,请联系客服！',
            ];

        if (isset($data['is_special']) && $data['is_special']) {
            $mch_exist->is_special          = $data['is_special'];
            $mch_exist->special_rate        = isset($data['special_rate']) ? (10 - $data['special_rate']) * 10 : 0;
            $mch_exist->special_rate_remark = isset($data['special_rate_remark']) ? $data['special_rate_remark'] : '';
            if (!$mch_exist->save()) {
                return [
                    'code' => ApiCode::CODE_FAIL,
                    'msg' => '保存失败'
                ];
            }
        } else {
            $transaction = \Yii::$app->db->beginTransaction();
            try {
                if ($data['review_status'] == Mch::REVIEW_STATUS_NOTPASS) { //审核不通过
                    EfpsMchReviewInfo::updateAll(['status' => 3], ["mch_id" => $data['id']]);
                    Mch::updateAll([
                        "review_status" => Mch::REVIEW_STATUS_NOTPASS,
                        "review_remark" => $data['review_remark']
                    ], ["id" => $data['id']]);
                } elseif($data['review_status'] == Mch::REVIEW_STATUS_CHECKED) { //审核通过
                    $reviewData = EfpsMchReviewInfo::find()->where(["mch_id" => $data['id']])->asArray()->one();
                    if(!$reviewData){
                        throw new \Exception("无法获取审核信息");
                    }
                    EfpsMchReviewInfo::updateAll(['status' => 2], ["mch_id" => $data['id']]);
                    Mch::updateAll(["review_status" => Mch::REVIEW_STATUS_CHECKED], [
                        "id" => $data['id']
                    ]);
                }
                $transaction->commit();
            } catch (\Exception $e) {
                $transaction->rollBack();
                return [
                    'code' => ApiCode::CODE_FAIL,
                    'msg' => $e->getMessage(),
                    'error' => [
                        'line' => $e->getLine()
                    ]
                ];
            }
        }
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '保存成功'
        ];
    }
}
