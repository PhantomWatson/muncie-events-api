<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class ReplaceResetPasswordHashWithExpires extends BaseMigration
{
    public function up(): void
    {
        $this->table('users')
            ->addColumn('reset_password_expires', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'after' => 'token',
            ])
            ->removeColumn('reset_password_hash')
            ->update();
    }

    public function down(): void
    {
        $this->table('users')
            ->addColumn('reset_password_hash', 'string', [
                'default' => null,
                'limit' => 32,
                'null' => true,
                'after' => 'token',
            ])
            ->removeColumn('reset_password_expires')
            ->update();
    }
}
