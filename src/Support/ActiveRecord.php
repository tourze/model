<?php

namespace tourze\Model\Support;
use Doctrine\DBAL\Connection;
use tourze\Base\Helper\Arr;

/**
 * 链式调用的接口实现
 *
 * @package tourze\Model\Model\Support
 */
trait ActiveRecord
{

    /**
     * Auto-update columns for creation
     *
     * @var string
     */
    protected $_createdColumn = null;

    /**
     * @param null $createdColumn
     * @return $this|string
     */
    public function createdColumn($createdColumn = null)
    {
        if ($createdColumn === null)
        {
            return $this->_createdColumn;
        }

        $this->_createdColumn = $createdColumn;

        return $this;
    }

    /**
     * Auto-update columns for updates
     *
     * @var string
     */
    protected $_updatedColumn = null;

    /**
     * @param null $updatedColumn
     * @return mixed
     */
    public function updatedColumn($updatedColumn = null)
    {
        if ($updatedColumn === null)
        {
            return $this->_updatedColumn;
        }

        $this->_updatedColumn = $updatedColumn;

        return $this;
    }

    /**
     * @var array
     */
    protected $_changed = [];

    /**
     * 当前模型有改动过的数据信息
     *
     * @param string $field field to check for changes
     *
     * @return  bool  Whether or not the field has changed
     */
    public function changed($field = null)
    {
        return (null === $field)
            ? $this->_changed
            : Arr::get($this->_changed, $field);
    }

    /**
     * 扩展的label方法
     *
     * @param          $key
     * @param   mixed  $value
     * @return  $this
     */
    public function label($key, $value = null)
    {
        if ($value === null)
        {
            return isset($this->_labels[$key]) ? $this->_labels[$key] : $key;
        }

        $this->_labels[$key] = $value;
        return $this;
    }

    protected $_labels = [];

    /**
     * Label definitions for validation
     *
     * @param null $labels
     * @return array
     */
    public function labels($labels = null)
    {
        if ($labels === null)
        {
            return $this->_labels;
        }

        $this->_labels = $labels;
        return $this;
    }

    /**
     * 一对一关系
     *
     * @var array
     */
    protected $_hasOne = [];

    /**
     * 读取/设置一对一关系
     *
     * @param null $hasOne
     *
     * @return $this|array
     */
    public function hasOne($hasOne = null)
    {
        if ($hasOne === null)
        {
            return $this->_hasOne;
        }
        $this->_hasOne = $hasOne;

        return $this;
    }

    /**
     * 从属关系
     *
     * @var array
     */
    protected $_belongsTo = [];

    /**
     * 读取/设置从属关系
     *
     * @param null $belongsTo
     *
     * @return $this|array
     */
    public function belongsTo($belongsTo = null)
    {
        if ($belongsTo === null)
        {
            return $this->_belongsTo;
        }
        $this->_belongsTo = $belongsTo;

        return $this;
    }

    /**
     * 一对多关系
     *
     * @var array
     */
    protected $_hasMany = [];

    /**
     * 读取/设置一对多关系
     *
     * @param null $hasMany
     *
     * @return $this|array
     */
    public function hasMany($hasMany = null)
    {
        if ($hasMany === null)
        {
            return $this->_hasMany;
        }
        $this->_hasMany = $hasMany;

        return $this;
    }

    /**
     * 自动加载的关系
     *
     * @var array
     */
    protected $_loadWith = [];

    /**
     * 读取/设置自动加载关系
     *
     * @param null $loadWith
     *
     * @return $this|array
     */
    public function loadWith($loadWith = null)
    {
        if ($loadWith === null)
        {
            return $this->_loadWith;
        }
        $this->_loadWith = $loadWith;

        return $this;
    }

    /**
     * 当前对象数据
     *
     * @var array
     */
    protected $_object = [];

    /**
     * 读取/设置对象数据
     *
     * @param null $object
     *
     * @return $this|array
     */
    public function object($object = null)
    {
        if ($object === null)
        {
            return $this->_object;
        }
        $this->_object = $object;

        return $this;
    }


    /**
     * 外键后缀
     *
     * @var string
     */
    protected $_foreignKeySuffix = '_id';

