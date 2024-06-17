<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Support\Facades\Session;

use Faker\Factory as Faker;

class UserControllerTest extends TestCase
{

    public function testUserRegistration()
    {
		
		$faker = Faker::create();

		Session::forget('user_id');
		Session::forget('type_compte');
		$password = $faker->password;
        $response = $this->postJson('/api/register', [
			'email' => $faker->unique()->safeEmail,
			'password' => $password,
			'password_confirmation' => $password,
			'prenom' => $faker->firstName,
			'nom' => $faker->lastName,
			'type_compte' => $faker->randomElement(['Tourist', 'Guide']),
			'languages' => $faker->randomElement(['English', 'French', 'Spanish']),
			'nationalite' => $faker->country,
		]);

        $response->assertStatus(201);
        /*$response->assertJsonStructure([
            'uid', 'email', 'prenom', 'nom', 'type_compte', 'languages', 'nationalite', 'created_at'
        ]);*/

        $this->assertTrue(Session::has('user_id'));
        $this->assertTrue(Session::has('type_compte'));
    }


    public function testUserRegistrationEmailIsAlreadyExist()
    {
		
		$faker = Faker::create();

		Session::forget('user_id');
		Session::forget('type_compte');
		$password = $faker->password;
        $response = $this->postJson('/api/register', [
			'email' => "tourist@example.com",
			'password' => $password,
			'password_confirmation' => $password,
			'prenom' => $faker->firstName,
			'nom' => $faker->lastName,
			'type_compte' => $faker->randomElement(['Tourist', 'Guide']),
			'languages' => $faker->randomElement(['English', 'French', 'Spanish']),
			'nationalite' => $faker->country,
		]);

        $response->assertStatus(400);
        /*$response->assertJsonStructure([
            'uid', 'email', 'prenom', 'nom', 'type_compte', 'languages', 'nationalite', 'created_at'
        ]);*/
    }


    public function testUserRegistrationUserAlreadyLogged()
    {
		
		$faker = Faker::create();

		$this->postJson('/api/login', [
            'email' => 'tourist@example.com',
            'password' => '123456789',
        ]);
		
		$password = $faker->password;
        $response = $this->postJson('/api/register', [
			'email' => $faker->unique()->safeEmail,
			'password' => $password,
			'password_confirmation' => $password,
			'prenom' => $faker->firstName,
			'nom' => $faker->lastName,
			'type_compte' => $faker->randomElement(['Tourist', 'Guide']),
			'languages' => $faker->randomElement(['English', 'French', 'Spanish']),
			'nationalite' => $faker->country,
		]);

	    $response->assertStatus(401);
        /*$response->assertJsonStructure([
            'uid', 'email', 'prenom', 'nom', 'type_compte', 'languages', 'nationalite', 'created_at'
        ]);*/
    }


    public function testUserRegistrationInvalid()
    {
		
		$faker = Faker::create();

		Session::forget('user_id');
		Session::forget('type_compte');
		$password = $faker->password;
        $response = $this->postJson('/api/register', [
			'email' => $faker->unique()->safeEmail,
			'password' => $password,
			'password_confirmation' => $password,
			'prenom' => "",
			'nom' => "",
			'type_compte' => $faker->randomElement(['Tourist', 'Guide']),
			'languages' => $faker->randomElement(['English', 'French', 'Spanish']),
			'nationalite' => $faker->country,
		]);

        $response->assertStatus(500);
        /*$response->assertJsonStructure([
            'uid', 'email', 'prenom', 'nom', 'type_compte', 'languages', 'nationalite', 'created_at'
        ]);*/
    }
	

    public function testUserLogin()
    {
        Session::forget('user_id');
		Session::forget('type_compte');

        $response = $this->postJson('/api/login', [
            'email' => 'tourist@example.com',
            'password' => '123456789',
        ]);

        $response->assertStatus(201);
        /*$response->assertJsonStructure([
            'uid', 'email', 'prenom', 'nom', 'type_compte', 'languages', 'nationalite', 'created_at'
        ]);*/

        $this->assertTrue(Session::has('user_id'));
        $this->assertTrue(Session::has('type_compte'));
    }


    public function testUserLoginUserAlreadyLogged()
    {
		$this->postJson('/api/login', [
            'email' => 'tourist@example.com',
            'password' => '123456789',
        ]);
		
        $response = $this->postJson('/api/login', [
            'email' => 'tourist@example.com',
            'password' => '123456789',
        ]);

        $response->assertStatus(400);
        /*$response->assertJsonStructure([
            'uid', 'email', 'prenom', 'nom', 'type_compte', 'languages', 'nationalite', 'created_at'
        ]);*/
    }


