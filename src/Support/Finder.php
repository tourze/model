<?php

namespace tourze\Model\Support;

/**
 * 查找记录相关方法
 *
 * @package tourze\Model\Support
 */
interface Finder
{

    /**
     * 查找指定记录
     *
     * @param string|array $conditions
     * @return mixed
     */
    public function find($conditions = null);

    /**
     * 查找符合条件的多条记录
     *
     * @param string|array $conditions
     * @return mixed
     */
    public function findAll($conditions = null);

}
