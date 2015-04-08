<?php

use Phinx\Migration\AbstractMigration;

class RemoveOldFieldsQueueTable extends AbstractMigration
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
ALTER TABLE queue drop FOREIGN KEY fk_queue_realtor_id;
         Alter TABLE queue DROP column realtor_id;
         Alter TABLE queue DROP column pdf_link;
         Alter TABLE queue DROP column photo;
        ");
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->execute("
 ALTER TABLE queue ADD COLUMN realtor_id INT(11) unsigned default null;
 ALTER TABLE queue ADD COLUMN pdf_link text;
 ALTER TABLE queue ADD COLUMN photo text;
update queue set realtor_id = (select id from realtor limit 1) where request_type = 1;
ALTER TABLE queue ADD CONSTRAINT fk_queue_realtor_id FOREIGN KEY (realtor_id) references realtor(id);
        ");
    }
}