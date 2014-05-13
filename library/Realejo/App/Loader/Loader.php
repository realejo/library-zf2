<?php
/**
 * Gerenciador de tabelas e model carregados para evitar que sejam carregados na memória mais de uma vez
 *
 * @author     Realejo
 * @copyright  Copyright (c) 2014 Realejo Design Ltda. (http://www.realejo.com.br)
 */
namespace Realejo\App\Loader;

class Loader
{
    /**
     * @var array
     */
    private $_classes;

    /**
     * @var array
     */
    private $_tables;

    /**
     * @param string $model
     *
     * @return $model
     */
    public function getModel($model)
    {
        // Verifica se o model já foi previamente carregado
        if (!isset($this->_loaded[$model])) {
            $this->_loaded[$model] = new $model();

            // Verifica se existe loader aplicado a classe
            if (method_exists( $this->_loaded[$model] , 'setLoader' )) {
                $this->_loaded[$model]->setLoader($this);
            }
        }

        // Retorna o model
        return $this->_loaded[$model];
    }

    /**
     * @param string $table
     *
     * @return Zend_Db_Table
     */
    public function getTable($table)
    {
        // Verifica se existe uma tabela definida
        if (empty($table)) {
            throw new Exception("Tabela não definida em App_Loader::getTable()");
        }

        // Verifica se a tabela já foi previamente carregada
        if (!isset($this->_tables[$table])) {
            $this->_tables[$table] = new Zend_Db_Table($table);
        }

        // Retorna a tabela
        return $this->_tables[$table];
    }
}