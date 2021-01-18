<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 抽象模型服务层 增删改查
 * Author: xuyaoxiang
 * Date: 2020/10/27
 * Time: 20:27
 */

namespace app\services;

abstract class ModelServices
{
    use ReturnData;

    protected $pagination = null;
    protected $page = 1;
    protected $limit = 10;
    protected $mall_id = 0;
    protected $model_name;
    protected $primary_key;
    protected $scenarios = [
        'store' => 'store'
    ];
    protected $error_msg = [
        103 => '找不到模型',
        102 => '保存失败',
        104 => '删除失败',
        105 => '主键不能为空',
        106 => '添加场景验证规则不存在',
    ];

    private $error = ['code' => 999, 'msg' => "服务层错误"];

    public function __construct($model_name, $primary_key = 'id')
    {
        $this->model_name  = $model_name;
        $this->primary_key = $primary_key;
    }

    /**
     * 添加更新数据
     * @param array $params
     * @return bool|object
     */
    public function store($params = [])
    {

        $this->setDefaultParams($params);

        if (isset($params[$this->primary_key])) {

            $model = $this->model_name::find()->where([$this->primary_key => $params['id'], 'mall_id' => $this->mall_id])->one();

            if (!$model) {
                $this->addServiceError(103, $this->error_msg[103]);
                return false;
            }

        } else {

            $model = new $this->model_name();
        }

        $scenarios_list = $model->scenarios();

        if (!in_array($this->scenarios['store'], array_keys($scenarios_list))) {
            $this->addServiceError(106, $this->error_msg[106]);

            return false;
        }

        $model->scenario = "store";

        $model->attributes = $params;

        if (!$model->save()) {

            $this->addServiceError(102, $this->responseErrorMsg($model));

            return false;
        }

        return $model;
    }

    /**
     * 列表数据，带分页器
     * @param array $params
     * @param bool $as_array
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getList($params = [], $as_array = true)
    {
        $query = $this->query($params, $as_array);

        $query->page($this->pagination, $this->limit, $this->page);

        $all = $query->all();

        return [
            'list'       => $all,
            'pagination' => $this->pagination
        ];
    }

    /**
     * 设置默认参数
     */
    protected function setDefaultParams($params)
    {
        if (isset($params['limit']) && intval($params['limit'])) {
            $this->limit = $params['limit'];
        }

        if (isset($params['page']) && intval($params['page'])) {
            $this->page = $params['page'];
        }

        if (isset($params['mall_id']) && intval($params['mall_id'])) {
            $this->mall_id = $params['mall_id'];
        }
    }

    /**
     * 查询条件
     * @param array $params
     * @param bool $as_array
     * @return \app\models\BaseActiveQuery
     */
    protected function query($params = [], $as_array = true)
    {
        $this->setDefaultParams($params);

        $query = $this->model_name::find();

        $query->where(['deleted_at' => 0]);

        if ($params['mall_id']) {
            $query->andWhere(['mall_id' => $this->mall_id]);
        }

        $query->orderBy([$this->primary_key => 'desc']);

        $query->asArray($as_array);

        return $query;
    }

    /**
     * 删除数据
     * @param $params
     * @return bool
     */
    public function destroy($params)
    {
        $this->setDefaultParams($params);

        if (!isset($params[$this->primary_key])) {
            $this->addServiceError(105, $this->error_msg[105]);

            return false;
        }

        $model = $this->model_name::find()->where([$this->primary_key => $params[$this->primary_key],
                                                   'mall_id'          => $this->mall_id,
                                                   'deleted_at'       => 0])->one();

        if (!$model) {
            $this->addServiceError(103, $this->error_msg[103]);

            return false;
        }

        if (isset($model->is_delete)) {
            $model->is_delete = 1;
        }

        $model->deleted_at = time();

        if (!$model->save()) {
            $this->addServiceError(104, $this->responseErrorMsg($model));

            return false;
        }

        return true;
    }

    /**
     * @param $error_code
     * @param $error_msg
     */
    protected function addServiceError($error_code, $error_msg)
    {
        $this->error['code'] = $error_code;
        $this->error['msg']  = $error_msg;
    }

    /**
     * @return mixed
     */
    public function getServiceError()
    {
        return isset($this->error['code']) ? $this->error : false;
    }

    public function setScenarios($key, $value)
    {
        $this->scenarios[$key] = $value;
    }
}