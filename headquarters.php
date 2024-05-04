<?php

require 'vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

use MongoDB\Client as MongoClient;
use MongoDB\BSON\ObjectId as MongoObjectID;

define('CLASS_NONE', 0);

class Headquarters
{
    private $personalNumber; //personal number for session control
    private $db; // MongoDB database instance
    private $classesCollection; // MongoDB collection for classes
    private $soldiersCollection; // MongoDB collection for classes
    private $adminsCollection; // MongoDB collection for admins
    private $used_encrypt;
    private $currently_selected_classId;

    public function __construct($inputNumber)
    {
        // Set $personalNumber to null by default
        $personalNumber = null;

        // Check if $inputNumber is provided and not null
        if ($inputNumber !== null) {
            $personalNumber = $inputNumber;
        }
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
            $this->adminsCollection = $headquarters->adminsCollection;
        }
        // Make sure this is set to 'DB_CONNECT' when deploy!!!
        $dbConnectionString = $_ENV['DB_CONNECT'];
        // Connect to MongoDB
        $mongoClient = new MongoClient($dbConnectionString);
        // select Database in MDB
        $this->db = $mongoClient->selectDatabase('Tactease');
        $this->classesCollection = $this->db->selectCollection('classes');
        $this->soldiersCollection = $this->db->selectCollection('soldiers');
        $this->adminsCollection = $this->db->selectCollection('admins');
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
    public function verifyLogin($inputNumber, $inputPassword)
    {
        $intNumber = (int) $inputNumber;
        $adminsCollection = $this->db->selectCollection('soldiers');
    
        try {
            // Retrieve the admin document based on the input personalNumber
            $admin = $adminsCollection->findOne(['personalNumber' => $intNumber]);
    
            if ($admin && isset($admin['isAdmin']) && $admin['isAdmin'] && password_verify($inputPassword, $admin['password'])) {
                // Password is correct
                return 1;
            } else {
                // Password is incorrect or admin not found
                return 0;
            }
        } catch (\Exception $e) {
            // Handle any exceptions that occur during database operation
            // For example, log the error or display an error message
            error_log('Error verifying login: ' . $e->getMessage());
            return 0;
        }
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
    //get existing classes in the soldiers collection.
    public function getUniqueClasses()
    {
        // Array to store unique depClass combinations
        $uniqueClasses = [];
    
        // Retrieve soldiers collection
        $soldiersCollection = $this->db->selectCollection('soldiers');
    
        // Find distinct depClass combinations
        $distinctClasses = $soldiersCollection->distinct('depClass');
    
        // Iterate through distinct classes and parse classId and className
        foreach ($distinctClasses as $class) {
            if(isset($class['classId']) && isset($class['className'])){
            // Parse classId and className from depClass object
            $classId = $class['classId'];
            $className = $class['className'];
    
            // Check if the combination already exists in uniqueClasses array
            $exists = false;
            foreach ($uniqueClasses as $uniqueClass) {
                if ($uniqueClass['classId'] == $classId && $uniqueClass['className'] == $className) {
                    $exists = true;
                    break;
                }
            }
            
    
            // If the combination doesn't exist, add it to uniqueClasses array
            if (!$exists) {
                $uniqueClasses[] = [
                    'classId' => $classId,
                    'className' => $className
                ];
            }
            }
        }
    
        return $uniqueClasses;
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
        $soldier = $soldiersCollection->findOne(['_id' => $soldierId]);
        $soldierName = $soldier['fullName']; // Assuming 'fullName' field stores the soldier's name
        
        // Call JavaScript function to confirm deletion
        $this->confirmDelete($soldierName, $soldierId);
    }

	//Lock account for given amout of seconds
	private function lockout_seconds($lock_minutes){
		// implement lockout logic;
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

        //$encryptionMethod = $this->used_encrypt;

        // Encrypt the password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Create soldier document
        $soldierDocument = [
            'personalNumber' => intval($personalNumber),
            'fullName' => $fullName,
            'pakal' => $pakal,
            'requestList' => [], // Empty request list for new soldier
            'depClass' => null, // No class assigned initially
            'password' => $hashedPassword // Encrypted password
        ];

        // Insert soldier document into MongoDB collection
        $soldiersCollection = $this->db->selectCollection('soldiers');
        $soldiersCollection->insertOne($soldierDocument);
        echo "Soldier created successfully!\n";
    }
    public function createAdmin($personalNumber, $fullName, $pakal, $password)
    {
    
        // Check if soldier already exists
        if ($this->soldierExists($personalNumber)) {
            echo "Error: Soldier with personal number $personalNumber already exists in the system.\n";
            return;
        }

        // Encrypt the password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Create soldier document
        $adminDocument = [
            'personalNumber' => intval($personalNumber),
            'fullName' => $fullName,
            'pakal' => $pakal,
            'requestList' => [], // Empty request list for new soldier
            'depClass' => null, // No class assigned initially
            'password' => $hashedPassword, // Encrypted password
            'isAdmin' => true
        ];

        // Insert soldier document into MongoDB collection
        $adminsCollection = $this->db->selectCollection('soldiers');
        $adminsCollection->insertOne($adminDocument);
        echo "Admin created successfully!\n";
    }
    // create a class - add to given soldiers
    public function createClass($className, $commanderNumber, $soldiers, $numSoldiers)
    {
        // Generate a unique classId for the class
        $classId = $this->generateUniqueClassId();
    
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
            $depClass = [
                'classId' => $classId,
                'className' => $className
            ];
    
            // Update soldier document in the collection
            $updateResult = $soldiersCollection->updateOne(
                ['personalNumber' => intval($soldierNumber)],
                ['$set' => ['depClass' => $depClass]]
            );
    
            // Check if the update was successful
            if ($updateResult->getModifiedCount() > 0) {
                echo "Class information updated for soldier with personal number $soldierNumber\n";
            } else {
                echo "Error updating class information for soldier with personal number $soldierNumber\n";
            }
        }
    
        echo "Class saved successfully!\n";
    }
    //remove class from soldier
    public function removeClass($removeClassName, $removeClassId)
    {
        // Update the depClass field for each soldier
        $soldiersCollection = $this->db->selectCollection('soldiers');
    
        // Find all soldiers with the specified class to remove
        $soldiersWithClass = $soldiersCollection->find(['depClass.classId' => $removeClassId, 'depClass.className' => $removeClassName]);
    
        foreach ($soldiersWithClass as $soldier) {
            // Update soldier's depClass to null or safe default value
            $updateResult = $soldiersCollection->updateOne(
                ['_id' => $soldier['_id']],
                ['$unset' => ['depClass' => '']]
            );
    
            // Check if the update was successful
            if ($updateResult->getModifiedCount() > 0) {
                echo "Class removed for soldier with personal number {$soldier['personalNumber']}\n";
            } else {
                echo "Error removing class for soldier with personal number {$soldier['personalNumber']}\n";
            }
        }
    
        echo "Class $removeClassName removed from all soldiers successfully!\n";
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

        //Select a class and set it as the currently selected class
        public function selectClass($classId)
        {
            // Set the currently selected class ID
            $this->currently_selected_classId = $classId;

            echo "Class ID $classId selected.\n";
        }

        //Update a class
        public function updateClass($classIdInput, $newName)
        {
            // Convert classIdInput to integer
            $classId = intval($classIdInput);
            
            // Check if the conversion was successful
            if ($classId == 0 && $classIdInput !== '0') {
                echo "Invalid class ID: $classIdInput\n";
                return 0; // Exit the function if the class ID is invalid
            }
        
            // Retrieve the soldiers collection
            $soldiersCollection = $this->db->selectCollection('soldiers');
            
            // Update soldiers with matching classId in their depClass
            $updateResult = $soldiersCollection->updateMany(
                ['depClass.classId' => $classId],
                ['$set' => ['depClass.className' => $newName]]
            );
            
            // Check if any documents were modified
            if ($updateResult->getModifiedCount() > 0) {
                echo "Successfully updated className for soldiers with classId $classId to $newName.\n";
                return 1;
            } else {
                echo "No soldiers found with classId $classId.\n";
                return 0;
            }
        }

        public function deleteClass($classIdInput)
        {
            // Convert classId to integer
            $classId = intval($classIdInput);

            // Check if the conversion was successful
            if ($classId == 0 && $classIdInput !== '0') {
                echo "Invalid class ID: $classIdInput\n";
                return 0; // Exit the function if the class ID is invalid
            }

            // Retrieve the soldiers collection
            $soldiersCollection = $this->db->selectCollection('soldiers');

            // Update soldiers with matching classId by removing the depClass element
            $updateResult = $soldiersCollection->updateMany(
                ['depClass.classId' => $classId],
                ['$unset' => ['depClass' => '']]
            );

            // Check if any documents were modified
            if ($updateResult->getModifiedCount() > 0) {
                echo "Successfully removed class $classId from soldiers.\n";
                return 1;
            } else {
                echo "No soldiers found with classId $classId.\n";
                return 0;
            }
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
            echo "<a href='update_class.php'>Update a Class</a><br>";
            echo "<a href='create_account_admin.php'>Create a New Account - Admin</a><br>";
        }
}

// Example usage:
//$hq = new Headquarters();
//$hq->createClass();

// add "?\>" (without \ ) at the end if file is not .php or there is non-php code in the file.
