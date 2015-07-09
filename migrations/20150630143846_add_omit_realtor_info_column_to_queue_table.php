<?php

use Phinx\Migration\AbstractMigration;

class AddOmitRealtorInfoColumnToQueueTable extends AbstractMigration
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
        $this->execute(
            "ALTER TABLE `queue` ADD `omit_realtor_info` enum('0','1') COLLATE 'utf8_unicode_ci' NOT NULL DEFAULT '1' "
            ."AFTER `state`;"
        );
        $this->execute("UPDATE `queue` SET `omit_realtor_info` = '0';");
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
