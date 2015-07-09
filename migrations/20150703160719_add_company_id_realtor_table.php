<?php

use Phinx\Migration\AbstractMigration;

class AddCompanyIdRealtorTable extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
    public function change()
    {
    }
     */

    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->execute("UPDATE realtor SET realty_company_id='36';");
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
