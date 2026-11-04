<?php

namespace Tests\Support;

final class PackageDatabase
{
    public static function prepare(): void
    {
        (require dirname(__DIR__, 2).'/tests/database/migrations/0001_create_admins_table.php')->up();
    }
}
