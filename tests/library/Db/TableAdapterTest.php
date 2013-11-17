<?php
/**
 * TableAdapterTest test case.
 *
 * @author     Realejo
 * @version    $Id: CPF.php 33 2012-06-19 14:18:04Z rodrigo $
 * @copyright  Copyright (c) 2013 Realejo Design Ltda. (http://www.realejo.com.br)
 */
namespace Realejo\Db;

use PHPUnit_Framework_TestCase;
use PHPUnit_Extensions_Database_TestCase;

require_once 'Realejo/Db/TableAdapter.php';

/**
 * TableAdapter test case.
 */
class TableAdapterTest extends PHPUnit_Extensions_Database_TestCase
{
    /**
     * @var string
     */
    protected $tableName = "album";

    /**
     * @var string
     */
    protected $tableKeyName = "id";

    /**
     * @var TableAdapter
     */
    private $TableAdapter;

    /**
     * instancie o pdo apenas uma vez por limpeza de teste/carregamento de ambiente
     * @var
     */
    static private $pdo = null;

    /**
     * instancie PHPUnit_Extensions_Database_DB_IDatabaseConnection apenas uma vez por teste
     * @var
     */
    private $conn = null;

    /**
     * @return PHPUnit_Extensions_Database_DB_IDatabaseConnection
     */
    final public function getConnection()
    {
        if ($this->conn === null) {
            if (self::$pdo == null) {
                self::$pdo = new \PDO('sqlite::memory:');
            }
            $this->conn = $this->createDefaultDBConnection(self::$pdo, ':memory:');
        }

        return $this->conn;
    }

    public function testCreateDataSet()
    {
        $tableNames = array('album');
        $dataSet = $this->getConnection()->createDataSet();
    }

    /**
     * @return PHPUnit_Extensions_Database_DataSet_IDataSet
     */
    public function getDataSet()
    {
        return $this->createXmlDataSet('assets/exemplo.xml');
    }

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp()
    {
        parent::setUp();

    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown()
    {
        // TODO Auto-generated TableAdapterTest::tearDown()

        parent::tearDown();
    }

    public function getTableAdapter()
    {
        return new TableAdapter($this->tableName, $this->tableKeyName, $this->getConnection());
    }


    /**
     * Construct sem nome da tabela
     * @expectedException
     */
    public function testConstructSemTable()
    {
        new TableAdapter(null, $this->tableId);
    }

    /**
     * Construct sem nome da chave
     * @expectedException
     */
    public function testConstructSemKeyName()
    {
        new TableAdapter($this->tableName, null);
    }

    /**
     * Constructs the test case sem adapter. Por que não tem "applicaion.ini"
     * @expectedException
     */
    public function testConstructSemAdapter()
    {
        new TableAdapter($this->tableName, $this->tableKeyName);
    }

    /**
     * Tests TableAdapter->getOrder()
     */
    public function testGetOrder()
    {
        // TODO Auto-generated TableAdapterTest->testGetOrder()
        $this->markTestIncomplete("getOrder test not implemented");

        $this->TableAdapter()->getOrder(/* parameters */);

    }

    /**
     * Tests TableAdapter->setOrder()
     */
    public function testSetOrder()
    {
        // TODO Auto-generated TableAdapterTest->testSetOrder()
        $this->markTestIncomplete("setOrder test not implemented");

        $this->TableAdapter()->setOrder(/* parameters */);

    }

    /**
     * Tests TableAdapter->getWhere()
     */
    public function testGetWhere()
    {
        // TODO Auto-generated TableAdapterTest->testGetWhere()
        $this->markTestIncomplete("getWhere test not implemented");

        $this->TableAdapter()->getWhere(/* parameters */);

    }

    /**
     * Tests TableAdapter->getSelect()
     */
    public function testGetSelect()
    {
        // TODO Auto-generated TableAdapterTest->testGetSelect()
        $this->markTestIncomplete("getSelect test not implemented");

        $this->TableAdapter()->getSelect(/* parameters */);

    }

    /**
     * Tests TableAdapter->getSQlString()
     */
    public function testGetSQlString()
    {
        // TODO Auto-generated TableAdapterTest->testGetSQlString()
        $this->markTestIncomplete("getSQlString test not implemented");

        $this->TableAdapter()->getSQlString(/* parameters */);

    }

    /**
     * Tests TableAdapter->fetchAll()
     */
    public function testFetchAll()
    {
        // TODO Auto-generated TableAdapterTest->testFetchAll()
        $this->markTestIncomplete("fetchAll test not implemented");

        $this->TableAdapter()->fetchAll(/* parameters */);

    }

    /**
     * Tests TableAdapter->fetchRow()
     */
    public function testFetchRow()
    {
        // TODO Auto-generated TableAdapterTest->testFetchRow()
        $this->markTestIncomplete("fetchRow test not implemented");

        $this->TableAdapter()->fetchRow(/* parameters */);

    }

    /**
     * Tests TableAdapter->fetchAssoc()
     */
    public function testFetchAssoc()
    {
        // TODO Auto-generated TableAdapterTest->testFetchAssoc()
        $this->markTestIncomplete("fetchAssoc test not implemented");

        $this->TableAdapter()->fetchAssoc(/* parameters */);

    }

    /**
     * Tests TableAdapter->save()
     */
    public function testSave()
    {
        // TODO Auto-generated TableAdapterTest->testSave()
        $this->markTestIncomplete("save test not implemented");

        $this->TableAdapter()->save(/* parameters */);

    }

    /**
     * Tests TableAdapter->delete()
     */
    public function testDelete()
    {
        // TODO Auto-generated TableAdapterTest->testDelete()
        $this->markTestIncomplete("delete test not implemented");

        $this->TableAdapter()->delete(/* parameters */);

    }

}

