<?php

namespace RealejoTest\App\Model;

/**
 * DbTest test case.
 *
 * @link      http://github.com/realejo/libraray-zf2
 * @copyright Copyright (c) 2014 Realejo (http://realejo.com.br)
 * @license   http://unlicense.org
 */
use Realejo\App\Model\Db;

class DbMultipleKeyDeprecatedTest extends DbMultipleKeyTest
{

    protected $keys = array(
        Db::KEY_INTEGER => 'id_int',
        Db::KEY_STRING => 'id_string'
    );

}
