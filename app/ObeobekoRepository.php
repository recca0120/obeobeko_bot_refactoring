<?php

namespace App;

use Carbon\Carbon;

class ObeobekoRepository
{
    /**
     * Get empty content obeobeko.
     *
     * @return obeobekos
     */
    public static function getEmptyContentObeobeko()
    {
        $obeobekos = Obeobeko::where('content', '')
                        ->whereDate('created_at', '<', Carbon::now()->subDay()->toDateString())
                        ->get();

        return $obeobekos;
    }

    /**
     * Get obeobeko list.
     *
     * @return obeobekos
     */
    public static function getObeobekoList()
    {
        $obeobekos = Obeobeko::where('content', '!=', '')->orderBy('created_at', 'desc');

        return $obeobekos;
    }

    /**
     * Get random obeobeko.
     *
     * @return obeobeko
     */
    public static function getRandomObeobeko()
    {
        $obeobeko = Obeobeko::where('content', '!=', '')->inRandomOrder()->first();

        return $obeobeko;
    }

    /**
     * Search obeobeko list.
     *
     * @param string $query
     *
     * @return obeobekos
     */
    public static function search($query)
    {
        $obeobekos = Obeobeko::where('content', 'like', '%'.$query.'%')
                        ->orderBy('created_at', 'desc');

        return $obeobekos;
    }
}
