<?php
/**
 * Model com acesso ao BD, Cache e Paginator padronizado.
 * Também permite que tenha acesso ao Loader
 *
 * @author     Realejo
 * @copyright  Copyright (c) 2014 Realejo Design Ltda. (http://www.realejo.com.br)
 */
namespace Realejo\App\Model;

use \Zend\Db\TableGateway\Feature\GlobalAdapterFeature;
use \Zend\Db\Adapter\AdapterInterface;
use \Zend\Db\TableGateway\TableGateway;

class Base
{

    /**
     *
     * @var Zend\Db\TableGateway\TableGateway
     */
    private $_tableGateway;

    /**
     *
     * @var Zend\Db\TableGateway\AdapterInterface
     */
    private $_dbAdapter;

    /**
     *
     * @var \Realejo\App\Loader\Loader
     */
    private $_loader;

    /**
     * Não pode ser usado dentro do Loader pois cada classe tem configurações diferentes
     *
     * @var App_Model_Paginator
     */
    private $_paginator;

    /**
     * Não pode ser usado dentro do Loader pois cada classe tem configurações diferentes
     *
     * @var App_Model_Cache
     */
    private $_cache;

    /**
     * Define se deve usar o cache ou não
     *
     * @var boolean
     */
    protected $useCache = false;

    /**
     * Define de deve usar o paginator
     *
     * @var boolean
     */
    private $usePaginator = false;

    /**
     * Define a tabela a ser usada
     *
     * @var string
     */
    protected $table;

    /**
     * Define o nome da chave
     *
     * @var string
     */
    protected $key;

    /**
     * Define a ordem padrão a ser usada na consultas
     *
     * @var string
     */
    protected $order;

    /**
     * Define se deve remover os registros ou apenas marcar como removido
     *
     * @var boolean
     */
    protected $useDeleted = false;

    /**
     * Define se deve mostrar os registros marcados como removido
     *
     * @var boolean
     */
    protected $showDeleted = false;

    /**
     * Campo a ser usado no <option>
     *
     * @var string
     */
    protected $select_option = '{nome}';

    /**
     * Campos a serem adicionados no <option> como data
     *
     * @var string array
     */
    protected $select_option_data;

    public function __construct($table = null, $key = null, $dbAdapter = null)
    {
        if ((empty($table) || ! is_string($table)) && ! isset($this->table)) {
            throw new \Exception('Nome da tabela inválido');
        }

        if ((empty($key) || ! is_string($key)) && ! isset($this->key)) {
            throw new \Exception('Nome da chave inválido');
        }

        // Define a chave e o nome da tabela
        $this->key = $key;
        $this->table = $table;

        // Define o adapter padrão
        if (! empty($dbAdapter)) {
            $this->_dbAdapter = $dbAdapter;
        }
    }

    /**
     *
     * @return App_Loader
     */
    public function getLoader()
    {
        if (! isset($this->_loader)) {
            $this->setLoader(new \Realejo\App\Loader\Loader());
        }

        return $this->_loader;
    }

    public function setLoader($loader)
    {
        $this->_loader = $loader;
    }

    /**
     *
     * @return TableGateway
     */
    public function getTableGateway()
    {
        if (empty($this->table)) {
            throw new \Exception('Tabela não definida em ' . get_class($this) . '::getTable()');
        }

        // Define o adapter padrão
        if (empty($this->_dbAdapter)) {
            $this->_dbAdapter = GlobalAdapterFeature::getStaticAdapter();
        }

        // Verifica se tem adapter válido
        if (! ($this->_dbAdapter instanceof AdapterInterface)) {
            throw new \Exception("Adapter dever ser uma instancia de AdapterInterface");
        }
        $this->_tableGateway = new TableGateway($this->table, $this->_dbAdapter);

        // retorna o tabela
        return $this->_tableGateway;
    }

