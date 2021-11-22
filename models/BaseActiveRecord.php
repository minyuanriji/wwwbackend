<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: zal
 * Date: 2020-04-08
 * Time: 15:12
 */
namespace app\models;

use app\component\jobs\AdminActionJob;
use app\forms\common\OperateModelForm;
use Yii;
use yii\data\Pagination;
use yii\db\ActiveQuery;
use function EasyWeChat\Kernel\Support\get_client_ip;

class BaseActiveRecord extends \yii\db\ActiveRecord
{
    const YES = 1;

    const NO = 0;

    const IS_DELETE_YES = self::YES;

    const IS_DELETE_NO = self::NO;

    /** @var int 支付方式：1=微信支付，2=货到付款，3=余额支付，4=支付宝支付，5=百度支付，6=头条支付 */
    const PAY_TYPE_WECHAT = 1;
    const PAY_TYPE_HUODAO = 2;
    const PAY_TYPE_BALANCE = 3;
    const PAY_TYPE_ALIPAY = 4;
    const PAY_TYPE_BAIDU = 5;
    const PAY_TYPE_TOUTIAO = 6;

    public $fillable = [];
    public $guarded = [];
    public $isLog = true; // 单独开关
    public static $log = true; // 全局开关

    
    protected static $error     = '';
    protected static $_sql      = [];
    protected static $_query    = '';
    protected static $_params   = [];



    /**
     * 有sql添加、更新操作时自动追加添加时间、更新时间
     * @return bool
     */
    public function beforeValidate()
    {
        $time = time();
        $insert = $this->isNewRecord;
        $isCreatedAt = false;
        $isUpdatedAt = false;
        $isDeletedAt = false;
        $isDelete = false;
        if (isset($this->attributes) && is_array($this->attributes())) {
            foreach ($this->attributes() as $item) {
                $item === 'created_at' ? $isCreatedAt = true : '';
                $item === 'updated_at' ? $isUpdatedAt = true : '';
                $item === 'deleted_at' ? $isDeletedAt = true : '';
                $item === 'is_delete' ? $isDelete = true : '';
            }
        }

        if ($insert === true && $isCreatedAt === true) {
            $this->created_at = $time;
        }

        if ($isUpdatedAt === true) {
            $this->updated_at = $time;
        }

        if ($isDelete === true && $isDeletedAt === true) {
            if ((int)$this->is_delete === 1) {
                $this->deleted_at = $time;
            } else {
                $this->deleted_at = 0;
            }
        }

        return parent::beforeValidate();
    }

    /**
     * @return BaseActiveQuery
     */
    public static function find()
    {
        return \Yii::createObject(BaseActiveQuery::className(), [get_called_class()]);
    }

    /**
     * 查询单条记录
     * @param array $wheres
     * @return MemberLevel|null
     */
    public static function getOneData($wheres = []){
        return self::findOne($wheres);
    }

