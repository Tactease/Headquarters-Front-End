<?php

require 'vendor/autoload.php';

use MongoDB\Client as MongoClient;

class Headquarters
{
    private $db; // MongoDB database instance
    private $classesCollection; // MongoDB collection for classes

    public function __construct()
    {
        // Connect to MongoDB (REPLACE with link to actual DB)
        $mongoClient = new MongoClient("mongodb://localhost:27017");
        // select Database in MDB
        $this->db = $mongoClient->selectDatabase('headquarters');
        $this->classesCollection = $this->db->selectCollection('classes');
    }

    //check if soldier exists in the system
    private function soldierExists($soldierNumber)
    {
        $soldiersCollection = $this->db->selectCollection('soldiers');
    
        // Search for soldier with given personal number
        $soldier = $soldiersCollection->findOne(['personalNumber' => intval($soldierNumber)]);
    
        return $soldier !== null; // Return true if soldier exists, false otherwise
    }
    
    //create a new soldier
    public function createSoldier()
    {
        $soldiersCollection = $this->db->selectCollection('soldiers');
    
        // Ask for soldier's personal number
        $soldierNumber = readline("Enter soldier's personal number: ");
    
        // Check if soldier already exists
        if ($this->soldierExists($soldierNumber)) {
            echo "Error: Soldier with personal number $soldierNumber already exists in the system.\n";
            return;
        }
    
        // Ask for soldier's full name and pakal
        $fullName = readline("Enter soldier's full name: ");
        $pakal = readline("Enter soldier's pakal: ");
    
        // Create soldier document
        $soldierDocument = [
            'personalNumber' => intval($soldierNumber),
            'fullName' => $fullName,
            'pakal' => $pakal,
            'requestList' => [], // Empty request list for new soldier
            'depClass' => null, // No class assigned initially
            'password' => null // No password initially
        ];
    
        // Insert soldier document into MongoDB collection
        $soldiersCollection->insertOne($soldierDocument);
        echo "Soldier created successfully!\n";
    }
    //todo similar function for commander

    public function createClass()
    {
        // Ask for data
        $className = readline("Enter the class name: ");
        $commanderNumber = readline("Enter the commander's personal number: ");
        $numSoldiers = intval(readline("Enter the number of soldiers (1 to 65): "));

        // Array to store soldiers of the class
        $soldiers = [];

        // Loop to add soldiers to the class
        for ($i = 0; $i < $numSoldiers; $i++) {
            // Ask for soldier's personal number
            $soldierNumber = readline("Enter soldier's personal number: ");

            // Check if soldier exists
            if (!$this->soldierExists($soldierNumber)) {
                echo "Error: Soldier with personal number $soldierNumber doesn't exist in the system.\n";
                continue;
            }

            // If soldier exists, add to the class
            $soldiers[] = $soldierNumber;
        }

        // Save the class to the MongoDB collection
        $this->classesCollection->insertOne([
            'name' => $className,
            'commander_number' => $commanderNumber,
            'soldiers' => $soldiers
        ]);
        echo "Class saved successfully!\n";
    }
}

// Example usage:
$hq = new Headquarters();
$hq->createClass();

// add "?\>" (without \ ) at the end if file is not .php
