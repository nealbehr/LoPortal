<?php

use Phinx\Migration\AbstractMigration;

class AlterUsersTableAddFirstTime extends AbstractMigration
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
ALTER TABLE users ADD first_time enum('0','1') COLLATE 'utf8_unicode_ci' NOT NULL DEFAULT '0' AFTER deleted;
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
