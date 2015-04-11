<?php

use Phinx\Migration\AbstractMigration;

class ModifyUserForeignKeyTokensTable extends AbstractMigration
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
ALTER TABLE tokens DROP FOREIGN KEY fk_tokens_user_id;

ALTER TABLE `tokens`
ADD CONSTRAINT `fk_tokens_user_id`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
        ");
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->execute("
ALTER TABLE tokens DROP FOREIGN KEY fk_tokens_user_id;

ALTER TABLE `tokens`
ADD CONSTRAINT `fk_tokens_user_id`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
        ");
    }
}