<?php

/**
 * There is a bug when retrieve cache for adapter based on filesystem.
 * So, this class is used to override the _getCacheInternalId method
 *
 * https://github.com/zendframework/zend-paginator/issues/1
 * https://github.com/zendframework/zend-paginator/issues/41
 */

namespace Realejo\Utils;

class Paginator extends \Zend\Paginator\Paginator
{
    protected function _getCacheInternalId()
    {
        return md5(serialize([
            spl_object_hash($this->getAdapter()),
            $this->getItemCountPerPage()
        ]));
    }
}