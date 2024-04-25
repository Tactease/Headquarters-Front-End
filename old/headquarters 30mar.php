<?php

require 'vendor/autoload.php';

use MongoDB\Client as MongoClient;

class Headquarters
{
    private $db; // MongoDB database instance
    private $classesCollection; // MongoDB collection for classes
    private $soldiersCollection; // MongoDB collection for classes
    private $used_encrypt;

    public function __construct()
    {
        // Connect to MongoDB (REPLACE with link to actual DB)
        $mongoClient = new MongoClient("mongodb://localhost:27017/tactease_test1");
        // select Database in MDB
        $this->db = $mongoClient->selectDatabase('headquarters');
        $this->classesCollection = $this->db->selectCollection('classes');
        $this->soldiersCollection = $this->db->selectCollection('soldiers');

        // GLOBAL ENCRYPTION METHOD - select encryption method to be used everywhere here
        // When global encryption method is decided - implement it in encrypt function and put its name here.
        $this->used_encrypt = 'md5';
    }

    // SERVICE FUNCTIONS - if service function is not here, it's right under related main function.
    //check if soldier exists in the system
    private function soldierExists($soldierNumber)
    {
        $soldiersCollection = $this->db->selectCollection('soldiers');
    
        // Search for soldier with given personal number
        $soldier = $soldiersCollection->findOne(['personalNumber' => intval($soldierNumber)]);
    
        return $soldier !== null; // Return true if soldier exists, false otherwise
    }

    // Function to encrypt a password (may be used for other strings as well)
    private function encrypt($password, $method)
    {
        switch ($method) {
            case 'md5':
                return md5($password); // Example: MD5 encryption
            case 'sha256':
                return hash('sha256', $password); // Example: SHA-256 encryption
            // Add more encryption methods as needed
            default:
                throw new InvalidArgumentException("Unsupported encryption method: $method");
        }
    }

	// Function to verify password (Compare hashed/encrypted password)
	private function verifyPassword($inputPassword, $storedPassword)
	{
		// ENCRYPTION METHOD MUST BE SAME AS FOR SOLDIER'S PASSWORD ENCRYPTION
		$encryptionMethod = readline("Enter encryption method (e.g., md5, sha256): ");
		// OPTIONAL - use global method set in constructor - use when global method is determined
		//$encryptionMethod = $this->used_encrypt;
		
		// Encrypt the password
		// Encrypt the input password using the same method as stored password
		$encryptedInputPassword = $this->encrypt($inputPassword, $encryptionMethod);
	
		// Compare hashed or encrypted passwords
		return $encryptedInputPassword === $storedPassword;
	}

    //update class for soldier
    private function updateSoldierClass($soldierNumber, $classId, $className)
    {
        // Update soldier's document in MongoDB collection
        $soldiersCollection = $this->db->selectCollection('soldiers');
        
        // Find soldier by personal number
        $soldier = $soldiersCollection->findOne(['personalNumber' => intval($soldierNumber)]);
        
        // If soldier not found, return
        if (!$soldier) {
            echo "Error: Soldier with personal number $soldierNumber not found.\n";
            return;
        }

        // Update soldier's class information
        $soldier['depClass'] = [
            'classId' => intval($classId),
            'className' => $className
        ];

        // Update soldier document in the collection
        $soldiersCollection->replaceOne(['_id' => $soldier['_id']], $soldier);

        echo "Soldier's class updated successfully!\n";
    }

    // Function to DELETE soldier document from MongoDB collection
    private function deleteSoldierFromCollection($soldierId)
    {
        $soldiersCollection = $this->db->selectCollection('soldiers');
        
        // Retrieve soldier's name based on _id
        $soldier = $soldiersCollection->findOne(['_id' => new MongoDB\BSON\ObjectID($soldierId)]);
        $soldierName = $soldier['fullName']; // Assuming 'fullName' field stores the soldier's name
        
        // Confirm deletion
        echo "Are you sure want to delete soldier named $soldierName? (yes/no): ";
        $confirmation = strtolower(trim(readline()));

        // Perform deletion if confirmed
        if ($confirmation === 'yes') {
            $result = $soldiersCollection->deleteOne(['_id' => new MongoDB\BSON\ObjectID($soldierId)]);
            if ($result->getDeletedCount() === 1) {
                echo "Soldier deleted successfully!\n";
            } else {
                echo "Failed to delete soldier.\n";
            }
        } else {
            echo "The soldier named $soldierName was NOT deleted.\n";
        }
    }

	//Lock account for given amout of seconds
	private function lockout_seconds($lock_minutes){
		//TODO implement lockout logic;
	}
    
    // MAIN FUNCTIONS
    //create a new soldier
    public function createSoldier() //create account
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
    
        // Ask for the password
        $rawPassword = readline("Enter soldier's password: ");
        // Ask for encryption method
        $encryptionMethod = readline("Enter encryption method (e.g., md5, sha256): ");
        // OPTIONAL - use global method set in constructor - use when global method will be determined
        //$encryptionMethod = $this->used_encrypt;

        // Encrypt the password
        $encryptedPassword = $this->encrypt($rawPassword, $encryptionMethod);

        // Create soldier document
        $soldierDocument = [
            'personalNumber' => intval($soldierNumber),
            'fullName' => $fullName,
            'pakal' => $pakal,
            'requestList' => [], // Empty request list for new soldier
            'depClass' => null, // No class assigned initially
            'password' => $encryptedPassword // Encrypted password
        ];

