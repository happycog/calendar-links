<?php

namespace Spatie\CalendarLinks\Generators;

use Spatie\CalendarLinks\Link;
use Spatie\CalendarLinks\Generator;

/**
 * @see https://icalendar.org/RFC-Specifications/iCalendar-RFC-5545/
 */
class Ics implements Generator
{
    public function generate(Link $link): string
    {
        $url = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'BEGIN:VEVENT',
            'UID:'.$this->generateEventUid($link),
            'SUMMARY:'.$link->title,
        ];

        if ($link->allDay) {
            $dateTimeFormat = 'Ymd';
            $url[] = 'DTSTART:'.$link->from->format($dateTimeFormat);
            $url[] = 'DTEND:'.$link->to->format($dateTimeFormat);
        } else {
            $dateTimeFormat = "e:Ymd\THis";
            $url[] = 'DTSTART;TZID='.$link->from->format($dateTimeFormat);
            $url[] = 'DTEND;TZID='.$link->to->format($dateTimeFormat);
        }

        if ($link->description) {
            $url[] = 'DESCRIPTION:'.$this->escapeString($link->description);
        }
        if ($link->address) {
            $url[] = 'LOCATION:'.$this->escapeString($link->address);
        }

        $url[] = 'END:VEVENT';
        $url[] = 'END:VCALENDAR';
        $redirectLink = implode('%0A', $url);

        return 'data:text/calendar;charset=utf8,'.$redirectLink;
    }

    protected function escapeString(string $field): string
    {
        return addcslashes($field, "\n,");
    }

    /**
     * @see https://tools.ietf.org/html/rfc5545#section-3.8.4.7
     */
    private function generateEventUid(Link $link): string
    {
        return md5($link->from.$link->to.$link->title.$link->address);
    }
}
