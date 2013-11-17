<?php
/**
 * TableGateway
 *
 * @author     Realejo
 * @version    $Id: $
 * @copyright  Copyright (c) 2013 Realejo Design Ltda. (http://www.realejo.com.br)
 */
namespace Realejo\Db;

use \Zend\Db\TableGateway\Feature\GlobalAdapterFeature;
use \Zend\Db\Adapter\AdapterInterface;
use \Zend\Db\TableGateway\TableGateway;
use \Zend\Db\Sql\Sql;

class TableAdapter
{
    /**
     * @var Zend\Db\TableGateway\TableGateway
     */
    protected $tableGateway;

    /**
     * @var string
     */
    protected $key = null;

    /**
     * @var string
     */
    protected $table = null;

    /**
     * @var array|null
     */
    protected $where = null;

    /**
     * @var string|array
     */
    protected $order = null;

    public function __construct($table, $key, $dbAdapter = null)
    {
        if (empty($table) || !is_string($table)) {
            throw \Exception('Nome da tabela inválido');
        }

        if (empty($key) || !is_string($key)) {
            throw \Exception('Nome da chave inválido');
        }

        // Define o adapter padrão
        if (empty($dbAdapter)) {
            $dbAdapter = GlobalAdapterFeature::getStaticAdapter();
        }

        // Verifica se tem adapter válido
        if ( !($dbAdapter instanceof AdapterInterface)) {
            throw new \Exception("Adapter dever ser uma instancia de AdapterInterface");

        }
        $this->tableGateway = new TableGateway($table, $dbAdapter);

        // Define a chave e o nome da tabela
        $this->key   = $key;
        $this->table = $table;
    }

    /**
     * Retorna a ordem padrão a ser usada
     *
     * @return string|array
     */
    public function getOrder()
    {
        return $this->order;
    }

	/**
	 * Define a ordem padrão a ser usada
	 *
     * @param string|array $order
     *
     * @return TableAdapter
     */
    public function setOrder($order)
    {
        $this->order = $order;

        return $this;
    }

	/*
     * Return the where clause
     *
     * @param string|array $where  OPTIONAL An SQL WHERE clause.
     *
     * @return array|null
     */
    public function getWhere($where = null)
    {
        // Sets where is array
        $this->where = array();

        // Checks $where is not null
        if (is_null($where)) {
            $this->where[] = "{$this->tableGateway->getTable()}.deleted=0";

        } else {

            // Checks $where is deleted
            if (!isset($where['deleted'])) {
                $where['deleted'] = 0;
            }

            // Checks $where is not array
            if (!is_array($where)) $where = array($where);
            foreach ($where as $id=>$w) {

                // Checks $where is not string
                if (is_string($w)) {
                    $this->where[] = $w;

                // Checks is deleted
                } elseif ($id === 'deleted' && $w === false) {
                    $this->where[] = "{$this->tableGateway->getTable()}.deleted=0";

                } elseif ($w === 'deleted' || ($id === 'deleted' && $w === true)) {
                    $this->where[] = "{$this->tableGateway->getTable()}.deleted=1";

                // Checks ativos
                } elseif ($w === 'ativo' || ($id === 'ativo' && $w === true)) {
                    $this->where[] = "{$this->tableGateway->getTable()}.ativo=1";

                } elseif ($id === 'ativo' && $w === false) {
                    $this->where[] = "{$this->tableGateway->getTable()}.ativo=0";

                // Checks $id is not numeric and $w is numeric
                } elseif (!is_numeric($id) && is_numeric($w)) {
                    $this->where[] = "{$this->tableGateway->getTable()}.$id=$w";

                // Checks $id is not numeric and $w is string
                } elseif (!is_numeric($id) && is_string($id)) {
                    $this->where[] = "{$this->tableGateway->getTable()}.$id='$w'";

                // Return $id is not numeric and $w is string
                } else {
                    throw new \Exception('Condição inválida em TableAdapter::getWhere()');
                }
            }
        } // End $where

        return $this->where;
    }

    public function getSelect($where = null, $order = null, $count = null, $offset = null)
    {
        /**
         * @var \Zend\Db\Sql\Select
         */
        $select = $this->tableGateway->getSql()->select();

        // Define a ordem
        if (is_null($order)) $order = $this->getOrder();
        $select->order($order);

        // Verifica se há paginação
        if (!is_null($count)) $select->limit($count);

        // Verifica se há paginação
        if (!is_null($offset)) $select->offset($offset);

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
     * @param mixed     $where   Condições SQL
     * @param array|int $order   Ordem dos registros
     * @param int       $count   Limite de registros
     * @param int       $offset  Offset
     * @return array
     */
    public function fetchAll($where = null, $order = null, $count = null, $offset = null)
    {
        /**
         * @var \Zend\Db\Sql\Select
         */
        $select = $this->getSelect($where, $order, $count, $offset);

        // build result set
        $resultSet = $this->tableGateway->selectWith($select);

        // Retorna os registros
        return (count($resultSet) > 0) ? $resultSet->toArray() : null;
    }

    /**
     * Recupera um registro
     *
     * @param mixed $where condições para localizar o usuário
     *
     * @return array|null Array com os dados do usuário ou null se não localizar
     */
    public function fetchRow($where, $order = null)
    {
        // Define o código do usuário
        if (is_numeric($where)) $where = array($this->key=>$where);

        // Recupera o usuário
        $row = $this->fetchAll($where, $order, 1, null);

        // Retorna o usuário
        return (!is_null($row) && count($row)>0)? $row[0] : null;
    }


    /**
     * Retorna um array associado com os usuários com a chave sendo o código deles
     *
     * @param mixed     $where  Condições SQL
     * @param array|int $order  Ordem dos usuários
     * @param int       $count
     * @param int       $offset
     * @param boolean   $cache
     * @return array
     */
    public function fetchAssoc($where = null, $order = null, $count = null, $offset = null, $cache = true)
    {
        $usuario = $this->getUsuario($where, $order, $count, $offset, $cache);
        $return = array();
        foreach ($usuario as $u) {
            $return[$u[$this->key]] = $u;
        }

        return $return;
    }

    public function save($dados)
    {

        if (!isset($dados[$this->key])) {

            return $this->tableGateway->insert($dados);
        } else {
            // Caso não seja, envia um Exception
            if (!is_numeric($dados[$this->key])) {
                throw new \Exception("Inválido o Código '{$dados[$this->key]}' em '{$this->table}'::save()");
            }

            if ($this->fetchRow($dados[$this->key])) {
                $this->tableGateway->update($dados, array($this->key => $dados[$this->key]));

                return true;
            } else {
                throw new \Exception("{$this->table} id does not exist");
            }
        }
    }

    public function delete($id)
    {
        // Caso não seja, envia um Exception
        if (!is_numeric($id)) {
            throw new \Exception("Inválido o Código $id em '{$this->table}'::delete()");
        }

        $this->tableGateway->update(array('deleted'=>1), array($this->key => $id));
    }

}