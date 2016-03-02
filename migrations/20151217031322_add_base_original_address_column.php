<?php

use Phinx\Migration\AbstractMigration;

class AddBaseOriginalAddressColumn extends AbstractMigration
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
        $this->execute("ALTER TABLE `address` ADD `base_original_address` varchar(255) COLLATE 'utf8_general_ci' NULL "
            ."AFTER `place_id`;");
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}