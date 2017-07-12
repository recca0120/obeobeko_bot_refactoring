<?php

namespace App;

use Carbon\Carbon;

class ObeobekoRepository
{
    /**
     * $obeobeok.
     *
     * @var \App\Obeobeko
     */
    protected $obeobeok;

    /**
     * __construct.
     *
     * @param \App\Obeobeko $obeobeok
     */
    public function __construct(Obeobeko $obeobeok)
    {
        $this->obeobeok = $obeobeok;
    }

    /**
     * Get empty content obeobeko.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getEmptyContentObeobeko()
    {
        return $this->obeobeok->where('content', '')
                        ->whereDate('created_at', '<', Carbon::now()->subDay()->toDateString())
                        ->get();
    }

    /**
     * Get obeobeko list.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getObeobekoList()
    {
        return $this->obeobeok->where('content', '!=', '')->orderBy('created_at', 'desc');
    }

    /**
     * Get random obeobeko.
     *
     * @return \App\Obeobeko
     */
    public function getRandomObeobeko()
    {
        return $this->obeobeok->where('content', '!=', '')->inRandomOrder()->first();
    }

    /**
     * Search obeobeko list.
     *
     * @param string $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function search($query)
    {
        return $this->obeobeok->where('content', 'like', '%'.$query.'%')
                        ->orderBy('created_at', 'desc');
    }
}
