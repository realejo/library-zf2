<?php
/**
 * Gerenciador de classes carregadas para evitar que sejam carregados na memória mais de uma vez
 *
 * @todo deveria usar usar o ServiceManager?
 *
 * @copyright  Copyright (c) 2014 Realejo Design Ltda. (http://www.realejo.com.br)
 */
namespace Realejo\App\Loader;

class Loader
{
    /**
     * @var array
     */
    private $_models;

    /**
     * Verifica se um model já está carregado
     * Se não não estiver, cria ele
     *
     * @param string $model
     *
     * @return mixed
     */
    public function getModel($model)
    {
        // Verifica se o model já foi previamente carregado
        if (!$this->hasModel($model)) {

            // Cria o model
            $object = new $model();

            // Verifica se existe loader aplicado ao model
            if (method_exists( $object , 'setLoader' )) {
                $object->setLoader($this);
            }

            // Grava na lista de models já carregados
            $this->_models[$model] = $object;
        }

        // Retorna o model
        return $this->_models[$model];
    }

    /**
     * Grava um objeto dentro do loader
     *
     * @param string $model
     * @param mixed $object
     *
     * @return \Realejo\App\Loader\Loader
     */
    public function setModel($model, $object)
    {
        // Verifica se existe loader aplicado ao model
        if (method_exists( $object , 'setLoader' )) {
            $object->setLoader($this);
        }

        // Grava na lista de models já carregados
        $this->_models[$model] = $object;

        // Retorna o loader
        return $this;
    }

    /**
     * Retorna se o model já está carregado
     *
     * @param string $model
     *
     * @return boolean
     */
    public function hasModel($model)
    {
        return isset($this->_models[$model]);
    }
}