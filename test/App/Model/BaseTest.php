<?php

namespace RealejoTest\App\Model;

/**
 * BaseTest test case.
 *
 * @link      http://github.com/realejo/libraray-zf2
 * @copyright Copyright (c) 2014 Realejo (http://realejo.com.br)
 * @license   http://unlicense.org
 */

use Realejo\App\Model\Base;
use RealejoTest\BaseTestCase;
use Zend\Db\Adapter\Adapter;

class BaseTest extends BaseTestCase
{
    /**
     * @var string
     */
    protected $tableName = 'album';

    /**
     * @var string
     */
    protected $tableKeyName = 'id';

    protected $tables = array('album');

    /**
     * @var Base
     */
    private $Base;

    /**
     * @var Adapter
     */
    protected $zendAdapter = null;

    protected $defaultValues
        = array(
            array(
                'id'      => 1,
                'artist'  => 'Rush',
                'title'   => 'Rush',
                'deleted' => 0
            ),
            array(
                'id'      => 2,
                'artist'  => 'Rush',
                'title'   => 'Moving Pictures',
                'deleted' => 0
            ),
            array(
                'id'      => 3,
                'artist'  => 'Dream Theater',
                'title'   => 'Images And Words',
                'deleted' => 0
            ),
            array(
                'id'      => 4,
                'artist'  => 'Claudia Leitte',
                'title'   => 'Exttravasa',
                'deleted' => 1
            )
        );

    /**
     *
     * @return BaseTest
     */
    public function insertDefaultRows()
    {
        foreach ($this->defaultValues as $row) {
            $this->getAdapter()->query(
                "INSERT into {$this->tableName}({$this->tableKeyName}, artist, title, deleted)
                VALUES ({$row[$this->tableKeyName]}, '{$row['artist']}', '{$row['title']}', {$row['deleted']});",
                Adapter::QUERY_MODE_EXECUTE
            );
        }
        return $this;
    }

    /**
     *
     * @return BaseTest
     */
    public function truncateTable()
    {
        $this->dropTables()->createTables();
        return $this;
    }


    /**
     * Prepares the environment before running a test.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->dropTables()->createTables()->insertDefaultRows();
    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown(): void
    {
        parent::tearDown();
        $this->dropTables()->closeAdapterConnection();
    }

    /**
     * @return Base
     */
    public function getBase($reset = false)
    {
        if ($this->Base === null || $reset === true) {
            $this->Base = new Base($this->tableName, $this->tableKeyName, $this->getAdapter());
        }
        return $this->Base;
    }

    /**
     * Construct sem nome da tabela
     */
    public function testConstructSemTableName(): void
    {
        $this->expectException(\Exception::class);
        new Base(null, $this->tableKeyName);
    }

    /**
     * Construct sem nome da chave
     */
    public function testConstructSemKeyName(): void
    {
        $this->expectException(\Exception::class);
        new Base($this->tableName, null);
    }

    /**
     * test a criação com a conexão local de testes
     */
    public function testCreateBase(): void
    {
        $Base = new Base($this->tableName, $this->tableKeyName, $this->getAdapter());
        $this->assertInstanceOf('Realejo\App\Model\Base', $Base);
    }

    /**
     * teste o adapter
     */
    public function testAdapter(): void
    {
        $this->assertInstanceOf('\Zend\Db\Adapter\Adapter', $this->getAdapter());
    }

    /**
     * Tests Base->getOrder():void
     */
    public function testOrder(): void
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
     * Tests Base->getWhere():void
     */
    public function testWhere(): void
    {
        // Marca pra usar o campo deleted
        $this->getBase()->setUseDeleted(true);

        // Verifica a ordem padrão
        $this->assertEquals(array("{$this->tableName}.deleted=0"), $this->getBase()->getWhere());
        $this->assertEquals(array("{$this->tableName}.deleted=0"), $this->getBase()->getWhere(null));
        $this->assertEquals(array("{$this->tableName}.deleted=0"), $this->getBase()->getWhere(array()));
        $this->assertEquals(array("{$this->tableName}.deleted=0"), $this->getBase()->getWhere(''));
        $this->assertEquals(array("{$this->tableName}.deleted=0"), $this->getBase()->getWhere(0));

        $this->assertEquals(
            array("{$this->tableName}.deleted=1"),
            $this->getBase()->getWhere(array('deleted' => true))
        );
        $this->assertEquals(array("{$this->tableName}.deleted=1"), $this->getBase()->getWhere(array('deleted' => 1)));
        $this->assertEquals(
            array("{$this->tableName}.deleted=0"),
            $this->getBase()->getWhere(array('deleted' => false))
        );
        $this->assertEquals(array("{$this->tableName}.deleted=0"), $this->getBase()->getWhere(array('deleted' => 0)));

        $this->assertEquals(
            array(
                "outratabela.campo=0",
                "{$this->tableName}.deleted=0"
            ),
            $this->getBase()->getWhere(array('outratabela.campo' => 0))
        );

        $this->assertEquals(
            array(
                "outratabela.deleted=1",
                "{$this->tableName}.deleted=0"
            ),
            $this->getBase()->getWhere(array('outratabela.deleted' => 1))
        );

        $this->assertEquals(
            array(
                "{$this->tableName}.{$this->tableKeyName}=1",
                "{$this->tableName}.deleted=0"
            ),
            $this->getBase()->getWhere(array($this->tableKeyName => 1))
        );

        $dbExpression = new \Zend\Db\Sql\Expression('now()');
        $this->assertEquals(
            array(
                $dbExpression,
                "{$this->tableName}.deleted=0"
            ),
            $this->getBase()->getWhere(array($dbExpression))
        );
    }

