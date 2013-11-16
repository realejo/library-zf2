<?php
/**
 * Csv test case.
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
class TableAdapterTest extends PHPUnit_Framework_TestCase
{

    /**
     *
     * @var TableAdapter
     */
    private $TableAdapter;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp ()
    {
        parent::setUp();

        // TODO Auto-generated TableAdapterTest::setUp()

        $this->TableAdapter = new TableAdapter(/* parameters */);

    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown ()
    {
        // TODO Auto-generated TableAdapterTest::tearDown()

        $this->TableAdapter = null;

        parent::tearDown();
    }

    /**
     * Constructs the test case.
     */
    public function __construct ()
    {
        // TODO Auto-generated constructor
    }

    /**
     * Tests TableAdapter->__construct()
     */
    public function test__construct ()
    {
        // TODO Auto-generated TableAdapterTest->test__construct()
        $this->markTestIncomplete("__construct test not implemented");

        $this->TableAdapter->__construct(/* parameters */);

    }

    /**
     * Tests TableAdapter->getOrder()
     */
    public function testGetOrder ()
    {
        // TODO Auto-generated TableAdapterTest->testGetOrder()
        $this->markTestIncomplete("getOrder test not implemented");

        $this->TableAdapter->getOrder(/* parameters */);

    }

    /**
     * Tests TableAdapter->setOrder()
     */
    public function testSetOrder ()
    {
        // TODO Auto-generated TableAdapterTest->testSetOrder()
        $this->markTestIncomplete("setOrder test not implemented");

        $this->TableAdapter->setOrder(/* parameters */);

    }

    /**
     * Tests TableAdapter->getWhere()
     */
    public function testGetWhere ()
    {
        // TODO Auto-generated TableAdapterTest->testGetWhere()
        $this->markTestIncomplete("getWhere test not implemented");

        $this->TableAdapter->getWhere(/* parameters */);

    }

    /**
     * Tests TableAdapter->getSelect()
     */
    public function testGetSelect ()
    {
        // TODO Auto-generated TableAdapterTest->testGetSelect()
        $this->markTestIncomplete("getSelect test not implemented");

        $this->TableAdapter->getSelect(/* parameters */);

    }

    /**
     * Tests TableAdapter->getSQlString()
     */
    public function testGetSQlString ()
    {
        // TODO Auto-generated TableAdapterTest->testGetSQlString()
        $this->markTestIncomplete("getSQlString test not implemented");

        $this->TableAdapter->getSQlString(/* parameters */);

    }

    /**
     * Tests TableAdapter->fetchAll()
     */
    public function testFetchAll ()
    {
        // TODO Auto-generated TableAdapterTest->testFetchAll()
        $this->markTestIncomplete("fetchAll test not implemented");

        $this->TableAdapter->fetchAll(/* parameters */);

    }

    /**
     * Tests TableAdapter->fetchRow()
     */
    public function testFetchRow ()
    {
        // TODO Auto-generated TableAdapterTest->testFetchRow()
        $this->markTestIncomplete("fetchRow test not implemented");

        $this->TableAdapter->fetchRow(/* parameters */);

    }

    /**
     * Tests TableAdapter->fetchAssoc()
     */
    public function testFetchAssoc ()
    {
        // TODO Auto-generated TableAdapterTest->testFetchAssoc()
        $this->markTestIncomplete("fetchAssoc test not implemented");

        $this->TableAdapter->fetchAssoc(/* parameters */);

    }

    /**
     * Tests TableAdapter->save()
     */
    public function testSave ()
    {
        // TODO Auto-generated TableAdapterTest->testSave()
        $this->markTestIncomplete("save test not implemented");

        $this->TableAdapter->save(/* parameters */);

    }

    /**
     * Tests TableAdapter->delete()
     */
    public function testDelete ()
    {
        // TODO Auto-generated TableAdapterTest->testDelete()
        $this->markTestIncomplete("delete test not implemented");

        $this->TableAdapter->delete(/* parameters */);

    }

}

