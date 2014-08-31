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
     *
     * @var array
     */
    protected $completeTree = array(
        array(1, Food, null, 1, 24),
        array(2, Fruit, 1, 2, 13),
        array(3, Red, 2, 3, 8),
        array(4, Yellow, 2, 9, 10),
        array(5, Green, 2, 11, 12),
        array(6, Cherry, 3, 4, 5),
        array(7, Banana, 3, 6, 7),
        array(8, Meat, 1, 14, 19),
        array(9, Beef, 8, 15, 16),
        array(10, Pork, 8, 17, 18),
        array(11, Vegetable, 1, 20, 23),
        array(12, Carrot, 11, 21, 22),
    );


    /**
     * Será populada com os valores da arvore completa sem as informações left,right
     * @var array
     */
    protected $defaultRows = array();
    protected $treeRows = array();

    protected $tables = array('mptt');

    /**
     * @var Mptt
     */
    private $Mptt;

    public function __construct()
    {
        $fields = array('id', 'name', 'parent_id', 'lft', 'rgt');
        foreach ($this->completeTree as $values) {
            $row = array_combine($fields, $values);
            $this->treeRows[] = $row;
            unset($row['left']);
            unset($row['right']);
            $this->defaultRows[] = $row;
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
     * Tests Mptt->setTraversal()
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

        $this->assertEquals($this->treeRows, $mptt->fetchAll());
    }
}

