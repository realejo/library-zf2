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
    private $_classes;

    /**
     * Verifica se uma classe já carregada
     * Se não não estiver carregada, cria ela
     *
     * @param string $class
     *
     * @return mixed
     */
    public function getModel($class)
    {
        // Verifica se o model já foi previamente carregado
        if (!isset($this->_classes[$model])) {
            $this->_classes[$class] = new $class();

            // Verifica se existe loader aplicado a classe
            if (method_exists( $this->_classes[$class] , 'setLoader' )) {
                $this->_classes[$class]->setLoader($this);
            }
        }

        // Retorna o model
        return $this->_classes[$class];
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
        $this->_classes[$class] = new $class();

        // Verifica se existe loader aplicado a classe
        if (method_exists( $this->_classes[$class] , 'setLoader' )) {
            $this->_classes[$class]->setLoader($this);
        }

        // Retorna o loader
        return $this;
    }

    /**
     * Retorna se a classe já está carregada
     *
     * @param string $class
     *
     * @return mixed
     */
    public function hasModel($class)
    {
        return isset($this->_classes[$class]);
    }
}