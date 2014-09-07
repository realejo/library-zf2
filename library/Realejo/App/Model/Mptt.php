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
        if (empty($this->_traversal)) return;

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
            $this->_traversal['column'] = $this->getKey();
        }

        if (!in_array($this->_traversal['column'], $columns)) {
            throw new \Exception("Column '" . $this->_traversal['column'] . "' not found in table for tree traversal");
        }

        // Check for reference column
        if (!isset($this->_traversal['refColumn'])) {
            throw new \Exception("Unable to determine reference column for traversal");
        }

        if (!in_array($this->_traversal['refColumn'], $columns)) {
            throw new \Exception("Column '" . $this->_traversal['refColumn'] . "' not found in table for tree traversal");
        }

        // Check the order
        if (!isset($this->_traversal['order'])) {
            $this->_traversal['order'] = $this->getKey();
        }

        if (!in_array($this->_traversal['order'], $columns)) {
            throw new \Exception("Column '" . $this->_traversal['order'] . "' not found in table for tree traversal");
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

        if (!empty($parentId)) {
            $select->where(array($this->_traversal['refColumn'] => $parentId));
        } else {
            $select->where(new \Zend\Db\Sql\Predicate\Expression("{$this->_traversal['refColumn']} IS NULL OR {$this->_traversal['refColumn']} = 0"));
        }

        // Define the order
        $select->order($this->_traversal['order']);

        $rightValue = $leftValue + 1;

        $rowset = $this->getTableGateway()->selectWith($select);
        foreach ($rowset as $row) {
            $rightValue = $this->_rebuildTreeTraversal($row->{$this->_traversal['column']}, $rightValue);
        }

        if (!empty($parentId)) {
            $this->getTableGateway()
                 ->update(array(
                     $this->_traversal['left'] => $leftValue,
                     $this->_traversal['right'] => $rightValue
                 ), array($this->_traversal['column'] => $parentId));
        }

        return $rightValue + 1;
    }

    /**
     * Override insert method
     *
     * @param mixed $set
     *
     * @return primary key
     */
    public function insert($set)
    {
        return $this->isTraversable() ? $this->_insertTraversable($set) : parent::insert($set);
    }

    /**
     * Calculates left and right values for new row and inserts it.
     * Also adjusts all rows to make room for the new row.
     *
     * @param array $set
     * @return int $id
     */
    protected function _insertTraversable($set)
    {
        $this->_verifyTraversable();

        // Disable traversable flag to prevent automatic traversable manipulation during updates.
        $isTraversable = $this->_isTraversable;
        $this->_isTraversable = false;

        if (array_key_exists($this->_traversal['refColumn'], $set) && $set[$this->_traversal['refColumn']] > 0) {
            // Find parent
            $parent_id = $set[$this->_traversal['refColumn']];
            $parent = $this->getTableGateway()->select(array($this->getKey()=>$parent_id))->current();
            if (null === $parent) {
                throw new \Exception("Traversable error: Parent id {$parent_id} not found");
            }

            $lt = (double) $parent->{$this->_traversal['left']};
            $rt = (double) $parent->{$this->_traversal['right']};

            // Find siblings
            $select = $this->getTableGateway()->getSql()->select();
            $select->where(array($this->_traversal['refColumn']=>$parent_id));

            $siblings = $this->getTableGateway()->selectWith($select);

            // Define the position of the new node
            // Checks if it has any sibling on the left, considering the defined order
            $previousSibling = null;
            foreach($siblings as $s) {
                if (is_string($s[$this->_traversal['order']])) {
                    if (strcmp($s[$this->_traversal['order']], $set[$this->_traversal['order']]) > 0) {
                        break;
                    }

                } else {
                    if ($s[$this->_traversal['order']] >= $set[$this->_traversal['order']]) {
                        break;
                    }
                }
                $previousSibling = $s;
            }

            // If there is a sibling on the left, use it for positioning
            if (!empty($previousSibling)) {
                $lt = (double) $previousSibling->{$this->_traversal['left']};
                $rt = (double) $previousSibling->{$this->_traversal['right']};
                $pos = $rt;

                $set[$this->_traversal['left']] = $rt + 1;
                $set[$this->_traversal['right']] = $rt + 2;

            // Insert o the start os the list os siblings or alone
            } else {
                $set[$this->_traversal['left']] = $lt + 1;
                $set[$this->_traversal['right']] = $lt + 2;
                $pos = $lt;
            }

            // Make room for the new node
            $this->getTableGateway()->update(
                array(
                    $this->_traversal['left'] => new \Zend\Db\Sql\Predicate\Expression("{$this->_traversal['left']} + 2"),
                ), new \Zend\Db\Sql\Predicate\Expression("{$this->_traversal['left']} > $pos")
            );

            $this->getTableGateway()->update(
                array(
                    $this->_traversal['right'] => new \Zend\Db\Sql\Predicate\Expression("{$this->_traversal['right']} + 2"),
                ), new \Zend\Db\Sql\Predicate\Expression("{$this->_traversal['right']} > $pos")
            );
        } else {
            $select = $this->getTableGateway()->getSql()->select();
            $select->reset('columns')->columns(array('theMax' => new \Zend\Db\Sql\Predicate\Expression("MAX({$this->_traversal['right']})")));
            $maxRt = (double) $this->getTableGateway()->selectWith($select)->current()->theMax;
            $set[$this->_traversal['left']] = $maxRt + 1;
            $set[$this->_traversal['right']] = $maxRt + 2;
        }

        // Do insert
        $id = $this->getTableGateway()->insert($set);

        // Reset isTraversable flag to previous value.
        $this->_isTraversable = $isTraversable;

        return $id;
    }

    /**
     * Override delete method
     *
     * @param mixed $key
     */
    public function delete($key)
    {
        return $this->isTraversable() ? $this->_deleteTraversable($key) : parent::delete($key);
    }

    /**
     * Remove the row and calculate left and right values for the remaining rows.
     * It will delete all child nodes from the row
     *
     * @param array $key
     */
    protected function _deleteTraversable($key)
    {
        $this->_verifyTraversable();

        // Disable traversable flag to prevent automatic traversable manipulation during updates.
        $isTraversable = $this->_isTraversable;
        $this->_isTraversable = false;

        // Get the row to be deleted
        $row = $this->fetchRow($key);

        // Delete the node and it's childs
        $delete = $this->getTableGateway()->delete(new \Zend\Db\Sql\Predicate\Expression("{$this->_traversal['left']} >= {$row[$this->_traversal['left']]} and {$this->_traversal['right']} <= {$row[$this->_traversal['right']]}"));

        // Fixes the left,right for the remaining nodes
        $fix = $row[$this->_traversal['right']] - $row[$this->_traversal['left']] + 1;
        $this->getTableGateway()->update(
            array(
                $this->_traversal['left'] => new \Zend\Db\Sql\Predicate\Expression("{$this->_traversal['left']} - $fix"),
            ), new \Zend\Db\Sql\Predicate\Expression("{$this->_traversal['left']} > {$row[$this->_traversal['left']]}")
        );
        $this->getTableGateway()->update(
            array(
                $this->_traversal['right'] => new \Zend\Db\Sql\Predicate\Expression("{$this->_traversal['right']} - $fix"),
            ), new \Zend\Db\Sql\Predicate\Expression("{$this->_traversal['right']} > {$row[$this->_traversal['right']]}")
        );


        // Reset isTraversable flag to previous value.
        $this->_isTraversable = $isTraversable;

        return $delete;
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