    public function afterSave($insert, $changedAttributes)
    {
         if (!($this->isLog === true && self::$log === true)) {
             parent::afterSave($insert, $changedAttributes);
             return true;
         }
         try {
             $isSave = true;
             //来源1前台2后台
             $from = ActionLog::FROM_BEFORE;
             try {
                 if (!\Yii::$app->user->isGuest) {
                     $adminId = \Yii::$app->user->id;
                 }
                 if (!\Yii::$app->admin->isGuest) {
                     $adminId = \Yii::$app->admin->id;
                     $from = ActionLog::FROM_AFTER;
                     // 管理员 新增操作不记录日志
                     //if (($admin->admin_type == 1 || $admin->admin_type == 2) && $insert === true) {
                     //    $isSave = true;
                    // }
                 } else {
                     $adminId = 0;
                 }
             } catch (\Exception $e) {
                 $adminId = 0;
             }
             try {
                 $mallId = \Yii::$app->mall->id;
             } catch (\Exception $e) {
                 $mallId = 0;
             }
             // 更新时 保存日志
             if ($this->isLog === true && $isSave) {
                 // 去除以下字段 不记录日志
                 $arr = ['created_at', 'updated_at', 'deleted_at'];
                 $afterUpdate = $this->attributes;
                 $newBeforeUpdate = [];
                 $newAfterUpdate = [];
                 $remark = '数据新增';
                 if(isset($afterUpdate['updated_at'])){
                     $remark = "数据修改";
                 }
                 if (isset($afterUpdate['is_delete']) && $afterUpdate['is_delete'] == 1) {
                     $remark = '数据删除';
                 }

                 foreach ($changedAttributes as $key => $item) {
                     if (in_array($key, $arr)) {
                         unset($changedAttributes[$key]);
                         continue;
                     }
                     if ($item != $afterUpdate[$key]) {
                         try {
                             $newBeforeUpdate[$key] = \Yii::$app->serializer->decode($item);
                         } catch (\Exception $e) {
                             $newBeforeUpdate[$key] = $item;
                         }

                         try {
                             $newAfterUpdate[$key] = \Yii::$app->serializer->decode($afterUpdate[$key]);
                         } catch (\Exception $e) {
                             $newAfterUpdate[$key] = $afterUpdate[$key];
                         }
                     }
                 }

                 if ($newBeforeUpdate) {
                     // 黑名单之外的数据
                     if ($this->guarded) {
                         foreach ($this->guarded as $item) {
                             unset($newBeforeUpdate[$item]);
                             unset($newAfterUpdate[$item]);
                         }
                     }

                     // 白名单之内的数据
                     if (!$this->guarded && $this->fillable) {
                         foreach ($newBeforeUpdate as $key => $item) {
                             if (!in_array($key, $this->fillable)) {
                                 unset($newBeforeUpdate[$key]);
                                 unset($newAfterUpdate[$key]);
                             }
                         }
                     }

                     $modelName = $this->formName();
//                     $modelName = 'app\\models\\' . $this->formName();
//                     $dataArr = [
//                         'newBeforeUpdate' => $newBeforeUpdate,
//                         'newAfterUpdate' => $newAfterUpdate,
//                         'modelName' => $modelName,
//                         'modelId' => $this->attributes['id'],
//                         'remark' => $remark,
//                         'operator' => $adminId,
//                         'mall_id' => $mallId,
//                         'from' => $from
//                     ];
//                     $class = new AdminActionJob($dataArr);
//                     $queueId = \Yii::$app->queue->delay(10)->push($class);
                     if($from == ActionLog::FROM_AFTER){
                         try {
                             $modules = OperateModelForm::getModuleName($modelName,$modelName);
                             $logName = $modules["model"];
                             $moduleName = $modules["module"];
                             $form = new AdminOperateLog();
                             $form->mall_id = $mallId;
                             $form->admin_id = $adminId;
                             $form->model_id = $this->attributes['id'];
                             $form->model = $modelName;
                             $form->module = $moduleName;
                             $form->before_update = \Yii::$app->serializer->encode($newBeforeUpdate);
                             $form->after_update = \Yii::$app->serializer->encode($newAfterUpdate);
                             $form->name = $logName;
                             $form->remark = $remark ?: '数据更新';
                             $form->operate_ip = get_client_ip();
                             $res = $form->save();
                             \Yii::warning('操作日志存储成功,日志ID:' . $form->id);
                         } catch (\Exception $e) {
                             \Yii::error('操作日志存储失败,日志ID:' . $form->id);
                             \Yii::error($e->getMessage());
                         }
                     }
                 }
             }
             parent::afterSave($insert, $changedAttributes); // TODO: Change the autogenerated stub
         } catch (\Exception $e) {
             throw $e;
         }
    }

