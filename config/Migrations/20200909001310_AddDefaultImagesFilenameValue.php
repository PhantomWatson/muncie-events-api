<?php
// @codingStandardsIgnoreFile
use Migrations\AbstractMigration;

class AddDefaultImagesFilenameValue extends AbstractMigration
{
    /**
     * Change Method.
     *
     * @return void
     */
    public function change()
    {
        $this
            ->table('images')
            ->changeColumn('filename', 'string', ['default' => ''])
            ->update();
    }
}
