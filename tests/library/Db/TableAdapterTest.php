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
use \Zend\Db\Adapter\Adapter;
use Realejo\Db\TableAdapter;
//require_once 'Realejo/Db/TableAdapter.php';

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
    protected $TableAdapter;

    /**
     * @var Zend\Db\Adapter\Adapter
     */
    protected $pdoAdapter = null;

    protected $defaultValues = array(
        array('id' => 1, 'artist' => 'Rush', 'title' => 'Rush', 'deleted' => 0),
        array('id' => 2, 'artist' => 'Rush', 'title' => 'Moving Pictures', 'deleted' => 0),
        array('id' => 3, 'artist' => 'Dream Theater', 'title' => 'Images And Words', 'deleted' => 0),
        array('id' => 4, 'artist' => 'Claudia Leitte', 'title' => 'Exttravasa', 'deleted' => 1)
    );

    /**
     * @return \Zend\Db\Adapter\Adapter
     */
    public function getPdoAdapter()
    {
        if ($this->pdoAdapter === null) {
            $this->pdoAdapter = new \Zend\Db\Adapter\Adapter(array(
                'driver'   => 'Pdo_Sqlite',
                'database' => realpath(__DIR__ . '/../../assets') . '/sqlite.db'
             ));
        }
        return $this->pdoAdapter;
    }

    /**
     * @return \Realejo\Db\TableAdapterTest
     */
    public function createTable()
    {
        $conn = $this->getPdoAdapter();
        $conn->query("
                CREATE TABLE {$this->tableName} (
                {$this->tableKeyName} INTEGER PRIMARY KEY ASC,
                artist varchar(100) NOT NULL,
                title varchar(100) NOT NULL,
                deleted INTEGER UNSIGNED NOT NULL DEFAULT 0
        );", Adapter::QUERY_MODE_EXECUTE);

        return $this;
    }

    /**
     * @return \Realejo\Db\TableAdapterTest
     */
    public function insertDefaultRows()
    {
        $pdoAdapter = $this->getPdoAdapter();
        foreach($this->defaultValues as $row) {
            $pdoAdapter->query("INSERT into {$this->tableName}({$this->tableKeyName}, artist, title, deleted)
                                VALUES ({$row[$this->tableKeyName]}, '{$row['artist']}', '{$row['title']}', {$row['deleted']});",
                               Adapter::QUERY_MODE_EXECUTE);
        }
        return $this;
    }

    /**
     * @return \Realejo\Db\TableAdapterTest
     */
    public function dropTable()
    {
        $this->getPdoAdapter()->query("DROP TABLE IF EXISTS {$this->tableName}", Adapter::QUERY_MODE_EXECUTE);
        return $this;
    }

    /**
     * @return \Realejo\Db\TableAdapterTest
     */
    public function truncateTable()
    {
        $this->dropTable()->createTable();
        return $this;
    }

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp()
    {
        parent::setUp();
        $this->dropTable()->createTable()->insertDefaultRows();
    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown()
    {
        parent::tearDown();
        $this->dropTable();
    }

    /**
     * @return TableAdapter
     */
    public function getTableAdapter($reset = false)
    {
        if ($this->TableAdapter === null || $reset === true) {
            $this->TableAdapter = new TableAdapter($this->tableName, $this->tableKeyName, $this->getPdoAdapter());
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
    public function testConstructComAdapterInvalido()
    {
        $tableAdapter = new TableAdapter($this->tableName, $this->tableKeyName, new \PDO('sqlite::memory:'));
    }

    /**
     * test a criação com a conexão local de testes
     */
    public function testCreateTableAdapter()
    {
        $tableAdapter = new TableAdapter($this->tableName, $this->tableKeyName, $this->getPdoAdapter());
        $this->assertInstanceOf('Realejo\Db\TableAdapter', $tableAdapter);
    }

    /**
     * teste o adapter PDO
     */
    public function testPdoAdatper()
    {
        $this->assertInstanceOf('\Zend\Db\Adapter\Adapter', $this->getPdoAdapter());
        $this->assertInstanceOf('\Zend\Db\Adapter\Adapter', $this->pdoAdapter);
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
    public function testWhere()
    {
        // Verifica a ordem padrão
        $this->assertEquals(array("{$this->tableName}.deleted=0"), $this->getTableAdapter()->getWhere());
        $this->assertEquals(array("{$this->tableName}.deleted=0"), $this->getTableAdapter()->getWhere(null));
        $this->assertEquals(array("{$this->tableName}.deleted=0"), $this->getTableAdapter()->getWhere(array()));
        $this->assertEquals(array("{$this->tableName}.deleted=0"), $this->getTableAdapter()->getWhere(''));
        $this->assertEquals(array("{$this->tableName}.deleted=0"), $this->getTableAdapter()->getWhere(0));

        $this->assertEquals(array("{$this->tableName}.deleted=1"), $this->getTableAdapter()->getWhere(array('deleted'=>true)));
        $this->assertEquals(array("{$this->tableName}.deleted=1"), $this->getTableAdapter()->getWhere(array('deleted'=>1)));
        $this->assertEquals(array("{$this->tableName}.deleted=0"), $this->getTableAdapter()->getWhere(array('deleted'=>false)));
        $this->assertEquals(array("{$this->tableName}.deleted=0"), $this->getTableAdapter()->getWhere(array('deleted'=>0)));

        $this->assertEquals(array(
                "outratabela.campo=0",
                "{$this->tableName}.deleted=0"
        ), $this->getTableAdapter()->getWhere(array('outratabela.campo'=>0)));

        $this->assertEquals(array(
                "outratabela.deleted=1",
                "{$this->tableName}.deleted=0"
                ), $this->getTableAdapter()->getWhere(array('outratabela.deleted'=>1)));

        $this->assertEquals(array(
                "{$this->tableName}.{$this->tableKeyName}=1",
                "{$this->tableName}.deleted=0"
        ), $this->getTableAdapter()->getWhere(array($this->tableKeyName=>1)));

       $dbExpression = new \Zend\Db\Sql\Expression('now()');
        $this->assertEquals(array(
                $dbExpression,
                "{$this->tableName}.deleted=0"
                ), $this->getTableAdapter()->getWhere(array($dbExpression)));

    }

    /**
     * Tests campo deleted
     */
    public function testDeletedField()
    {
        // Verifica se deve remover o registro
        $this->assertTrue($this->getTableAdapter()->getUseDeleted());
        $this->assertTrue($this->getTableAdapter()->setUseDeleted(true)->getUseDeleted());
        $this->assertFalse($this->getTableAdapter()->setUseDeleted(false)->getUseDeleted());
        $this->assertFalse($this->getTableAdapter()->getUseDeleted());


        // Verifica se deve mostrar o registro
        $this->assertFalse($this->getTableAdapter()->getShowDeleted());
        $this->assertFalse($this->getTableAdapter()->setShowDeleted(false)->getShowDeleted());
        $this->assertTrue($this->getTableAdapter()->setShowDeleted(true)->getShowDeleted());
        $this->assertTrue($this->getTableAdapter()->getShowDeleted());
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
        $albuns = $this->defaultValues;
        unset($albuns[3]); // remov o deleted=1
        $this->assertEquals($albuns, $this->getTableAdapter()->fetchAll());

        $this->getTableAdapter()->setShowDeleted(true);
        $this->assertEquals($this->defaultValues, $this->getTableAdapter()->fetchAll());
        $this->assertEquals(4, count($this->getTableAdapter()->fetchAll()));
        $this->getTableAdapter()->setShowDeleted(false);
        $this->assertEquals(3, count($this->getTableAdapter()->fetchAll()));

        // Verifica o where
        $this->assertEquals(2, count($this->getTableAdapter()->fetchAll(array('artist'=>$albuns[0]['artist']))));
        $this->assertNull($this->getTableAdapter()->fetchAll(array('artist'=>$this->defaultValues[3]['artist'])));
    }

    /**
     * Tests TableAdapter->fetchRow()
     */
    public function testFetchRow()
    {
        // Verifica os itens que existem
        $this->assertEquals($this->defaultValues[0], $this->getTableAdapter()->fetchRow(1));
        $this->assertEquals($this->defaultValues[1], $this->getTableAdapter()->fetchRow(2));
        $this->assertEquals($this->defaultValues[2], $this->getTableAdapter()->fetchRow(3));

        // Verifica o item removido
        $this->assertNull($this->getTableAdapter()->fetchRow(4));
        $this->getTableAdapter()->setShowDeleted(true);
        $this->assertEquals($this->defaultValues[3], $this->getTableAdapter()->fetchRow(4));
        $this->getTableAdapter()->setShowDeleted(false);
        $this->assertNull($this->getTableAdapter()->fetchRow(4));
    }

    /**
     * Tests TableAdapter->fetchAssoc()
     */
    public function testFetchAssoc()
    {
        $albuns = $this->getTableAdapter()->fetchAssoc();
        $this->assertEquals(3, count($albuns));
        $this->assertEquals($this->defaultValues[0], $albuns[1]);
        $this->assertEquals($this->defaultValues[1], $albuns[2]);
        $this->assertEquals($this->defaultValues[2], $albuns[3]);

        $albuns = $this->getTableAdapter()->setShowDeleted(true)->fetchAssoc();
        $this->assertEquals(4, count($albuns));
        $this->assertEquals($this->defaultValues[0], $albuns[1]);
        $this->assertEquals($this->defaultValues[1], $albuns[2]);
        $this->assertEquals($this->defaultValues[2], $albuns[3]);
        $this->assertEquals($this->defaultValues[3], $albuns[4]);

        $albuns = $this->getTableAdapter()->setShowDeleted(false)->fetchAssoc();
        $this->assertEquals(3, count($albuns));
        $this->assertEquals($this->defaultValues[0], $albuns[1]);
        $this->assertEquals($this->defaultValues[1], $albuns[2]);
        $this->assertEquals($this->defaultValues[2], $albuns[3]);
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
        $row = $this->defaultValues[0];

        // Verifica se o registro existe
        $this->assertEquals($row, $this->getTableAdapter()->fetchRow(1));

        // Remove o registro
        $this->getTableAdapter()->delete(1);
        $row['deleted'] = 1;

        // Verifica se foi removido
        $this->assertNull($this->getTableAdapter()->fetchRow(1));

        // Marca para mostrar os removidos
        $this->getTableAdapter()->setShowDeleted(true);

        // Verifica se o registro existe
        $this->assertEquals($row, $this->getTableAdapter()->fetchRow(1));

        // Marca para remover o registro da tabela
        $this->getTableAdapter()->setUseDeleted(false);

        // Remove o registro
        $this->getTableAdapter()->delete(1);

        // Verifica se ele foi removido
        $this->assertNull($this->getTableAdapter()->fetchRow(1));
    }
}

