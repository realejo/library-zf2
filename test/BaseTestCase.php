<?php
namespace RealejoTest;

/**
 * Test case para as funcionalidades padrões
 *
 * @link      http://github.com/realejo/libraray-zf2
 * @copyright Copyright (c) 2014 Realejo (http://realejo.com.br)
 * @license   http://unlicense.org
 */
use Zend\Db\Adapter\Adapter;

class BaseTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Zend\Db\Adapter\Adapter
     */
    protected $adapter = null;

    /**
     * Lista de tabelas que serão criadas e dropadas
     *
     * @var array
     */
    protected $tables = array();

    public function __construct($tables = null)
    {
        if (!empty($tables) && is_array($tables)) {
            $this->tables = $tables;
        }
    }

    /**
     * @return \Zend\Db\Adapter\Adapter
     */
    public function getAdapter()
    {
        if (!isset($this->adapter)) {

            // Receupera as configurações do banco de dados
            $config = TEST_ROOT . '/configs/db.php';
            if (!file_exists($config)) {
                $this->fail("Arquivo de configuração do banco de dados $config não encontrado.");
            }
            $this->adapter = new \Zend\Db\Adapter\Adapter(require $config);

            \Zend\Db\TableGateway\Feature\GlobalAdapterFeature::setStaticAdapter($this->adapter);
        }
        return $this->adapter;
    }

    /**
     *
     * @return SetupTest
     */
    public function createTables($tables = null)
    {
        if (empty($tables)) {
            $tables = $this->tables;
        }

        // Recupera o script para criar as tabelas
        foreach($tables as $tbl) {
            $create = TEST_ROOT  . "/_files/sql/$tbl.create.sql";
            if (!file_exists($create)) {
                $this->fail("create não encontrado em $create");
            }

            // Cria a tabela de usuários
            $this->getAdapter()->query(file_get_contents($create), Adapter::QUERY_MODE_EXECUTE);
        }

        return $this;
    }

    /**
     * @return SetupTest
     */
    public function dropTables($tables = null)
    {
        if (empty($tables)) {
            $tables = array_reverse($this->tables);
        }

        if (!empty($tables)) {
            // Recupera o script para remover as tabelas
            foreach($tables as $tbl) {
                $drop = TEST_ROOT . "/_files/sql/$tbl.drop.sql";
                if (!file_exists($drop)) {
                    $this->fail("drop não encontrado em $drop");
                }

                // Remove a tabela de usuários
                $this->getAdapter()->query(file_get_contents($drop), Adapter::QUERY_MODE_EXECUTE);
            }
        }

        return $this;
    }

	public function clearApplicationData()
    {
        // Verifica se há APPLICATION_DATA
        if (!defined('APPLICATION_DATA')) {
            $this->fail('APPLICATION_DATA não definido');
        }
        // Verifica se a pasta existe e tem permissão de escrita
        if (!is_dir(APPLICATION_DATA) || !is_writeable(APPLICATION_DATA)) {
            $this->fail('APPLICATION_DATA não definido');
        }

        // Apaga todo o conteudo dele
        $this->rrmdir(APPLICATION_DATA);

        return $this->isApplicationDataEmpty();
    }

    public function isApplicationDataEmpty()
    {
        // Verifica se há APPLICATION_DATA
        if (!defined('APPLICATION_DATA')) {
            $this->fail('APPLICATION_DATA não definido');
        }
        // Verifica se a pasta existe e tem permissão de escrita
        if (!is_dir(APPLICATION_DATA) || !is_writeable(APPLICATION_DATA)) {
            $this->fail('APPLICATION_DATA não definido');
        }

        // Retorna se está vazio
        return (count(scandir(APPLICATION_DATA)) == 3);
    }

    public function rrmdir($dir)
    {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != ".." && $object != ".gitignore") {
                    if (filetype($dir . "/" . $object) == "dir") {
                        $this->rrmdir($dir . "/" . $object);
                    } else {
                        unlink($dir . "/" . $object);
                    }
                }
            }
            reset($objects);
            // Não apaga o APPLICATION_DATA
            if ($dir != APPLICATION_DATA) {
                rmdir($dir);
            }
        }
    }


    /**
     * Retorna as tabelas padrões
     *
     * @return array
     */
    public function getTables()
    {
        return $this->tables;
    }

    /**
     * Define as tabelas a serem usadas com padrão
     *
     * @param array $tables
     *
     * @return BaseTestCase
     */
    public function setTables($tables)
    {
        $this->tables = $tables;

        return $this;
    }
}
