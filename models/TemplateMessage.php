<?php

namespace app\models;

use app\services\ReturnData;

/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 微信模板
 * Author: xuyaoxiang
 * Date: 2020/10/7
 * Time: 15:08
 */
class TemplateMessage extends BaseActiveRecord
{
    use ReturnData;

    private $mall_id;

    public function setMallId($mall_id)
    {
        $this->mall_id = $mall_id;
        return $this;
    }

    public static function tableName()
    {
        return '{{%template_message}}';
    }

    public function rules()
    {
        return [
            [['type', 'mall_id'], 'integer'],
            [['tempkey', 'name', 'tempid'], 'string'],
            [['created_at', 'updated_at', 'deleted_at', 'name', 'tempkey'], 'safe'],
            [['content'], 'string', 'max' => 1000],
            [['name'], 'default', 'value' => null]
        ];
    }

    public function attributeLabels()
    {
        return [
            'id'         => 'ID',
            'type'       => '0=订阅消息,1=微信模板消息',
            'tempkey'    => '模板编号',
            'name'       => '模板名',
            'content'    => '回复内容',
            'tempid'     => '模板ID',
            'created_at' => '添加时间',
            'updated_at' => '更新时间',
            'deleted_at' => '删除时间',
            'mall_id'    => '商城id',
        ];
    }

    public function getOne($params)
    {
        $query = $this->query($params);

        return $query->one();
    }

    public function query($params)
    {
        $query = self::find()->where(['mall_id' => $this->mall_id]);

        if (!isset($params['is_all']) or $params['is_all'] != true) {
            $query->andWhere(['deleted_at' => 0]);
        }

        if (isset($params['tempkey'])) {
            $query->andWhere(['tempkey' => $params['tempkey']]);
        }

        return $query;
    }

    /**
     * 添加模板消息
     * @param array $params
     * @return array
     */
    public function addTemplateMessage($params = [])
    {
        $this->attributes = $params;

        if (!$this->save()) {

            return $this->returnApiResultData(98, $this->responseErrorMsg($this));
        } else {

            return $this->returnApiResultData(0, "添加成功", $this);
        }
    }

    /**
     * 请求接口,添加模板
     * @param $shortId
     * @return array
     */
    public function addTemplate($shortId)
    {
        $app = \Yii::$app->wechat->app;

        $data = $app->template_message->addTemplate($shortId);

        /* return
         *
         * $data=[
         *  'errcode'=>0,
         * 'errmsg'=>"ok",
         * "template_id"=>"2O0LuGyxows93Y6D6LMKrF09P5tpfoNift3n7AjwT5o",
         * ];
         */
        //请求成功，入数据库
        if ($data['errcode'] == 0) {

            $params['tempid']  = $data['template_id'];
            $params['tempkey'] = $shortId;
            $params['type']    = 1;
            $params['mall_id'] = $this->mall_id;
            return $this->addTemplateMessage($params);
        }

        return $this->returnApiResultData($data['errcode'], $data['errmsg']);
    }

    /**
     * 删除模板消息
     * @param array $params
     * @return bool
     */
    public function delTemplateMessage($params = [], $api_del = true)
    {
        $item = $this->getOne($params);
        if ($item) {

            if ($api_del) {
                $this->apiDelTemplateMessage($item->tempid);
            }

            $item->deleted_at = time();
            return $item->save();
        }
        return false;
    }

    public function apiDelTemplateMessage($tempid)
    {
        $app = \Yii::$app->wechat->app;

        return $app->template_message->deletePrivateTemplate($tempid);
    }
}
