<?php

require 'vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

use MongoDB\Client as MongoClient;
use MongoDB\BSON\ObjectId as MongoObjectID;

define('CLASS_NONE', 0);

class Headquarters
{
    private $db; // MongoDB database instance
    private $classesCollection; // MongoDB collection for classes
    private $soldiersCollection; // MongoDB collection for classes
    private $used_encrypt;
    private $currently_selected_classId;

    public function __construct()
    {
        // Start or resume session
        session_start();

        // Check if Headquarters object is stored in session
        if (!isset($_SESSION['headquarters'])) {
            // If not, create a new Headquarters object and store it in session
            $_SESSION['headquarters'] = $this;
        } else {
            // If already exists, retrieve the existing Headquarters object from session
            $headquarters = $_SESSION['headquarters'];
            // Copy properties to this instance
            $this->db = $headquarters->db;
            $this->classesCollection = $headquarters->classesCollection;
            $this->soldiersCollection = $headquarters->soldiersCollection;
        }
        $dbConnectionString = $_ENV['DB_CONNECT'];
        // Connect to MongoDB (REPLACE with link to actual DB)
        $mongoClient = new MongoClient($dbConnectionString);
        // select Database in MDB
        $this->db = $mongoClient->selectDatabase('Tactease');
        $this->classesCollection = $this->db->selectCollection('classes');
        $this->soldiersCollection = $this->db->selectCollection('soldiers');
        $this->currently_selected_classId = CLASS_NONE;
        // GLOBAL ENCRYPTION METHOD - select encryption method to be used everywhere here
        // When global encryption method is decided - implement it in encrypt function and put its name here.
        $this->used_encrypt = 'md5';
    }

    // SERVICE FUNCTIONS - if service function is not here, it's right under related main function.
    //check if soldier exists in the system
    public function soldierExists($soldierNumber)
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
		//$encryptionMethod = readline("Enter encryption method (e.g., md5, sha256): ");
		// use global method set in constructor - set to global method when it is determined
        //currently set to md5 (see above, in __construct() function)
		$encryptionMethod = $this->used_encrypt;
		
		// Encrypt the password
		// Encrypt the input password using the same method as stored password
		$encryptedInputPassword = $this->encrypt($inputPassword, $encryptionMethod);
	
