<?php
declare(strict_types = 1);

namespace ISTSI\Helpers;

class DateTime
{
    const BEFORE_START = 0;
    const BEFORE_END = 1;
    const BETWEEN = 2;
    const AFTER_START = 3;
    const AFTER_END = 4;

    public static function isBefore($pointer, $equal = false)
    {
        $now = new \DateTime();
        $pointer = new \DateTime($pointer);

        return $equal ? $now <= $pointer : $now < $pointer;
    }

    public static function isBetween($start, $end, $equal = false)
    {
        $now = new \DateTime();
        $start = new \DateTime($start);
        $end = new \DateTime($end);

        return $equal ? $now >= $start && $now <= $end : $now > $start && $now < $end;
    }

    public static function isAfter($pointer, $equal = false)
    {
        $now = new \DateTime();
        $pointer = new \DateTime($pointer);

        return $equal ? $now >= $pointer : $now > $pointer;
    }
}