    /**
     * Tests campo deleted
     */
    public function testDeletedField(): void
    {
        // Verifica se deve remover o registro
        $this->assertFalse($this->getBase()->getUseDeleted());
        $this->assertTrue($this->getBase()->setUseDeleted(true)->getUseDeleted());
        $this->assertFalse($this->getBase()->setUseDeleted(false)->getUseDeleted());
        $this->assertFalse($this->getBase()->getUseDeleted());

        // Verifica se deve mostrar o registro
        $this->assertFalse($this->getBase()->getShowDeleted());
        $this->assertFalse($this->getBase()->setShowDeleted(false)->getShowDeleted());
        $this->assertTrue($this->getBase()->setShowDeleted(true)->getShowDeleted());
        $this->assertTrue($this->getBase()->getShowDeleted());
    }

    /**
     * Tests Base->getSQlString():void
     */
    public function testGetSQlString(): void
    {
        // Verfiica o padrão não usar o campo deleted e não mostrar os removidos
        $this->assertEquals(
            'SELECT `album`.* FROM `album`',
            $this->getBase()->getSQlString(),
            'showDeleted=false, useDeleted=false'
        );

        // Marca para usar o campo deleted
        $this->getBase()->setUseDeleted(true);
        $this->assertEquals(
            'SELECT `album`.* FROM `album` WHERE album.deleted=0',
            $this->getBase()->getSQlString(),
            'showDeleted=false, useDeleted=true'
        );

        // Marca para não usar o campo deleted
        $this->getBase()->setUseDeleted(false);

        $this->assertEquals(
            'SELECT `album`.* FROM `album` WHERE album.id=1234',
            $this->getBase()->getSQlString(array('id' => 1234))
        );
        $this->assertEquals(
            "SELECT `album`.* FROM `album` WHERE album.texto='textotextotexto'",
            $this->getBase()->getSQlString(array('texto' => 'textotextotexto'))
        );
    }

    /**
     * Tests Base->testGetSQlSelect():void
     */
    public function testGetSQlSelect(): void
    {
        $select = $this->getBase()->getSQlSelect();
        $this->assertInstanceOf('Zend\Db\Sql\Select', $select);
        $this->assertEquals(
            $select->getSqlString($this->getAdapter()->getPlatform()),
            $this->getBase()->getSQlString()
        );
    }

