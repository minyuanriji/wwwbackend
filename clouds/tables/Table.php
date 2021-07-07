<?php
/**
 * @link:http://www.######.com/
 * @copyright: Copyright (c) #### ########
 * Author: Mr.Lin
 * Email: 746027209@qq.com
 * Date: 2021-07-05 16:56
 */
namespace app\clouds\tables;


use yii\base\BaseObject;
use yii\db\ActiveQuery;

class Table extends BaseObject
{
    private $query;

    function __construct(ActiveQuery $query, $config = [])
    {
        parent::__construct($config);
        $this->query = $query;
    }

    public function where($condition, $params = [])
    {
        $this->query->where($condition, $params);
        return $this;
    }

    public function leftJoin($table, $on = '', $params = [])
    {
        $this->query->leftJoin($table, $on, $params);
        return $this;
    }

    public function innerJoin($table, $on = '', $params = [])
    {
        $this->query->innerJoin($table, $on, $params);
        return $this;
    }

    public function rightJoin($table, $on = '', $params = [])
    {
        $this->query->rightJoin($table, $on, $params);
        return $this;
    }

    public function groupBy($columns)
    {
        $this->query->groupBy($columns);
        return $this;
    }

    public function count($q = '*', $db = null)
    {
        $this->query->count($q, $db);
        return $this;
    }

    public function one($db = null)
    {
        return $this->query->one($db);
    }

    public function all($db = null)
    {
        return $this->query->all($db);
    }


    public function alias($alias)
    {
        $this->query->alias($alias);
        return $this;
    }

    public function andWhere($condition, $params = [])
    {
        $this->query->andWhere($condition, $params);
        return $this;
    }

    public function getQuery()
    {
        return $this->query;
    }

    public static function find($class)
    {
        $table = new Table($class::find());
        return $table;
    }

    public static function findOne($class, $condition)
    {
        return $class::findOne($condition);
    }
}