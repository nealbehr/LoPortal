<?php

use Phinx\Migration\AbstractMigration;

class AddAdminUserUsersTable extends AbstractMigration
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
insert into users(first_name, last_name, email, roles, salt, password, state)
values("AdminFirst", "AdminLast", "admin@1rex.com", 'a:1:{i:0;s:10:"ROLE_ADMIN";}', "8fc0f7bdedc76589af80d302e35ed4ac", "$2y$10$8fc0f7bdedc76589af80duZcob7UzGCsrgaAhLq5q3VsVuugSYOPW", 1);
EOL
        );
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->execute('delete from users where email = "admin@1rex.com"');
    }
}