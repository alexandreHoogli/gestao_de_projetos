<?php

namespace modules\mailbox\core;

require_once __DIR__.'/../third_party/node.php';
require_once __DIR__.'/../vendor/autoload.php';
use Firebase\JWT\JWT as Mailbox_JWT;
use Firebase\JWT\Key as Mailbox_Key;
use WpOrg\Requests\Requests as Mailbox_Requests;

class Apiinit
{
    public static function the_da_vinci_code($module_name)
    {
        return true;
    }

    
    public static function ease_of_mind($module_name)
    {
    }

    
    public static function activate($module)
    {
    }

    
    public static function getUserIP()
    {
    }


    public static function pre_validate($module_name, $code = '') {
        return ['status' => true];
    }
}