    /**
     * 读取/设置自动加载关系
     *
     * @param null $foreignKeySuffix
     *
     * @return $this|string
     */
    public function foreignKeySuffix($foreignKeySuffix = null)
    {
        if ($foreignKeySuffix === null)
        {
            return $this->_foreignKeySuffix;
        }
        $this->_foreignKeySuffix = $foreignKeySuffix;

        return $this;
    }

    /**
     * 当前对象名
     *
     * @var string
     */
    protected $_objectName;

    /**
     * 读取/设置自动加载关系
     *
     * @param null $objectName
     *
     * @return $this|string
     */
    public function objectName($objectName = null)
    {
        if ($objectName === null)
        {
            return $this->_objectName;
        }
        $this->_objectName = $objectName;

        return $this;
    }

    /**
     * 表名
     *
     * @var string
     */
    protected $_tableName;

    /**
     * 设置和读取表名
     *
     * @param $tableName
     *
     * @return $this|string
     */
    public function tableName($tableName = null)
    {
        if ($tableName === null)
        {
            return $this->_tableName;
        }
        $this->_tableName = $tableName;

        return $this;
    }

    /**
     * 字段数组
     *
     * @var array
     */
    protected $_tableColumns = null;

    /**
     * 设置和读取自动序列化/反序列化的字段
     *
     * @param $tableColumns
     *
     * @return $this|array
     */
    public function tableColumns($tableColumns = null)
    {
        if ($tableColumns === null)
        {
            return $this->_tableColumns;
        }
        $this->_tableColumns = $tableColumns;

        return $this;
    }

    /**
     * 自动序列化/反序列化的字段
     *
     * @var array
     */
    protected $_serializeColumns = [];

    /**
     * 设置和读取自动序列化/反序列化的字段
     *
     * @param $serializeColumns
     *
     * @return $this|array
     */
    public function serializeColumns($serializeColumns = null)
    {
        if ($serializeColumns === null)
        {
            return $this->_serializeColumns;
        }
        $this->_serializeColumns = $serializeColumns;

        return $this;
    }

    /**
     * 当前模型使用的数据库组
     *
     * @var String
     */
    protected $_dbGroup = null;

    /**
     * 设置和读取当前模型使用的数据库组
     *
     * @param $dbGroup
     *
     * @return $this|string
     */
    public function dbGroup($dbGroup = null)
    {
        if ($dbGroup === null)
        {
            return $this->_dbGroup;
        }
        $this->_dbGroup = $dbGroup;

        return $this;
    }

    /**
     * 当前使用的数据库对象
     *
     * @var Connection
     */
    protected $_db = null;

    /**
     * 设置和读取当前模型使用的数据库访问实例
     *
     * @param Connection $db
     *
     * @return $this|Connection
     */
    public function db($db = null)
    {
        if ($db === null)
        {
            return $this->_db;
        }
        $this->_db = $db;

        return $this;
    }

    /**
     * The message filename used for validation errors.
     * Defaults to self::$_objectName
     *
     * @var string
     */
    protected $_errorFileName = null;

    /**
     * 设置和读取错误异常文本文件
     *
     * @param $errorFileName
     * @return $this|array
     */
    public function errorFileName($errorFileName = null)
    {
        if ($errorFileName === null)
        {
            return $this->_errorFileName;
        }
        $this->_errorFileName = $errorFileName;

        return $this;
    }

    protected $_asObject = false;

    /**
     * 设置和读取错误异常文本文件
     *
     * @param $asObject
     * @return $this|mixed
     */
    public function asObject($asObject = null)
    {
        if ($asObject === null)
        {
            return $this->_asObject;
        }
        $this->_asObject = $asObject;

        return $this;
    }

    /**
     * 序列化数据
     *
     * @param $value
     * @return string
     */
    protected function _serializeValue($value)
    {
        return json_encode($value);
    }

    /**
     * 反序列数据
     *
     * @param $value
     * @return mixed
     */
    protected function _unserializeValue($value)
    {
        return json_decode($value, true);
    }

    /**
     * Database methods pending
     *
     * @var array
     */
    protected $_dbPending = [];

