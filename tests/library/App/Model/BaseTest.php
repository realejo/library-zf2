<?php
/**
 * TableAdapterTest test case.
 *
 * @author     Realejo
 * @copyright  Copyright (c) 2014 Realejo Design Ltda. (http://www.realejo.com.br)
 */
use Realejo\App\Model\Base, Zend\Db\Adapter\Adapter, Realejo\Db\TableAdapter;

/**
 * Base test case.
 */
class BaseTest extends PHPUnit_Framework_TestCase
{

    /**
     *
     * @var string
     */
    protected $tableName = "album";

    /**
     *
     * @var string
     */
    protected $tableKeyName = "id";

    /**
     *
     * @var Base
     */
    private $Base;

    /**
     *
     * @var Zend\Db\Adapter\Adapter
     */
    protected $pdoAdapter = null;

    protected $defaultValues = array(
        array(
            'id' => 1,
            'artist' => 'Rush',
            'title' => 'Rush',
            'deleted' => 0
        ),
        array(
            'id' => 2,
            'artist' => 'Rush',
            'title' => 'Moving Pictures',
            'deleted' => 0
        ),
        array(
            'id' => 3,
            'artist' => 'Dream Theater',
            'title' => 'Images And Words',
            'deleted' => 0
        ),
        array(
            'id' => 4,
            'artist' => 'Claudia Leitte',
            'title' => 'Exttravasa',
            'deleted' => 1
        )
    );

    /**
     *
     * @return \Zend\Db\Adapter\Adapter
     */
    public function getPdoAdapter()
    {
        if ($this->pdoAdapter === null) {
            $this->pdoAdapter = new \Zend\Db\Adapter\Adapter(array(
                'driver' => 'Pdo_Sqlite',
                'database' => realpath(__DIR__ . '/../../assets') . '/sqlite.db'
            ));
        }
        return $this->pdoAdapter;
    }