    /**
     * Return the where clause
     *
     * @param string|array $where
     *            OPTIONAL An SQL WHERE clause.
     *
     * @return array null
     */
    public function getWhere($where = null)
    {
        // Sets where is array
        $this->where = array();

        // Checks $where is not null
        if (empty($where)) {
            if (! $this->showDeleted) {
                $this->where[] = "{$this->getTableGateway()->getTable()}.deleted=0";
            }
        } else {

            // Checks $where is deleted
            if (! isset($where['deleted']) && ! $this->showDeleted) {
                $where['deleted'] = 0;
            }

            // Checks $where is not array
            if (! is_array($where))
                $where = array(
                    $where
                );
            foreach ($where as $id => $w) {

                // Checks $where is not string
                if ($w instanceof \Zend\Db\Sql\Expression) {
                    $this->where[] = $w;

                // Checks is deleted
                } elseif ($id === 'deleted' && $w === false) {
                    $this->where[] = "{$this->getTableGateway()->getTable()}.deleted=0";

                } elseif ($id === 'deleted' && $w === true) {
                    $this->where[] = "{$this->getTableGateway()->getTable()}.deleted=1";

                } elseif ((is_numeric($id) && $w === 'ativo') || ($id === 'ativo' && $w === true)) {
                    $this->where[] = "{$this->getTableGateway()->getTable()}.ativo=1";

                } elseif ($id === 'ativo' && $w === false) {
                    $this->where[] = "{$this->getTableGateway()->getTable()}.ativo=0";

                    // Checks $id is not numeric and $w is numeric
                } elseif (! is_numeric($id) && is_numeric($w)) {
                    if (strpos($id, '.') === false)
                        $id = $this->getTableGateway()->getTable() . ".$id";
                    $this->where[] = "$id=$w";

                /**
                 * Funciona direto com array, mas tem que verificar o impacto no join
                 * if (strpos($id, '.') === false) {
                 * $this->where[$id] = $w;
                 * } else {
                 * $this->where[] = "$id=$w";
                 * }
                 */

                    // Checks $id is not numeric and $w is string
                } elseif (! is_numeric($id) && is_string($id)) {
                    if (strpos($id, '.') === false)
                        $id = $this->getTableGateway()->getTable() . ".$id";
                    $this->where[] = "$id='$w'";

                /**
                 * Funciona direto com array, mas tem que verificar o impacto no join
                 * if (strpos($id, '.') === false) {
                 * $this->where[$id] = $w;
                 * } else {
                 * $this->where[] = "$id='$w'";
                 * }
                 */

                    // Return $id is not numeric and $w is string
                } else {
                    throw new \Exception('Condição inválida em TableAdapter::getWhere()');
                }
            }
        } // End $where

        return $this->where;
    }

    /**
     * Retorna o select para a consulta
     *
     * @param string|array $where
     *            OPTIONAL An SQL WHERE clause
     * @param string|array $order
     *            OPTIONAL An SQL ORDER clause.
     * @param int $count
     *            OPTIONAL An SQL LIMIT count.
     * @param int $offset
     *            OPTIONAL An SQL LIMIT offset.
     *
     * @return Zend_Db_Table_Select
     */
    public function getSelect($where = null, $order = null, $count = null, $offset = null)
    {
        /**
         *
         * @var \Zend\Db\Sql\Select
         */
        $select = $this->getTableGateway()
                       ->getSql()
                       ->select();

        // Define a ordem
        if (empty($order)) {
            $order = $this->getOrder();
        }
        if (!empty($order)) {
            $select->order($order);
        }

        // Verifica se há paginação
        if (!is_null($count))  {
            $select->limit($count);
        }

        // Verifica se há paginação
        if (!is_null($offset)) {
            $select->offset($offset);
        }

        // Define o where
        $select->where($this->getWhere($where));

        return $select;
    }

    public function getSQlString($where = null, $order = null, $count = null, $offset = null)
    {
        return $this->getSelect($where, $order, $count, $offset)->getSqlString();
    }

    /**
     * Retorna vários registros da tabela
     *
     * @param mixed $where
     *            Condições SQL
     * @param array|int $order
     *            Ordem dos registros
     * @param int $count
     *            Limite de registros
     * @param int $offset
     *            Offset
     * @return array
     */
    public function fetchAll($where = null, $order = null, $count = null, $offset = null)
    {
        /**
         *
         * @var \Zend\Db\Sql\Select
         */
        $select = $this->getSelect($where, $order, $count, $offset);

        // build result set
        $resultSet = $this->getTableGateway()->selectWith($select);

        // Retorna os registros
        return (count($resultSet) > 0) ? $resultSet->toArray() : null;
    }

