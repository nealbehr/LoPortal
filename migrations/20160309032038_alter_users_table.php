<?php

use Phinx\Migration\AbstractMigration;

class AlterUsersTable extends AbstractMigration
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
ALTER TABLE `users` CHANGE `email` `email` varchar(255) COLLATE 'utf8_general_ci' NOT NULL AFTER `last_name`;
ALTER TABLE `users` ADD UNIQUE `base_id` (`base_id`);
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