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
     * Verifica se uma classe já está carregada
     * Se não não estiver, cria ela
     *
     * @param string $class
     *
     * @return mixed
     */
    public function getModel($model)
    {
        // Verifica se o model já foi previamente carregado
        if (!$this->hasModel($model)) {
            $this->_models[$model] = new $model();

            // Verifica se existe loader aplicado a classe
            if (method_exists( $this->_models[$model] , 'setLoader' )) {
                $this->_models[$model]->setLoader($this);
            }
        }

        // Retorna o model
        return $this->_models[$model];
    }

    /**
     * Grava uma classe dentro do loader
     *
     * @param string $class
     * @param mixed $object
     *
     * @return \Realejo\App\Loader\Loader
     */
    public function setModel($class, $object)
    {
       // Verifica se existe loader aplicado a classe
        if (method_exists( $object , 'setLoader' )) {
            $object->setLoader($this);
        }

        $this->_models[$class] = $object;

        // Retorna o loader
        return $this;
    }

    /**
     * Retorna se a classe já está carregada
     *
     * @param string $class
     *
     * @return boolean
     */
    public function hasModel($class)
    {
        return isset($this->_models[$class]);
    }
}