		// Compare hashed or encrypted passwords
		return $encryptedInputPassword === $storedPassword;
	}
    #Generate ID for class
    private function generateUniqueClassId()
    {
    $classId = null;
    $existingClassIds = []; // Array to store existing classIds from the database

    // Fetch existing classIds from the database
    $existingClasses = $this->classesCollection->find([], ['projection' => ['classId' => 1]]);
    foreach ($existingClasses as $existingClass) {
        $existingClassIds[] = $existingClass['classId'];
    }

    // Generate a unique classId
    do {
        // Generate a random integer between 10000 and 99999
        $classId = mt_rand(1, 99999);
    } while (in_array($classId, $existingClassIds)); // Check if the generated ID already exists

    return $classId;
    }
    #get existing classes in the collection.
    public function getExistingClasses()
    {
    // Select the classes collection from the MongoDB database
    $classesCollection = $this->classesCollection;
    
    // Retrieve all documents (classes) from the collection
    $classesCursor = $classesCollection->find();

    // Initialize an array to store the existing classes
    $existingClasses = [];

    // Iterate over the cursor to extract class information
    foreach ($classesCursor as $class) {
        // Extract class ID and name from the document
        $classId = $class['classId'];
        $className = $class['name'];

        // Add class information to the array
        $existingClasses[] = [
            'classId' => $classId,
            'className' => $className
        ];
    }

    // Return the array of existing classes
    return $existingClasses;
    }

    public function getExistingSoldiers()
    {
    // Select the classes collection from the MongoDB database
    $soldiersCollection = $this->soldiersCollection;
    
    // Retrieve all documents (classes) from the collection
    $solCursor = $soldiersCollection->find();

    // Initialize an array to store the existing classes
    $existingSoldiers = [];

    // Iterate over the cursor to extract class information
    foreach ($solCursor as $soldier) {
        // Extract class ID and name from the document
        $personalNumber = $soldier['personalNumber'];
        $fullName = $soldier['fullName'];
        if (isset($soldier['depClass']) && isset($soldier['depClass']['classId'])){
        $classId = $soldier['depClass']['classId'];
        }
        else $classId = 0;
        // Add class information to the array
        $existingSoldiers[] = [
            'personalNumber' => $personalNumber,
            'fullName' => $fullName,
            'classId' => $classId
        ];
    }

    // Return the array of existing classes
    return $existingSoldiers;
    }

    // DELETE SOLDIER via direct input and popup scripts
    private function confirmDelete($soldierName, $soldierId) {
         //script to show a popup window with "are you sure..." and then info depending on actions done.
        echo "<script>";
        echo "var confirmation = confirm('Are you sure you want to delete soldier named $soldierName?');";
        echo "if (confirmation) {";
        echo "  window.location.href = 'delete_soldier.php?soldierId=$soldierId';";
        echo "} else {";
        echo "  alert('The soldier named $soldierName was NOT deleted.');";
        echo "}";
        echo "</script>";
    }
    
    public function deleteSoldierFromCollection($soldierId)
    {
        $soldiersCollection = $this->db->selectCollection('soldiers');
        
        // Retrieve soldier's name based on _id
        $soldier = $soldiersCollection->findOne(['_id' => new MongoObjectID($soldierId)]);
        $soldierName = $soldier['fullName']; // Assuming 'fullName' field stores the soldier's name
        
        // Call JavaScript function to confirm deletion
        $this->confirmDelete($soldierName, $soldierId);
    }

    // Function to DELETE soldier document from MongoDB collection
    // private function deleteSoldierFromCollection($soldierId)
    // {
    //     $soldiersCollection = $this->db->selectCollection('soldiers');
        
    //     // Retrieve soldier's name based on _id
    //     $soldier = $soldiersCollection->findOne(['_id' => new MongoObjectID($soldierId)]);
    //     $soldierName = $soldier['fullName']; // Assuming 'fullName' field stores the soldier's name
        
    //     // Confirm deletion
    //     echo "Are you sure want to delete soldier named $soldierName? (yes/no): ";
    //     $confirmation = strtolower(trim(readline()));

    //     // Perform deletion if confirmed
    //     if ($confirmation === 'yes') {
    //         $result = $soldiersCollection->deleteOne(['_id' => new MongoObjectID($soldierId)]);
    //         if ($result->getDeletedCount() === 1) {
    //             echo "Soldier deleted successfully!\n";
    //         } else {
    //             echo "Failed to delete soldier.\n";
    //         }
    //     } else {
    //         echo "The soldier named $soldierName was NOT deleted.\n";
    //     }
    // }

	//Lock account for given amout of seconds
	private function lockout_seconds($lock_minutes){
		//TODO implement lockout logic;
	}
    
    // MAIN FUNCTIONS
    //create a new soldier
    public function createSoldier($personalNumber, $fullName, $pakal, $password)
    {
        $soldiersCollection = $this->db->selectCollection('soldiers');
    
    
        // Check if soldier already exists
        if ($this->soldierExists($personalNumber)) {
            echo "Error: Soldier with personal number $personalNumber already exists in the system.\n";
            return;
        }
    
        // Ask for soldier's full name and pakal
        #$fullName = readline("Enter soldier's full name: ");
        #$pakal = readline("Enter soldier's pakal: ");
    
        // Ask for the password
        #$rawPassword = readline("Enter soldier's password: ");
        // Ask for encryption method
        #$encryptionMethod = readline("Enter encryption method (e.g., md5, sha256): ");
        // OPTIONAL - use global method set in constructor - use when global method will be determined
        $encryptionMethod = $this->used_encrypt;

        // Encrypt the password
        $encryptedPassword = $this->encrypt($password, $encryptionMethod);

        // Create soldier document
        $soldierDocument = [
            'personalNumber' => intval($personalNumber),
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
    #create a class with soldiers
    public function createClass($className, $commanderNumber, $soldiers, $numSoldiers)
    {
        // Generate a unique classId for the class
        $classId = $this->generateUniqueClassId();
    
        // Save the class to the MongoDB collection
        $this->classesCollection->insertOne([
            'classId' => $classId,
            'name' => $className,
            'commander_number' => $commanderNumber,
            'soldiers' => $soldiers
        ]);
    
        // Update the depClass field for each soldier
        $soldiersCollection = $this->db->selectCollection('soldiers');
    
        foreach ($soldiers as $soldierNumber) {
            // Find soldier by personal number
            $soldier = $soldiersCollection->findOne(['personalNumber' => intval($soldierNumber)]);
    
            // If soldier not found, skip to the next one
            if (!$soldier) {
                echo "Error: Soldier with personal number $soldierNumber not found.\n";
                continue;
            }
    
            // Update soldier's class information
            $soldier['depClass'] = [
                'classId' => $classId,
                'className' => $className
            ];
    
            // Update soldier document in the collection
            $soldiersCollection->replaceOne(['_id' => $soldier['_id']], $soldier);
        }

        $commander = $soldiersCollection->findOne(['commander' => intval($soldierNumber)]);
    
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
                // Ask for the new full name
                $newFullName = readline("Enter new full name: ");
        
                // Check if the input for new full name is empty
                if (!empty($newFullName)) {
                    // Perform update: Update soldier's fullName
                    $soldier['fullName'] = $newFullName;
                    
                    // Update soldier document in the MongoDB collection
                    $soldiersCollection = $this->db->selectCollection('soldiers');
                    $result = $soldiersCollection->replaceOne(['_id' => $soldier['_id']], $soldier);
        
                    // Notify user about the update
                    if ($result->getModifiedCount() === 1) {
                        echo "Account updated successfully!\n";
                    } else {
                        echo "Failed to update account.\n";
                    }
                } else {
                    echo "Warning: Input for new full name is empty. The name will not be changed.\n";
                }
            } elseif ($attempts_left > 0) {
                echo "Incorrect password. You have $attempts_left more tries.\n";
                $attempts_left = $attempts_left - 1;
            } else {
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

        //Select a class (for the ShowMainPage function)
        public function selectClass($classId)
        {
            // Ask the user to input the class ID
            //$classId = readline("Enter the class ID: ");
    
            // Set the currently selected class ID
            $this->currently_selected_classId = $classId;
    
            echo "Class ID $classId selected.\n";
        }

        //Show main page
        public function showMainPage()
        {
            echo "<br>";
            echo "<a href='select_class.php'>Select Class</a><br>";
            $selectClassId = $this->currently_selected_classId;
        
            // Check if a class is selected
            if ($selectClassId === CLASS_NONE) {
                echo "No class selected.";
                //return;
            } else {
                // Retrieve soldiers from the currently selected class
                $soldiersCursor = $this->getExistingSoldiers();
        
                // Initialize a flag to track if soldiers were found
                $soldiersFound = false;
        
                // Display soldiers' information in a table
                echo "<table>";
                echo "<tr><th>Personal Number</th><th>Name</th></tr>";
                foreach ($soldiersCursor as $soldier) {
                    if($soldier['classId'] == $selectClassId){
                    echo "<tr>";
                    echo "<td>{$soldier['personalNumber']}</td>";
                    echo "<td>{$soldier['fullName']}</td>";
                    echo "<td>{$soldier['classId']}</td>";
                    echo "</tr>";
                    $soldiersFound = true; // Set the flag to true if at least one soldier is found
                    }
                }
                echo "</table>";
        
                // Check if soldiers were found
                if (!$soldiersFound) {
                    echo "No soldiers within the selected class found.";
                }
            }
        
            // Links to create a class, create a new account, and update a class
            echo "<br>";
            echo "<a href='create_class.php'>Create a Class</a><br>";
            echo "<a href='create_account.php'>Create a New Account</a><br>";
            echo "<a href='update_class_get.php'>Update a Class</a><br>";
        }
}

// Example usage:
//$hq = new Headquarters();
//$hq->createClass();

// add "?\>" (without \ ) at the end if file is not .php or there is non-php code in the file.
