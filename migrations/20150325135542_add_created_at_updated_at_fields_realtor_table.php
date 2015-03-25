<?php

use Phinx\Migration\AbstractMigration;

class AddCreatedAtUpdatedAtFieldsRealtorTable extends AbstractMigration
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
ALTER TABLE realtor ADD COLUMN `created_at` datetime default null;
ALTER TABLE realtor ADD COLUMN `updated_at` datetime default null;
EOL
);
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->execute(<<<EOL
ALTER TABLE realtor DROP COLUMN `created_at`;
ALTER TABLE realtor DROP COLUMN `updated_at`;
EOL
        );
    }
}