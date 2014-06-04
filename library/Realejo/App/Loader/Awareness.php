<?php
/**
 * Gerenciador de cache utilizado pelo App_Model
 *
 * Ele cria automaticamente a pasta de cache, dentro de data/cache, baseado no nome da classe
 *
 * @author     Realejo
 * @version    $Id: Awareness.php 54 2014-03-21 17:16:12Z rodrigo $
 * @copyright  Copyright (c) 2012 Realejo Design Ltda. (http://www.realejo.com.br)
 */
namespace Realejo\App\Loader;

abstract class Awareness
{

    /**
     *
     * @var App_Loader
     */
    private $_loader;

    /**
     * Retorna o App_Loader a ser usado
     *
     * @return App_Loader
     */
    public function getLoader()
    {
        if (! isset($this->_loader)) {
            $this->setLoader(new App_Loader());
        }

        return $this->_loader;
    }

    /**
     * Grava o App_Loader que deve ser usado
     * Ele é usado com DI durante a criação do model no App_Loader
     *
     * @param App_Loader $loader
     */
    public function setLoader($loader)
    {
        $this->_loader = $loader;
    }
}
