<?php
declare(strict_types = 1);

namespace ISTSI\Helpers;

class DateTime
{
    public static function isBetween($start, $end)
    {
        $now = new \DateTime();
        $start = new \DateTime($start);
        $end = new \DateTime($end);

        return $now >= $start && $now <= $end;
    }

    public static function isAfter($pointer)
    {
        $now = new \DateTime();
        $pointer = new \DateTime($pointer);

        return $now >= $pointer;
    }
}