    /**
     * Recupera um registro
     *
     * @param mixed $where
     *            condições para localizar o usuário
     *
     * @return array null com os dados do usuário ou null se não localizar
     */
    public function fetchRow($where, $order = null)
    {
        // Define o código do usuário
        if (is_numeric($where))
            $where = array(
                $this->key => $where
            );

            // Recupera o usuário
        $row = $this->fetchAll($where, $order, 1);

        // Retorna o usuário
        return (! is_null($row) && count($row) > 0) ? $row[0] : null;
    }

    /**
     * Retorna um array associado com os usuários com a chave sendo o código deles
     *
     * @param mixed $where
     *            Condições SQL
     * @param array|int $order
     *            Ordem dos usuários
     * @param int $count
     * @param int $offset
     *
     * @return array
     */
    public function fetchAssoc($where = null, $order = null, $count = null, $offset = null)
    {
        $rowset = $this->fetchAll($where, $order, $count, $offset);
        $return = array();
        foreach ($rowset as $row) {
            $return[$row[$this->key]] = $row;
        }

        return $return;
    }

    /**
     * Retorna o total de registros encontrados com a consulta
     *
     * @todo se usar consulta com mais de uma tabela talvez de erro
     *
     * @param string|array $where
     *            An SQL WHERE clause
     *
     * @return int
     */
    public function fetchCount($where = null)
    {
        // Define o select
        $select = $this->getSelect($where);

        // Altera as colunas
        $select->reset('columns')->columns(new Zend_Db_Expr('count(*) as total'));

        $fetchRow = $this->fetchRow($select);

        if (empty($fetchRow)) {
            return 0;
        } else {
            return $fetchRow['total'];
        }
    }

    /**
     * Retorna o HTML de um <select> apra usar em formulários
     *
     * @param string $nome        Nome/ID a ser usado no <select>
     * @param string $selecionado Valor pré seleiconado
     * @param string $opts        Opções adicionais
     *
     * As opções adicionais podem ser
     *  - where       => filtro para ser usando no fetchAll()
     *  - placeholder => legenda quando nenhum estiver selecionado e/ou junto com show-empty
     *  - show-empty  => mostra um <option> vazio no inicio mesmo com um selecionado
     *
     * @return string
     */
    public function getHtmlSelect($nome, $selecionado = null, $opts = null)
    {
        // Recupera os registros
        $where = (isset($opts['where'])) ? $opts['where'] : null;
        $fetchAll = $this->fetchAll();

        // Verifica o select_option_data
        if (isset($this->select_option_data) && is_string($this->select_option_data)) {
            $this->select_option_data = array(
                $this->select_option_data
            );
        }

        // Verifica se deve manter um em branco
        $showEmpty = (isset($opts['show-empty']) && $opts['show-empty'] === true);

        // Define ao plcaeholder aser usado
        $placeholder = (isset($opts['placeholder'])) ? $opts['placeholder'] : '';

        // Monta as opções
        $options = '';
        if (! empty($fetchAll)) {
            foreach ($fetchAll as $row) {
                preg_match_all('/\{([a-z_]*)\}/', $this->select_option, $matches);

                // Troca pelos valores
                foreach ($matches[1] as $i => $m) {
                    $matches[1][$i] = $row[$m];
                }

                // Define o option
                $option = str_replace($matches[0], $matches[1], $this->select_option);

                // Verifica se deve adicionar campos ao data
                $data = '';
                if (isset($this->select_option_data)) {
                    $data = '';
                    foreach ($this->select_option_data as $name => $field) {
                        if (is_numeric($name)) {
                            $name = $field;
                        }
                        $data .= " data-$name=\"{$row[$field]}\"";
                    }
                }
                $options .= "<option value=\"{$row[$this->key]}\" $data>$option</option>";
            }
        }

        // Verifica se tem valor padrão
        if (! is_null($selecionado)) {
            $temp = str_replace("<option value=\"$selecionado\"", "<option value=\"$selecionado\" selected=\"selected\"", $options);
            if ($temp === $options)
                $selecionado = null;
            $options = $temp;
        }

        // Abre o select
        $select = "<select class=\"form-control\" name=\"$nome\" id=\"$nome\">";

        // Verifica se tem valor padrão selecionado
        if (empty($selecionado) || $showEmpty)
            $select .= "<option value=\"\">$placeholder</option>";

        // Coloca as opções
        $select .= $options;

        // Fecha o select
        $select .= '</select>';

        // Retorna o select
        return $select;
    }

