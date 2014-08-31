<?php
/**
 * Aplicação de MPTT
 *
 * @link      http://github.com/realejo/libraray-zf2
 * @copyright Copyright (c) 2014 Realejo (http://realejo.com.br)
 * @license   http://unlicense.org
 *
 * @see http://www.sitepoint.com/print/hierarchical-data-database
 */
namespace Realejo\App\Model;

use Realejo\App\Model\Db;

class Mptt extends Db
{
    /**
     * Traversal tree information for
     * Modified Preorder Tree Traversal Model
     *
     * Values:
     *  'left'      => column name for left value, default: lft
     *  'right'     => column name for right value, default: rgt
     *  'column'    => column name for identifying row (primary key assumed)
     *  'refColumn' => column name for parent id (if not set, will look in reference map for own table match)
     *  'order'     => order by for rebuilding tree (e.g. "`name` ASC, `age` DESC")
     *
     * @var array
     */
    protected $_traversal = array();

    /**
     * Automatically is set to true once traversal info is set and verified
     *
     * @var boolean
    */
    protected $_isTraversable = false;

    /**
     * Modified to initialize traversal
     *
     */
    public function __construct($table = null, $key = null, $dbAdapter = null)
    {
        parent::__construct($table, $key, $dbAdapter);
        $this->_initTraversal();
    }

    /**
     * Prepares the traversal information
     *
     */
    protected function _initTraversal()
    {
        if (empty($this->_traversal) || $this->_isTraversable) return;

        $columns = $this->getColumns();

        // Verify 'left' value and column
        if (!isset($this->_traversal['left'])) {
            $this->_traversal['left'] = 'lft';
        }

        if (!in_array($this->_traversal['left'], $columns)) {
            throw new \Exception("Column '" . $this->_traversal['left'] . "' not found in table for tree traversal");
        }

        // Verify 'right' value and column
        if (!isset($this->_traversal['right'])) {
            $this->_traversal['right'] = 'rgt';
        }

        if (!in_array($this->_traversal['right'], $columns)) {
            throw new \Exception("Column '" . $this->_traversal['right'] . "' not found in table for tree traversal");
        }

        // Check for identifying column
        if (!isset($this->_traversal['column'])) {
            if (!isset($this->key)) {
                throw new \Exception("Unable to determine primary key for tree traversal");
            }

            if (is_array($this->key)) {
                throw new \Exception("Cannot use compound primary key as identifying column for tree traversal, please specify the column manually");
            }

            $this->_traversal['column'] = $this->key;
        }

        // Check for reference column
        if (!isset($this->_traversal['refColumn'])) {
            throw new \Exception("Unable to determine reference column for traversal");
        }

        if (!in_array($this->_traversal['refColumn'], $columns)) {
            throw new \Exception("Column '" . $this->_traversal['refColumn'] . "' not found in table for tree traversal");
        }

        $this->_isTraversable = true;
    }

    /**
     * Public function to rebuild tree traversal. The recursive function
     * _rebuildTreeTraversal() must be called without arguments.
     *
     * @return $this - Fluent interface
     */
    public function rebuildTreeTraversal()
    {
        $this->_rebuildTreeTraversal();

        return $this;
    }

    /**
     * Recursively rebuilds the modified preorder tree traversal
     * data based on a parent id column
     *
     * @param int $parentId
     * @param int $leftValue
     * @return int new right value
     */
    protected function _rebuildTreeTraversal($parentId = null, $leftValue = 0)
    {
        $this->_verifyTraversable();

        // Do not use getSQLSelect() to avoid defined joins
        $select = $this->getTableGateway()
                       ->getSql()->select();

        if ($parentId > 0) {
            $select->where(array($this->_traversal['refColumn'] => $parentId));
        } else {
            $select->where(new \Zend\Db\Sql\Predicate\Expression("{$this->_traversal['refColumn']} IS NULL OR {$this->_traversal['refColumn']} = 0"));
        }

        if (array_key_exists('order', $this->_traversal)) {
            $select->order($this->_traversal['order']);
        }

        $rightValue = $leftValue + 1;

        $rowset = $this->getTableGateway()->selectWith($select);
        foreach ($rowset as $row) {
            $rightValue = $this->_rebuildTreeTraversal($row->{$this->_traversal['column']}, $rightValue);
        }

        if ($parentId > 0) {
            $this->getTableGateway()
                 ->update(array(
                     $this->_traversal['left'] => $leftValue,
                     $this->_traversal['right'] =>  $rightValue
                 ), array($this->_traversal['column'] =>$parentId));
        }

        return $rightValue + 1;
    }

    /**
     * Returns columns names
     *
     * @todo colocar no cache
     *
     * @return array columns
     */
    public function getColumns()
    {
        if (!isset($this->_columns)) {
            $metadata = new \Zend\Db\Metadata\Metadata($this->getTableGateway()->getAdapter());
            $this->_columns = $metadata->getColumnNames($this->getTable());
        }

        return $this->_columns;
    }

    /**
     * Defines the traversal info
     *
     * if passes a string, assumes it's the refColumn
     *
     * @param string|array $traversal
     *
     * @return self
     */
    public function setTraversal($traversal)
    {
        // Verifica se é apenas o campo de referencia
        if (is_string($traversal)) {
            $traversal = array('refColumn'=>$traversal);
        }

        $this->_traversal = $traversal;

        $this->_initTraversal();

        return $this;
    }

    /**
     * Return if the table is traversable
     * Only set to true after initTraversal
     */
    public function isTraversable()
    {
        return $this->_isTraversable;
    }


    /**
     * Verifies that the current table is a traversable
     *
     * @throws Exception - Table is not traversable
     */
    protected function _verifyTraversable()
    {
        if (!$this->isTraversable()) {
            throw new \Exception("Table {$this->table} is not traversable");
        }
    }
}
