<?php

namespace Aura\Http;


class MockOAuthSignature extends OAuthSignature
{
    public function sign($sbs)
    {
        return $sbs;
    }
}