    /**
     * 格式化字段名
     *
     * @param $column
     * @return string
     */
    protected function formatColumnName($column)
    {
        if (false === strpos($column, '.'))
        {
            $column = $this->_objectName . '.' . $column;
        }

        return $column;
    }

    /**
     * 精简条件查询
     *
     * @param   mixed  $column 字段名，或附加的查询字符串，或者是包含查询条件的数组
     * @param   string $op     操作符
     * @param   mixed  $value  字段值
     * @param   string $dbMethod
     */
    protected function parseWhereMethod($column, $op, $value, $dbMethod = 'where')
    {
        // 如果是数组的话，那么按照数组格式来做
        if (is_array($column))
        {
            foreach ($column as $k => $v)
            {
                // 如果是整数的话，那么可能是非关联的数据
                if (is_int($k) && is_array($v))
                {
                    // 根据数值个数来做判断
                    switch (count($v))
                    {
                        case 2:
                            $this->parseWhereMethod($v[0], '=', $v[1], $dbMethod);
                            break;
                        case 3:
                            $this->parseWhereMethod($v[0], $v[1], $v[2], $dbMethod);
                            break;
                        default:
                            // 跳过
                    }
                }
                else
                {
                    $this->parseWhereMethod($k, '=', $v, $dbMethod);
                }
            }

            return;
        }

        $column = $this->formatColumnName($column);

        // 如果没有操作符，那么第一个参数就当做完整的查询条件
        if ($op === null)
        {
            $this->_dbPending[] = [
                'name' => $dbMethod,
                'args' => [$column],
            ];
        }
        else
        {
            // 根据字段和数值，生成一个hash
            $variable = 'v' . md5($column . md5(serialize($value)));

            // IN特殊处理
            if ($op == 'IN')
            {
                $this->_dbPending[] = [
                    'name' => $dbMethod,
                    'args' => ["$column $op (:$variable)"],
                ];

                $this->_dbPending[] = [
                    'name' => 'setParameter',
                    'args' => [$variable, $value, Connection::PARAM_INT_ARRAY],
                ];
            }
            else
            {
                $this->_dbPending[] = [
                    'name' => $dbMethod,
                    'args' => ["$column $op :$variable"],
                ];

                $this->_dbPending[] = [
                    'name' => 'setParameter',
                    'args' => [$variable, $value],
                ];
            }
        }
    }

    /**
     * where查询方法，跟[andWhere]的实现差不多
     *
     * @param   mixed  $column 字段名，或附加的查询字符串
     * @param   string $op     操作符
     * @param   mixed  $value  字段值
     *
     * @return  $this
     */
    public function where($column, $op = null, $value = null)
    {
        $this->parseWhereMethod($column, $op, $value, 'where');

        return $this;
    }

    /**
     * Creates a new "AND WHERE" condition for the query.
     *
     * @param   mixed  $column column name or [$column, $alias] or object
     * @param   string $op     logic operator
     * @param   mixed  $value  column value
     *
     * @return  $this
     */
    public function andWhere($column, $op = null, $value = null)
    {
        $this->parseWhereMethod($column, $op, $value, 'andWhere');

        return $this;
    }

    /**
     * Creates a new "OR WHERE" condition for the query.
     *
     * @param   mixed  $column column name or [$column, $alias] or object
     * @param   string $op     logic operator
     * @param   mixed  $value  column value
     *
     * @return  $this
     */
    public function orWhere($column, $op = null, $value = null)
    {
        $this->parseWhereMethod($column, $op, $value, 'orWhere');

        return $this;
    }

    /**
     * Alias of andWhereOpen()
     *
     * @return  $this
     */
    public function whereOpen()
    {
        return $this->andWhereOpen();
    }

    /**
     * Opens a new "AND WHERE (...)" grouping.
     *
     * @return  $this
     */
    public function andWhereOpen()
    {
        // Add pending database call which is executed after query type is determined
        $this->_dbPending[] = [
            'name' => 'andWhereOpen',
            'args' => [],
        ];

        return $this;
    }

