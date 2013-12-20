<?php

namespace Fuel\Migrations;

class Kalender
{

    function up()
    {
        \DBUtil::create_table('kalender', array(
            'id'     => array('type' => 'int', 'constraint' => 11, 'auto_increment' => true),
            'title'  => array('type' => 'varchar', 'constraint' => 150),
            'titlehtml' => array('type' => 'varchar', 'constraint' => 255),
            'start'  => array('type' => 'timestamp'),
            'end'    => array('type' => 'timestamp'),
            'allday' => array('type' => 'boolean'),
            'info'   => array('type' => 'text'),
            'by'     => array('type' => 'varchar', 'constraint' => 100),
            'byhtml' => array('type' => 'varchar', 'constraint' => 255),
            'place'  => array('type' => 'varchar', 'constraint' => 100)
        ), array('id'));
    }

    function down()
    {
       \DBUtil::drop_table('kalender');
    }
}