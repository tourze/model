<?php

namespace tourze\Model;

use tourze\Base\Security\Validation;
use tourze\Model\Exception\ModelException;
use tourze\Model\Exception\ValidationException;

/**
 * MPTT模型类
 *
 * @property MPTT parent
 * @package tourze\Model
 */
class MPTT extends Model
{

    /**
     * @var string left字段名
     */
    protected $_leftColumn = 'lft';

    /**
     * @var string right字段名
     */
    protected $_rightColumn = 'rgt';

    /**
     * @var string level字段名
     */
    protected $_levelColumn = 'lvl';

    /**
     * @var string  scope字段名
     */
    protected $_scopeColumn = 'scope';

    /**
     * @var string  parent字段名
     */
    protected $_parentColumn = 'parent_id';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        // 默认排序
        if ( ! isset($this->_sorting))
        {
            $this->_sorting = [$this->_leftColumn => 'ASC'];
        }
    }

    /**
     * 检查是否有下级节点
     *
     * @return  bool
     */
    public function hasChildren()
    {
        return $this->size() > 2;
    }

    /**
     * 当前节点是个叶子节点
     *
     * @return  bool
     */
    public function isLeaf()
    {
        return ! $this->hasChildren();
    }

    /**
     * 当前节点是否是目标节点的衍生节点
     *
     * @param  MPTT|int|array $target 实例或者主键ID
     * @return bool
     */
    public function isDescendant($target)
    {
        if ( ! ($target instanceof $this))
        {
            $target = new self($target);
        }

        return (
            $this->{$this->_leftColumn} > $target->{$target->_leftColumn}
            && $this->{$this->_rightColumn} < $target->{$target->_rightColumn}
            && $this->{$this->_scopeColumn} = $target->{$target->_scopeColumn}
        );
    }

    /**
     * 当前节点是否是目标节点的直接下级节点
     *
     * @param  static|int|array $target 实例或者主键ID
     * @return bool
     */
    public function isChild($target)
    {
        if ( ! ($target instanceof $this))
        {
            $target = new self($target);
        }

        return (int) $this->{$this->_parentColumn} === (int) $target->pk();
    }

    /**
     * 检测当前节点是否是指定节点的父节点
     *
     * @param  static|int|array $target 实例或者主键ID
     * @return bool
     */
    public function isParent($target)
    {
        if ( ! ($target instanceof $this))
        {
            $target = new self($target);
        }

        return ((int) $this->pk() === (int) $target->{$this->_parentColumn});
    }

    /**
     * 检查当前元素是否跟目标元素相邻
     *
     * @param  static|int|array $target 实例或者主键ID
     * @return bool
     */
    public function isSibling($target)
    {
        if ( ! ($target instanceof $this))
        {
            $target = new self($target);
        }

        if ((int) $this->pk() === (int) $target->pk())
        {
            return false;
        }

        return ((int) $this->{$this->_parentColumn} === (int) $target->{$target->_parentColumn});
    }

    /**
     * 检查当前节点是否为根节点
     *
     * @return  bool
     */
    public function isRoot()
    {
        return ($this->left() === 1);
    }

    /**
     * @inheritdoc
     */
    public function save(Validation $validation = null)
    {
        if ( ! $this->loaded())
        {
            return $this->makeRoot($validation);
        }
        elseif ($this->loaded() === true)
        {
            return parent::save($validation);
        }

        return false;
    }

    /**
     * 创建一个新的root节点
     *
     * @param Validation $validation
     * @param int        $scope
     * @return static
     */
    public function makeRoot(Validation $validation = null, $scope = null)
    {
        // If node already exists, and already root, exit
        if ($this->loaded() && $this->isRoot())
        {
            return $this;
        }

        // delete node space first
        if ($this->loaded())
        {
            $this->deleteSpace($this->left(), $this->size());
        }

        if (is_null($scope))
        {
            // Increment next scope
            $scope = self::getNextScope();
        }
        elseif ( ! $this->scopeAvailable($scope))
        {
            return false;
        }

        $this->{$this->_scopeColumn} = $scope;
        $this->{$this->_levelColumn} = 1;
        $this->{$this->_leftColumn} = 1;
        $this->{$this->_rightColumn} = 2;
        $this->{$this->_parentColumn} = null;

        return parent::save($validation);
    }

    /**
     * 设置当前节点的父级
     *
     * @param  static|int|array $target 实例或者主键ID
     * @param  string           $column 保存父级ID的字段
     * @return static
     */
    protected function parentFrom($target, $column = null)
    {
        if ( ! $target instanceof $this)
        {
            $target = new self($target);
        }

        if ($column === null)
        {
            $column = $target->primaryKey();
        }

        if ($target->loaded())
        {
            $this->{$this->_parentColumn} = $target->{$column};
        }
        else
        {
            $this->{$this->_parentColumn} = null;
        }

        return $target;
    }

    /**
     * 插入一个新节点，作为目标节点的第一个子节点
     *
     * @param  static|int|array $target 实例或者主键ID
     * @return static
     */
    public function insertAsFirstChild($target)
    {
        $target = $this->parentFrom($target);
        return $this->insert($target, $this->_leftColumn, 1, 1);
    }

    /**
     * 插入一个新节点，作为目标节点的最后一个子节点
     *
     * @param  static|int|array $target 实例或者主键ID
     * @return static
     */
    public function insertAsLastChild($target)
    {
        $target = $this->parentFrom($target, $this->primaryKey());
        return $this->insert($target, $this->_rightColumn, 0, 1);
    }

    /**
     * 插入一个新节点，作为目标节点的左节点
     *
     * @param  static|int|array $target 实例或者主键ID
     * @return static
     */
    public function insertAsPrevSibling($target)
    {
        $target = $this->parentFrom($target, $this->_parentColumn);
        return $this->insert($target, $this->_leftColumn, 0, 0);
    }

    /**
     * 插入一个新节点，作为目标节点的右节点
     *
     * @param  static|int|array $target 实例或者主键ID
     * @return static
     */
    public function insertAsNextSibling($target)
    {
        $target = $this->parentFrom($target, $this->_parentColumn);
        return $this->insert($target, $this->_rightColumn, 1, 0);
    }

    /**
     * Insert the object
     *
     * @access  protected
     * @param  self|int|array $target       实例或者主键ID
     * @param  string         $copyLeftFrom target object property to take new left value from
     * @param  int            $leftOffset   offset for left value
     * @param  int            $levelOffset  offset for level value
     * @return static
     * @throws ValidationException
     */
    protected function insert($target, $copyLeftFrom, $leftOffset, $levelOffset)
    {
        if ($this->loaded())
        {
            return false;
        }

        if ( ! $target instanceof $this)
        {
            $target = new self($target);
            if ( ! $target->loaded())
            {
                return false;
            }
        }
        else
        {
            $target->reload();
        }

        $this->{$this->_leftColumn} = $target->{$copyLeftFrom} + $leftOffset;
        $this->{$this->_rightColumn} = $this->{$this->_leftColumn} + 1;
        $this->{$this->_levelColumn} = $target->{$this->_levelColumn} + $levelOffset;
        $this->{$this->_scopeColumn} = $target->{$this->_scopeColumn};

        $this->createSpace($this->{$this->_leftColumn});

        try
        {
            parent::save();
        }
        catch (ValidationException $e)
        {
            // We had a problem saving, make sure we clean up the tree
            $this->deleteSpace($this->left());
            throw $e;
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function delete()
    {
        $this->db()
            ->createQueryBuilder()
            ->delete($this->_tableName)
            ->where($this->_leftColumn . ' >=' . $this->left())
            ->where($this->_rightColumn . ' <= ' . $this->right())
            ->where($this->_scopeColumn . ' = ' . $this->scope())
            ->execute();

        $this->deleteSpace($this->left(), $this->size());
    }

    /**
     * 返回一个select用的数组列表，带缩进的喔
     *
     * @param string $key    first table column.
     * @param string $value  second table column.
     * @param string $indent character used for indenting.
     * @return array
     */
    public function selectList($key = 'id', $value = 'name', $indent = null)
    {
        $result = (new self)
            ->where($this->_scopeColumn, '=', $this->{$this->_scopeColumn})
            ->orderBy($this->_leftColumn, 'ASC')
            ->findAll();

        $array = [];
        if (is_string($indent))
        {
            /** @var static $record */
            foreach ($result as $record)
            {
                $array[$record->get($key)] = str_repeat($indent, $record->get($this->_levelColumn)) . $record->get($value);
            }
        }
        else
        {
            /** @var static $record */
            foreach ($result as $record)
            {
                $array[$record->get($key)] = $record->get($value);
            }
        }
        return $array;
    }

    /**
     * @param  static|int|array $target 实例或者主键ID
     * @return $this
     */
    public function moveToFirstChild($target)
    {
        $target = $this->parentFrom($target, $this->primaryKey());
        return $this->move($target, true, 1, 1, true);
    }

    /**
     * @param  static|int|array $target 实例或者主键ID
     * @return $this
     */
    public function moveToLastChild($target)
    {
        $target = $this->parentFrom($target, $this->primaryKey());
        return $this->move($target, false, 0, 1, true);
    }

    /**
     * @param  static|int|array $target 实例或者主键ID
     * @return $this
     */
    public function moveToPrevSibling($target)
    {
        $target = $this->parentFrom($target, $this->_parentColumn);
        return $this->move($target, true, 0, 0, false);
    }

    /**
     * @param  static|int|array $target 实例或者主键ID
     * @return $this
     */
    public function moveToNextSibling($target)
    {
        //$target = $this->parentFrom($target, $this->parent_column);
        return $this->move($target, false, 1, 0, false);
    }

    /**
     * @param  MPTT|int|array $target 实例或者主键ID
     * @param  bool|int       $leftColumn
     * @param  bool|int       $leftOffset
     * @param  bool|int       $levelOffset
     * @param  bool|int       $allowRootTarget
     * @return $this|bool
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function move($target, $leftColumn, $leftOffset, $levelOffset, $allowRootTarget)
    {
        if ( ! $this->loaded())
        {
            return false;
        }

        // store the changed parent id before reload
        $parentID = $this->{$this->_parentColumn};

        // 保证数据是最新的
        $this->reload();

        if ( ! $target instanceof $this)
        {
            $target = new self($target);
            if ( ! $target->loaded())
            {
                return false;
            }
        }
        else
        {
            $target->reload();
        }

        // Stop $this being moved into a descendant or itself or disallow if target is root
        if ($target->isDescendant($this)
            || $this->pk() === $target->pk()
            || ($allowRootTarget === false && $target->isRoot())
        )
        {
            return false;
        }

        if ($levelOffset > 0)
        {
            // We're moving to a child node so add 1 to left offset.
            $leftOffset = ($leftColumn === true) ? ($target->left() + 1) : ($target->right() + $leftOffset);
        }
        else
        {
            $leftOffset = ($leftColumn === true) ? $target->left() : ($target->right() + $leftOffset);
        }

        $levelOffset = $target->level() - $this->level() + $levelOffset;
        $size = $this->size();

        $this->createSpace($leftOffset, $size);

        $this->reload();

        $offset = ($leftOffset - $this->left());

        $this->db()
            ->createQueryBuilder()
            ->update($this->_tableName)
            ->set($this->_leftColumn, $this->_leftColumn . '+' . $offset)
            ->set($this->_rightColumn, $this->_rightColumn . '+' . $offset)
            ->set($this->_levelColumn, $this->_levelColumn . '+' . $levelOffset)
            ->set($this->_scopeColumn, $target->scope())
            ->where($this->_leftColumn . '>=' . $this->left())
            ->andWhere($this->_rightColumn . '<=' . $this->right())
            ->andWhere($this->_scopeColumn . '=' . $this->scope())
            ->execute();

        $this->deleteSpace($this->left(), $size);

        // all went well so save the parent_id if changed
        if ($parentID != $this->{$this->_parentColumn})
        {
            $this->{$this->_parentColumn} = $parentID;
            $this->save();
        }

        $this->reload();

        return $this;
    }

    /**
     * Returns the next available value for scope.
     *
     * @return  int
     **/
    protected function getNextScope()
    {
        $scope = $this->db()
            ->createQueryBuilder()
            ->select('IFNULL(MAX(`' . $this->_scopeColumn . '`), 0) as scope')
            ->from($this->_tableName)
            ->execute()
            ->fetch();

        if ($scope && intval($scope['scope']) > 0)
        {
            return intval($scope['scope']) + 1;
        }

        return 1;
    }

    /**
     * 返回当前对象实例的根节点
     *
     * @param  int $scope scope
     * @return bool|\tourze\Model\MPTT
     * @throws ModelException
     */
    public function root($scope = null)
    {
        if (is_null($scope) AND $this->loaded())
        {
            $scope = $this->scope();
        }
        elseif (is_null($scope) AND ! $this->loaded())
        {
            throw new ModelException(':method must be called on an ORM_MPTT object instance.', [
                ':method' => 'root'
            ]);
        }

        return new self([
            $this->_leftColumn  => 1,
            $this->_scopeColumn => $scope
        ]);
    }

    /**
     * Returns all root node's
     *
     * @return MPTT
     */
    public function roots()
    {
        return (new self)
            ->where($this->_leftColumn, '=', 1)
            ->findAll();
    }

    /**
     * 返回当前节点的临近父节点
     *
     * @return MPTT
     */
    public function parent()
    {
        return $this->parents(true, false, 'ASC', true);
    }

    /**
     * 返回当前节点的所有父节点
     *
     * @param  bool   $root             包含root节点
     * @param  bool   $withSelf         是否包含当前节点
     * @param  string $direction        排序
     * @param  bool   $directParentOnly 只读取直接的上级信息
     * @return MPTT|MPTT[]
     */
    public function parents($root = true, $withSelf = false, $direction = 'ASC', $directParentOnly = false)
    {
        $suffix = $withSelf ? '=' : '';

        $query = new self;
        $query
            ->where($this->_leftColumn, '<' . $suffix, $this->left())
            ->where($this->_rightColumn, '>' . $suffix, $this->right())
            ->where($this->_scopeColumn, '=', $this->scope())
            ->orderBy($this->_leftColumn, $direction);

        if ( ! $root)
        {
            $query->where($this->_leftColumn, '!=', 1);
        }

        if ($directParentOnly)
        {
            $query
                ->where($this->_levelColumn, '=', $this->level() - 1)
                ->limit(1);
        }

        return $directParentOnly ? $query->find() : $query->findAll();
    }

    /**
     * 获取当前节点的直接下级节点
     *
     * @param  bool   $self      是否包含当前节点
     * @param  string $direction 排序
     * @param  int    $limit     读取条数
     * @return MPTT
     */
    public function children($self = false, $direction = 'ASC', $limit = 0)
    {
        return $this->descendants($self, $direction, true, false, $limit);
    }

    /**
     * 返回一个完整的树
     *
     * @param  bool $scope 只返回指定的scope
     * @return MPTT[]
     */
    public function fullTree($scope = null)
    {
        $result = new self;

        if ( ! is_null($scope))
        {
            $result->where($this->_scopeColumn, '=', $scope);
        }
        else
        {
            $result
                ->orderBy($this->_scopeColumn, 'ASC')
                ->orderBy($this->_leftColumn, 'ASC');
        }

        return $result->findAll();
    }

    /**
     * Returns the siblings of the current node
     *
     * @param  bool   $self      include the current node
     * @param  string $direction direction to order the left column by
     * @return MPTT[]
     */
    public function siblings($self = false, $direction = 'ASC')
    {
        $query = new self;
        $query
            ->where($this->_leftColumn, '>', $this->parent->left())
            ->where($this->_rightColumn, '<', $this->parent->right())
            ->where($this->_scopeColumn, '=', $this->scope())
            ->where($this->_levelColumn, '=', $this->level())
            ->orderBy($this->_leftColumn, $direction);

        if ( ! $self)
        {
            $query->where($this->primaryKey(), '<>', $this->pk());
        }

        return $query->findAll();
    }

    /**
     * 返回当前节点的叶子节点
     *
     * @param  bool   $self      是否包含当前节点
     * @param  string $direction 排序
     * @return MPTT
     */
    public function leaves($self = false, $direction = 'ASC')
    {
        return $this->descendants($self, $direction, true, true);
    }

    /**
     * 返回当前节点的后代
     *
     * @param  bool   $self               是否包含当前节点
     * @param  string $direction          排序
     * @param  bool   $directChildrenOnly 只包含相邻的下级节点
     * @param  bool   $leavesOnly         只包含叶子节点
     * @param  int    $limit              要获取的记录条数
     * @return MPTT[]
     */
    public function descendants($self = false, $direction = 'ASC', $directChildrenOnly = false, $leavesOnly = false, $limit = 0)
    {
        $left_operator = $self ? '>=' : '>';
        $right_operator = $self ? '<=' : '<';

        $query = new self;
        $query
            ->where($this->_leftColumn, $left_operator, $this->left())
            ->where($this->_rightColumn, $right_operator, $this->right())
            ->where($this->_scopeColumn, '=', $this->scope())
            ->orderBy($this->_leftColumn, $direction);

        if ($directChildrenOnly)
        {
            if ($self)
            {
                $query
                    ->andWhereOpen()
                    ->where($this->_levelColumn, '=', $this->level())
                    ->orWhere($this->_levelColumn, '=', $this->level() + 1)
                    ->andWhereClose();
            }
            else
            {
                $query->where($this->_levelColumn, '=', $this->level() + 1);
            }
        }

        if ($leavesOnly)
        {
            $query
                ->where($this->_rightColumn, '=', $this->_leftColumn . ' + 1');
        }

        if ($limit)
        {
            $query->limit($limit);
        }

        return $query->findAll();
    }

    /**
     * 在树中间插入空隙
     *
     * @param int $start 开始位置
     * @param int $size  要添加的空隙
     */
    protected function createSpace($start, $size = 2)
    {
        $this->db()
            ->createQueryBuilder()
            ->update($this->_tableName)
            ->set($this->_leftColumn, $this->_leftColumn . ' + ' . $size)
            ->where($this->_leftColumn . '>=' . $start)
            ->where($this->_scopeColumn . '=' . $this->scope())
            ->execute();

        $this->db()
            ->createQueryBuilder()
            ->update($this->_tableName)
            ->set($this->_rightColumn, $this->_rightColumn . ' + ' . $size)
            ->where($this->_rightColumn . '>=' . $start)
            ->where($this->_scopeColumn . '=' . $this->scope())
            ->execute();
    }

    /**
     * 删除空隙
     *
     * @param int $start 开始位置
     * @param int $size  要添加的空隙
     */
    protected function deleteSpace($start, $size = 2)
    {
        $this->db()
            ->createQueryBuilder()
            ->update($this->_tableName)
            ->set($this->_leftColumn, $this->_leftColumn . ' - ' . $size)
            ->where($this->_leftColumn . '>=' . $start)
            ->where($this->_scopeColumn . '=' . $this->scope())
            ->execute();

        $this->db()
            ->createQueryBuilder()
            ->update($this->_tableName)
            ->set($this->_rightColumn, $this->_rightColumn . ' - ' . $size)
            ->where($this->_rightColumn . '>=' . $start)
            ->where($this->_scopeColumn . '=' . $this->scope())
            ->execute();
    }

    /**
     * 返回做节点
     *
     * @return int
     */
    public function left()
    {
        return (int) $this->{$this->_leftColumn};
    }

    /**
     * 返回右节点
     *
     * @return int
     */
    public function right()
    {
        return (int) $this->{$this->_rightColumn};
    }

    /**
     * 返回当前节点的级别
     *
     * @return int
     */
    public function level()
    {
        return (int) $this->{$this->_levelColumn};
    }

    /**
     * 返回当前节点的scope
     *
     * @return int
     */
    public function scope()
    {
        return (int) $this->{$this->_scopeColumn};
    }

    /**
     * 返回当前节点的尺寸
     *
     * @return int
     */
    public function size()
    {
        return $this->right() - $this->left() + 1;
    }

    /**
     * 返回当前节点的后代数
     *
     * @return int
     */
    public function count()
    {
        return ($this->size() - 2) / 2;
    }

    /**
     * 检查指定的scope是否有效
     *
     * @param  int $scope 要检查的scope
     * @return bool
     */
    protected function scopeAvailable($scope)
    {
        return (bool) ! (new self)
            ->where($this->_scopeColumn, '=', $scope)
            ->countAll();
    }

    /**
     * 根据parentColumn来重构整个树
     *
     * @param  int  $left   Starting value for left branch
     * @param  MPTT $target Target node to use as root
     * @return int
     */
    public function rebuildTree($left = 1, $target = null)
    {
        if (is_null($target) && ! $this->loaded())
        {
            return false;
        }
        elseif (is_null($target))
        {
            $target = $this;
        }

        if ( ! $target->loaded())
        {
            $target->find();
        }

        if (is_null($left))
        {
            $left = $target->{$target->_leftColumn};
        }

        $right = $left + 1;
        $children = $target->children();

        /** @var MPTT $child */
        foreach ($children as $child)
        {
            $right = $child->rebuildTree($right);
        }

        $target->{$target->_leftColumn} = $left;
        $target->{$target->_rightColumn} = $right;
        $target->save();

        return $right + 1;
    }

    /**
     * Magic get function, maps field names to class functions.
     *
     * @param  string $column name of the field to get
     * @return mixed
     */
    public function __get($column)
    {
        switch ($column)
        {
            case 'parents':
                return $this->parents();
            case 'children':
                return $this->children();
            case 'first_child':
                return $this->children(false, 'ASC', 1);
            case 'last_child':
                return $this->children(false, 'DESC', 1);
            case 'siblings':
                return $this->siblings();
            case 'root':
                return $this->root();
            case 'roots':
                return $this->roots();
            case 'leaves':
                return $this->leaves();
            case 'descendants':
                return $this->descendants();
            default:
                return parent::__get($column);
        }
    }

    /**
     * @return MPTT
     */
    public function getParent()
    {
        return $this->parent();
    }

    /**
     * @return MPTT[]
     */
    public function getFullTree()
    {
        return $this->fullTree();
    }
}
