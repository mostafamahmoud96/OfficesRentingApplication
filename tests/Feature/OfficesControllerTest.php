<?php

namespace Tests\Feature;

use App\Models\Office;
use App\Models\Reservation;
use App\Models\Tag;
use App\Models\User;
use App\Models\Image;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
        $response->assertJsonCount(3, 'data');
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

        Office::factory()->create(['hidden' => true]);
        Office::factory()->create(['approval_status' => Office::APPROVAL_PENDING]);

        $response = $this->get('/api/offices');

        $response->assertOk();
        $response->assertJsonCount(3, 'data');
    }
    /**
     * @test
     */
    public function itfilterByHostId()
    {
        Office::factory(3)->create();
        $host = User::factory()->create();

        $office = Office::factory()->for($host)->create();

        $response = $this->get('/api/offices?host_id=' . $host->id);

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
        $this->assertEquals($office->id, $response->json('data')[0]['id']);

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
            '/api/offices?user_id=' . $user->id);

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
        $this->assertEquals($office->id, $response->json('data')[0]['id']);

    }
    /**
     * @test
     */
    public function itIncludesImagesTagsAndUser()
    {
        $user = User::factory()->create();
        $tag =Tag::factory()->create();

        $office = Office::factory()->for($user)->create();

        $office->tags()->attach($tag);
        // $office->images()->attach($image);

        $office->images()->create(['path'=>'image.jpg']);

        $response = $this->get('/api/offices');
        $response->assertOk();
        $this->assertIsArray($response->json('data')[0]['tags']);
        $this->assertCount(1,$response->json('data')[0]['tags']);
        $this->assertIsArray($response->json('data')[0]['images']);
        $this->assertCount(1,$response->json('data')[0]['images']);
        $this->assertEquals($user->id, $response->json('data')[0]['user']['id']);


    }
    /**
     * @test
     */
    public function itReturnsTheNumberOfActiveReservations()
    {
        $office = Office::factory()->create();
        Reservation::factory()->for($office)->create(['status'=>Reservation::STATUS_ACTIVE]);
        Reservation::factory()->for($office)->create(['status'=>Reservation::STATUS_CANCELLED]);
        $response = $this->get('/api/offices');
        $response->assertOk();
        // $response->dump();
        $this->assertEquals(1,$response->json('data')[0]['reservations_count']);
    }

}
