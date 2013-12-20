<?php

namespace Fuel\Migrations;

class Kalender
{

    function up()
    {
        $query = static::ny();
        $query->values(array(
            'Juleverksted',
            'Pigefaarsamlingen',
            'Peisestua',
            '2013-12-01',
            '2013-12-01',
            true))->execute();

        $query = static::ny();
        $query->values(array(
            'Spillkveld',
            'BSG',
            'Peisestua',
            '2013-12-05',
            '2013-12-05',
            true))->execute();

        $query = static::ny();
        $query->values(array(
            'Julemøte',
            'Festforeningen',
            'Spisesalen/peisestua',
            '2013-12-06',
            '2013-12-06',
            true))->execute();

        $query = static::ny();
        $query->values(array(
            'Nyttårsfeiring på Småbruket',
            'Hyttestyret',
            'Småbruket',
            '2013-12-30',
            '2014-01-02',
            true))->execute();
    }

    static function ny() {
        $query = \DB::insert("kalender");
        $query->columns(array(
            'title',
            'by',
            'place',
            'start',
            'end',
            'allday'));
        return $query;
    }

    function down() {}
}