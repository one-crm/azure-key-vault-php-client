<?php

namespace OneCRM\KeyVault\Authentication;

interface AuthenticatorInterface
{
    public function getAuthenticationPayload();
}
