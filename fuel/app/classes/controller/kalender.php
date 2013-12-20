<?php

use \Model\Kalender;
use \Eluceo\iCal\Component\Calendar;
use \Eluceo\iCal\Component\Event;

class Controller_Kalender extends Controller_Template
{
	public function action_index()
	{
		$events = Kalender::find_all();
		//var_dump($events);die;

		$this->template->title = "Kalender";
		$this->template->content = View::forge('kalender/index');
	}

	public function action_ical()
	{
		$events = Kalender::find_all(); // TODO: sort
		$cal = new Calendar("blindern-studenterhjem.no");
		$cal->setName("Blindern Studenterhjem");

		foreach ($events as $event) {
			$v = new Event();
			$v->setDtStart(new \DateTime($event->start));
			$v->setDtEnd(new \DateTime($event->end));
			$v->setNoTime($event->allday);
			$v->setSummary($event->title);
			$cal->addEvent($v);
		}

		$response = new Response($cal->render(), 200, array(
			#'Content-Type' => 'text/calendar; charset=utf-8',
			#'Content-Disposition' => 'attachment; filename="cal.ics"'
		));
		return $response;
	}
}