<?php
/**
 * Model com acesso ao BD, Cache e Paginator padronizado.
 * Também permite que tenha acesso ao Loader
 *
 * @link      http://github.com/realejo/libraray-zf2
 * @copyright Copyright (c) 2014 Realejo (http://realejo.com.br)
 * @license   http://unlicense.org
 */
namespace Realejo\App\Model;

use Realejo\App\Loader\Loader;
use Laminas\Db\TableGateway\Feature\GlobalAdapterFeature;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\TableGateway\TableGateway;
use Laminas\Paginator\Adapter\DbSelect;
use Realejo\Utils\Paginator as LaminasPaginator;
use Laminas\Db\Sql;

class Base
{

    const KEY_STRING   = 'STRING';
    const KEY_INTEGER  = 'INTEGER';
    const KEY_DATE     = 'DATE';
    const KEY_DATETIME = 'DATETIME';
    const KEY_FLOAT    = 'FLOAT';

    /**
     * @todo isso é realmente necessário?!
     * @var array
     */
    protected $where;

    /**
     *
     * @var TableGateway
     */
    private $_tableGateway;

    /**
     *
     * @var AdapterInterface
     */
    private $_dbAdapter;

    /**
     *
     * @var Loader
     */
    private $_loader;

    /**
     * Não pode ser usado dentro do Loader pois cada classe tem configurações diferentes
     *
     * @var Paginator
     */
    private $_paginator;

    /**
     * Não pode ser usado dentro do Loader pois cada classe tem configurações diferentes
     *
     * @var \Laminas\Cache\Storage\Adapter\Filesystem
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
    protected $htmlSelectOption = '{nome}';

    /**
     * Campos a serem adicionados no <option> como data
     *
     * @var string|array
     */
    protected $htmlSelectOptionData;

    /**
     * Se o nome da tabela e a chave não estiverem hardcoded é preciso informar
     *
     * @param string $table               OPCIONAL. Nome da tabela
     * @param string $key                 OPCIONAL. Chave da tabela
     * @param AdapterInterface $dbAdapter OPCIONAL. Adapter a ser usado. Se não informado será usado o padrão
     * @throws \Exception
     */
    public function __construct($table = null, $key = null, AdapterInterface $dbAdapter = null)
    {
        // Verifica o nome da tabela
        if (empty($table) && !is_string($table)) {
            if (isset($this->table)) {
                $table = $this->table;
            } else {
                throw new \Exception('Nome da tabela inválido');
            }
        }

        // Verifica o nome da chave
        if (empty($key) && !is_string($key)) {
            if (isset($this->key)) {
                $key = $this->key;
            } else {
                throw new \Exception('Nome da chave inválido');
            }
        }

        // Define a chave e o nome da tabela
        $this->key = $key;
        $this->table = $table;

        // Define o adapter padrão
        if (!empty($dbAdapter)) {
            $this->setAdapter($dbAdapter);
        }
    }

    /**
     * @return Loader
     */
    public function getLoader()
    {
        if (! isset($this->_loader)) {
            $this->setLoader(new Loader());
        }

        return $this->_loader;
    }

    /**
     * @param Loader $loader
     *
     * @return $this
     */
    public function setLoader(Loader $loader)
    {
        $this->_loader = $loader;

        return $this;
    }

    /**
     * @return TableGateway
     * @throws \Exception
     */
    public function getTableGateway()
    {
        // Verifica se já está carregado
        if (isset($this->_tableGateway)) {
            return $this->_tableGateway;
        }

        if (empty($this->table)) {
            throw new \Exception('Tabela não definida em ' . get_class($this) . '::getTable()');
        }

        $this->_tableGateway = new TableGateway($this->table, $this->getAdapter());

        // retorna o TableGateway
        return $this->_tableGateway;
    }

