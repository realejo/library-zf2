<?php
/**
 * MpttTest test case.
 *
 * @link      http://github.com/realejo/libraray-zf2
 * @copyright Copyright (c) 2014 Realejo (http://realejo.com.br)
 * @license   http://unlicense.org
 */
use Realejo\App\Model\Mptt;

/**
 * Mptt test case.
 */
class MpttTest extends BaseTestCase
{
    /**
     * Árvore mptt completa e *correta*
     * com left,right ordenado pelo id
     *
     * @var array
     */
    protected $idOrderedTree = array(
        array(1,  'Food',      null, 1, 24),
        array(2,  'Fruit',     1, 2, 13),
        array(3,  'Red',       2, 3, 6),
        array(4,  'Yellow',    2, 7, 10),
        array(5,  'Green',     2, 11, 12),
        array(6,  'Cherry',    3, 4, 5),
        array(7,  'Banana',    4, 8, 9),
        array(8,  'Meat',      1, 14, 19),
        array(9,  'Beef',      8, 15, 16),
        array(10, 'Pork',      8, 17, 18),
        array(11, 'Vegetable', 1, 20, 23),
        array(12, 'Carrot',   11, 21, 22),
    );

    /**
     * Árvore mptt completa e *correta*
     * com left,right ordenado pelo name
     *
     * @var array
     */
    protected $nameOrderedTree = array(
        array(1,  'Food',      null, 1, 24),
        array(2,  'Fruit',     1, 2, 13),
        array(3,  'Red',       2, 5, 8),
        array(4,  'Yellow',    2, 9, 12),
        array(5,  'Green',     2, 3, 4),
        array(6,  'Cherry',    3, 6, 7),
        array(7,  'Banana',    4, 10, 11),
        array(8,  'Meat',      1, 14, 19),
        array(9,  'Beef',      8, 15, 16),
        array(10, 'Pork',      8, 17, 18),
        array(11, 'Vegetable', 1, 20, 23),
        array(12, 'Carrot',   11, 21, 22),
    );

    /**
     * Será populada com os valores da arvore completa
     * @var array
     */
    protected $idOrderedRows = array();
    protected $nameOrderedRows = array();

    /**
     * Será populada com os valores da arvore completa sem as informações left,right
     * @var array
     */
    protected $defaultRows = array();

    protected $tables = array('mptt');

    /**
     * @var Mptt
     */
    private $Mptt;

    public function __construct()
    {
        $fields = array('id', 'name', 'parent_id', 'lft', 'rgt');
        foreach ($this->idOrderedTree as $values) {
            $row = array_combine($fields, $values);
            $this->idOrderedRows[] = $row;
            unset($row['lft']);
            unset($row['rgt']);
            $this->defaultRows[] = $row;
        }

        foreach ($this->nameOrderedTree as $values) {
            $row = array_combine($fields, $values);
            $this->nameOrderedRows[] = $row;
        }
    }