    /**
     * Tests Base->fetchAll():void
     */
    public function testFetchAll(): void
    {
        // O padrão é não usar o campo deleted
        $albuns = $this->getBase()->fetchAll();
        $this->assertCount(4, $albuns, 'showDeleted=false, useDeleted=false');

        // Marca para mostrar os removidos e não usar o campo deleted
        $this->getBase()->setShowDeleted(true)->setUseDeleted(false);
        $this->assertCount(4, $this->getBase()->fetchAll(), 'showDeleted=true, useDeleted=false');

        // Marca pra não mostar os removidos e usar o campo deleted
        $this->getBase()->setShowDeleted(false)->setUseDeleted(true);
        $this->assertCount(3, $this->getBase()->fetchAll(), 'showDeleted=false, useDeleted=true');

        // Marca pra mostrar os removidos e usar o campo deleted
        $this->getBase()->setShowDeleted(true)->setUseDeleted(true);
        $albuns = $this->getBase()->fetchAll();
        $this->assertCount(4, $albuns, 'showDeleted=true, useDeleted=true');

        // Marca não mostrar os removios
        $this->getBase()->setShowDeleted(false);

        $albuns = $this->defaultValues;
        unset($albuns[3]); // remove o deleted=1
        $this->assertEquals($albuns, $this->getBase()->fetchAll());

        // Marca mostrar os removios
        $this->getBase()->setShowDeleted(true);

        $this->assertEquals($this->defaultValues, $this->getBase()->fetchAll());
        $this->assertCount(4, $this->getBase()->fetchAll());
        $this->getBase()->setShowDeleted(false);
        $this->assertCount(3, $this->getBase()->fetchAll());

        // Verifica o where
        $this->assertCount(2, $this->getBase()->fetchAll(array('artist' => $albuns[0]['artist'])));
        $this->assertNull($this->getBase()->fetchAll(array('artist' => $this->defaultValues[3]['artist'])));

        // Verifica o paginator com o padrão
        $paginator = $this->getBase()->setUsePaginator(true)->fetchAll();
        $paginator = $paginator->toJson();
        $fetchAll = $this->getBase()->setUsePaginator(false)->fetchAll();
        $this->assertNotEquals(json_encode($this->defaultValues), $paginator);
        $this->assertEquals(json_encode($fetchAll, JSON_FORCE_OBJECT), $paginator);

        // Verifica o paginator alterando o paginator
        $this->getBase()->getPaginator()->setPageRange(2)
            ->setCurrentPageNumber(1)
            ->setItemCountPerPage(2);
        $paginator = $this->getBase()->setUsePaginator(true)->fetchAll();
        $paginator = $paginator->toJson();
        $this->assertNotEquals(json_encode($this->defaultValues), $paginator);
        $fetchAll = $this->getBase()->setUsePaginator(false)->fetchAll(null, null, 2);
        $this->assertEquals(json_encode($fetchAll, JSON_FORCE_OBJECT), $paginator);

        // Apaga qualquer cache
        $this->assertTrue($this->getBase()->getCache()->flush(), 'apaga o cache');

        // Define exibir os delatados
        $this->getBase()->setShowDeleted(true);

        // Liga o cache
        $this->getBase()->setUseCache(true);
        $this->assertEquals($this->defaultValues, $this->getBase()->fetchAll(), 'Igual');
        $this->assertCount(4, $this->getBase()->fetchAll(), 'Deve conter 4 registros 1');

        // Grava um registro "sem o cache saber"
        $this->getBase()->getTableGateway()->insert(
            array('id' => 10, 'artist' => 'nao existo por enquanto', 'title' => 'bla bla', 'deleted' => 0)
        );

        $this->assertCount(4, $this->getBase()->fetchAll(), 'Deve conter 4 registros 2');
        $this->assertTrue($this->getBase()->getCache()->flush(), 'apaga o cache');
        $this->assertCount(5, $this->getBase()->fetchAll(), 'Deve conter 5 registros');

        // Define não exibir os deletados
        $this->getBase()->setShowDeleted(false);
        $this->assertCount(4, $this->getBase()->fetchAll(), 'Deve conter 4 registros 3');

        // Apaga um registro "sem o cache saber"
        $this->getBase()->getTableGateway()->delete(array("id" => 10));
        $this->getBase()->setShowDeleted(true);
        $this->assertCount(5, $this->getBase()->fetchAll(), 'Deve conter 5 registros');
        $this->assertTrue($this->getBase()->getCache()->flush(), 'apaga o cache');
        $this->assertCount(4, $this->getBase()->fetchAll(), 'Deve conter 4 registros 4');
    }

