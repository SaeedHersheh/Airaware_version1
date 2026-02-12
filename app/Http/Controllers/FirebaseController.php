<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Database;

class FirebaseController extends Controller
{
    protected $database;

    public function __construct()
    {
        $factory = (new Factory)
        ->withServiceAccount(storage_path('app/firebase/firebase_credentials.json'))
        ->withDatabaseUri('https://fir-crudsql-default-rtdb.firebaseio.com/');

        $this->database = $factory->createDatabase();
    }



    public function saveData($node,$data)
    {
        return $this->database->getReference($node)
        ->push($data);
    }


    public function getData($node)
    {
        return $this->database->getReference($node)->getValue();
    }

    public function getNewestData($node)
    {
        // orderByKey sorts by the unique IDs Firebase creates
    // limitToLast(1) tells Firebase to only send back the very last item
    return $this->database->getReference($node)
        ->orderByKey()
        ->limitToLast(1)
        ->getValue();
    }


    public function deleteData($node,$key)
    {
        return $this->database->getReference("$node/$key")->remove();
    }

public function testConnection()
{
    try {
        // Try reading the root node
        $data = $this->database->getReference('/')->getValue();

        if ($data) {
            return response()->json([
                'status' => 'success',
                'message' => 'Firebase connection works!',
                'data' => $data
            ]);
        } else {
            return response()->json([
                'status' => 'success',
                'message' => 'Firebase connection works, but no data yet.'
            ]);
        }
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Firebase connection failed: ' . $e->getMessage()
        ]);
    }
}



public function getLatestCitiesData($node, $limit = 10)
{
    return $this->database->getReference($node)
    ->orderByKey()
    ->limitToLast($limit)
    ->getValue();
}

}
