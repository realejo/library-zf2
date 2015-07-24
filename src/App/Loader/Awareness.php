<?php
/**
 * Realejo Library ZF2
 *
 * Gerenciador de cache utilizado pelo App_Model
 *
 * Ele cria automaticamente a pasta de cache, dentro de data/cache, baseado no nome da classe
 *
 * @link      http://github.com/realejo/libraray-zf2
 * @copyright Copyright (c) 2014 Realejo (http://realejo.com.br)
 * @license   http://unlicense.org
 */
namespace Realejo\App\Loader;

abstract class Awareness
{
    /**
     * @var \Realejo\App\Loader\Loader
     */
    private $_loader;

    /**
     * Retorna o App_Loader a ser usado
     *
     * @return \Realejo\App\Loader\Loader
     */
    public function getLoader()
    {
        if (! isset($this->_loader)) {
            $this->setLoader(new \Realejo\App\Loader\Loader());
        }

        return $this->_loader;
    }

    /**
     * Grava o App_Loader que deve ser usado
     * Ele é usado com DI durante a criação do model no App_Loader
     *
     * @param \Realejo\App\Loader\Loader $loader
     */
    public function setLoader($loader)
    {
        $this->_loader = $loader;
    }
}
