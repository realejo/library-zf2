<?php

namespace RealejoTest\App\Model;

/**
 * PaginatorTest test case.
 *
 * @link      http://github.com/realejo/libraray-zf2
 * @copyright Copyright (c) 2014 Realejo (http://realejo.com.br)
 * @license   http://unlicense.org
 */
use Realejo\App\Model\Paginator;

class PaginatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     *
     * @var Paginator
     */
    private $Paginator;


    /**
     * @return Base
     */
    public function getPaginator()
    {
        if ($this->Paginator === null) {
            $this->Paginator = new Paginator();
        }
        return $this->Paginator;
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
        parent::tearDown();
    }

    /**
     * getPageRange
     */
    public function testGetPageRange()
    {
        // Recupera o Page Range
        $page = $this->getPaginator()->getPageRange();

        // Verifica se o conteudo veio correto
        $this->assertEquals(10, $page);
        $this->assertTrue(is_int($page));
        $this->assertFalse(is_string($page));
    }

    /**
     * getCurrentPageNumber
     */
    public function testGetCurrentPageNumber()
    {
        // Recupera o Current Page Number
        $page = $this->getPaginator()->getCurrentPageNumber();

        // Verifica se o conteudo veio correto
        $this->assertEquals(1, $page);
        $this->assertTrue(is_int($page));
        $this->assertFalse(is_string($page));
    }

    /**
     * getItemCountPerPage
     */
    public function testGetItemCountPerPage()
    {
        // Recupera o Item Count Per Page
        $page = $this->getPaginator()->getItemCountPerPage();

        // Verifica se o conteudo veio correto
        $this->assertEquals(10, $page);
        $this->assertTrue(is_int($page));
        $this->assertFalse(is_string($page));
    }

    /**
     * setPageRange
     */
    public function testSetPageRange()
    {
        // Recupera o Page Range
        $page = $this->getPaginator()->getPageRange();

        // Verifica se o conteudo veio correto
        $this->assertEquals(10, $page);
        $this->assertTrue(is_int($page));
        $this->assertFalse(is_string($page));

        // Define o valor do Page Range
        $page = $this->getPaginator()->setPageRange(15);

        // Verifica se o conteudo veio correto
        $this->assertTrue(is_object($page));

        // Recupera o Page Range
        $page = $this->getPaginator()->getPageRange();

        // Verifica se o conteudo veio correto
        $this->assertEquals(15, $page);
        $this->assertTrue(is_int($page));
        $this->assertFalse(is_string($page));
    }

    /**
     * setCurrentPageNumber
     */
    public function testSetCurrentPageNumber()
    {
        // Recupera o Current Page Number
        $page = $this->getPaginator()->getCurrentPageNumber();

        // Verifica se o conteudo veio correto
        $this->assertEquals(1, $page);
        $this->assertTrue(is_int($page));
        $this->assertFalse(is_string($page));

        // Define o Current Page Number
        $page = $this->getPaginator()->setCurrentPageNumber(2);

        // Verifica se o conteudo veio correto
        $this->assertTrue(is_object($page));

        // Recupera o Current Page Number
        $page = $this->getPaginator()->getCurrentPageNumber();

        // Verifica se o conteudo veio correto
        $this->assertEquals(2, $page);
        $this->assertTrue(is_int($page));
        $this->assertFalse(is_string($page));
    }

    /**
     * setItemCountPerPage
     */
    public function testSetItemCountPerPage()
    {
        // Recupera o Item Count Per Page
        $page = $this->getPaginator()->getItemCountPerPage();

        // Verifica se o conteudo veio correto
        $this->assertEquals(10, $page);
        $this->assertTrue(is_int($page));
        $this->assertFalse(is_string($page));

        // Recupera o Item Count Per Page
        $page = $this->getPaginator()->setItemCountPerPage(20);

        // Verifica se o conteudo veio correto
        $this->assertTrue(is_object($page));

        // Recupera o Item Count Per Page
        $page = $this->getPaginator()->getItemCountPerPage();

        // Verifica se o conteudo veio correto
        $this->assertEquals(20, $page);
        $this->assertTrue(is_int($page));
        $this->assertFalse(is_string($page));
    }
}

