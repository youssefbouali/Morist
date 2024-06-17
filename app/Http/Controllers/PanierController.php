<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class PanierController extends FirebaseController
{
	/**
	 * Display basket Offers.
	 */
	public function getPanier(Request $request)
	{
		$userId = Session::get('user_id');
		$panier = Session::get('panier', []);
		
		$page = $request->query('page', 1);
		$limit = $request->query('limit', 10);
		$offset = ($page - 1) * $limit; //0 //10 //20   //0 - 10 // 10 - 20
		
		$jsonArray = [];
		
		
		$panier = array_slice($panier, $offset, $limit);
		
		foreach ($panier as $index => $item) {
			
			$data = $this->database->getReference("Data/Offre/$item")->getValue();
			$jsonArray[$item] = $data;
			
			
			$id_offre = $item;
			
			$reference = $this->database->getReference("Data/Star");
			$idQuery = $reference->orderByChild('identify')
								->startAt($id_offre.$userId)
								->endAt($id_offre.$userId . "\uf8ff");
			
			$results2 = $idQuery->getValue();
			
			if ($results2) {
				
				$jsonArray[$item]["starred"] = true;
				
			} else {
				
				$jsonArray[$item]["starred"] = false;
				
			}
			
			$jsonArray[$item]["pannied"] = true;
		}
		
		return response()->json($jsonArray, 200);
	}
	
	
	
	/**
	 * Add offer to basket.
	 */
	public function postPanier(Request $request)
	{
		$panier = Session::get('panier', []);
		$newItem = $request->input('id_offre');
		
		if (!in_array($newItem, $panier)) {
			
			$data = $this->database->getReference("Data/Offre/$newItem")->getValue();
			
			if($data) {
				try {
					$panier[] = $newItem;
					
					Session::put('panier', $panier);
					
					return response()->json($panier, 201);
					
				} catch (\Exception $e) {
					return response()->json(['errors' => [0 => $e->getMessage()]], 500);
				}
			
			} else {
				return response()->json(['errors' => [0 => "Offer not exist"]], 404);
			}
			
		} else {
			return response()->json(['errors' => [0 => "Offer Already exist in Basket"]], 400);
		}
	}
	
	
	
	/**
	 * Delete offer to basket.
	 */
	public function destroyPanier(Request $request, string $id_offre)
	{
		$panier = Session::get('panier', []);
		
		try {
			foreach ($panier as $index => $item) {
				if ($panier[$index] == $id_offre) {
					unset($panier[$index]);
					$panier = array_values($panier);
					break;
				}
			}
			
			Session::put('panier', $panier);
			
			return response()->json($panier, 200);
			
		} catch (\Exception $e) {
			return response()->json(['errors' => [0 => $e->getMessage()]], 500);
		}
	}
	
}