<?php

use Phinx\Migration\AbstractMigration;

class AddColumsTableUsers extends AbstractMigration
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
ALTER TABLE users ADD COLUMN title VARCHAR(100) DEFAULT NULL,
                            ADD COLUMN account_name VARCHAR(255) DEFAULT NULL,
                            ADD COLUMN street VARCHAR(255) DEFAULT NULL,
                            ADD COLUMN city VARCHAR(255) DEFAULT NULL,
                            ADD COLUMN province VARCHAR(2) DEFAULT NULL,
                            ADD COLUMN zip_code SMALLINT(6) DEFAULT NULL,
                            ADD COLUMN phone VARCHAR(100) DEFAULT NULL,
                            ADD COLUMN mobile VARCHAR(100) DEFAULT NULL,
                            ADD COLUMN nmls SMALLINT(6) DEFAULT NULL,
                            ADD COLUMN pmp VARCHAR(10) DEFAULT NULL,
                            ADD COLUMN territory VARCHAR(20) DEFAULT NULL,
                            ADD COLUMN sales_director VARCHAR(255)  DEFAULT NULL;

EOL
    );
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->execute(<<<EOL
ALTER TABLE users DROP COLUMN title,
                             DROP COLUMN account_name,
                             DROP COLUMN street,
                             DROP COLUMN city,
                             DROP COLUMN province,
                             DROP COLUMN zip_code,
                             DROP COLUMN phone,
                             DROP COLUMN mobile,
                             DROP COLUMN nmls,
                             DROP COLUMN pmp,
                             DROP COLUMN territory,
                             DROP COLUMN sales_director;

EOL
        );
    }
}