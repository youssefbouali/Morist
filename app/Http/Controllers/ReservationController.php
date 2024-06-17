<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class ReservationController extends FirebaseController
{
	/**
	 * Display Reservations.
	 */
	public function indexReservation()
	{
		$userId = Session::get('user_id');
		
		if ($userId) {
			$type_compte = Session::get('type_compte');
			if ($type_compte == "Tourist") {
				
				$reference = $this->database->getReference("Data/Reservation");
				$idQuery = $reference->orderByChild('id_user')
										->startAt($userId)
										->endAt($userId . "\uf8ff");
				
				$results = $idQuery->getValue();
				
				
			
				if ($results) {
					foreach ($results as $index => $item) {
						$key = $item["id_offre"];
						$data = $this->database->getReference("Data/Offre/$key")->getValue();
						
						if ($data) {
							$results[$index]["titre"] = $data["titre"];
							
							$dateMin = new \DateTime($item["reservation_date_min"]);
							$dateMax = new \DateTime($item["reservation_date_max"]);

							$interval = $dateMin->diff($dateMax);
							$days = $interval->days + 1;

							$results[$index]["totalprix"] = $data["prix"] * $item["nombre_personnes"] * $days;
							
						} else {
							$results[$index]["titre"] = "Unknown";
							$results[$index]["totalprix"] = 0;
						}
					}
				} else {
					$results = [];
				}
				
				return response()->json($results, 200);
				
			} elseif ($type_compte == "Guide") {
				
				$reference = $this->database->getReference("Data/Reservation");
				$idQuery = $reference->orderByChild('publisher_offre')
										->startAt($userId)
										->endAt($userId . "\uf8ff");
				
				$results = $idQuery->getValue();
				
				if ($results) {
					foreach ($results as $index => $item) {
						$key = $item["id_offre"];
						$data = $this->database->getReference("Data/Offre/$key")->getValue();
						
						if ($data) {
							$results[$index]["titre"] = $data["titre"];
							
							$dateMin = new \DateTime($item["reservation_date_min"]);
							$dateMax = new \DateTime($item["reservation_date_max"]);

							$interval = $dateMin->diff($dateMax);
							$days = $interval->days + 1;

							$results[$index]["totalprix"] = $data["prix"] * $item["nombre_personnes"] * $days;
							
						} else {
							$results[$index]["titre"] = "Unknown";
							$results[$index]["totalprix"] = 0;
						}
					}
				} else {
					$results = [];
				}
				
				return response()->json($results, 200);
			}
			
		} else {
			return response()->json(['errors' => [0 => 'User not logged in']], 401);
		}
	}
	
	
	
	/**
	 * Show the form for creating a new Reservation.
	 */
	// public function createReservation()
	// {
		
	// }
		
	/**
	 * Store a newly created Reservation in storage.
	 */
	public function storeReservation(Request $request)
	{
		
		$id_offre = $request->input('id_offre');
		$userId = Session::get('user_id');
		$type_compte = Session::get('type_compte');
		
		if ($userId && $type_compte == "Tourist") {
			
			$results = $this->database->getReference("Data/Offre/$id_offre")->getValue();
			
			if ($results) {
				
				$publisher_offre = $results["id_user"];
				
				
				try {
					//$data = $request->all();
					
					$rules = [
						'id_offre' => 'required|string|min:1|max:50',
						'nombre_personnes' => 'required|integer|min:1|max:1000000',
						'reservation_date_min' => 'required|date_format:Y-m-d',
						'reservation_date_max' => 'required|date_format:Y-m-d',
					];
					
					$validatedData = $request->validate($rules);
					
					
					
					$reservationDateMin = $validatedData['reservation_date_min'];
					$reservationDateMax = $validatedData['reservation_date_max'];
					$currentDate = date('Y-m-d');
					
					if (strtotime($reservationDateMax) < strtotime($reservationDateMin)) {
						return response()->json(['errors' => [0 => 'The reservation end date must be greater than the start date']], 400);
					}
					
					if (strtotime($reservationDateMin) < strtotime($currentDate)) {
						return response()->json(['errors' => [0 => 'The reservation start date must be a future date']], 400);
					}
					
					$data = $validatedData;
					
					$result = $this->database->getReference('Data/Reservation')->push($data);
					$key = $result->getKey();
					$this->database->getReference("Data/Reservation/$key")->update([
					'id' => $result->getKey(), 'id_user' => $userId, 'publisher_offre' => $publisher_offre, 'etat_confirmation' => "Waiting", 'date' => date('Y-m-d H:i:s') ]);
					
					return response()->json($result->getValue(), 201);
					
				} catch (\Exception $e) {
					return response()->json(['errors' => [0 => $e->getMessage()]], 500);
				}
				
			} else {
				return response()->json(['errors' => [0 => 'Offer Not Found']], 404);
			}
			
		} else {
			return response()->json(['errors' => [0 => 'User not logged in']], 401);
		}
	}
	
	
	
	/**
	 * Display the specified Reservation.
	 */
	public function showReservation(string $id)
	{
		$userId = Session::get('user_id');
		
		if ($userId) {
			$data = $this->database->getReference("Data/Reservation/$id")->getValue();
			
			if ($data) {
				
				return response()->json($data, 200);
				
			} else {
				return response()->json(['errors' => [0 => 'Reservation Not Found']], 404);
			}
			
		} else {
			return response()->json(['errors' => [0 => 'User not logged in']], 401);
		}
	}
	
	
	
	/**
	 * Show the form for editing the specified Reservation.
	 */
	// public function editReservation(string $id_offre)
	// {
		
	// }
		
	/**
	 * Update the specified Reservation in storage.
	 */
	public function updateReservation(Request $request, string $id)
	{
		$userId = Session::get('user_id');
		
		if ($userId) {
			$data = $this->database->getReference("Data/Reservation/$id")->getValue();
			if ($data["id_user"] == $userId OR $data["publisher_offre"] == $userId) {
				
				try {
					//$data = $request->all();
					
					$rules = [
						'etat_confirmation' => 'required|string|in:Confirmed,Canceled',
					];
					
					$validatedData = $request->validate($rules);
					$data = $validatedData;
					
					$result = $this->database->getReference("Data/Reservation/$id")->update($data);
					//$result = $this->database->getReference("Data/Reservation/$id")->set($data);
					
					return response()->json($result->getValue(), 201);
					
				} catch (\Exception $e) {
					return response()->json(['errors' => [0 => $e->getMessage()]], 500);
				}
			}
			
		} else {
			return response()->json(['errors' => [0 => 'User not logged in']], 401);
		}
	}
	
	
	
	/**
	 * Remove the specified Reservation from storage.
	 */
	public function destroyReservation(string $id)
	{
		$userId = Session::get('user_id');
		
		if ($userId) {
			$data = $this->database->getReference("Data/Reservation/$id")->getValue();
			
			if ($data) {
				
				if ($data["id_user"] == $userId OR $data["publisher_offre"] == $userId) {
					
					try {
						$result = $this->database->getReference("Data/Reservation/$id")->remove();
						
						return response()->json($result->getValue(), 200);
						
					} catch (\Exception $e) {
						return response()->json(['errors' => [0 => $e->getMessage()]], 500);
					}
					
				} else {
					return response()->json(['errors' => [0 => 'User not have access']], 403);
				}
			
			} else {
				return response()->json(['errors' => [0 => 'Reservation Not Found']], 404);
			}
			
		} else {
			return response()->json(['errors' => [0 => 'User not logged in']], 401);
		}
	}
	
}