<?php

use Phinx\Migration\AbstractMigration;

class AddSalesManagerEmailFieldUsersTable extends AbstractMigration
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
ALTER TABLE users ADD COLUMN sales_director_email VARCHAR(255) DEFAULT NULL;
EOL
        );
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->execute(<<<EOL
ALTER TABLE users DROP COLUMN sales_director_email;
EOL
        );
    }
}