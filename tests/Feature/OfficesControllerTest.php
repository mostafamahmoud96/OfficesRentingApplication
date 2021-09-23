<?php

namespace Tests\Feature;

use App\Models\Office;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class OfficesControllerTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    /**
     * @test
     */
    public function itListAllOfficesInPaginatedWay()
    {
        Office::factory(3)->create();

        $response = $this->get('/api/offices');
        // dd($response->json());
        //$response->assertOk()->dump();
        // $response->dump();

        $response->assertOk();
        $response->assertJsonCount(3,'data');
        $this->assertNotNull($response->json('data')[0]['id']);
        $this->assertNotNull($response->json('meta'));
        $this->assertNotNull($response->json('links'));
        // $this->assertCount(3,$response->json('data'));
    }
    /**
     * @test
     */
    public function itOnlyListOfficesThatAreNotHiddenAndApproved()
    {
        Office::factory(3)->create();

        Office::factory()->create(['hidden'=>true]);
        Office::factory()->create(['approval_status'=> Office::APPROVAL_PENDING]);

        $response = $this->get('/api/offices');

        $response->assertOk();
        $response->assertJsonCount(3,'data');
    }
    /**
     * @test
     */
    public function itfilterByHostId()
    {
        Office::factory(3)->create();
        $host = User::factory()->create();

         $office = Office::factory()->for($host)->create();


        $response = $this->get('/api/offices?host_id='.$host->id);

        $response->assertOk();
        $response->assertJsonCount(1,'data');
        $this->assertEquals($office->id , $response->json('data')[0]['id']);

    }
     /**
     * @test
     */
    public function itfilterByUserId()
    {
        Office::factory(3)->create();

        $user = User::factory()->create();
        $office = Office::factory()->create();

        Reservation::factory()->for(Office::factory())->create();
        Reservation::factory()->for($office)->for($user)->create();


        $response = $this->get(
        '/api/offices?user_id='.$user->id);

        $response->assertOk();
        $response->assertJsonCount(1,'data');
        $this->assertEquals($office->id , $response->json('data')[0]['id']);

    }
}
