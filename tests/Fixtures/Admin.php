<?php

namespace Tests\Fixtures;

use Illuminate\Foundation\Auth\User as Authenticatable;

class Admin extends Authenticatable
{
    protected $table = 'sso_admins';

    protected $guarded = [];
}
