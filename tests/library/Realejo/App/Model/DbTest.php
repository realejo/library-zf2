<?php
/**
 * TableAdapterTest test case.
 *
 * @link      http://github.com/realejo/libraray-zf2
 * @copyright Copyright (c) 2014 Realejo (http://realejo.com.br)
 * @license   http://unlicense.org
 */
use Realejo\App\Model\Db;

class DbTest extends BaseTestCase
{

    /**
     * @var Db
     */
    private $Db;

    protected $tables = array('album');

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
        $this->dropTables();
    }
    /**
     * @return Db
     */
    public function getDb($reset = false)
    {
        if ($this->Db === null || $reset === true) {
            $this->Db = new Db('album', 'id', $this->getAdapter());
        }
        return $this->Db;
    }

    /**
     * Construct sem nome da tabela
     * @expectedException Exception
     */
    public function testConstructSemTableName()
    {
        new Db(null, 'keyname');
    }

    /**
     * Construct sem nome da chave
     * @expectedException Exception
     */
    public function testConstructSemKeyName()
    {
        new Db('tablename', null);
    }

    /**
     * Verifica se tudo foi criado corretamente no MySQL
     */
    public function testSetupMysql()
    {
        $this->setup();
        $this->assertTrue(true);
    }

    /**
     * Tests Db->insert()
     */
    public function testInsert()
    {
        // Certifica que a tabela está vazia
        $this->assertNull($this->getDb()->fetchAll(), 'Verifica se há algum registro pregravado');

        $this->assertFalse($this->getDb()->insert(array()), 'Verifica inclusão inválida 1');
        $this->assertFalse($this->getDb()->insert(null), 'Verifica inclusão inválida 2');

        $row = array(
                'artist' => 'Rush',
                'title' => 'Rush',
                'deleted' => '0'
        );

        $id = $this->getDb()->insert($row);
        $this->assertEquals(1, $id, 'Verifica a chave criada=1');

        $this->assertNotNull($this->getDb()->fetchAll(), 'Verifica o fetchAll não vazio');
        $this->assertEquals($row, $this->getDb()->getLastInsertSet(), 'Verifica o set do ultimo insert');
        $this->assertCount(1, $this->getDb()->fetchAll(), 'Verifica se apenas um registro foi adicionado');

        $row = array_merge(array('id'=>$id), $row);

        $this->assertEquals(array($row), $this->getDb()->fetchAll(), 'Verifica se o registro adicionado corresponde ao original pelo fetchAll()');
        $this->assertEquals($row, $this->getDb()->fetchRow(1), 'Verifica se o registro adicionado corresponde ao original pelo fetchRow()');

        $row = array(
                'id' => 2,
                'artist' => 'Rush',
                'title' => 'Test For Echos',
                'deleted' => '0'
        );

        $id = $this->getDb()->insert($row);
        $this->assertEquals(2, $id, 'Verifica a chave criada=2');

        $this->assertCount(2, $this->getDb()->fetchAll(), 'Verifica que há DOIS registro');
        $this->assertEquals($row, $this->getDb()->fetchRow(2), 'Verifica se o SEGUNDO registro adicionado corresponde ao original pelo fetchRow()');
        $this->assertEquals($row, $this->getDb()->getLastInsertSet());

        $row = array(
                'artist' => 'Rush',
                'title' => 'Moving Pictures',
                'deleted' => '0'
        );
        $id = $this->getDb()->insert($row);
        $this->assertEquals(3, $id);
        $this->assertEquals($row, $this->getDb()->getLastInsertSet(), 'Verifica se o TERCEIRO registro adicionado corresponde ao original pelo getLastInsertSet()');

        $row = array_merge(array('id'=>$id), $row);

        $this->assertCount(3, $this->getDb()->fetchAll());
        $this->assertEquals($row, $this->getDb()->fetchRow(3), 'Verifica se o TERCEIRO registro adicionado corresponde ao original pelo fetchRow()');

        // Teste com \Zend\Db\Sql\Expression
        $id = $this->getDb()->insert(array('title'=>new \Zend\Db\Sql\Expression('now()')));
        $this->assertEquals(4, $id);
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