    /**
     * Tests Base->fetchRow():void
     */
    public function testFetchRow(): void
    {
        // Marca pra usar o campo deleted
        $this->getBase()->setUseDeleted(true);

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
     * Tests Base->fetchAssoc():void
     */
    public function testFetchAssoc(): void
    {
        // O padrão é não usar o campo deleted
        $albuns = $this->getBase()->fetchAssoc();
        $this->assertCount(4, $albuns, 'showDeleted=false, useDeleted=false');
        $this->assertEquals($this->defaultValues[0], $albuns[1]);
        $this->assertEquals($this->defaultValues[1], $albuns[2]);
        $this->assertEquals($this->defaultValues[2], $albuns[3]);
        $this->assertEquals($this->defaultValues[3], $albuns[4]);

        // Marca para mostrar os removidos e não usar o campo deleted
        $this->getBase()->setShowDeleted(true)->setUseDeleted(false);
        $this->assertCount(4, $this->getBase()->fetchAssoc(), 'showDeleted=true, useDeleted=false');

        // Marca pra não mostar os removidos e usar o campo deleted
        $this->getBase()->setShowDeleted(false)->setUseDeleted(true);
        $this->assertCount(3, $this->getBase()->fetchAssoc(), 'showDeleted=false, useDeleted=true');

        // Marca pra mostrar os removidos e usar o campo deleted
        $this->getBase()->setShowDeleted(true)->setUseDeleted(true);
        $albuns = $this->getBase()->fetchAssoc();
        $this->assertCount(4, $albuns, 'showDeleted=true, useDeleted=true');
        $this->assertEquals($this->defaultValues[0], $albuns[1]);
        $this->assertEquals($this->defaultValues[1], $albuns[2]);
        $this->assertEquals($this->defaultValues[2], $albuns[3]);
        $this->assertEquals($this->defaultValues[3], $albuns[4]);
    }

    /**
     * Tests Base->getLoader():void
     */
    public function testGetLoader(): void
    {
        // TODO Auto-generated BaseTest->testGetLoader():void
        $this->markTestIncomplete("getLoader test not implemented");

        $this->Base->getLoader(/* parameters */);
    }

    /**
     * Tests Base->setLoader():void
     */
    public function testSetLoader(): void
    {
        // TODO Auto-generated BaseTest->testSetLoader():void
        $this->markTestIncomplete("setLoader test not implemented");

        $this->Base->setLoader(/* parameters */);
    }

    /**
     * Tests Base->getTable():void
     */
    public function testGetTableGetKey(): void
    {
        $Base = new Base('tablename', 'keyname');
        $this->assertNotNull($Base->getTable());
        $this->assertNotNull($Base->getKey());
        $this->assertEquals('tablename', $Base->getTable());
        $this->assertEquals('keyname', $Base->getKey());
        /*
        // @todo permitir chaves compostas
        $Base = new Base('tablename', array('key1', 'key2'));
        $this->assertNotNull($Base->getTable());
        $this->assertNotNull($Base->getKey());
        $this->assertEquals('tablename', $Base->getTable());
        $this->assertEquals(array('key1', 'key2'), $Base->getKey());
         */
    }

    /**
     * Tests Base->getSelect():void
     */
    public function testGetSelect(): void
    {
        // TODO Auto-generated BaseTest->testGetSelect():void
        $this->markTestIncomplete("getSelect test not implemented");

        $this->Base->getSelect(/* parameters */);
    }

    /**
     * Tests Base->getTableSelect():void
     */
    public function testGetTableSelect(): void
    {
        // TODO Auto-generated BaseTest->testGetTableSelect():void
        $this->markTestIncomplete("getTableSelect test not implemented");

        $this->Base->getTableSelect(/* parameters */);
    }

    /**
     * Tests Base->fetchCount():void
     */
    public function testFetchCount(): void
    {
        // TODO Auto-generated BaseTest->testFetchCount():void
        $this->markTestIncomplete("fetchCount test not implemented");

        $this->Base->fetchCount(/* parameters */);
    }

    /**
     * Tests Base->getHtmlSelect():void
     */
    public function testGetHtmlSelect(): void
    {
        // TODO Auto-generated BaseTest->testGetHtmlSelect():void
        $this->markTestIncomplete("getHtmlSelect test not implemented");

        $this->Base->getHtmlSelect(/* parameters */);
    }

    /**
     * Tests Base->getCache():void
     */
    public function testGetCache(): void
    {
        // TODO Auto-generated BaseTest->testGetCache():void
        $this->markTestIncomplete("getCache test not implemented");

        $this->Base->getCache(/* parameters */);
    }

    /**
     * Tests Base->setUseCache():void
     */
    public function testSetUseCache(): void
    {
        // TODO Auto-generated BaseTest->testSetUseCache():void
        $this->markTestIncomplete("setUseCache test not implemented");

        $this->Base->setUseCache(/* parameters */);
    }

    /**
     * Tests Base->getUseCache():void
     */
    public function testGetUseCache(): void
    {
        // TODO Auto-generated BaseTest->testGetUseCache():void
        $this->markTestIncomplete("getUseCache test not implemented");

        $this->Base->getUseCache(/* parameters */);
    }

    /**
     * Tests Base->getPaginator():void
     */
    public function testGetPaginator(): void
    {
        // TODO Auto-generated BaseTest->testGetPaginator():void
        $this->markTestIncomplete("getPaginator test not implemented");

        $this->Base->getPaginator(/* parameters */);
    }

    /**
     * Tests Base->setUsePaginator():void
     */
    public function testSetUsePaginator(): void
    {
        // TODO Auto-generated BaseTest->testSetUsePaginator():void
        $this->markTestIncomplete("setUsePaginator test not implemented");

        $this->Base->setUsePaginator(/* parameters */);
    }

    /**
     * Tests Base->getUsePaginator():void
     */
    public function testGetUsePaginator(): void
    {
        // TODO Auto-generated BaseTest->testGetUsePaginator():void
        $this->markTestIncomplete("getUsePaginator test not implemented");

        $this->Base->getUsePaginator(/* parameters */);
    }
}

