<?php

use Phinx\Migration\AbstractMigration;

class RenameTableRealtor extends AbstractMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-change-method
     *
     * Uncomment this method if you would like to use it.
     *
    public function change()
    {
    }
     */

    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->execute('ALTER TABLE realtor RENAME TO queue_realtor;');
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