    /**
     * Return the where clause
     *
     * @param string|array $where OPTIONAL Consulta SQL
     * @return array null
     * @throws \Exception
     */
    public function getWhere($where = null)
    {
        // Sets where is array
        $this->where = array();

        // Checks $where is not null
        if (empty($where)) {
            if ($this->getUseDeleted() && !$this->getShowDeleted()) {
                $this->where[] = "{$this->getTableGateway()->getTable()}.deleted=0";
            }
        } else {

            // Checks $where is deleted
            if ($this->getUseDeleted() && !$this->getShowDeleted() && !isset($where['deleted'])) {
                $where['deleted'] = 0;
            }

            // Checks $where is not array
            if (! is_array($where)) {
                $where = array(
                    $where
                );
            }

            // Process clauses
            foreach ($where as $id => $w) {

                // Checks $where is not string
                //@todo deveria ser Expression ou PredicateInterface
                if ($w instanceof Sql\Expression || $w instanceof Sql\Predicate\PredicateInterface) {
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
                    if (strpos($id, '.') === false) {
                        $id = $this->getTableGateway()->getTable() . ".$id";
                    }
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
                    if (strpos($id, '.') === false) {
                        $id = $this->getTableGateway()->getTable() . ".$id";
                    }
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
     * @param mixed     $where  OPTIONAL Condições SQL
     * @param array|int $order  OPTIONAL Ordem dos registros
     * @param int       $count  OPTIONAL Limite de registros
     * @param int       $offset OPTIONAL Offset
     *
     * @return Sql\Select
     */
    public function getSelect($where = null, $order = null, $count = null, $offset = null)
    {
        /**
         * @var Sql\Select
         */
        $select = $this->getSQLSelect();

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
        $where = $this->getWhere($where);
        if (!empty($where)) {
            $select->where($where);
        }

        return $select;
    }

    /**
     * Retorna o Select básico do model
     * Sobrescreva este método para incluir os joins
     *
     * @return Sql\Select
     */
    public function getSQLSelect()
    {
        return $this->getTableGateway()
                    ->getSql()
                    ->select();
    }

    /**
     * Retorna a consulta SQL que será executada
     *
     * @param mixed     $where  OPTIONAL Condições SQL
     * @param array|int $order  OPTIONAL Ordem dos registros
     * @param int       $count  OPTIONAL Limite de registros
     * @param int       $offset OPTIONAL Offset
     *
     * @return string
     */
    public function getSQlString($where = null, $order = null, $count = null, $offset = null)
    {
        return $this->getSelect($where, $order, $count, $offset)->getSqlString($this->getTableGateway()->getAdapter()->getPlatform());
    }

    /**
     * Retorna vários registros da tabela
     *
     * @param mixed     $where  OPTIONAL Condições SQL
     * @param array|int $order  OPTIONAL Ordem dos registros
     * @param int       $count  OPTIONAL Limite de registros
     * @param int       $offset OPTIONAL Offset
     *
     * @return array
     */
    public function fetchAll($where = null, $order = null, $count = null, $offset = null)
    {
        // Cria a assinatura da consulta
        if ($where instanceof Sql\Select) {
            $md5 = md5($where->getSqlString($this->getTableGateway()->getAdapter()->getPlatform()));
        } else {
            $md5 = md5(
                    var_export($where, true)
                    . var_export($order, true)
                    . var_export($count, true)
                    . var_export($offset, true)
                    . var_export($this->getShowDeleted(), true)
                    . var_export($this->getUseDeleted(), true));
        }

        // Verifica se tem no cache
        // o Zend_Paginator precisa do Zend_Paginator_Adapter_DbSelect para acessar o cache
        if ($this->getUseCache() && !$this->getUsePaginator() && $this->getCache()->hasItem($md5)) {
            return $this->getCache()->getItem($md5);

        } else {

            // Recupera a clausula where dos ExtraFields
            $extraFields = null;

            if ($where instanceof Sql\Select) {
                $select = $where;
            } else {
                if (isset($where['extra-fields'])) {
                    $extraFields = $where['extra-fields'];
                    unset($where['extra-fields']);
                }

                /**
                 *
                 * @var Sql\Select
                 */
                $select = $this->getSelect($where, $order, $count, $offset);
            }

            // Verifica se deve usar o Paginator
            if ($this->getUsePaginator()) {

                $paginatorAdapter = new DbSelect(
                                        // our configured select object
                                        $select,
                                        // the adapter to run it against
                                        $this->getTableGateway()->getAdapter()
                                    );
                $fetchAll = new LaminasPaginator($paginatorAdapter);

                // Verifica se deve usar o cache
                if ($this->getUseCache()) {
                    $fetchAll->setCacheEnabled(true)->setCache($this->getCache());
                }

                // Configura o paginator
                $fetchAll->setPageRange($this->getPaginator()->getPageRange());
                $fetchAll->setCurrentPageNumber($this->getPaginator()->getCurrentPageNumber());
                $fetchAll->setItemCountPerPage($this->getPaginator()->getItemCountPerPage());

            } else {
                // Recupera os registros do banco de dados
                $fetchAll = $this->getTableGateway()->selectWith($select);

                // Verifica se foi localizado algum registro
                if ( !is_null($fetchAll) && count($fetchAll) > 0 ) {
                    // Passa o $fetch para array para poder incluir campos extras
                    $fetchAll = $fetchAll->toArray();

                    // Verifica se deve adicionar campos extras
                    $fetchAll = $this->getFetchAllExtraFields($fetchAll, $extraFields);
                } else {
                    $fetchAll = null;
                }

                // Grava a consulta no cache
                if ($this->getUseCache()) $this->getCache()->setItem($md5, $fetchAll);
            }

            // Some garbage collection
            unset($select);

            // retorna o resultado da consulta
            return $fetchAll;
        }
    }

    /**
     * Recupera um registro
     *
     * @param mixed $where Condições para localizar o registro
     * @param array|string $order OPTIONAL Ordem a ser considerada
     * @return array|null array com os dados do registro ou null se não localizar
     * @throws \Exception
     */
    public function fetchRow($where, $order = null)
    {
        // Define se é a chave da tabela
        if (is_numeric($where) || is_string($where)) {

            // Verifica se há chave definida
            if (empty($this->key)) {
                throw new \Exception('Chave não definida em ' . get_class($this) . '::fetchRow()');
            }

            // Verifica se é uma chave multipla ou com cast
           if (is_array($this->key)) {

               // Não é possível acessar um registro com chave multipla usando apenas uma delas
               if (count($this->key) != 1) {
                   throw new \Exception('Não é possível acessar chaves múltiplas informando apenas uma em ' . get_class($this) . '::fetchRow()');
               }

               $where = array(key($this->getKey())=>$where);
            } else {

                $where = array($this->key=>$where);
            }
        }

        // Recupera o registro
        $row = $this->fetchAll($where, $order, 1);

        // Verifica se está usando o paginator e
        // garante que sempre vai retornar um array
        // sem desligar o paginator
        if ($row instanceof LaminasPaginator) {
            if ($row->count() > 0) {
                return (array) $row->getCurrentItems()->current();
            }
            return null;
        }

        // Retorna o registro encontrado
        return (!empty($row)) ? $row[0] : null;
    }

    /**
     * Retorna um array associado com os registros com a chave do banco de dados sendo o chave do array
     *
     * @param mixed     $where  OPTIONAL Condições SQL
     * @param array|int $order  OPTIONAL Ordem dos registros
     * @param int       $count  OPTIONAL Limite de registros
     * @param int       $offset OPTIONAL Offset
     *
     * @return array
     */
    public function fetchAssoc($where = null, $order = null, $count = null, $offset = null)
    {
        // Recupera todos os registros
        $fetchAll = $this->fetchAll($where, $order, $count, $offset);

        // Veririca se foi localizado algum registro
        if (empty($fetchAll)) {
            return null;
        }

        // Associa pela chave da tabela
        $fetchAssoc = array();
        $key = $this->getKey();

        // Verifica se a chave é um array
        if (is_array($key)) {

            // Recupera a primeira chave do array
            $keys = array_keys($key);
            $key  = $keys[0];
        }

        foreach ($fetchAll as $row) {
            $fetchAssoc[$row[$key]] = $row;
        }

        // Some garbage collection
        unset($fetchAll);

        // Retorna o array reordenado
        return $fetchAssoc;
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
        $select->reset('columns')->columns(['total' => new Sql\Expression('count(*)')]);

        $fetchRow = $this->getTableGateway()->selectWith($select);

        if ( !is_null($fetchRow) && count($fetchRow) > 0 ) {
            $fetchRow = $fetchRow->toArray();
            return $fetchRow[0];
        } else {
            return 0;
        }
    }

    /**
     * Inclui campos extras ao retorno do fetchAll quando não estiver usando a paginação
     *
     * @param array $fetchAll
     * @param array $where
     *
     * @return array
     */
    protected function getFetchAllExtraFields($fetchAll, $where = null)
    {
        return $fetchAll;
    }

    /**
     * Retorna o HTML de um <select> apra usar em formulários
     *
     * @param string $nome        Nome/ID a ser usado no <select>
     * @param string $selecionado Valor pré selecionado
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
        $fetchAll = $this->fetchAll($where);

        // Verifica o select_option_data
        if (isset($this->htmlSelectOptionData) && is_string($this->htmlSelectOptionData)) {
            $this->htmlSelectOptionData = array(
                $this->htmlSelectOptionData
            );
        }

        // Verifica se deve manter um em branco
        $showEmpty = (isset($opts['show-empty']) && $opts['show-empty'] === true);

        // Define ao placeholder aser usado
        $placeholder = (isset($opts['placeholder'])) ? $opts['placeholder'] : '';

        // Define a chave a ser usada
        if (isset($opts['key']) && !empty($opts['key']) && is_string($opts['key'])) {
            $key = $opts['key'];
        } else {
            $key = $this->getKey();
        }

        // Monta as opções
        $options = '';
        if (! empty($fetchAll)) {
            foreach ($fetchAll as $row) {
                preg_match_all('/\{([a-z_]*)\}/', $this->htmlSelectOption, $matches);

                // Troca pelos valores
                foreach ($matches[1] as $i => $m) {
                    $matches[1][$i] = $row[$m];
                }

                // Define o option
                $option = str_replace($matches[0], $matches[1], $this->htmlSelectOption);

                // Verifica se deve adicionar campos ao data
                $data = '';
                if (isset($this->htmlSelectOptionData)) {
                    $data = '';
                    foreach ($this->htmlSelectOptionData as $name => $field) {
                        if (is_numeric($name)) {
                            $name = $field;
                        }
                        $data .= " data-$name=\"{$row[$field]}\"";
                    }
                }
                $options .= "<option value=\"{$row[$key]}\" $data>$option</option>";
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
     * @return \Laminas\Cache\Storage\Adapter\Filesystem
     */
    public function getCache()
    {
        if (! isset($this->_cache)) {
            $cache = new Cache();
            $this->_cache = $cache->getFrontend(get_class($this));
        }
        return $this->_cache;
    }

    /**
     * Define se deve usar o cache
     *
     * @param boolean $useCache
     * @return $this
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
     * @return Paginator
     */
    public function getPaginator()
    {
        if (! isset($this->_paginator)) {
            $this->_paginator = new Paginator();
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
     * @return string
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     *
     * @return string|array
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     *
     * @return string|array
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     *
     * @return string
     */
    public function getHtmlSelectOption()
    {
        return $this->htmlSelectOption;
    }

    /**
     *
     * @return array|string
     */
    public function getHtmlSelectOptionData()
    {
        return $this->htmlSelectOptionData;
    }

    /**
     *
     * @param string $order
     *
     * @return Base
     */
    public function setOrder($order)
    {
        $this->order = $order;
        return $this;
    }


    /**
     *
     * @param string $htmlSelectOption
     *
     * @return Base
     */
    public function setHtmlSelectOption($htmlSelectOption)
    {
        $this->htmlSelectOption = $htmlSelectOption;
        return $this;
    }

    /**
     *
     * @param array|string $htmlSelectOptionData
     *
     * @return Base
     */
    public function setHtmlSelectOptionData($htmlSelectOptionData)
    {
        $this->htmlSelectOptionData = $htmlSelectOptionData;
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
     * @return Base
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
     * @return Base
     */
    public function setShowDeleted($showDeleted)
    {
        $this->showDeleted = $showDeleted;

        // Mantem a cadeia
        return $this;
    }

    /**
     * @return AdapterInterface
     */
    public function getAdapter()
    {
        if (null === $this->_dbAdapter) {
            $this->_dbAdapter = GlobalAdapterFeature::getStaticAdapter();
        }

        return $this->_dbAdapter;
    }

    /**
     * @param AdapterInterface $dbAdapter
     *
     * @return Base
     */
    public function setAdapter(AdapterInterface $dbAdapter)
    {
        $this->_dbAdapter = $dbAdapter;

        return $this;
    }
}
