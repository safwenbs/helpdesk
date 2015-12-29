<?php

namespace Core\UsersBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class CoreUsersBundle extends Bundle
{
    public function getParent()
    {
        return 'FOSUserBundle';
    }
}
