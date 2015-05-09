<?php

use Phinx\Migration\AbstractMigration;

class ModifyFirstNameLastNameFieldsRealtorTable extends AbstractMigration
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
ALTER TABLE realtor modify column first_name varchar(255) default NULL;
ALTER TABLE realtor modify column last_name varchar(255) default NULL;
EOL
);
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->execute(<<<EOL
ALTER TABLE realtor modify column first_name varchar(255) NOT NULL;
ALTER TABLE realtor modify column last_name varchar(255) NOT NULL;
EOL
        );
    }
}