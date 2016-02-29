<?php

use Phinx\Migration\AbstractMigration;

class DeleteEmptyRealtors extends AbstractMigration
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
UPDATE queue AS q INNER JOIN realtor AS r ON q.realtor_id = r.id SET q.realtor_id = NULL, q.omit_realtor_info = '1' WHERE r.first_name IS NULL AND r.last_name IS NULL;
DELETE FROM realtor WHERE first_name IS NULL AND last_name IS NULL;
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