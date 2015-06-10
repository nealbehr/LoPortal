<?php

use Phinx\Migration\AbstractMigration;

class RealtorCompany extends AbstractMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-change-method
     *
     * Uncomment this method if you would like to use it.
     */
    public function change()
    {
        // create the table
        $table = $this->table('realtor');
        $table->renameColumn('estate_agency', 'realty_name')
            ->addColumn('realty_logo', 'string', ['limit' => 255, 'after' => 'realty_name'])
            ->update();

        $this->execute('alter table `realtor` change column `realty_name` `realty_name` varchar(50)');
        $this->execute("update realtor set realty_name='John L Scott Mortgage', realty_logo='https://s3.amazonaws.com/1rex/realty.logo/143239065380785.JPEG';");
    }
    
    /**
     * Migrate Up.
     */
    public function up()
    {
    
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}