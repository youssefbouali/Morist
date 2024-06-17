<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Support\Facades\Session;

use Faker\Factory as Faker;

class OfferControllerTest extends TestCase
{
    public function testExplore()
    {
		
		$faker = Faker::create();

		$response = $this->getJson('/api/explore/undefined?page='.$faker->randomElement(['1', '2', '3']).'&limit='.$faker->randomElement(['10', '15', '20']).'');

        $response->assertStatus(200);
        /*$response->assertJsonStructure([
            'uid', 'email', 'prenom', 'nom', 'type_compte', 'languages', 'nationalite', 'created_at'
        ]);*/
    }
	
	
    public function testExploreCity()
    {
		
		$faker = Faker::create();

		$response = $this->getJson('/api/explore/'.$faker->randomElement(['rabat', 'agadir', 'fes']).'?page='.$faker->randomElement(['1', '2', '3']).'&limit='.$faker->randomElement(['10', '15', '20']).'');

        $response->assertStatus(200);
        /*$response->assertJsonStructure([
            'uid', 'email', 'prenom', 'nom', 'type_compte', 'languages', 'nationalite', 'created_at'
        ]);*/
    }
	
	
    public function testSearch()
    {
		
		$faker = Faker::create();

		$response = $this->getJson('/api/search/'.$faker->randomElement(['Rabat', 'Agadir', 'Fes']).'?page='.$faker->randomElement(['1', '2', '3']).'&limit='.$faker->randomElement(['10', '15', '20']).'');

        $response->assertStatus(200);
        /*$response->assertJsonStructure([
            'uid', 'email', 'prenom', 'nom', 'type_compte', 'languages', 'nationalite', 'created_at'
        ]);*/
    }
	
}
