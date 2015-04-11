<?php

use Phinx\Migration\AbstractMigration;

class ModifyUserForeignKeyRecoveryPasswordTable extends AbstractMigration
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
ALTER TABLE recovery_password DROP FOREIGN KEY recovery_password_ibfk_1;

ALTER TABLE `recovery_password`
ADD CONSTRAINT `recovery_password_ibfk_1`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
        ");
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->execute("
ALTER TABLE recovery_password DROP FOREIGN KEY recovery_password_ibfk_1;

ALTER TABLE `recovery_password`
ADD CONSTRAINT `recovery_password_ibfk_1`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
        ");
    }
}