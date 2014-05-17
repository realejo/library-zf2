<?php
/**
 * Model basico para o App_Model com as funções de create, update e delete
 *
 * @author     Realejo
 * @version    $Id: Base.php 61 2014-04-01 16:26:39Z rodrigo $
 * @copyright  Copyright (c) 2012 Realejo Design Ltda. (http://www.realejo.com.br)
 */
namespace Realejo\App\Model;

class Db extends Base
{

    private $_lastInsertSet;

    private $_lastInsertKey;

    private $_lastUpdateSet;

    private $_lastUpdateDiff;

    private $_lastUpdateKey;

    private $_lastDeleteKey;

    /**
     * Grava um novo registro
     *
     * @param array $dados Dados a serem cadastrados
     *
     * @return int boolean
     */
    public function insert($set)
    {
        // Remove os campos vazios
        foreach ($set as $field => $value) {
            $set[$field] = trim($value);
            if ($set[$field] === '') {
                $set[$field] = null;
            }
        }

        // Grava o ultimo set incluído para referencia
        $this->_lastInsertSet = $set;

        // Grava o set no BD
        $key = $this->getTableGateway()->insert($set);

        // Grava a chave criada para referencia
        $this->_lastInsertKey = $key;

        // Limpa o cache se necessário
        if ($this->getUseCache()) {
            $this->getCache()->clean();
        }

        // Retorna o código do registro recem criado
        return $key;
    }

    /**
     * Altera um registro
     *
     * @param array $set Dados a serem atualizados
     * @param int   $key Chave do registro a ser alterado
     *
     * @return boolean
     */
    public function update($set, $key)
    {
        // Verifica se o código é válido
        if ( !is_numeric($key) ) {
            throw new \Exception("O código <b>'$key'</b> inválido em " . get_class($this) . "::update()");
        }

        // Recupera os dados existentes
        $row = $this->fetchRow($key);

        // Verifica se existe o registro
        if (empty($row)) {
            return false;
        }

        // Remove os campos vazios
        foreach ($set as $field => $value) {
            $set[$field] = trim($value);
            if ($set[$field] === '') {
                $set[$field] = null;
            }
        }

        // Verifica se há o que atualizar
        $diff = array_diff_assoc($set, $row);

        // Grava os dados alterados para referencia
        $this->_lastUpdateSet  = $set;
        $this->_lastUpdateKey  = $key;

        // Grava o que foi alterado
        $this->_lastUpdateDiff = array();
        foreach ($diff as $field=>$value) {
            $this->_lastUpdateDiff[$field] = array($row[$field], $value);
        }

        // Verifica se há algo para atualizar
        if (empty($diff)) {
            return false;
        }

        // Define a chave a ser usada
        $key = array( $this->key => $key );

        // Salva os dados alterados
        $return = $this->getTableGateway()->update($diff, $key);

        // Limpa o cache, se necessário
        if ($this->getUseCache()) {
            $this->getCache()->clean();
        }

        // Retorna que o registro foi alterado
        return $return;
    }

    /**
     * Excluí um registro
     *
     * @param int $cda Código da registro a ser excluído
     *
     * @return bool Informa se teve o regsitro foi removido
     */
    public function delete($key)
    {
        if (! is_numeric($key) || empty($key)) {
            throw new \Exception("O código <b>'$key'</b> inválido em " . get_class($this) . "::delete()");
        }

        // Grava os dados alterados para referencia
        $this->_lastDeleteKey = $key;

        // Define a chave a ser usada
        $key = array( $this->key => $key );

        // Verifica se deve marcar como removido ou remover o registro
        if ($this->useDeleted === true) {
            $return = $this->getTableGateway()->update(array('deleted' => 1), $key);
        } else {
            $return = $this->getTableGateway()->delete($key);
        }

        // Limpa o cache se necessario
        if ($this->getUseCache()) {
            $this->getCache()->clean();
        }

        // Retorna se o registro foi excluído
        return $return;
    }

    public function save($dados)
    {
        if (! isset($dados[$this->key])) {

            return $this->insert($dados);
        } else {
            // Caso não seja, envia um Exception
            if (! is_numeric($dados[$this->key])) {
                throw new \Exception("Inválido o Código '{$dados[$this->key]}' em '{$this->table}'::save()");
            }

            if ($this->fetchRow($dados[$this->key])) {
                return $this->update($dados, array(
                    $this->key => $dados[$this->key]
                ));
            } else {
                throw new \Exception("{$this->key} key does not exist");
            }
        }
    }

    /**
     *
     * @return array
     */
    public function getLastInsertSet()
    {
        return $this->_lastInsertSet;
    }

    /**
     *
     * @return int
     */
    public function getLastInsertKey()
    {
        return $this->_lastInsertKey;
    }

    /**
     *
     * @return array
     */
    public function getLastUpdateSet()
    {
        return $this->_lastUpdateSet;
    }

    /**
     *
     * @return array
     */
    public function getLastUpdateDiff()
    {
        return $this->_lastUpdateDiff;
    }

    /**
     *
     * @return int
     */
    public function getLastUpdateKey()
    {
        return $this->_lastUpdateKey;
    }

    /**
     *
     * @return int
     */
    public function getLastDeleteKey()
    {
        return $this->_lastDeleteKey;
    }
}