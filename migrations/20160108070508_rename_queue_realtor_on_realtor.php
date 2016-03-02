<?php

use Phinx\Migration\AbstractMigration;

class RenameQueueRealtorOnRealtor extends AbstractMigration
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
        $this->execute(<<<EOL
DROP TABLE IF EXISTS realtor;
RENAME TABLE queue_realtor TO realtor;
ALTER TABLE realtor ADD deleted enum('0','1') COLLATE 'utf8_unicode_ci' NOT NULL DEFAULT '0' AFTER id;
EOL
        );
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}