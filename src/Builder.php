<?php
/**
 * 557 easyswoole.
 * @author WiconWang <WiconWang@gmail.com>
 * @copyright  2019/8/18 下午12:33
 */

namespace ESGeneration;

class Builder
{

    function __construct()
    {
        defined('ES_ROOT') or define('ES_ROOT', realpath(getcwd()));
        defined('ESGENERATION_ROOT') or define('ESGENERATION_ROOT',  dirname(__FILE__) );
        defined('EASYSWOOLE_ROOT') or define('EASYSWOOLE_ROOT',  realpath(getcwd()));
    }


}