    /**
     * Opens a new "OR WHERE (...)" grouping.
     *
     * @return  $this
     */
    public function orWhereOpen()
    {
        $this->_dbPending[] = [
            'name' => 'orWhereOpen',
            'args' => [],
        ];

        return $this;
    }

    /**
     * Closes an open "AND WHERE (...)" grouping.
     *
     * @return  $this
     */
    public function whereClose()
    {
        return $this->andWhereClose();
    }

    /**
     * Closes an open "AND WHERE (...)" grouping.
     *
     * @return  $this
     */
    public function andWhereClose()
    {
        $this->_dbPending[] = [
            'name' => 'andWhereClose',
            'args' => [],
        ];

        return $this;
    }

    /**
     * Closes an open "OR WHERE (...)" grouping.
     *
     * @return  $this
     */
    public function orWhereClose()
    {
        $this->_dbPending[] = [
            'name' => 'orWhereClose',
            'args' => [],
        ];

        return $this;
    }

    /**
     * Applies sorting with "ORDER BY ..."
     *
     * @param   mixed  $column    column name or [$column, $alias] or object
     * @param   string $direction direction of sorting
     *
     * @return  $this
     */
    public function orderBy($column, $direction = null)
    {
        $this->_dbPending[] = [
            'name' => 'orderBy',
            'args' => [
                $column,
                $direction
            ],
        ];

        return $this;
    }

    /**
     * Return up to "LIMIT ..." results
     *
     * @param   integer $number maximum results to return
     *
     * @return  $this
     */
    public function limit($number)
    {
        $this->_dbPending[] = [
            'name' => 'setMaxResults',
            'args' => [$number],
        ];

        return $this;
    }

    /**
     * Enables or disables selecting only unique columns using "SELECT DISTINCT"
     *
     * @param   boolean $value enable or disable distinct columns
     *
     * @return  $this
     */
    public function distinct($value)
    {
        $this->_dbPending[] = [
            'name' => 'distinct',
            'args' => [$value],
        ];

        return $this;
    }

    /**
     * Choose the columns to select from.
     *
     * @param   mixed $columns column name or [$column, $alias] or object
     * @param   ...
     *
     * @return  $this
     */
    public function select($columns = null)
    {
        $columns = func_get_args();

        $this->_dbPending[] = [
            'name' => 'select',
            'args' => $columns,
        ];

        return $this;
    }

    /**
     * Choose the tables to select "FROM ..."
     *
     * @param   mixed $tables table name or [$table, $alias] or object
     * @param   ...
     *
     * @return  $this
     */
    public function from($tables)
    {
        $tables = func_get_args();

        $this->_dbPending[] = [
            'name' => 'from',
            'args' => $tables,
        ];

        return $this;
    }

    /**
     * Adds addition tables to "JOIN ...".
     *
     * @param      $fromAlias
     * @param      $join
     * @param      $alias
     * @param null $condition
     * @return $this
     */
    public function leftJoin($fromAlias, $join, $alias, $condition = null)
    {
        $this->_dbPending[] = [
            'name' => 'leftJoin',
            'args' => [
                $fromAlias,
                $join,
                $alias,
                $condition
            ],
        ];

        return $this;
    }

    /**
     * Adds addition tables to "JOIN ...".
     *
     * @param      $fromAlias
     * @param      $join
     * @param      $alias
     * @param null $condition
     * @return $this
     */
    public function join($fromAlias, $join, $alias, $condition = null)
    {
        $this->_dbPending[] = [
            'name' => 'join',
            'args' => [
                $fromAlias,
                $join,
                $alias,
                $condition
            ],
        ];

        return $this;
    }

    /**
     * Creates a "GROUP BY ..." filter.
     *
     * @param   mixed $columns column name or [$column, $alias] or object
     * @param   ...
     *
     * @return  $this
     */
    public function groupBy($columns)
    {
        $columns = func_get_args();

        // Add pending database call which is executed after query type is determined
        $this->_dbPending[] = [
            'name' => 'groupBy',
            'args' => $columns,
        ];

        return $this;
    }