    /**
     * 统计
     * @Author bing
     * @DateTime 2020-10-27 15:23:39
     * @copyright: Copyright (c) 2020 广东七件事集团
     * @param string $where
     * @return void
     */
    public static function count($params,$limit = 0){
        $model = static::find();
        self::paramPack($params, $model);
        if($limit > 0) $model->limit($limit);
        self::$_sql[] = $model->createCommand()->getRawSql();
        return $model->count();
    }

    /**
     * 获取一个
     * @Author bing
     * @DateTime 2020-10-27 15:24:02
     * @copyright: Copyright (c) 2020 广东七件事集团
     * @param [type] $where
     * @param boolean $order
     * @return void
     */
    public static function getOne($where, $order=false){
        $model = static::find()->where($where);
        if($order) $model->orderBy($order);
        self::$_sql[] = $model->createCommand()->getRawSql();
        return $model->one();
    }

    /**
     * 获取一条关联查询
     * @Author bing
     * @DateTime 2020-10-27 15:24:36
     * @copyright: Copyright (c) 2020 广东七件事集团
     * @param array $params
     * @return void
     */
    public static function getUnionOne($params=[]){
        $params['limit'] = 1;
        $list = self::lists($params);
        if($list === false){
            return false;
        }
        return isset($list[0]) ? $list[0] : []; 
    }
    
    /**
     * 获取数据列表总数
     * @Author bing
     * @DateTime 2020-10-27 15:25:34
     * @copyright: Copyright (c) 2020 广东七件事集团
     * @param array $params
     * @return void
     */
    public static function listCount($params=[]){
        $query = static::find();
        self::paramPack($params, $query);
        self::$_sql[] = $query->createCommand()->getRawSql();
        $count = $query->count();
        return $count;
    }
    
    /**
     * 获取数据列表
     * @Author bing
     * @DateTime 2020-10-27 15:25:51
     * @copyright: Copyright (c) 2020 广东七件事集团
     * @param array $params
     * @param boolean $is_array
     * @return mixed
     */
    public static function lists($params=[],$is_array=false){
        $list = [];
        $query = static::find();
        try{
            self::paramPack($params, $query);
            
            if(!empty($params['limit'])){
                $query->limit($params['limit']);
            }
            if(!empty($params['offset'])){
                $query->offset($params['offset']);
            }
            elseif(!empty($params['page']) && !empty($params['limit'])){
                $query->offset(($params['page']-1)*$params['limit']);
            }
            self::$_sql[] = $query->createCommand()->getRawSql();
            $list =  $is_array ?  $query->AsArray()->all() : $query->all();
        }
        
        catch (\Exception $exc){
            self::$error = $exc->getMessage();
            //$this->errors = $exc->getMessage();
            return false;
        }
        return $list;
    }
    
    /**
     * 通用分页列表数据集获取方法
     * @Author bing
     * @DateTime 2020-10-27 15:26:13
     * @copyright: Copyright (c) 2020 广东七件事集团
     * @param array        $params   参数集合
     * @param boolean|int     $count   总数 如果传进来，则不再执行查询总数 否则默认会查询
     * @param boolean $is_array
     * @return void
     */
    public static function listPage($params=[], $count=false,$is_array=false){
        $return = ['list' => [], 'count' => 0];
        $query = static::find();
        try{
            self::paramPack($params, $query);

            //每页条数
            if(!empty($params['limit'])){
                $return['page_size'] = intval($params['limit']);
            }
            empty($return['page_size']) && $return['page_size'] = 20;

            //当前页数 page:当前页  limit:每页条数

            if(isset($params['page']) && intval($params['page']) > 0){
                $return['page'] = $params['page'];
            }else{
                $page = Yii::$app->request->post('page') ?? Yii::$app->request->get('page');
                $return['page'] = !empty($page) ? $page : 1;
            }
            if($count === false){
                $return['count'] = $query->count();
                $ss = self::$_sql[] = $query->createCommand()->getRawSql();
                //pr($return['count'],$ss);exit;
            }else{
                $return['count'] = $count;
            }
            $pages = new Pagination(['totalCount' => $return['count'] ?? 1]);
            $pages->setPageSize($return['page_size']);
            $return['pagination'] = $pages;
            //总页数
            $return['page_count'] = (!empty($return['count'] ) && $return['count']  > 0) ? ceil($return['count']  / $return['page_size']) : 1;
            //边界处理
            if($return['page'] > $return['page_count']) $return['page'] = $return['page_count'];
            $query->orderBy('id DESC')->limit($return['page_size']);
            $query->offset(($return['page'] - 1) * $return['page_size']);
            // $return['pagination'] = $pages;
            $return['list'] = $is_array ?  $query->AsArray()->all() : $query->all();
            //if(YII_DEBUG) $return['sql']  = self::$_sql[] = $query->createCommand()->getRawSql();
        }catch (\Exception $exc){
            self::$error = $exc->getMessage();
            //$this->errors = $exc->getMessage();
            return false;
        }
        return $return;
    }
    
