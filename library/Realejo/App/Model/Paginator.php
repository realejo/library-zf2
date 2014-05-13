<?php
/**
 * Gerenciador do paginator utilizado pelo App_Model
 *
 * Ele é usado apenas para guarda a configuração da paginação. O paginator é
 * criado direto na consulta no retorno do fetchAll
 *
 * @author     Realejo
 * @copyright  Copyright (c) 2014 Realejo Design Ltda. (http://www.realejo.com.br)
 */
namespace Realejo\App\Model;

class Paginator
{

    private $_paginator = array(
        'PageRange' => 10,
        'CurrentPageNumber' => 1,
        'ItemCountPerPage' => 10
    );

    public function setPageRange($pageRange)
    {
        $this->_paginator['PageRange'] = $pageRange;

        // Mantem a cadeia
        return $this;
    }

    public function setCurrentPageNumber($currentPageNumber)
    {
        $this->_paginator['CurrentPageNumber'] = $currentPageNumber;

        // Mantem a cadeia
        return $this;
    }

    public function setItemCountPerPage($itemCountPerPage)
    {
        $this->_paginator['ItemCountPerPage'] = $itemCountPerPage;

        // Mantem a cadeia
        return $this;
    }

    public function getPageRange()
    {
        return $this->_paginator['PageRange'];
    }

    public function getCurrentPageNumber()
    {
        return $this->_paginator['CurrentPageNumber'];
    }

    public function getItemCountPerPage()
    {
        return $this->_paginator['ItemCountPerPage'];
    }
}