<?php

use Phinx\Migration\AbstractMigration;

class AddFirstNameLastNameRealtorTable extends AbstractMigration
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
        $this->execute('
ALTER TABLE realtor ADD COLUMN first_name VARCHAR(255) NOT NULL;
ALTER TABLE realtor ADD COLUMN last_name VARCHAR(255) NOT NULL;
ALTER TABLE realtor DROP COLUMN full_name;
        ');
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->execute('
ALTER TABLE realtor DROP COLUMN first_name;
ALTER TABLE realtor DROP COLUMN last_name;
ALTER TABLE realtor ADD COLUMN full_name VARCHAR(255);
        ');
    }
}