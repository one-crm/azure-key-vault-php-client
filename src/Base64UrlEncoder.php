<?php

namespace OneCRM\KeyVault;

/* Taken from https://gist.github.com/muffycompo/a378dcfa73c3cf354eb8 and
    https://base64.guru/developers/php/examples/base64url
*/
class Base64UrlEncoder
{
    public static function encode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    public static function decode($data)
    {
        return base64_decode(strtr($data, '-_', '+/'), true);
    }
}
