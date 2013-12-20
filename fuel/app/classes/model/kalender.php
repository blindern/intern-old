<?php

namespace Model;

class Kalender extends \Model_Crud {
	protected static $_table_name = 'kalender';
	protected static $_properties = array(
		'id',
		'title',
		'titlehtml',
		'start',
		'end',
		'allday',
		'info',
		'by',
		'byhtml',
		'place');
	protected static $_rules = array(
		'title' => 'required',
		'start' => 'required',
		'end'   => 'required');
}