    /**
     *
     * @return \Realejo\Db\TableAdapterTest
     */
    public function truncateTable()
    {
        $this->dropTables()->createTables();
        return $this;
    }

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp()
    {
        parent::setUp();
        $this->dropTables()->createTables();
    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown()
    {
        parent::tearDown();
        //$this->dropTables();
    }

    /**
     * Tests Mptt->__construct()
     */
    public function test__construct()
    {
        // Cria a tabela sem a implementação do transversable
        $mptt = new Mptt('mptt', 'id');
        $this->assertInstanceOf('\Realejo\App\Model\Mptt', $mptt);
        $this->assertInstanceOf('\Realejo\App\Model\Db', $mptt);
    }

    /**
     * Tests Mptt->setTraversal()
     * @expectedException Exception
     */
    public function testSetTraversalIncomplete()
    {
        // Cria a tabela sem a implementação do transversable
        $mptt = new Mptt('mptt', 'id');
        $this->assertInstanceOf('\Realejo\App\Model\Mptt', $mptt);
        $this->assertInstanceOf('\Realejo\App\Model\Db', $mptt);

        $mptt = $mptt->setTraversal(array());

        $this->assertInstanceOf('\Realejo\App\Model\Mptt', $mptt);

        // The Exception
        $mptt->setTraversal(array('invalid'=>'invalid'));
    }

    /**
     * Tests Mptt->getColumns()
     */
    public function testGetColumns()
    {
        $mptt = new Mptt('mptt', 'id');
        $this->assertInternalType('array', $mptt->getColumns());
        $this->assertNotNull($mptt->getColumns());
        $this->assertNotEmpty($mptt->getColumns());
        $this->assertEquals(array('id', 'name', 'parent_id', 'lft', 'rgt'), $mptt->getColumns());
    }

    /**
     * Tests Mptt->setTraversal()
     */
    public function testSetTraversal()
    {
        $mptt = new Mptt('mptt', 'id');
        $this->assertFalse($mptt->isTraversable());
        $mptt->setTraversal('parent_id');
        $this->assertTrue($mptt->isTraversable());
    }

    /**
     * Tests Mptt->rebuildTreeTraversal()
     */
    public function testRebuildTreeTraversal()
    {
        // Cria a tablea com os valores padrões
        $mptt = new Mptt('mptt', 'id');
        $this->assertNull($mptt->fetchAll());
        foreach($this->defaultRows as $row) {
            $mptt->insert($row);
        }
        $this->assertNotNull($mptt->fetchAll());
        $this->assertCount(count($this->defaultRows), $mptt->fetchAll());

        // Set traversal
        $this->assertFalse($mptt->isTraversable());
        $mptt->setTraversal('parent_id');
        $this->assertTrue($mptt->isTraversable());

        // Rebuild Tree
        $mptt->rebuildTreeTraversal();

        $this->assertEquals($this->idOrderedRows, $mptt->fetchAll());

        $mptt->setTraversal(array('refColumn'=>'parent_id', 'order'=>'name'));

        // Rebuild Tree
        $mptt->rebuildTreeTraversal();
        $this->assertTrue($mptt->isTraversable());

        $this->assertEquals($this->nameOrderedRows, $mptt->fetchAll());
    }

    /**
     * Tests Mptt->rebuildTreeTraversal()
     */
    public function testInsert()
    {
        // Cria a tablea com os valores padrões
        $mptt = new Mptt('mptt', 'id');
        $this->assertNull($mptt->fetchAll());

        // Set traversal
        $this->assertFalse($mptt->isTraversable());
        $mptt->setTraversal(array('refColumn'=>'parent_id', 'order'=>'name'));
        $this->assertTrue($mptt->isTraversable());

        // Insert default rows
        foreach($this->defaultRows as $row) {
            $mptt->insert($row);
        }
        $this->assertNotNull($mptt->fetchAll());
        $this->assertCount(count($this->defaultRows), $mptt->fetchAll());

        // Assert if left/right is correct
        $this->assertEquals($this->nameOrderedRows, $mptt->fetchAll());

        // reset the table
        $this->dropTables()->createTables();
        $this->assertNull($mptt->fetchAll());

        // Set traversal ordered by id
        $mptt->setTraversal(array('refColumn'=>'parent_id'));
        $this->assertTrue($mptt->isTraversable());

        // insert default rows
        foreach($this->defaultRows as $row) {
            $mptt->insert($row);
        }
        $this->assertNotNull($mptt->fetchAll());
        $this->assertCount(count($this->defaultRows), $mptt->fetchAll());

        // Assert if left/right is correct
        $this->assertEquals($this->idOrderedRows, $mptt->fetchAll());
    }

    /**
     * Tests Mptt->rebuildTreeTraversal()
     */
    public function testDelete()
    {
        // Cria a tablea com os valores padrões
        $mptt = new Mptt('mptt', 'id');
        $this->assertNull($mptt->fetchAll());

        // Set traversal
        $this->assertFalse($mptt->isTraversable());
        $mptt->setTraversal(array('refColumn'=>'parent_id', 'order'=>'name'));
        $this->assertTrue($mptt->isTraversable());

        // Insert default rows
        foreach($this->defaultRows as $row) {
            $mptt->insert($row);
        }
        $this->assertNotNull($mptt->fetchAll());
        $this->assertCount(count($this->defaultRows), $mptt->fetchAll());

        // Assert if left/right is correct
        $this->assertEquals($this->nameOrderedRows, $mptt->fetchAll());

        // Remove a single node (Beef/9)
        $mptt->delete(9);

        // Verify its parent (Meat/8)
        $row = $mptt->fetchRow(8);
        $this->assertNotNull($row);
        $this->assertEquals(14, $row['lft']);
        $this->assertEquals(17, $row['rgt']);

        // Verify its sibling (Pork/10)
        $row = $mptt->fetchRow(10);
        $this->assertNotNull($row);
        $this->assertEquals(15, $row['lft']);
        $this->assertEquals(16, $row['rgt']);

        // Verify the root (Food/1)
        $row = $mptt->fetchRow(1);
        $this->assertNotNull($row);
        $this->assertEquals(1, $row['lft']);
        $this->assertEquals(22, $row['rgt']);

        // Verify its uncle (Vegetable/11)
        $row = $mptt->fetchRow(11);
        $this->assertNotNull($row);
        $this->assertEquals(18, $row['lft']);
        $this->assertEquals(21, $row['rgt']);

        // Verify its another uncle (Fruit/2)
        $row = $mptt->fetchRow(2);
        $this->assertNotNull($row);
        $this->assertEquals(2, $row['lft']);
        $this->assertEquals(13, $row['rgt']);

        // Put it back
        $mptt->insert($this->defaultRows[9-1]);

        // Assert if left/right is correct
        $this->assertEquals($this->nameOrderedRows, $mptt->fetchAll());

        // Remove a node with child (Meat/8)
        $mptt->delete(8);

        // Verify its childs is gone
        $this->assertNull($mptt->fetchRow(8));
        $this->assertNull($mptt->fetchRow(9));
        $this->assertNull($mptt->fetchRow(10));

        // Verify the root (Food/1)
        $row = $mptt->fetchRow(1);
        $this->assertNotNull($row);
        $this->assertEquals(1, $row['lft']);
        $this->assertEquals(18, $row['rgt']);

        // Verify its uncle (Vegetable/11)
        $row = $mptt->fetchRow(11);
        $this->assertNotNull($row);
        $this->assertEquals(14, $row['lft']);
        $this->assertEquals(17, $row['rgt']);

        // Verify its another uncle (Fruit/2)
        $row = $mptt->fetchRow(2);
        $this->assertNotNull($row);
        $this->assertEquals(2, $row['lft']);
        $this->assertEquals(13, $row['rgt']);

        // Put them back
        $mptt->insert($this->defaultRows[8-1]);
        $mptt->insert($this->defaultRows[10-1]);
        $mptt->insert($this->defaultRows[9-1]);

        // Assert if left/right is correct
        $this->assertEquals($this->nameOrderedRows, $mptt->fetchAll());
    }
}

