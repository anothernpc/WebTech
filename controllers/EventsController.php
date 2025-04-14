<?php

namespace controllers;
use services\EventService;

class EventsController
{
    function viewAction(){
        (new \services\EventService)->viewAction();
    }

}