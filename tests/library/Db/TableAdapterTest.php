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

require_once 'Realejo/Db/TableAdapter.php';

/**
 * TableAdapter test case.
 */
class TableAdapterTest extends \PHPUnit_Framework_TestCase
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

    private $pdo = null;

    /**
     * instancie PHPUnit_Extensions_Database_DB_IDatabaseConnection apenas uma vez por teste
     * @var
     */
    private $conn = null;

    public function getConnection()
    {
        if ($this->pdo === null) {
            $this->pdo = new \Zend\Db\Adapter\Adapter(array(
                'driver'   => 'Pdo_Sqlite',
             ));
        }
        return $this->pdo;
    }

    public function createDatabase()
    {
        $conn = $this->getConnection();
        $conn->exec("
                CREATE TABLE album (
                id smallint(10) NOT NULL auto_increment,
                artist varchar(100) NOT NULL,
                title varchar(100) NOT NULL,
                deleted tinyint(1) UNSIGNED NOT NULL DEFALT 0,
                PRIMARY KEY (id)
        );");
    }

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp()
    {
        // TODO Auto-generated TableAdapterTest::setUp()
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
        if ($this->TableAdapter === null) {
            $this->TableAdapter = new TableAdapter($this->tableName, $this->tableKeyName, $this->getConnection());
        }
        return $this->TableAdapter;
    }

    /**
     * Construct sem nome da tabela
     * @expectedException Exception
     */
    public function testConstructSemTableName()
    {
        new TableAdapter(null, $this->tableKeyName);
    }

    /**
     * Construct sem nome da chave
     * @expectedException Exception
     */
    public function testConstructSemKeyName()
    {
        new TableAdapter($this->tableName, null);
    }

    /**
     * Constructs the test case sem adapter. Por que não tem "applicaion.ini"
     * @expectedException Exception
     */
    public function testConstructSemAdapter()
    {
        new TableAdapter($this->tableName, $this->tableKeyName);
    }

    /**
     * Constructs the test case copm adapter inválido. Ele deve ser Zend\Db\Adapter\Adapter\AdapterInterface
     * @expectedException Exception
     */
    public function testCosntructComAdapterInvalido()
    {
        $tableAdapter = new TableAdapter($this->tableName, $this->tableKeyName, new \PDO('sqlite::memory:'));
    }

    /**
     * test a criação com a conexão local de testes
     */
    public function testCreateTableAdapter()
    {
        $tableAdapter = new TableAdapter($this->tableName, $this->tableKeyName, $this->getConnection());
        $this->assertInstanceOf('Realejo\Db\TableAdapter', $tableAdapter);
    }


    /**
     * Tests TableAdapter->getOrder()
     */
    public function testOrder()
    {
        // Verifica a ordem padrão
        $this->assertNull($this->getTableAdapter()->getOrder());

        // Define uma nova ordem com string
        $this->getTableAdapter()->setOrder('id');
        $this->assertEquals('id', $this->getTableAdapter()->getOrder());

        // Define uma nova ordem com string
        $this->getTableAdapter()->setOrder('title');
        $this->assertEquals('title', $this->getTableAdapter()->getOrder());


        // Define uma nova ordem com array
        $this->getTableAdapter()->setOrder(array('id', 'title'));
        $this->assertEquals(array('id', 'title'), $this->getTableAdapter()->getOrder());
    }


    /**
     * Tests TableAdapter->getWhere()
     */
    public function testGetWhere()
    {
        // TODO Auto-generated TableAdapterTest->testGetWhere()
        $this->markTestIncomplete("getWhere test not implemented");

        $this->getTableAdapter()->getWhere(/* parameters */);

    }

    /**
     * Tests TableAdapter->getSelect()
     */
    public function testGetSelect()
    {
        // TODO Auto-generated TableAdapterTest->testGetSelect()
        $this->markTestIncomplete("getSelect test not implemented");

        $this->getTableAdapter()->getSelect(/* parameters */);

    }

    /**
     * Tests TableAdapter->getSQlString()
     */
    public function testGetSQlString()
    {
        // TODO Auto-generated TableAdapterTest->testGetSQlString()
        $this->markTestIncomplete("getSQlString test not implemented");

        $this->getTableAdapter()->getSQlString(/* parameters */);

    }

    /**
     * Tests TableAdapter->fetchAll()
     */
    public function testFetchAll()
    {
        // TODO Auto-generated TableAdapterTest->testFetchAll()
        $this->markTestIncomplete("fetchAll test not implemented");

        $this->getTableAdapter()->fetchAll(/* parameters */);

    }

    /**
     * Tests TableAdapter->fetchRow()
     */
    public function testFetchRow()
    {
        // TODO Auto-generated TableAdapterTest->testFetchRow()
        $this->markTestIncomplete("fetchRow test not implemented");

        $this->getTableAdapter()->fetchRow(/* parameters */);

    }

    /**
     * Tests TableAdapter->fetchAssoc()
     */
    public function testFetchAssoc()
    {
        // TODO Auto-generated TableAdapterTest->testFetchAssoc()
        $this->markTestIncomplete("fetchAssoc test not implemented");

        $this->getTableAdapter()->fetchAssoc(/* parameters */);

    }

    /**
     * Tests TableAdapter->save()
     */
    public function testSave()
    {
        // TODO Auto-generated TableAdapterTest->testSave()
        $this->markTestIncomplete("save test not implemented");

        $this->getTableAdapter()->save(/* parameters */);

    }

    /**
     * Tests TableAdapter->delete()
     */
    public function testDelete()
    {
        // TODO Auto-generated TableAdapterTest->testDelete()
        $this->markTestIncomplete("delete test not implemented");

        $this->getTableAdapter()->delete(/* parameters */);

    }

}

