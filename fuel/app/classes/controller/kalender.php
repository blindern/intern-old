<?php

use \Model\Kalender;

class Controller_Kalender extends Controller_Template
{
	public function action_index()
	{
		$events = Kalender::get_events();

		$this->template->title = "Kalender";
		$this->template->content = View::forge('kalender/index');
	}
}