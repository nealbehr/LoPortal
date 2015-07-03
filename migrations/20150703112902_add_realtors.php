<?php

use Phinx\Migration\AbstractMigration;

class AddRealtors extends AbstractMigration
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
        $this->execute("INSERT INTO `realtor` (`first_name`, `last_name`, `bre_number`, `phone`, `email`, `photo`) "
            ."VALUES ('Frank', 'Cheese', '1934578',	'510.123.2458',	'joanna.umali@1rex.com', "
            ."'https://s3-us-west-1.amazonaws.com/1rex/realtor/14329273211593.JPEG'), ('Bonnie', 'Kyte', '185739', "
            ."'415.234.5962', 'joanna.umali@1rex.com', "
            ."'https://s3-us-west-1.amazonaws.com/1rex/realtor/143267857582613.JPEG'), ('John',	'Smith', '18324838', "
            ."'510.990.2843', 'john.smith@realtor.com',	"
            ."'https://s3-us-west-1.amazonaws.com/1rex/realtor/143265873063895.JPEG');");
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
