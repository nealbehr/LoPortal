<?php

use Phinx\Migration\AbstractMigration;

class CreateSyncLog extends AbstractMigration
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
CREATE TABLE sync_log (
    `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
    `full_log` varchar(255) DEFAULT NULL,
    `start_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
    `end_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
    PRIMARY KEY(id)
    ) ENGINE = InnoDB DEFAULT CHARSET=utf8;
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