    /**
     *
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
     *
     * @return \Realejo\Db\TableAdapterTest
     */
    public function insertDefaultRows()
    {
        $pdoAdapter = $this->getPdoAdapter();
        foreach ($this->defaultValues as $row) {
            $pdoAdapter->query("INSERT into {$this->tableName}({$this->tableKeyName}, artist, title, deleted)
        VALUES ({$row[$this->tableKeyName]}, '{$row['artist']}', '{$row['title']}', {$row['deleted']});", Adapter::QUERY_MODE_EXECUTE);
        }
        return $this;
    }

    /**
     *
     * @return \Realejo\Db\TableAdapterTest
     */
    public function dropTable()
    {
        $this->getPdoAdapter()->query("DROP TABLE IF EXISTS {$this->tableName}", Adapter::QUERY_MODE_EXECUTE);
        return $this;
    }

    /**
     *
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
     * @return Base
     */
    public function getBase($reset = false)
    {
        if ($this->Base === null || $reset === true) {
            $this->Base = new Base($this->tableName, $this->tableKeyName, $this->getPdoAdapter());
        }
        return $this->Base;
    }

    /**
     * Construct sem nome da tabela
     * @expectedException Exception
     */
    public function testConstructSemTableName()
    {
        new Base(null, $this->tableKeyName);
    }

    /**
     * Construct sem nome da chave
     * @expectedException Exception
     */
    public function testConstructSemKeyName()
    {
        new Base($this->tableName, null);
    }

    /**
     * Constructs the test case sem adapter. Por que não tem "applicaion.ini"
     * @expectedException Exception
     */
    public function testConstructSemAdapter()
    {
        new Base($this->tableName, $this->tableKeyName);
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
        $this->assertNull($this->getBase()->getOrder());

        // Define uma nova ordem com string
        $this->getBase()->setOrder('id');
        $this->assertEquals('id', $this->getBase()->getOrder());

        // Define uma nova ordem com string
        $this->getBase()->setOrder('title');
        $this->assertEquals('title', $this->getBase()->getOrder());


        // Define uma nova ordem com array
        $this->getBase()->setOrder(array('id', 'title'));
        $this->assertEquals(array('id', 'title'), $this->getBase()->getOrder());
    }


    /**
     * Tests TableAdapter->getWhere()
     */
    public function testWhere()
    {
        // Verifica a ordem padrão
        $this->assertEquals(array("{$this->tableName}.deleted=0"), $this->getBase()->getWhere());
        $this->assertEquals(array("{$this->tableName}.deleted=0"), $this->getBase()->getWhere(null));
        $this->assertEquals(array("{$this->tableName}.deleted=0"), $this->getBase()->getWhere(array()));
        $this->assertEquals(array("{$this->tableName}.deleted=0"), $this->getBase()->getWhere(''));
        $this->assertEquals(array("{$this->tableName}.deleted=0"), $this->getBase()->getWhere(0));

        $this->assertEquals(array("{$this->tableName}.deleted=1"), $this->getBase()->getWhere(array('deleted'=>true)));
        $this->assertEquals(array("{$this->tableName}.deleted=1"), $this->getBase()->getWhere(array('deleted'=>1)));
        $this->assertEquals(array("{$this->tableName}.deleted=0"), $this->getBase()->getWhere(array('deleted'=>false)));
        $this->assertEquals(array("{$this->tableName}.deleted=0"), $this->getBase()->getWhere(array('deleted'=>0)));

        $this->assertEquals(array(
            "outratabela.campo=0",
            "{$this->tableName}.deleted=0"
        ), $this->getBase()->getWhere(array('outratabela.campo'=>0)));

            $this->assertEquals(array(
                "outratabela.deleted=1",
                "{$this->tableName}.deleted=0"
        ), $this->getBase()->getWhere(array('outratabela.deleted'=>1)));

        $this->assertEquals(array(
                            "{$this->tableName}.{$this->tableKeyName}=1",
                            "{$this->tableName}.deleted=0"
        ), $this->getBase()->getWhere(array($this->tableKeyName=>1)));

        $dbExpression = new \Zend\Db\Sql\Expression('now()');
        $this->assertEquals(array(
            $dbExpression,
                "{$this->tableName}.deleted=0"
        ), $this->getBase()->getWhere(array($dbExpression)));

    }

    /**
     * Tests campo deleted
     */
    public function testDeletedField()
    {
        // Verifica se deve remover o registro
        $this->assertFalse($this->getBase()->getUseDeleted());
        $this->assertFalse($this->getBase()->setUseDeleted(false)->getUseDeleted());
        $this->assertTrue($this->getBase()->setUseDeleted(true)->getUseDeleted());
        $this->assertFalse($this->getBase()->getUseDeleted());


        // Verifica se deve mostrar o registro
        $this->assertFalse($this->getBase()->getShowDeleted());
        $this->assertFalse($this->getBase()->setShowDeleted(false)->getShowDeleted());
        $this->assertTrue($this->getBase()->setShowDeleted(true)->getShowDeleted());
        $this->assertTrue($this->getBase()->getShowDeleted());
    }

    /**
     * Tests TableAdapter->getSQlString()
     */
    public function testGetSQlString()
    {
        // TODO Auto-generated TableAdapterTest->testGetSQlString()
        $this->markTestIncomplete("getSQlString test not implemented");

        $this->getBase()->getSQlString(/* parameters */);

    }

    /**
     * Tests TableAdapter->fetchAll()
     */
    public function testFetchAll()
    {
        $albuns = $this->defaultValues;
        unset($albuns[3]); // remov o deleted=1
        $this->assertEquals($albuns, $this->getBase()->fetchAll());

        $this->getBase()->setShowDeleted(true);
        $this->assertEquals($this->defaultValues, $this->getBase()->fetchAll());
        $this->assertEquals(4, count($this->getBase()->fetchAll()));
        $this->getBase()->setShowDeleted(false);
        $this->assertEquals(3, count($this->getBase()->fetchAll()));

        // Verifica o where
        $this->assertEquals(2, count($this->getBase()->fetchAll(array('artist'=>$albuns[0]['artist']))));
        $this->assertNull($this->getBase()->fetchAll(array('artist'=>$this->defaultValues[3]['artist'])));
    }

    /**
     * Tests TableAdapter->fetchRow()
     */
    public function testFetchRow()
    {
        // Verifica os itens que existem
        $this->assertEquals($this->defaultValues[0], $this->getBase()->fetchRow(1));
        $this->assertEquals($this->defaultValues[1], $this->getBase()->fetchRow(2));
        $this->assertEquals($this->defaultValues[2], $this->getBase()->fetchRow(3));

        // Verifica o item removido
        $this->assertNull($this->getBase()->fetchRow(4));
        $this->getBase()->setShowDeleted(true);
        $this->assertEquals($this->defaultValues[3], $this->getBase()->fetchRow(4));
        $this->getBase()->setShowDeleted(false);
        $this->assertNull($this->getBase()->fetchRow(4));
    }

    /**
     * Tests TableAdapter->fetchAssoc()
     */
    public function testFetchAssoc()
    {
        $albuns = $this->getBase()->fetchAssoc();
        $this->assertEquals(3, count($albuns));
        $this->assertEquals($this->defaultValues[0], $albuns[1]);
        $this->assertEquals($this->defaultValues[1], $albuns[2]);
        $this->assertEquals($this->defaultValues[2], $albuns[3]);

        $albuns = $this->getBase()->setShowDeleted(true)->fetchAssoc();
        $this->assertEquals(4, count($albuns));
        $this->assertEquals($this->defaultValues[0], $albuns[1]);
        $this->assertEquals($this->defaultValues[1], $albuns[2]);
        $this->assertEquals($this->defaultValues[2], $albuns[3]);
        $this->assertEquals($this->defaultValues[3], $albuns[4]);

        $albuns = $this->getBase()->setShowDeleted(false)->fetchAssoc();
        $this->assertEquals(3, count($albuns));
        $this->assertEquals($this->defaultValues[0], $albuns[1]);
        $this->assertEquals($this->defaultValues[1], $albuns[2]);
        $this->assertEquals($this->defaultValues[2], $albuns[3]);
    }


















    /**
     * Constructs the test case.
     */
    public function __construct()
    {
        // TODO Auto-generated constructor
    }

    /**
     * Tests Base->getLoader()
     */
    public function testGetLoader()
    {
        // TODO Auto-generated BaseTest->testGetLoader()
        $this->markTestIncomplete("getLoader test not implemented");

        $this->Base->getLoader(/* parameters */);
    }

    /**
     * Tests Base->setLoader()
     */
    public function testSetLoader()
    {
        // TODO Auto-generated BaseTest->testSetLoader()
        $this->markTestIncomplete("setLoader test not implemented");

        $this->Base->setLoader(/* parameters */);
    }

    /**
     * Tests Base->getTable()
     */
    public function testGetTable()
    {
        // TODO Auto-generated BaseTest->testGetTable()
        $this->markTestIncomplete("getTable test not implemented");

        $this->Base->getTable(/* parameters */);
    }

    /**
     * Tests Base->getSelect()
     */
    public function testGetSelect()
    {
        // TODO Auto-generated BaseTest->testGetSelect()
        $this->markTestIncomplete("getSelect test not implemented");

        $this->Base->getSelect(/* parameters */);
    }

    /**
     * Tests Base->getTableSelect()
     */
    public function testGetTableSelect()
    {
        // TODO Auto-generated BaseTest->testGetTableSelect()
        $this->markTestIncomplete("getTableSelect test not implemented");

        $this->Base->getTableSelect(/* parameters */);
    }

    /**
     * Tests Base->fetchCount()
     */
    public function testFetchCount()
    {
        // TODO Auto-generated BaseTest->testFetchCount()
        $this->markTestIncomplete("fetchCount test not implemented");

        $this->Base->fetchCount(/* parameters */);
    }

    /**
     * Tests Base->getHtmlSelect()
     */
    public function testGetHtmlSelect()
    {
        // TODO Auto-generated BaseTest->testGetHtmlSelect()
        $this->markTestIncomplete("getHtmlSelect test not implemented");

        $this->Base->getHtmlSelect(/* parameters */);
    }

    /**
     * Tests Base->getCache()
     */
    public function testGetCache()
    {
        // TODO Auto-generated BaseTest->testGetCache()
        $this->markTestIncomplete("getCache test not implemented");

        $this->Base->getCache(/* parameters */);
    }

    /**
     * Tests Base->setUseCache()
     */
    public function testSetUseCache()
    {
        // TODO Auto-generated BaseTest->testSetUseCache()
        $this->markTestIncomplete("setUseCache test not implemented");

        $this->Base->setUseCache(/* parameters */);
    }

    /**
     * Tests Base->getUseCache()
     */
    public function testGetUseCache()
    {
        // TODO Auto-generated BaseTest->testGetUseCache()
        $this->markTestIncomplete("getUseCache test not implemented");

        $this->Base->getUseCache(/* parameters */);
    }

    /**
     * Tests Base->getPaginator()
     */
    public function testGetPaginator()
    {
        // TODO Auto-generated BaseTest->testGetPaginator()
        $this->markTestIncomplete("getPaginator test not implemented");

        $this->Base->getPaginator(/* parameters */);
    }

    /**
     * Tests Base->setUsePaginator()
     */
    public function testSetUsePaginator()
    {
        // TODO Auto-generated BaseTest->testSetUsePaginator()
        $this->markTestIncomplete("setUsePaginator test not implemented");

        $this->Base->setUsePaginator(/* parameters */);
    }

    /**
     * Tests Base->getUsePaginator()
     */
    public function testGetUsePaginator()
    {
        // TODO Auto-generated BaseTest->testGetUsePaginator()
        $this->markTestIncomplete("getUsePaginator test not implemented");

        $this->Base->getUsePaginator(/* parameters */);
    }
}

