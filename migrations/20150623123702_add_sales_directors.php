<?php

use Phinx\Migration\AbstractMigration;

class AddSalesDirectors extends AbstractMigration
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
        $data = [[
            'name'  => 'Mike Lyon',
            'email' => 'mike.lyon@1rex.com',
            'phone' => '925-548-5157',
        ], [
            'name'  => 'Paul Careaga',
            'email' => 'paul.careaga@1rex.com',
            'phone' => '253-677-4470',
        ],[
            'name'  => 'Jim McGuire',
            'email' => 'jim.mcguire@1rex.com',
            'phone' => '310-909-6167',
        ]];

        foreach ($data as $val) {
            $this->execute(
                "insert into sales_director(".implode(", ", array_keys($val)).") values ('".implode("', '", $val)."')"
            );
        }
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->dropTable('sales_director');
    }
}
