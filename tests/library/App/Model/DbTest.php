<?php
/**
 * TableAdapterTest test case.
 *
 * @author     Realejo
 * @copyright  Copyright (c) 2014 Realejo Design Ltda. (http://www.realejo.com.br)
 */
use Realejo\App\Model\Db, Zend\Db\Adapter\Adapter;

/**
 * Db test case.
 */
class DbTest extends PHPUnit_Framework_TestCase
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
     * @var Zend\Db\Adapter\Adapter
     */
    protected $pdoAdapter = null;

    /**
     *
     * @var Db
     */
    private $Db;

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
        $this->dropTable()->createTable();
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
     * @return Db
     */
    public function getDb($reset = false)
    {
        if ($this->Db === null || $reset === true) {
            $this->Db = new Db($this->tableName, $this->tableKeyName, $this->getPdoAdapter());
        }
        return $this->Db;
    }

    /**
     * Construct sem nome da tabela
     * @expectedException Exception
     */
    public function testConstructSemTableName()
    {
        new Db(null, $this->tableKeyName);
    }

    /**
     * Construct sem nome da chave
     * @expectedException Exception
     */
    public function testConstructSemKeyName()
    {
        new Db($this->tableName, null);
    }

    /**
     * Tests Db->insert()
     */
    public function testInsert()
    {
        // Certifica que a tabela está vazia
        $this->assertNull($this->getDb()->fetchAll());

        $this->assertFalse($this->getDb()->insert(array()));
        $this->assertFalse($this->getDb()->insert(null));

        $row = array(
            'id' => 1,
            'artist' => 'Rush',
            'title' => 'Rush',
            'deleted' => 0
        );

        $this->getDb()->insert($row);

        $this->assertNotNull($this->getDb()->fetchAll());
        $this->assertCount(1, $this->getDb()->fetchAll());
        $this->assertEquals(array($row), $this->getDb()->fetchAll());
        $this->assertEquals($row, $this->getDb()->fetchRow(1));
        $this->assertEquals($row, $this->getDb()->getLastInsertSet());

        $row = array(
            'id' => 2,
            'artist' => 'Rush',
            'title' => 'Moving Pictures',
            'deleted' => 0
        );

        $this->getDb()->insert($row);

        $this->assertNotNull($this->getDb()->fetchAll());
        $this->assertCount(2, $this->getDb()->fetchAll());
        $this->assertEquals($row, $this->getDb()->fetchRow(2));
        $this->assertEquals($row, $this->getDb()->getLastInsertSet());
    }

    /**
     * Tests Db->update()
     */
    public function testUpdate()
    {
        // Certifica que a tabela está vazia
        $this->assertNull($this->getDb()->fetchAll());

        $row1 = array(
            'id' => 1,
            'artist'  => 'Não me altere',
            'title'   => 'Rush',
            'deleted' => 0
        );

        $row2 = array(
            'id' => 2,
            'artist'  => 'Rush',
            'title'   => 'Rush',
            'deleted' => 0
        );

        $this->getDb()->insert($row1);
        $this->getDb()->insert($row2);

        $this->assertNotNull($this->getDb()->fetchAll());
        $this->assertCount(2, $this->getDb()->fetchAll());
        $this->assertEquals($row1, $this->getDb()->fetchRow(1));
        $this->assertEquals($row2, $this->getDb()->fetchRow(2));

        $row = array(
            'artist'  => 'Rush',
            'title'   => 'Moving Pictures',
        );

        $this->getDb()->update($row, 2);
        $row['id'] = '2';
        $row['deleted'] = '0';

        $this->assertNotNull($this->getDb()->fetchAll());
        $this->assertCount(2, $this->getDb()->fetchAll());
        $this->assertEquals($row, $this->getDb()->fetchRow(2), 'Alterou o 2?' );

        $this->assertEquals($row1, $this->getDb()->fetchRow(1), 'Alterou o 1?');
        $this->assertNotEquals($row2, $this->getDb()->fetchRow(2), 'O 2 não é mais o mesmo?');

        unset($row['id']);
        unset($row['deleted']);
        $this->assertEquals($row, $this->getDb()->getLastUpdateSet(), 'Os dados diferentes foram os alterados?');
        $this->assertEquals(array('title'=>array($row2['title'], $row['title'])), $this->getDb()->getLastUpdateDiff(), 'As alterações foram detectadas corretamente?');

        $this->assertFalse($this->getDb()->update(array(), 2));
        $this->assertFalse($this->getDb()->update(null, 2));

    }

    /**
     * Tests TableAdapter->delete()
     */
    public function testDelete()
    {
        $row = array(
            'id' => 1,
            'artist' => 'Rush',
            'title' => 'Rush',
            'deleted' => 0
        );
        $this->getDb()->insert($row);

        // Verifica se o registro existe
        $this->assertEquals($row, $this->getDb()->fetchRow(1));

        // Marca para usar o campo deleted
        $this->getDb()->setUseDeleted(true);

        // Remove o registro
        $this->getDb()->delete(1);
        $row['deleted'] = 1;

        // Verifica se foi removido
        $this->assertNull($this->getDb()->fetchRow(1));

        // Marca para mostrar os removidos
        $this->getDb()->setShowDeleted(true);

        // Verifica se o registro existe
        $this->assertEquals($row, $this->getDb()->fetchRow(1));

        // Marca para remover o registro da tabela
        $this->getDb()->setUseDeleted(false);

        // Remove o registro
        $this->getDb()->delete(1);

        // Verifica se ele foi removido
        $this->assertNull($this->getDb()->fetchRow(1));
    }
}

