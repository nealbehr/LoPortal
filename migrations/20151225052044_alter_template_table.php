<?php

use Phinx\Migration\AbstractMigration;

class AlterTemplateTable extends AbstractMigration
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
            "ALTER TABLE `template` ADD `lenders_all` enum('0','1') COLLATE 'utf8_unicode_ci' NOT NULL DEFAULT '1' "
            ."AFTER `format_id`;"
        );
        $this->execute(
            "ALTER TABLE `template` ADD `states_all` enum('0','1') COLLATE 'utf8_unicode_ci' NOT NULL DEFAULT '1' "
            ."AFTER `lenders_all`;"
        );
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}