    /**
     * Alias of andHaving()
     *
     * @param   mixed  $column column name or [$column, $alias] or object
     * @param   string $op     logic operator
     * @param   mixed  $value  column value
     *
     * @return  $this
     */
    public function having($column, $op, $value = null)
    {
        return $this->andHaving($column, $op, $value);
    }

    /**
     * Creates a new "AND HAVING" condition for the query.
     *
     * @param   mixed  $column column name or [$column, $alias] or object
     * @param   string $op     logic operator
     * @param   mixed  $value  column value
     *
     * @return  $this
     */
    public function andHaving($column, $op, $value = null)
    {
        $this->_dbPending[] = [
            'name' => 'andHaving',
            'args' => [
                $column,
                $op,
                $value
            ],
        ];

        return $this;
    }

    /**
     * Creates a new "OR HAVING" condition for the query.
     *
     * @param   mixed  $column column name or [$column, $alias] or object
     * @param   string $op     logic operator
     * @param   mixed  $value  column value
     *
     * @return  $this
     */
    public function orHaving($column, $op, $value = null)
    {
        $this->_dbPending[] = [
            'name' => 'orHaving',
            'args' => [
                $column,
                $op,
                $value
            ],
        ];

        return $this;
    }

    /**
     * Alias of andHavingOpen()
     *
     * @return  $this
     */
    public function havingOpen()
    {
        return $this->andHavingOpen();
    }

    /**
     * Opens a new "AND HAVING (...)" grouping.
     *
     * @return  $this
     */
    public function andHavingOpen()
    {
        $this->_dbPending[] = [
            'name' => 'andHavingOpen',
            'args' => [],
        ];

        return $this;
    }

    /**
     * Opens a new "OR HAVING (...)" grouping.
     *
     * @return  $this
     */
    public function orHavingOpen()
    {
        $this->_dbPending[] = [
            'name' => 'orHavingOpen',
            'args' => [],
        ];

        return $this;
    }

    /**
     * Closes an open "AND HAVING (...)" grouping.
     *
     * @return  $this
     */
    public function havingClose()
    {
        return $this->andHavingClose();
    }

    /**
     * Closes an open "AND HAVING (...)" grouping.
     *
     * @return  $this
     */
    public function andHavingClose()
    {
        $this->_dbPending[] = [
            'name' => 'andHavingClose',
            'args' => [],
        ];

        return $this;
    }

    /**
     * Closes an open "OR HAVING (...)" grouping.
     *
     * @return  $this
     */
    public function orHavingClose()
    {
        $this->_dbPending[] = [
            'name' => 'orHavingClose',
            'args' => [],
        ];

        return $this;
    }

    /**
     * Start returning results after "OFFSET ..."
     *
     * @param   integer $number starting result number
     *
     * @return  $this
     */
    public function offset($number)
    {
        $this->_dbPending[] = [
            'name' => 'setFirstResult',
            'args' => [$number],
        ];

        return $this;
    }

    /**
     * Enables the query to be cached for a specified amount of time.
     *
     * @param   integer $lifetime number of seconds to cache
     *
     * @return  $this
     * @uses    Base::$cacheLife
     */
    public function cached($lifetime = null)
    {
        $this->_dbPending[] = [
            'name' => 'cached',
            'args' => [$lifetime],
        ];

        return $this;
    }

    /**
     * Set the value of a parameter in the query.
     *
     * @param   string $param parameter key to replace
     * @param   mixed  $value value to use
     * @param null     $type
     * @return $this
     */
    public function param($param, $value, $type = null)
    {
        return $this->setParameter($param, $value, $type);
    }

    /**
     * Adds "USING ..." conditions for the last created JOIN statement.
     *
     * @param   string $columns column name
     *
     * @return  $this
     */
    public function using($columns)
    {
        $this->_dbPending[] = [
            'name' => 'using',
            'args' => [$columns],
        ];

        return $this;
    }

    /**
     * 绑定参数
     *
     * @param      $key
     * @param      $value
     * @param null $type
     * @return $this
     */
    public function setParameter($key, $value, $type = null)
    {
        $this->_dbPending[] = [
            'name' => 'setParameter',
            'args' => [$key, $value, $type],
        ];

        return $this;
    }
}
