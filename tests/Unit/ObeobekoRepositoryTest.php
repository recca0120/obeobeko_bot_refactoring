<?php

namespace Tests\Unit;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\ObeobekoRepository;
use Carbon\Carbon;

class ObeobekoRepositoryTest extends TestCase
{
    protected function tearDown() {
        m::close();
    }

    public function testGetEmptyContentObeobeko()
    {
        $obeobekoRepo = new ObeobekoRepository(
            $model = m::mock('App\Obeobeko')
        );

        $yesterday = Carbon::now()->subDay()->toDateString();
        $model->shouldReceive('where')->once()->with('content', '')->andReturnSelf();
        $model->shouldReceive('whereDate')->once()->with('created_at', '<', $yesterday)->andReturnSelf();
        $model->shouldReceive('get')->once()->andReturn(
            $obeobekos = ['foo' => 'bar']
        );

        $this->assertSame($obeobekos, $obeobekoRepo->getEmptyContentObeobeko());
    }

    public function testGetObeobekoList()
    {
        $obeobekoRepo = new ObeobekoRepository(
            $model = m::mock('App\Obeobeko')
        );

        $model->shouldReceive('where')->once()->with('content', '!=', '')->andReturnSelf();
        $model->shouldReceive('orderBy')->once()->with('created_at', 'desc')->andReturnSelf();

        $this->assertSame($model, $obeobekoRepo->getObeobekoList());
    }

    public function testGetRandomObeobeko()
    {
        $obeobekoRepo = new ObeobekoRepository(
            $model = m::mock('App\Obeobeko')
        );

        $model->shouldReceive('where')->once()->with('content', '!=', '')->andReturnSelf();
        $model->shouldReceive('inRandomOrder')->once()->andReturnSelf();
        $model->shouldReceive('first')->once()->andReturnSelf();

        $this->assertSame($model, $obeobekoRepo->getRandomObeobeko());
    }

    public function testSearch()
    {
        $obeobekoRepo = new ObeobekoRepository(
            $model = m::mock('App\Obeobeko')
        );

        $query = 'foo';
        $model->shouldReceive('where')->once()->with('content', 'like', '%'.$query.'%')->andReturnSelf();
        $model->shouldReceive('orderBy')->once()->with('created_at', 'desc')->andReturnSelf();

        $this->assertSame($model, $obeobekoRepo->search($query));
    }
}
