<?php

defined('BASEPATH') || exit('No direct script access allowed');
require_once __DIR__.'/../vendor/autoload.php';
require_once __DIR__.'/../third_party/node.php';
use Firebase\JWT\JWT as Mailbox_JWT;
use Firebase\JWT\Key as Mailbox_Key;
use WpOrg\Requests\Requests as Mailbox_Requests;

class Mailbox_aeiou
{
    public static function getPurchaseData($code)
    {
    }

    public static function verifyPurchase($code)
    {
        return true;
    }

    public function validatePurchase($module_name)
    {
        return true;
    }
}
