<?php

/**
 * There is a bug when retrieve cache for adapter based on filesystem.
 * So, this class is used to override the _getCacheInternalId method
 *
 * https://github.com/zendframework/zend-paginator/issues/1
 * https://github.com/zendframework/zend-paginator/issues/41
 */

namespace Realejo\Utils;

use Zend\Paginator\Adapter\DbSelect;

class Paginator extends \Zend\Paginator\Paginator
{
    protected function _getCacheInternalId()
    {
        $adapter = $this->getAdapter();

        if ($adapter instanceof DbSelect) {
            $reflection = new \ReflectionObject($adapter);
            $property = $reflection->getProperty('select');
            $property->setAccessible(true);
            $select = $property->getValue($adapter);
            return md5(
                $reflection->getName()
                . hash('sha512', $select->getSQLString())
                . $this->getItemCountPerPage()
            );
        }

        return parent::_getCacheInternalId();
    }
}