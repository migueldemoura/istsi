<?php
declare(strict_types = 1);

namespace ISTSI\Helpers;

class Registration
{
    public static function isOpen($start, $end)
    {
        try {
            $now = new \DateTime();
            $start = new \DateTime($start);
            $end = new \DateTime($end);
        } catch (\Exception $exception) {
            return false;
        }

        return ($now >= $start && $now <= $end);
    }
}
