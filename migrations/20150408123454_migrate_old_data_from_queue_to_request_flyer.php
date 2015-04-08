<?php

use Phinx\Migration\AbstractMigration;

class MigrateOldDataFromQueueToRequestFlyer extends AbstractMigration
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
        $this->execute("
insert into request_flyer(queue_id, realtor_id, pdf_link, photo)
select id, realtor_id, pdf_link, photo
from queue
where request_type = 1
        ");
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->execute("truncate table request_flyer;");
    }
}