    public function testUserLoginWrongPassword()
    {
        Session::forget('user_id');
		Session::forget('type_compte');

        $response = $this->postJson('/api/login', [
            'email' => 'tourist@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401);
        /*$response->assertJsonStructure([
            'uid', 'email', 'prenom', 'nom', 'type_compte', 'languages', 'nationalite', 'created_at'
        ]);*/
    }


    public function testUserLoginNotExistMail()
    {
        Session::forget('user_id');
		Session::forget('type_compte');

        $response = $this->postJson('/api/login', [
            'email' => 'notexistmail@example.com',
            'password' => '123456789',
        ]);

        $response->assertStatus(404);
        /*$response->assertJsonStructure([
            'uid', 'email', 'prenom', 'nom', 'type_compte', 'languages', 'nationalite', 'created_at'
        ]);*/
    }
	
	
	
	
	
	
	
	
	
	
	
	
	

    public function testSetUserSuccessfullyUpdatesUser()
    {
        $faker = Faker::create();

		//Session::put('user_id', 'testUserId');
		$this->postJson('/api/login', [
            'email' => 'tourist@example.com',
            'password' => '123456789',
        ]);

        $response = $this->putJson('/api/user', [
            'email' => 'tourist@example.com',
			'prenom' => $faker->firstName,
			'nom' => $faker->lastName,
			'type_compte' => 'Tourist',
			'languages' => $faker->randomElement(['English', 'French', 'Spanish']),
			'nationalite' => $faker->country,
            'old_password' => '123456789',
            'password' => '123456789',
            'password_confirmation' => '123456789',
        ]);

        $response->assertStatus(201);
    }


    public function testSetUserFailsValidation()
    {
        $faker = Faker::create();

		//Session::put('user_id', 'testUserId');
		$this->postJson('/api/login', [
            'email' => 'tourist@example.com',
            'password' => '123456789',
        ]);

        $response = $this->putJson('/api/user', [
            'email' => '',
			'prenom' => $faker->firstName,
			'nom' => $faker->lastName,
			'type_compte' => 'Tourist',
			'languages' => $faker->randomElement(['English', 'French', 'Spanish']),
			'nationalite' => $faker->country,
        ]);

        $response->assertStatus(500); // Laravel default for validation errors
    }


    public function testSetUserSessionNotExist()
    {
        $faker = Faker::create();

		Session::forget('user_id');
		Session::forget('type_compte');

        //Session::put('user_id', 'testUserId');

        $response = $this->putJson('/api/user', [
            'email' => 'tourist@example.com',
			'prenom' => $faker->firstName,
			'nom' => $faker->lastName,
			'type_compte' => 'Tourist',
			'languages' => $faker->randomElement(['English', 'French', 'Spanish']),
			'nationalite' => $faker->country,
            'old_password' => '123456789',
            'password' => '123456789',
            'password_confirmation' => '123456789',
        ]);

        $response->assertStatus(401);
    }
	

    public function testSetUserUserNotExist()
    {
        $faker = Faker::create();

		Session::put('user_id', 'testUserId');

        $response = $this->putJson('/api/user', [
            'email' => 'tourist@example.com',
			'prenom' => $faker->firstName,
			'nom' => $faker->lastName,
			'type_compte' => 'Tourist',
			'languages' => $faker->randomElement(['English', 'French', 'Spanish']),
			'nationalite' => $faker->country,
            'old_password' => '123456789',
            'password' => '123456789',
            'password_confirmation' => '123456789',
        ]);

        $response->assertStatus(404);
    }
	

    public function testSetUserFailsDueToAuthentication()
    {
        $faker = Faker::create();

		//Session::put('user_id', 'testUserId');
		$this->postJson('/api/login', [
            'email' => 'tourist@example.com',
            'password' => '123456789',
        ]);

        $response = $this->putJson('/api/user', [
            'email' => 'tourist@example.com',
			'prenom' => $faker->firstName,
			'nom' => $faker->lastName,
			'type_compte' => 'Tourist',
			'languages' => $faker->randomElement(['English', 'French', 'Spanish']),
			'nationalite' => $faker->country,
            'old_password' => 'wrongpassword',
            'password' => '123456789',
            'password_confirmation' => '123456789',
        ]);

        $response->assertStatus(500);
    }
	

    public function testLogout()
    {
        //Session::put('user_id', 'testUserId');
		$this->postJson('/api/login', [
            'email' => 'tourist@example.com',
            'password' => '123456789',
        ]);

        $response = $this->postJson('/api/logout');

        $response->assertStatus(204);
    }
	

    public function testLogoutSessionNotExist()
    {
        //Session::put('user_id', 'testUserId');
		Session::forget('user_id');
		Session::forget('type_compte');

        

        $response = $this->postJson('/api/logout');

        $response->assertStatus(200);
    }
	

    public function testGetSessionUser()
    {
        //Session::put('user_id', 'testUserId');
		$this->postJson('/api/login', [
            'email' => 'tourist@example.com',
            'password' => '123456789',
        ]);

        $response = $this->getJson('/api/user');

        $response->assertStatus(200);
    }
	

    public function testGetSessionUserSessionNotExist()
    {
        //Session::put('user_id', 'testUserId');
		Session::forget('user_id');
		Session::forget('type_compte');

        

        $response = $this->getJson('/api/user');

        $response->assertStatus(401);
    }
	

    public function testGetUser()
    {
        //Session::put('user_id', 'testUserId');
		$this->postJson('/api/login', [
            'email' => 'tourist@example.com',
            'password' => '123456789',
        ]);

        $response = $this->getJson('/api/user/AjVs8ZOHs0SbKE9fUYkNVBlDMvj1');

        $response->assertStatus(200);
    }
	

    public function testGetSessionUserNotExist()
    {
        //Session::put('user_id', 'testUserId');
		Session::forget('user_id');
		Session::forget('type_compte');

        

        $response = $this->getJson('/api/user/notexistid');

        $response->assertStatus(404);
    }


}