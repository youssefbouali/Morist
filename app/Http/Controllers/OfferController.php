<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class OfferController extends FirebaseController
{
	/**
	 * list all session user offers
	 */
	
	public function indexSessionOffre(Request $request)
	{
		$panier = Session::get('panier', []);
		$userId = Session::get('user_id');
		
		$page = $request->query('page', 1);
		$limit = $request->query('limit', 10);
		$offset = ($page - 1) * $limit;
		
		if ($userId) {
			$reference = $this->database->getReference("Data/Offre");
			
			$idQuery = $reference->orderByChild('id_user')
									->startAt($userId)
									->endAt($userId . "\uf8ff")
									->limitToFirst($offset + $limit);
			
			$results = $idQuery->getValue();
			if($results){
				$results = array_slice($results, $offset, $limit);
				
				
				foreach ($results as $index => $item) {
					
					$id_offre = $item["id"];
					
					
					$userData = $this->auth->getUser($item["id_user"])->customClaims;
					$results[$index]["prenom"] = $userData["prenom"];
					$results[$index]["nom"] = $userData["nom"];
					
					
					$reference = $this->database->getReference("Data/Star");
					$idQuery = $reference->orderByChild('identify')
											->startAt($id_offre.$userId)
											->endAt($id_offre.$userId . "\uf8ff");
					
					$results2 = $idQuery->getValue();
					
					if ($results2) {
						
						$results[$index]["starred"] = true;
						
					} else {
					
						$results[$index]["starred"] = false;
						
					}
					
					
					if (in_array($id_offre, $panier)) {
						
						$results[$index]["pannied"] = true;
						
					} else {
						
						$results[$index]["pannied"] = false;
						
					}
					
				}
			}
			
			return response()->json($results, 200);
			
		} else {
			return response()->json(['errors' => [0 => 'User not logged in']], 401);
		}
	}
	
	
	
	/**
	 * list all get user offers
	 */
	
	public function indexUserOffre(Request $request, string $user)
	{
		$panier = Session::get('panier', []);
		$userId = Session::get('user_id');
		
		$page = $request->query('page', 1);
		$limit = $request->query('limit', 10);
		$offset = ($page - 1) * $limit;
		
		if ($user == "undefined") {
			$user = $userId;
		}
		
		
		$reference = $this->database->getReference("Data/Offre");
		
		$idQuery = $reference->orderByChild('id_user')
								->startAt($user)
								->endAt($user . "\uf8ff")
								->limitToFirst($offset + $limit);
		
		$results = $idQuery->getValue();
		$results = array_slice($results, $offset, $limit);
		
		
		
		foreach ($results as $index => $item) {
			
			$id_offre = $item["id"];
			
			
			$userData = $this->auth->getUser($item["id_user"])->customClaims;
			$results[$index]["prenom"] = $userData["prenom"];
			$results[$index]["nom"] = $userData["nom"];
				
				
			$reference = $this->database->getReference("Data/Star");
			$idQuery = $reference->orderByChild('identify')
									->startAt($id_offre.$userId)
									->endAt($id_offre.$userId . "\uf8ff");
			
			$results2 = $idQuery->getValue();
			
			if ($results2) {
				
				$results[$index]["starred"] = true;
				
			} else {
				
				$results[$index]["starred"] = false;
				
			}
			
			
			if (in_array($id_offre, $panier)) {
				
				$results[$index]["pannied"] = true;
				
			} else {
				
				$results[$index]["pannied"] = false;
				
			}
			
		}
		
		return response()->json($results, 200);
		
	}
	
	
	
	/**
	 * Show the form for creating a new Offer.
	 */
	// public function createOffre()
	// {
		
	// }
		
	/**
	 * Store a newly created Offer in storage.
	 */
	public function storeOffre(Request $request)
	{
		
		$userId = Session::get('user_id');
		$type_compte = Session::get('type_compte');
		
		if ($userId && $type_compte == "Guide") {
			
			try {
				//$data = $request->all();
				
				$rules = [
					'titre' => 'required|string|min:1|max:255',
					'description' => 'required|string|min:1|max:500',
					'city' => 'required|string|min:2|max:50',
					'image' => 'required|string',
					'prix' => 'required|integer|min:1|max:600',
				];
				
				$validatedData = $request->validate($rules);
				$data = $validatedData;
				
				// $data = [
					// 'titre' => $validatedData['titre'],
					// 'description' => $validatedData['description'],
					// 'city' => $validatedData['city'],
					// 'image' => $validatedData['image'],
					// 'prix' => $validatedData['prix'],
				// ];
				
				
				$base64_image = $request->input('image');
				
				$image_patterns = [
					'jpeg' => '/^data:image\/jpeg;base64,/',
					'png' => '/^data:image\/png;base64,/',
					'gif' => '/^data:image\/gif;base64,/',
				];
				
				$image_type = null;
				foreach ($image_patterns as $type => $pattern) {
					if (preg_match($pattern, $base64_image)) {
						$base64_image = preg_replace($pattern, '', $base64_image);
						$image_type = $type;
						break;
					}
				}
				
				if (!$image_type) {
					return response()->json(['message' => 'Invalid image data'], 400);
				}
				
				$image_data = base64_decode($base64_image);
				
				$random_name = uniqid() . '.jpg';
				
				$file_path = __DIR__ . '/../../../public/storage/' . $random_name;
				
				file_put_contents($file_path, $image_data);
				
				$data['image'] = "//morist.mywire.org:3000/storage/".$random_name;
				
				$result = $this->database->getReference('Data/Offre')->push($data);
				$key = $result->getKey();
				$this->database->getReference("Data/Offre/$key")->update([
				'id' => $result->getKey(), 'id_user' => $userId, 'date' => date('Y-m-d H:i:s') ]);
				
				return response()->json($result->getValue(), 201);
				
			} catch (\Exception $e) {
				return response()->json(['errors' => [0 => $e->getMessage()]], 500);
			}
			
		} else {
			return response()->json(['errors' => [0 => 'User not logged in']], 401);
		}
	}
	
	
	
	/**
	 * Display the specified Offer.
	 */
	public function showOffre(string $id)
	{
		$panier = Session::get('panier', []);
		$userId = Session::get('user_id');
		$results = $this->database->getReference("Data/Offre/$id")->getValue();
		
		if ($results) {
			
			$id_offre = $id;
			
			$userData = $this->auth->getUser($results["id_user"])->customClaims;
			$results["prenom"] = $userData["prenom"];
			$results["nom"] = $userData["nom"];
				
				
			$reference = $this->database->getReference("Data/Star");
			$idQuery = $reference->orderByChild('identify')
									->startAt($id_offre.$userId)
									->endAt($id_offre.$userId . "\uf8ff");
			
			$results2 = $idQuery->getValue();
			
			if ($results2) {
				
				$results["starred"] = true;
				
			} else {
				
				$results["starred"] = false;
				
			}
			
			
			if (in_array($id_offre, $panier)) {
				
				$results["pannied"] = true;
				
			} else {
				
				$results["pannied"] = false;
				
			}
			
			return response()->json($results, 200);
			
		} else {
			return response()->json(['errors' => [0 => 'Offer Not Found']], 404);
		}
	}
	
	
	
	/**
	 * Show the form for editing the specified Offer.
	 */
	// public function editOffre(string $id)
	// {
		
	// }
	
	/**
	 * Update the specified Offer in storage.
	 */
	public function updateOffre(Request $request, string $id)
	{
		$userId = Session::get('user_id');
		$type_compte = Session::get('type_compte');
		
		if ($userId && $type_compte == "Guide") {
			
			$data = $this->database->getReference("Data/Offre/$id")->getValue();
			
			if ($data["id_user"] == $userId) {
				
				try {
					//$data = $request->all();
					
					$rules = [
						'titre' => 'required|string|min:1|max:255',
						'description' => 'required|string|min:1|max:500',
						'city' => 'required|string|min:2|max:50',
						//'image' => 'string',
						'prix' => 'required|integer|min:1|max:600',
					];
					
					$validatedData = $request->validate($rules);
					$data = $validatedData;
					
					// $data = [
					// 'titre' => $validatedData['titre'],
					// 'description' => $validatedData['description'],
					// 'city' => $validatedData['city'],
					// 'image' => $validatedData['image'],
					// 'prix' => $validatedData['prix'],
					// ];
					
					
					$base64_image = $request->input('image');
					
					if ($base64_image) {
					
						$image_patterns = [
							'jpeg' => '/^data:image\/jpeg;base64,/',
							'png' => '/^data:image\/png;base64,/',
							'gif' => '/^data:image\/gif;base64,/',
						];
						
						$image_type = null;
						foreach ($image_patterns as $type => $pattern) {
							if (preg_match($pattern, $base64_image)) {
								$base64_image = preg_replace($pattern, '', $base64_image);
								$image_type = $type;
								break;
							}
						}
						
						if (!$image_type) {
							return response()->json(['message' => 'Invalid image data'], 400);
						}
						
						$image_data = base64_decode($base64_image);
						
						$random_name = uniqid() . '.jpg';
						
						$file_path = __DIR__ . '/../../../public/storage/' . $random_name;
						
						file_put_contents($file_path, $image_data);
						
						$data['image'] = "//morist.mywire.org:3000/storage/".$random_name;
						
					}
					
					$result = $this->database->getReference("Data/Offre/$id")->update($data);
					//$result = $this->database->getReference("Data/Offre/$id")->set($data);
					
					return response()->json($result->getValue(), 201);
					
				} catch (\Exception $e) {
					return response()->json(['errors' => [0 => $e->getMessage()]], 500);
				}
				
			} else {
				return response()->json(['errors' => [0 => 'User not have access']], 403);
			}
			
		} else {
			return response()->json(['errors' => [0 => 'User not logged in']], 401);
		}
	}
	
	
	
	/**
	 * Remove the specified Offer from storage.
	 */
	public function destroyOffre(string $id)
	{
		$userId = Session::get('user_id');
		$type_compte = Session::get('type_compte');
		
		if ($userId && $type_compte == "Guide") {
			$data = $this->database->getReference("Data/Offre/$id")->getValue();
			if ($data["id_user"] == $userId) {
				
				try {
				$result = $this->database->getReference("Data/Offre/$id")->remove();
				
				$starsRef = $this->database->getReference("Data/Star");
				$starsQuery = $starsRef->orderByChild('id_offre')->equalTo($id);
				$starsSnapshot = $starsQuery->getSnapshot();
				
				foreach ($starsSnapshot->getValue() as $starKey => $star) {
					$this->database->getReference("Data/Star/$starKey")->remove();
				}

				return response()->json($result->getValue(), 200);
				
				} catch (\Exception $e) {
					return response()->json(['errors' => [0 => $e->getMessage()]], 500);
				}
				
			} else {
				return response()->json(['errors' => [0 => 'User not have access']], 403);
			}
			
		} else {
			return response()->json(['errors' => [0 => 'User not logged in']], 401);
		}
	}



	
	
	
	/**
	 * Display paginated Offers from storage.
	 */
	public function exploreall(Request $request)
	{
		$panier = Session::get('panier', []);
		$userId = Session::get('user_id');
		
		$page = $request->query('page', 1);
		$limit = $request->query('limit', 10);
		$offset = ($page - 1) * $limit;
		
		$minprice = $request->query('minprice');
		$maxprice = $request->query('maxprice');
		
		$offersReference = $this->database->getReference("Data/Offre");
		
		if (!$minprice AND !$maxprice) {
										  
			$offersReferenceorderBy = $offersReference
										  ->orderByKey()
										  ->limitToFirst($offset + $limit);
										  
		} elseif ($minprice AND $maxprice) {
										  
			$offersReferenceorderBy = $offersReference
										  ->orderByChild('prix')
										  ->startAt($minprice)
										  ->endAt($maxprice)
										  ->limitToFirst($offset + $limit);
		} elseif ($minprice) {
										  
			$offersReferenceorderBy = $offersReference
										  ->orderByChild('prix')
										  ->startAt($minprice)
										  ->endAt("600")
										  ->limitToFirst($offset + $limit);
		} elseif ($maxprice) {
										  
			$offersReferenceorderBy = $offersReference
										  ->orderByChild('prix')
										  ->startAt("1")
										  ->endAt($maxprice)
										  ->limitToFirst($offset + $limit);
		}
		
		$results = $offersReferenceorderBy->getValue();
		$results = array_slice($results, $offset, $limit);
		
		foreach ($results as $index => $item) {
			$id_offre = $item["id"];
			
			$userData = $this->auth->getUser($item["id_user"])->customClaims;
			$results[$index]["prenom"] = $userData["prenom"];
			$results[$index]["nom"] = $userData["nom"];
			
			
			$reference = $this->database->getReference("Data/Star");
			$idQuery = $reference->orderByChild('identify')
								  ->startAt($id_offre . $userId)
								  ->endAt($id_offre . $userId . "\uf8ff");
			
			$results2 = $idQuery->getValue();
			
			if ($results2) {
				
				$results[$index]["starred"] = true;
				
			} else {
			
				$results[$index]["starred"] = false;
				
			}
			
			
			if (in_array($id_offre, $panier)) {
				
				$results[$index]["pannied"] = true;
				
			} else {
				
				$results[$index]["pannied"] = false;
				
			}
		}
		
		return response()->json($results, 200);
	}



	
	
	
	/**
	 * Display all Offers in city from storage.
	 */
	public function explore(Request $request, string $city)
	{
		$panier = Session::get('panier', []);
		$userId = Session::get('user_id');
		//$reference = $this->database->getReference("Data/Offre");
		
		$page = $request->query('page', 1);
		$limit = $request->query('limit', 10);
		$offset = ($page - 1) * $limit;
		
		
		
		$minprice = $request->query('minprice');
		$maxprice = $request->query('maxprice');
		
		$offersReference = $this->database->getReference("Data/Offre")
										  ->orderByChild('city')
										  ->startAt($city)
										  ->endAt($city . "\uf8ff")
										  ->limitToFirst($offset + $limit);
		
		/*if (!$minprice AND !$maxprice) {
										  
			$offersReferenceorderBy = $offersReference;
										  
		} elseif ($minprice AND $maxprice) {
										  
			$offersReferenceorderBy = $offersReference
										  ->orderByChild('prix')
										  ->startAt($minprice)
										  ->endAt($maxprice)
										  ->limitToFirst($offset + $limit);
		} elseif ($minprice) {
										  
			$offersReferenceorderBy = $offersReference
										  ->orderByChild('prix')
										  ->startAt($minprice)
										  ->endAt("600")
										  ->limitToFirst($offset + $limit);
		} elseif ($maxprice) {
										  
			$offersReferenceorderBy = $offersReference
										  ->orderByChild('prix')
										  ->startAt("1")
										  ->endAt($maxprice)
										  ->limitToFirst($offset + $limit);
		}*/
		
		$results = $offersReference->getValue();
		
		if ($minprice !== null) {
			$results = array_filter($results, function ($item) use ($minprice) {
				return $item['prix'] >= $minprice;
			});
		}
		if ($maxprice !== null) {
			$results = array_filter($results, function ($item) use ($maxprice) {
				return $item['prix'] <= $maxprice;
			});
		}
		
		$results = array_slice($results, $offset, $limit);
		
		
		foreach ($results as $index => $item) {
			
			$id_offre = $item["id"];
			
			$userData = $this->auth->getUser($item["id_user"])->customClaims;
			$results[$index]["prenom"] = $userData["prenom"];
			$results[$index]["nom"] = $userData["nom"];
			
			
			$reference = $this->database->getReference("Data/Star");
			$idQuery = $reference->orderByChild('identify')
									->startAt($id_offre.$userId)
									->endAt($id_offre.$userId . "\uf8ff");
			
			$results2 = $idQuery->getValue();
			
			if ($results2) {
				
				$results[$index]["starred"] = true;
				
			} else {
				
				$results[$index]["starred"] = false;
				
			}
			
			
			if (in_array($id_offre, $panier)) {
				
				$results[$index]["pannied"] = true;
				
			} else {
				
				$results[$index]["pannied"] = false;
				
			}
		}
		
		return response()->json($results, 200);
	}
	
	
	
	/**
	 * Display all Offers Searched from storage.
	 */
	public function search(Request $request, string $q)
	{
		$panier = Session::get('panier', []);
		$userId = Session::get('user_id');
		$reference = $this->database->getReference("Data/Offre");
		
		$page = $request->query('page', 1);
		$limit = $request->query('limit', 10);
		$offset = ($page - 1) * $limit;
		
		$titleQuery = $reference->orderByChild('titre')
								->startAt($q)
								->endAt($q . "\uf8ff");
		
		$descriptionQuery = $reference->orderByChild('description')
									  ->startAt($q)
									  ->endAt($q . "\uf8ff")
									  ->limitToFirst($offset + $limit);
		
		$titleResults = $titleQuery->getValue();
		$descriptionResults = $descriptionQuery->getValue();
		
		$results = array_merge($titleResults, $descriptionResults);
		$minprice = $request->query('minprice');
		$maxprice = $request->query('maxprice');
		if ($minprice !== null) {
			$results = array_filter($results, function ($item) use ($minprice) {
				return $item['prix'] >= $minprice;
			});
		}
		if ($maxprice !== null) {
			$results = array_filter($results, function ($item) use ($maxprice) {
				return $item['prix'] <= $maxprice;
			});
		}
	
		$results = array_slice($results, $offset, $limit);
		
		
		
		foreach ($results as $index => $item) {
			
			$id_offre = $item["id"];
			
			$userData = $this->auth->getUser($item["id_user"])->customClaims;
			$results[$index]["prenom"] = $userData["prenom"];
			$results[$index]["nom"] = $userData["nom"];
			
			
			$reference = $this->database->getReference("Data/Star");
			$idQuery = $reference->orderByChild('identify')
								->startAt($id_offre.$userId)
								->endAt($id_offre.$userId . "\uf8ff");
			
			$results2 = $idQuery->getValue();
			
			if ($results2) {
				
				$results[$index]["starred"] = true;
				
			} else {
				
				$results[$index]["starred"] = false;
				
			}
			
			
			if (in_array($id_offre, $panier)) {
				
				$results[$index]["pannied"] = true;
				
			} else {
				
				$results[$index]["pannied"] = false;
				
			}
		}
		
		return response()->json($results, 200);
	}
	
}