<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class UserController extends FirebaseController
{
	/**
	 * Register a user.
	 */
	public function register(Request $request)
	{
		
		$userId = Session::get('user_id');
		
		if (!$userId) {
			
			$rules = [
				'email' => 'required|string|min:2|max:50',
				'password' => 'required|string|min:6|max:50',
				'prenom' => 'required|string|min:2|max:50',
				'nom' => 'required|string|min:2|max:50',
				'type_compte' => 'required|string|in:Guide,Tourist',
				'languages' => 'required|string|min:2|max:50',
				'nationalite' => 'required|string|min:2|max:50',
			];
				
			try {
				$validatedData = $request->validate($rules);
				//$data = $validatedData;
			} catch (\Exception $e) {
				return response()->json(['errors' => [0 => $e->getMessage()]], 500);
			}
			
			
			$email = $validatedData['email'];
			$password = $validatedData['password'];
			$prenom = $validatedData['prenom'];
			$nom = $validatedData['nom'];
			
			$type_compte = $validatedData['type_compte'];
			$languages = $validatedData['languages'];
			$nationalite = $validatedData['nationalite'];
			
			$user = null;
			try {
				$user = $this->auth->getUserByEmail($email);
			} catch (\Exception $e) {
			}
			
			if (!$user) {
				if ($request->input('password') == $request->input('password_confirmation')) {
			
					try {
						$user = $this->auth->createUser([
							'email' => $email,
							'password' => $password,
						]);
						
						$userProperties = [
							'id' => $user->uid,
							'email' => $email,
							'prenom' => $prenom,
							'nom' => $nom,
							'type_compte' => $type_compte,
							'languages' => $languages,
							'languages' => $languages,
							'nationalite' => $nationalite,
							'created_at' => date('Y-m-d H:i:s'),
						];
						   
						
						$this->auth->setCustomUserClaims($user->uid, $userProperties);
						Session::put('user_id', $user->uid);
						Session::put('type_compte', $userProperties["type_compte"]);
						
						$user = $this->auth->getUserByEmail($email);
						
						//return response()->json(['message' => 'User registered successfully'], 204);
						return response()->json($user, 201);
						
					} catch (\Exception $e) {
						return response()->json(['errors' => [0 => $e->getMessage()]], 500);
					}
				} else {
					return response()->json(['errors' => [0 => "The password and the password confirmation not match"]], 500);
				}
			} else {
				return response()->json(['errors' => [0 => "The email address is already in use by another account"]], 400);
			}
		} else {
			return response()->json(['errors' => [0 => "User already logged"]], 401);
		}
	}
	
	
	
	/**
	 * Update user informations.
	 */
	public function setUser(Request $request)
	{
		$userId = Session::get('user_id');
		
		if ($userId) {
			
			try {
				$userData = $this->auth->getUser($userId)->customClaims;
			} catch (\Exception $e) {
				return response()->json(['errors' => [0 => 'User not exist']], 404);
			}
			if ($userData) {
				$rules = [
					'patente' => 'required|string|min:2|max:50',
					'email' => 'required|string|min:2|max:50',
					'prenom' => 'required|string|min:2|max:50',
					'nom' => 'required|string|min:2|max:50',
					'type_compte' => 'required|string|in:Guide,Tourist',
					'languages' => 'required|string|min:2|max:50',
					'nationalite' => 'required|string|min:2|max:50',
				];
				
				
				try {
					$validatedData = $request->validate($rules);
				} catch (\Exception $e) {
					return response()->json(['errors' => [0 => $e->getMessage()]], 500);
				}
				
				//$data = $validatedData;
				
				$patente = $validatedData['patente'];
				$email = $validatedData['email'];
				$prenom = $validatedData['prenom'];
				$nom = $validatedData['nom'];
				
				$type_compte = $validatedData['type_compte'];
				$languages = $validatedData['languages'];
				$nationalite = $validatedData['nationalite'];
				
				if ($request->input('old_password') OR $request->input('password')) {
					
					$rules = [
						'old_password' => 'required|string',
						'password' => 'required|string|min:6|max:50',
						'password_confirmation' => 'required|string|min:6|max:50',
					];
					try {
						$validatedDataPass = $request->validate($rules);
					} catch (\Exception $e) {
						return response()->json(['errors' => [0 => $e->getMessage()]], 500);
					}
					$password = $validatedDataPass['password'];
					
					if ($validatedDataPass["password"] != $validatedDataPass["password_confirmation"]) {
						return response()->json(['errors' => [0 => "The password and the password confirmation not match"]], 500);
					} else {
						
						try {
						
							$userlogin = $this->auth->signInWithEmailAndPassword($email, $validatedDataPass['old_password']);
						
						} catch (\Exception $e) {
							return response()->json(['errors' => [0 => $e->getMessage()]], 500);
						}
					}
				}
					
					
				try {
					if (!$request->input('password')) {
						$user = $this->auth->updateUser($userId, [
							'email' => $email,
						]);
					} else {
						$user = $this->auth->updateUser($userId, [
							'email' => $email,
							'password' => $password,
						]);
					}
					
					$userProperties = [
						'id' => $user->uid,
						'patente' => $patente,
						'email' => $email,
						'prenom' => $prenom,
						'nom' => $nom,
						'type_compte' => $type_compte,
						'languages' => $languages,
						'languages' => $languages,
						'nationalite' => $nationalite,
						'created_at' => date('Y-m-d H:i:s'),
					];
					   
					
					$this->auth->setCustomUserClaims($user->uid, $userProperties);
					Session::put('user_id', $user->uid);
					Session::put('type_compte', $userProperties["type_compte"]);
					
					$user = $this->auth->getUserByEmail($email);
					
					//return response()->json(['message' => 'User registered successfully'], 204);
					return response()->json($user, 201);
					
				} catch (\Exception $e) {
					return response()->json(['errors' => [0 => $e->getMessage()]], 500);
				}
			
			}
			
		} else {
			return response()->json(['errors' => [0 => 'User not logged in']], 401);
		}
	}
	
	
	
	/**
	 * User login.
	 */
	public function login(Request $request)
	{
		$userId = Session::get('user_id');
		
		if (!$userId) {
			$email = $request->input('email');
			$password = $request->input('password');
			
			try {
				$user = $this->auth->getUserByEmail($email);
			} catch (\Exception $e) {
				return response()->json(['errors' => [0 => 'User not found']], 404);
			}
				
			if ($user) {
					try {
						$userlogin = $this->auth->signInWithEmailAndPassword($email, $password);
					} catch (\Exception $e) {
						return response()->json(['errors' => [0 => 'Invalid password']], 401);
					}
					
					if ($userlogin) {
						try {
							Session::put('user_id', $user->uid);
							$userData = $this->auth->getUser($user->uid)->customClaims;
							Session::put('type_compte', $userData["type_compte"]);
							
							//return response()->json(['message' => 'User Loged successfully'], 204);
							return response()->json($user, 201);
						} catch (\Exception $e) {
							return response()->json(['errors' => [0 => $e->getMessage()]], 500);
						}
						
					}
					
				}
				
			
		} else {
			return response()->json(['errors' => [0 => 'User already logged in']], 400);
		}
	}
	
	
	
	/**
	 * Display get user informations.
	 */
	public function getUser(Request $request, string $user)
	{
		try {
			$userData = $this->auth->getUser($user)->customClaims;
		} catch (\Exception $e) {
			return response()->json(['errors' => [0 => $e->getMessage()]], 404);
		}
		if ($userData) {
			return response()->json($userData, 200);
		}
	}
	
	
	
	/**
	 * Display session user informations.
	 */
	public function getSessionUser()
	{
		$userId = Session::get('user_id');
		
		if ($userId) {
			$userData = $this->auth->getUser($userId)->customClaims;
			if ($userData) {
				return response()->json($userData, 200);
			} else {
				return response()->json(['errors' => [0 => 'User not found']], 404);
			}
		
		} else {
			return response()->json(['errors' => [0 => 'User not logged in']], 401);
		}
	}
	
	
	
	/**
	 * Logout and forget session.
	 */
	public function logout()
	{
		$userId = Session::get('user_id');
		
		if ($userId) {
			try {
				Session::forget('user_id');
				Session::forget('type_compte');
				
				return response()->json(['message' => 'Logout successful'], 204);
				
			} catch (\Exception $e) {
				return response()->json(['errors' => [0 => $e->getMessage()]], 500);
			}
			
		}/* else {
			return response()->json(['errors' => [0 => 'User not logged in']], 401);
		}*/
	}
	
}