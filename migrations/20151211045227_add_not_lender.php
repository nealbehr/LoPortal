<?php

use Phinx\Migration\AbstractMigration;

class AddNotLender extends AbstractMigration
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
            "INSERT INTO `lender` (`name`, `picture`, `created_at`, `updated_at`) "
            ."VALUES ('Not Lender', NULL, NULL, NULL);"
        );
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}