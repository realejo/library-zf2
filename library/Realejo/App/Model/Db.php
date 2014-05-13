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

    /**
     * Grava um novo registro
     *
     * @param array $dados
     *            a serem cadastrados
     *
     * @return int boolean
     */
    public function create($dados)
    {
        // Remove os campos vazios
        foreach ($dados as $c => $v) {
            $dados[$c] = trim($v);
            if ($dados[$c] === '')
                $dados[$c] = null;
        }

        // Cadastra a area
        $cd = $this->getTableGateway()->insert($dados);

        // Limpa o cache
        if ($this->getUseCache()) {
            $this->getCache()->clean();
        }

        // Retorna o código do registro recem criado
        return $cd;
    }

    /**
     * Atualização de Area
     *
     * @param array $dados
     *            Dados a serem atualizados
     * @param int $cda
     *            Código da area a ser atualizada
     *
     * @return boolean
     */
    public function update($dados, $cd)
    {
        // Verifica se o código é válido
        if (! is_numeric($cd)) {
            throw new \Exception("O código <b>'$cd'</b> inválido em " . get_class($this) . "::update()");
        }

        // Recupera os dados existentes
        $row = $this->fetchRow($cd);

        // Verifica se existe o registro
        if (empty($row)) {
            return false;
        }

        // Remove os campos vazios
        foreach ($dados as $c => $v) {
            $dados[$c] = trim($v);
            if ($dados[$c] === '')
                $dados[$c] = null;
        }

        // Verifica se há oq atualizar
        $diff = array_diff_assoc($dados, $row);

        // Verifica se há algo para atualizar
        if (empty($diff)) {
            return false;
        }

        // Salva os dados alterados
        $return = $this->getTableGateway()->update($diff, $cd);

        // Limpa o cache
        if ($this->getUseCache()) {
            $this->getCache()->clean();
        }

        // Retorna que o registro foi alterado
        return $return;
    }

    /**
     * Excluí um registro
     *
     * @param int $cda
     *            Código da registro a ser excluído
     *
     * @return bool Informa se teve o regsitro foi removido
     */
    public function delete($key)
    {
        if (! is_numeric($key) || empty($key)) {
            throw new \Exception("O código <b>'$key'</b> inválido em " . get_class($this) . "::delete()");
        }

        // Verifica se deve marcar como removido ou remover o registro
        if ($this->useDeleted === true) {
            $return = $this->getTableGateway()->update(array('deleted'=>1), array($this->key => $key));
        } else {
            $return = $this->getTableGateway()->delete(array($this->key => $key));
        }

        // Limpa o cache
        if ($this->getUseCache()) {
            $this->getCache()->clean();
        }

        // Retorna se o registro foi excluído
        return $return;
    }

    public function save($dados)
    {

        if (!isset($dados[$this->key])) {

            return $this->insert($dados);

        } else {
            // Caso não seja, envia um Exception
            if (!is_numeric($dados[$this->key])) {
                throw new \Exception("Inválido o Código '{$dados[$this->key]}' em '{$this->table}'::save()");
            }

            if ($this->fetchRow($dados[$this->key])) {
                return $this->update($dados, array($this->key => $dados[$this->key]));

            } else {
                throw new \Exception("{$this->key} key does not exist");
            }
        }
    }

}