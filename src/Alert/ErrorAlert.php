<?php
declare(strict_types=1);

namespace App\Alert;

class ErrorAlert
{
    public static function send(string $message): void
    {
        $alert = new Alert();
        $alert->addLine($message);
        $alert->addLine(print_r(debug_backtrace(limit: 1), true));
        $alert->send(Alert::TYPE_ERRORS);
    }
}