        // Insert soldier document into MongoDB collection
        $soldiersCollection = $this->db->selectCollection('soldiers');
        $soldiersCollection->insertOne($soldierDocument);
        echo "Soldier created successfully!\n";
    }

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

    public function removeClass()
    {
        // Ask for class name
        $className = readline("Enter the class name: ");

        // Search for the class
        $class = $this->classesCollection->findOne(['name' => $className]);

        // If class doesn't exist, ask for input again
        if (!$class) {
            echo "Error: Class '$className' not found.\n";
            return;
        }

        // Ask for commander's personal number
        $commanderNumber = readline("Enter the commander's personal number: ");

        // If commander's personal number doesn't match, ask for input again
        if ($class['commander_number'] !== $commanderNumber) {
            echo "Error: Commander's personal number does not match.\n";
            return;
        }

        // Check if all soldiers in the class exist
        foreach ($class['soldiers'] as $soldierNumber) {
            if (!$this->soldierExists($soldierNumber)) {
                echo "Error: Soldier with personal number $soldierNumber doesn't exist in the system.\n";
                return;
            }
        }

        // Confirm class removal
        $confirmation = readline("Are you sure want to remove class '$className'? (yes/no): ");
        if ($confirmation !== 'yes') {
            echo "Class removal aborted.\n";
            return;
        }

        // Remove class from soldiers' data
        foreach ($class['soldiers'] as $soldierNumber) {
            $this->updateSoldierClass($soldierNumber, null, null);
        }

        // Remove class from the DB
        $this->classesCollection->deleteOne(['name' => $className]);
        echo "Class '$className' removed successfully!\n";
    	}

        // Update or delete soldier's account
        public function updateAccount()
        {
            // Ask for soldier's personal number
            $soldierNumber = readline("Enter soldier's personal number: ");
    
            // Find soldier by personal number
            $soldiersCollection = $this->db->selectCollection('soldiers');
            $soldier = $soldiersCollection->findOne(['personalNumber' => intval($soldierNumber)]);
    
            // If soldier not found, display error message
            if (!$soldier) {
                echo "Error: Soldier with personal number $soldierNumber not found.\n";
                return;
            }
    
            // Display soldier's information
            echo "Soldier Information:\n";
            echo "Personal Number: {$soldier['personalNumber']}\n";
            echo "Full Name: {$soldier['fullName']}\n";
            echo "Pakal: {$soldier['pakal']}\n";
    
            // Prompt user for action (update or delete)
            $action = readline("Choose an action (update/delete): ");
    
            // Perform action based on user input
            switch ($action) {
                case 'update':
                    $this->updateSoldier($soldier); //see below
                    break;
                case 'delete':
                    $this->deleteSoldier($soldier); //see below
                    break;
                default:
                    echo "Invalid action.\n";
                    break;
            }
        }
    
        // Function to update soldier's account
        private function updateSoldier($soldier)
        {
            // Ask for password to permit the change
            $password = readline("Enter password to permit the change: ");

			$attempts_left = 3;
    
            // Verify password (Example: compare with stored hashed password)
            if ($this->verifyPassword($password, $soldier['password'])) {
                // Perform update (Example: Update soldier's information)
                // Update logic goes here...
    
                echo "Account updated successfully!\n";
            } 
			elseif($attempts_left > 0) {
                echo "Incorrect password. You have $attempts_left more tries.\n";
                $attempts_left = $attempts_left-1;
            }
			else{
				echo "Incorrect password. No more tries left. Your account has been locked for an hour.\n";
				$this->lockout_seconds(3600);
			}
        }
    
        //Function to delete soldier's account
        private function deleteSoldier($soldier)
        {
            // Ask for password to delete the account
            $password = readline("Enter password to delete the account: ");

			$attempts_left = 3;			
    
            // Verify password (Example: compare with stored hashed password)
            if ($this->verifyPassword($password, $soldier['password'])) {
                // Perform deletion (Example: Remove soldier from database)
                $this->deleteSoldierFromCollection($soldier['_id']);
    
                echo "Account deleted successfully!\n";
            }
			elseif($attempts_left > 0) {
                echo "Incorrect password. You have $attempts_left more tries.\n";
                $attempts_left = $attempts_left-1;
            }
			else{
				echo "Incorrect password. No more tries left. Your account has been locked for an hour.\n";
				$this->lockout_seconds(3600);
			}
        }

		//Help recover account by generating and updating a new password
		public function recoverAccount()
		{
			// Ask for soldier's personal number
			$soldierNumber = readline("Enter soldier's personal number: ");

			// Find soldier by personal number
			$soldiersCollection = $this->db->selectCollection('soldiers');
			$soldier = $soldiersCollection->findOne(['personalNumber' => intval($soldierNumber)]);

			// If soldier not found, display error message
			if (!$soldier) {
				echo "Error: Soldier with personal number $soldierNumber not found.\n";
				return;
			}

			// Generate new password
			$newPassword = $this->generatePassword();

			// Update soldier's document with new password
			$soldier['password'] = $this->encrypt($newPassword, $this->used_encrypt);
			$result = $soldiersCollection->replaceOne(['_id' => $soldier['_id']], $soldier);

			// Notify user about password update
			if ($result->getModifiedCount() === 1) {
				echo "New password generated and updated successfully!\n";
				echo "New Password: $newPassword\n";
			} else {
				echo "Failed to update password.\n";
			}
		}

	    // Function to generate a new random password
		private function generatePassword()
		{
			// Generate a new random password
			$length = 10; // Set the desired length of the password
			// character set for password.
			$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
			$password = '';
			for ($i = 0; $i < $length; $i++) {
				$password .= $characters[rand(0, strlen($characters) - 1)];
			}
			return $password;
		}
}

// Example usage:
//$hq = new Headquarters();
//$hq->createClass();

// add "?\>" (without \ ) at the end if file is not .php or there is non-php code in the file.
