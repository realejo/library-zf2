<?php
/**
 * Model basico para o App_Model com as funções de create, update e delete
 *
 * @link      http://github.com/realejo/libraray-zf2
 * @copyright Copyright (c) 2014 Realejo (http://realejo.com.br)
 * @license   http://unlicense.org
 */
namespace Realejo\App\Model;

use Realejo\App\Model\Base;

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

        // Verifica se há algo a ser adicionado
        if (empty($set)) {
            return false;
        }

        // Remove os campos vazios
        foreach ($set as $field => $value) {
            if (is_string($value)) {
                $set[$field] = trim($value);
                if ($set[$field] === '') {
                    $set[$field] = null;
                }
            }
        }

        // Grava o ultimo set incluído para referencia
        $this->_lastInsertSet = $set;

        // Grava o set no BD
        $this->getTableGateway()->insert($set);

        // Recupera a chave gerada do registro
        if (is_array($this->key)) {
            $key = array();
            foreach ($this->key as $k) {
                if (isset($set[$k])) {
                    $key[$k] = $set[$k];
                } else {
                    $key = false;
                    break;
                }
            }

        } elseif (isset($set[$this->key])) {
            $key = $set[$this->key];
        }

        if (empty($key)) {
            $key = $this->getTableGateway()->getAdapter()->getDriver()->getLastGeneratedValue();
        }

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
        if ( empty($key) ) {
            throw new \Exception("O código <b>'$key'</b> inválido em " . get_class($this) . "::update()");
        }

        // Verifica se há algo para alterar
        if (empty($set)) {
            return false;
        }

        // Recupera os dados existentes
        $row = $this->fetchRow($key);

        // Verifica se existe o registro
        if (empty($row)) {
            return false;
        }

        // Remove os campos vazios
        foreach ($set as $field => $value) {
            if (is_string($value)) {
                $set[$field] = trim($value);
                if ($set[$field] === '') {
                    $set[$field] = null;
                }
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

        // Salva os dados alterados
        $return = $this->getTableGateway()->update($diff, $this->_getKeyWhere($key));

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

        // Verifica se deve marcar como removido ou remover o registro
        if ($this->useDeleted === true) {
            $return = $this->getTableGateway()->update(array('deleted' => 1), $this->_getKeyWhere($key));
        } else {
            $return = $this->getTableGateway()->delete($this->_getKeyWhere($key));
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
                return $this->update($dados, $dados[$this->key]);
            } else {
                throw new \Exception("{$this->key} key does not exist");
            }
        }
    }


    /**
     * Retorna a chave no formato que ela deve ser usada
     *
     * @param Zend_Db_Expr|string|array $key
     *
     * @return Zend_Db_Expr|string
     */
    private function _getKeyWhere($key)
    {
        if ($key instanceof \Zend\Db\Sql\Expression || $key instanceof \Zend\Db\Sql\Predicate\PredicateInterface) {
            return $key;

        } elseif (is_string($this->getKey()) && is_numeric($key)) {
            return "{$this->getKey()} = $key";

        } elseif (is_string($this->getKey()) && is_string($key)) {
            return "{$this->getKey()} = '$key'";

        } elseif (is_array($this->getKey())) {
            $where    = array();
            $usedKeys = array();

            // Verifica as chaves definidas
            foreach ($this->getKey() as $type=>$definedKey) {

                // Verifica se é uma chave única com cast
                if (count($this->getKey()) === 1 && !is_array($key)) {

                    // Grava a chave como integer
                    if (is_numeric($type) || $type === self::KEY_INTEGER) {
                        $where[] = "$definedKey = $key";

                    // Grava a chave como string
                    } elseif ($type === self::KEY_STRING) {
                        $where[] = "$definedKey = '$key'";
                    }
                    $usedKeys[] = $definedKey;
                }

                // Verifica se a chave definida foi informada
                elseif (is_array($key) && isset($key[$definedKey])) {

                    // Grava a chave como integer
                    if (is_numeric($type) || $type === self::KEY_INTEGER) {
                        $where[] = "$definedKey = {$key[$definedKey]}";

                    // Grava a chave como string
                    } elseif ($type === self::KEY_STRING) {
                        $where[] = "$definedKey = '{$key[$definedKey]}'";
                    }

                    // Marca a chave com usada
                    $usedKeys[] = $definedKey;
                }
            }

            // Verifica se alguma chave foi definida
            if (empty($where)) {
                throw new \Exception('Nenhuma chave múltipla informada em ' . get_class($this) . '::_getWhere()');
            }

            // Verifica se todas as chaves foram usadas
            if ($this->getUseAllKeys() === true && is_array($this->getKey()) && count($usedKeys) !== count($this->getKey())) {
                throw new \Exception('Não é permitido usar chaves parciais ' . get_class($this) . '::_getWhere()');
            }
            return '(' . implode(') AND (', $where). ')';

        } else {
            throw new \Exception('Chave mal definida em ' . get_class($this) . '::_getWhere()');
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

    /**
     * @return boolean
     */
    public function getUseAllKeys ()
    {
        return $this->useAllKeys;
    }

    /**
     * @param boolean $useAllKeys
     *
     * @retrun self
     */
    public function setUseAllKeys ($useAllKeys)
    {
        $this->useAllKeys = $useAllKeys;

        return $this;
    }

}