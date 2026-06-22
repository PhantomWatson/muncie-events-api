<?php
declare(strict_types=1);

namespace App\Alert;

class NewEventAlert
{
    public static function send(string $title): void
    {
        $alert = new Alert();
        $alert->addLine("New event added: *$title*. <https://muncieevents.com/admin/moderate|Go to moderation page>");
        $alert->send(Alert::TYPE_EVENTS);
    }
}
