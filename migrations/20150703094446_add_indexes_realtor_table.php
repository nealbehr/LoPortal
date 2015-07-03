<?php

use Phinx\Migration\AbstractMigration;

class AddIndexesRealtorTable extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
    public function change()
    {
    }
     */

    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->execute("ALTER TABLE realtor ADD INDEX (first_name), ADD INDEX (last_name);");
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
