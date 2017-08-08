<?php

namespace FUB\UserBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class FUBUserBundle extends Bundle
{
    public function getParent()
    {
        return 'FOSUserBundle';
    }
}
