<?php

namespace App\Http\Controllers;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Contract\Auth;

class FirebaseController extends Controller
{
	/**
	 * ****************************************************************
	 * ****************************************************************
	 * **********************                    **********************
	 * **********************  *//* Morist *//*  **********************
	 * **********************                    **********************
	 * ****************************************************************
	 * ****************************************************************
	 */
	
	
	
	
	/**
	 * Attributes
	 */
	
	protected $database;
	protected $auth;
	
	/**
	 * construct
	 */
	
	public function __construct()
	{
		$firebase = (new Factory)
			->withServiceAccount(__DIR__.'/morist-bbb42-firebase-adminsdk-h52nn-e2252136c8.json')
			->withDatabaseUri('https://morist-bbb42-default-rtdb.europe-west1.firebasedatabase.app/');
		
		$this->database = $firebase->createDatabase();
		$this->auth = $firebase->createAuth();
	}
	
	// public function __invoke(){
		
	// }

	
}