<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class StarController extends FirebaseController
{
	/**
	 * Display Starred Offers.
	 */
	public function indexStar(Request $request)
	{
		$panier = Session::get('panier', []);
		$userId = Session::get('user_id');
		
		$page = $request->query('page', 1);
		$limit = $request->query('limit', 10);
		$offset = ($page - 1) * $limit;
		
		if ($userId) {
			$jsonArray = [];
			
			$results = $this->database->getReference('Data/Star')
										  ->orderByKey()
										  ->limitToFirst($offset + $limit)->getValue();
			
			if ($results) {
				$results = array_slice($results, $offset, $limit);
				
				foreach ($results as $index => $item) {
					
					$key = $item["id_offre"];
					$data = $this->database->getReference("Data/Offre/$key")->getValue();
					$jsonArray[$key] = $data;
					
					
					if (in_array($key, $panier)) {
						
						$jsonArray[$key]["pannied"] = true;
						
					} else {
						
						$jsonArray[$key]["pannied"] = false;
						
					}
					
					$jsonArray[$key]["starred"] = true;
				}
				
				return response()->json($jsonArray, 200);
			}
			
			
			
		} else {
			return response()->json(['errors' => [0 => 'User not logged in']], 401);
		}
	}
	
	
	
	/**
	 * Show the form for creating a new Star.
	 */
	// public function createStar()
	// {
		
	// }
		
	/**
	 * Store a newly created Star in storage.
	 */
	public function storeStar(Request $request)
	{
		
		$id_offre = $request->input('id_offre');
		$userId = Session::get('user_id');
		
		if ($userId) {
			
			$results = $this->database->getReference("Data/Offre/$id_offre")->getValue();
			
			if ($results) {
				
				$reference = $this->database->getReference("Data/Star");
				$idQuery = $reference->orderByChild('identify')
											->startAt($id_offre.$userId)
											->endAt($id_offre.$userId . "\uf8ff");
					
				if (!$idQuery->getValue()) {
					
					try {
						//$data = $request->all();
						
						$rules = [
							'id_offre' => 'required|string|min:1|max:255',
						];
						
						$validatedData = $request->validate($rules);
						$data = $validatedData;
						
						$result = $this->database->getReference('Data/Star')->push($data);
						$key = $result->getKey();
						$this->database->getReference("Data/Star/$key")->update([
						'identify' => $id_offre.$userId, 'id_user' => $userId, 'date' => date('Y-m-d H:i:s') ]);
						
						return response()->json($result->getValue(), 201);
						
					} catch (\Exception $e) {
						return response()->json(['errors' => [0 => $e->getMessage()]], 500);
					}
					
				} else {
					return response()->json(['errors' => [0 => "Offer Already Starred"]], 400);
				}
				
			} else {
				return response()->json(['errors' => [0 => 'Offer Not Found']], 404);
			}
			
		} else {
			return response()->json(['errors' => [0 => 'User not logged in']], 401);
		}
	}
	
	
	
	/**
	 * Display the specified Star.
	 */
	public function showStar(string $id_offre)
	{
		$userId = Session::get('user_id');
		
		if ($userId) {
			$reference = $this->database->getReference("Data/Star");
			
			$idQuery = $reference->orderByChild('identify')
								->startAt($id_offre.$userId)
								->endAt($id_offre.$userId . "\uf8ff");
			
			$results = $idQuery->getValue();
			
			if ($results) {
				return response()->json($results, 200);
				
			} else {
				return response()->json(['errors' => [0 => 'Star not exist']], 404);
			}
			
		} else {
			return response()->json(['errors' => [0 => 'User not logged in']], 401);
		}
	}
	
	
	
	/**
	 * Show the form for editing the specified Star.
	 */
	// public function editStar(string $id_offre)
	// {
		
	// }
		
	/**
	 * Remove the Star resource from storage.
	 */
	public function destroyStar(string $id_offre)
	{
		$userId = Session::get('user_id');
		
		if ($userId) {
			
			try {
				$reference = $this->database->getReference("Data/Star");
				
				$idQuery = $reference->orderByChild('identify')
								->startAt($id_offre.$userId)
								->endAt($id_offre.$userId . "\uf8ff");
				
				$result = $idQuery->getValue();
				$keys = array_keys($result);
				$key = $keys[0];
				
				if ($key) {
				
					$result = $this->database->getReference("Data/Star/$key")->remove();
					return response()->json($result->getValue(), 200);
				
				} else {
					return response()->json(['errors' => [0 => 'Star not exist']], 404);
				}
				
			} catch (\Exception $e) {
				return response()->json(['errors' => [0 => $e->getMessage()]], 500);
			}
			
		} else {
			return response()->json(['errors' => [0 => 'User not logged in']], 401);
		}
	}
	
}