    /**
     * Retorna o frontend para gravar o cache
     *
     * @return Zend_Cache_Frontend
     */
    public function getCache()
    {
        $cache = $this->getLoader()->getModel('App_Model_Cache');
        return $cache->getFrontend(get_class($this));
    }

    /**
     * Define se deve usar o cache
     *
     * @param boolean $useCache
     */
    public function setUseCache($useCache)
    {
        // Grava o cache
        $this->useCache = $useCache;

        // Mantem a cadeia
        return $this;
    }

    /**
     * Retorna se deve usar o cache
     *
     * @return boolean
     */
    public function getUseCache()
    {
        return $this->useCache;
    }

    /**
     * PAGINATOR
     * Diferente do cache, se gravar qualquer variável do paginator ele será criado
     */

    /**
     * Retorna o frontend para gravar o cache
     *
     * @return App_Model_Paginator
     */
    public function getPaginator()
    {
        if (! isset($this->_paginator)) {
            $this->_paginator = new App_Model_Paginator();
        }

        $this->usePaginator = true;

        return $this->_paginator;
    }

    /**
     * Define se deve usar o paginator
     *
     * @param boolean $usepaginator
     */
    public function setUsePaginator($usePaginator)
    {
        // Grava o paginator
        $this->usePaginator = $usePaginator;

        // Mantem a cadeia
        return $this;
    }

    /**
     * Retorna se deve usar o paginator
     *
     * @return boolean
     */
    public function getUsePaginator()
    {
        return $this->usePaginator;
    }

    /**
     * Getters and setters
     */
    /**
     *
     * @return the $table
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     *
     * @return the $key
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     *
     * @return the $order
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     *
     * @return the $select_option
     */
    public function getSelect_option()
    {
        return $this->select_option;
    }

    /**
     *
     * @return the $select_option_data
     */
    public function getSelect_option_data()
    {
        return $this->select_option_data;
    }

    /**
     *
     * @param string $table
     */
    public function setTable($table)
    {
        $this->table = $table;
        return $this;
    }

    /**
     *
     * @param string $key
     */
    public function setKey($key)
    {
        $this->key = $key;
        return $this;
    }

    /**
     *
     * @param string $order
     */
    public function setOrder($order)
    {
        $this->order = $order;
        return $this;
    }

    /**
     *
     * @param string $select_option
     */
    public function setSelect_option($select_option)
    {
        $this->select_option = $select_option;
        return $this;
    }

    /**
     *
     * @param
     *            Ambigous <string, multitype:> $select_option_data
     */
    public function setSelect_option_data($select_option_data)
    {
        $this->select_option_data = $select_option_data;
        return $this;
    }

    /**
     * Retorna se irá usar o campo deleted ou remover o registro quando usar delete()
     *
     * @return boolean
     */
    public function getUseDeleted()
    {
        return $this->useDeleted;
    }

    /**
     * Define se irá usar o campo deleted ou remover o registro quando usar delete()
     *
     * @param boolean $useDeleted
     *
     * @return TableAdapter
     */
    public function setUseDeleted($useDeleted)
    {
        $this->useDeleted = $useDeleted;

        // Mantem a cadeia
        return $this;
    }

    /**
     * Retorna se deve retornar os registros marcados como removidos
     *
     * @return boolean
     */
    public function getShowDeleted()
    {
        return $this->showDeleted;
    }

    /**
     * Define se deve retornar os registros marcados como removidos
     *
     * @param boolean $showDeleted
     *
     * @return TableAdapter
     */
    public function setShowDeleted($showDeleted)
    {
        $this->showDeleted = $showDeleted;

        // Mantem a cadeia
        return $this;
    }
}