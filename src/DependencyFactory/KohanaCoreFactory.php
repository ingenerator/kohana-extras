<?php
/**
 * @author    Craig Gosman <craig@ingenerator.com>
 * @licence   proprietary
 */

namespace Ingenerator\KohanaExtras\DependencyFactory;

class KohanaCoreFactory
{
    /**
     * @return \Log
     */
    public static function getLog()
    {
        return \Kohana::$log;
    }

    /**
     * @return \Request
     */
    public static function getRequest()
    {
        return \Request::initial();
    }

    /**
     * @return \Session
     */
    public static function getSession()
    {
        return \Session::instance();
    }
}