    /**
     * SQL组装
     * @Author bing
     * @DateTime 2020-10-27 15:27:08
     * @copyright: Copyright (c) 2020 广东七件事集团
     * @param array $params
     * @param [type] $query
     * @return void
     */
    private static function paramPack(array $params=[], &$query){
        if(!empty($params['alias'])){
            $alias = $params['alias'];
        } 

        if(!empty($params['table'])){
            $table =  isset($alias) ? ($params['table'] . ' ' . $alias) : $params['table'];
        }else{
            $table =  isset($alias) ? (static::tableName() . ' ' . $alias) : static::tableName();
        }
        $query->from($table);
        
        if(!empty($params['where'])){
            if(is_array($params['where'])){
                foreach($params['where'] as $where){
                    $query->andWhere($where);
                }
            }elseif(is_string($params['where'])){
                $query->andWhere($params['where']);
            }
        }

        if(!empty($params['order'])){
            $query->orderBy($params['order']);
        }

        
        if(!empty($params['join']) && is_array($params['join'])){
            foreach($params['join'] as $join){
                list($joinType,$joinTable,$joinOn) = $join;
                $query->join($joinType,$joinTable,$joinOn);
            }
        }

        if(!empty($params['with'])){
            $query->with($params['with']);
        }
        
        if(!empty($params['joinWith'])){
            $query->joinWith($params['joinWith']);
        }
        if(!empty($params['group'])){
            $query->groupBy($params['group']);
        }

        if(!empty($params['having'])){
            $query->having($params['having']);
        }

        if(!empty($params['select'])){
            $query->select($params['select']);
        }
        
        if(!empty($params['index'])){
            $query->indexBy($params['index']);
        }
    }

    /**
     * 获取错误信息
     * @Author bing
     * @DateTime 2020-10-16 15:01:06
     * @copyright: Copyright (c) 2020 广东七件事集团
     * @return mixed 错误信息
     */
    public static function getError(){
        $error = self::$error;
        if(empty($error)) $error = '未知错误';
        return $error;
    }

    /**
     * 获取错误信息
     * @Author bing
     * @DateTime 2020-10-16 15:01:06
     * @copyright: Copyright (c) 2020 广东七件事集团
     * @return mixed 错误信息
     */
    public static function getSql(){
        $sql = self::$_sql;
        if(empty($sql)) $sql = '';
        return $sql;
    }


    /**
     * 获取模型错误
     * @Author bing
     * @DateTime 2020-10-27 15:27:47
     * @copyright: Copyright (c) 2020 广东七件事集团
     * @param [type] $model
     * @return void
     */
    public function getErrorMessage($model = null){
        if (!$model) $model = $this;
        if(!empty($model->errors)){
            $msg = current($model->errors)[0];
        }else{
            $msg = self::$error ?? '未知错误';
        }
        return $msg;
    }
}
