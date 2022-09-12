<?php
namespace OCA\AppDirect\NextCloud;

//internal
use OCP\IUser;
use OCP\IUserManager;
use OCP\IGroup;
use OCP\IGroupManager;
use OCP\Group\ISubAdmin;
use OCP\Security\ISecureRandom;
use OCP\Security\Events\GenerateSecurePasswordEvent;
use OCP\EventDispatcher\IEventDispatcher;

//classes from external nextcloud apps
use OCA\GroupQuota\Quota\QuotaManager;
use OCA\Settings\Mailer\NewUserMailHelper;

//external classes
use Psr\Log\LoggerInterface;


class AccountManager {

	private $user;
	private $userManager;
	private $group;
	private $groupManager;
	private $subAdmin;
	private $groupQuotaManager;
	private $newUserMailHelper;
	private $logger;

  //constructors
  public function __construct(IUserManager $userManager,
															IGroupManager $groupManager,
															ISubAdmin $subAdmin,
															QuotaManager $quotaManager,
															IEventDispatcher $eventDispatcher,
															NewUserMailHelper $newUserMailHelper,
															LoggerInterface $logger) {
		$this->user = null;
		$this->userManager = $userManager;
		$this->group = null;
		$this->groupManager = $groupManager;
		$this->subAdmin = $subAdmin;
		$this->groupQuotaManager = $quotaManager;
		$this->eventDispatcher = $eventDispatcher;
		$this->newUserMailHelper = $newUserMailHelper;
		$this->logger = $logger;
	}

	/**
	* Only used for testing. The method behaves similar to the method 'createAccountWithEmail()', but here is no email
	* and no auto generated password.
	* @param string $username: the unique id of the user. Hes the account owner
	* @param string $password: the password of the user
	* @param string $companyName: the company name which the account is linked to.
	* @param integer $groupQuota: the groupquata size in bytes
	* @return string: a string for get the status of specific parameters when succesfull
	*								  the string of the exception message when something went wrong
	*/
  public function createAccount($userName, $password, $companyName, $groupQuota){

		//check for valid parameters
		if($this->isNullOrEmpty($userName) === true){
			return "username is null or empty";
		}
		if($this->isNullOrEmpty($password) === true){
			return "password is null or empty";
		}
		if($this->isNullOrEmpty($companyName) === true){
			return "companyName is null or empty";
		}
		if($this->isNullOrEmpty($groupQuota) === true){
			return "groupQuota is null or empty";
		}


		//get available username and company name. If a name is already taken, a number will added to make it unique
		$availableUsername = $this->getNextAvailableUserName($userName);
		$availableGroupName = $this->getNextAvailableGroupName($companyName);

		if($availableUsername === false || $availableGroupName === false){
			return "couldnt get an available uername or groupname";
		}


		//ceate the account with the given parameters

		//create the user and get the created user as IUser
		$this->user = $this->userManager->createUser($availableUsername, $password);

		//create the Group and get the created Group as IGroup
		$this->group = $this->groupManager->createGroup($availableGroupName);

		//set the groupquota for the created group;
		$this->groupQuotaManager->setGroupQuota($availableGroupName, $groupQuota);

		//assign the created user to the created group
		$this->group->addUser($this->user);

		//assign created user as subadmin to the created group
		$this->subAdmin->createSubAdmin($this->user, $this->group);

		//return string for testing and debug
		$returnMe = "AccountName: ''" . $this->group->getGID() . "' Username: '" . $this->user->getUID();
		return $returnMe;
  }


	/**
	* The method is called when you want to create a new nextcloud account. It creates a new group with a user a subadmin
	* and sets the groupquota for the group. Than it sends an email to give the new user the oppertunity to set his password.
	* There is a simular method for testing purposes. See -> 'public function createAccount(...)'
	* @param string $strSuggestedUserName: the unique id of the user. Hes the account owner
	* @param string $strSuggestedCompanyName: the password of the user
	* @param string $userEmail: the company name which the account is linked to.
	* @param integer $groupQuota: the groupquata size [TODO: IN BYTES?]
	* @return mixed: true on sucess
	*								 string with failure reason on failure.
	*/

	//public function createAccountWithEmail($strSuggestedUserName, $strSuggestedCompanyName, $userEmail, $groupQuota){
	public function createAccountWithEmail($strSuggestedUserName, $strSuggestedCompanyName, $userEmail, $groupQuota, $displayName){

		//check for valid parameters
		if($this->isNullOrEmpty($strSuggestedUserName) === true){
			return "userName is null or empty";
		}
		if($this->isNullOrEmpty($strSuggestedCompanyName) === true){
			return "companyName is null or empty";
		}
		if($this->isNullOrEmpty($userEmail) === true){
			return "userEmail is null or empty";
		}
		if($this->isNullOrEmpty($groupQuota) === true){
			return "groupQuota is null or empty";
		}
		if($this->isNullOrEmpty($displayName) === true){
			return "displayName is null or empty";
		}


		//Create Passwort
		$generatePasswordResetToken = false;

		$passwordEvent = new GenerateSecurePasswordEvent();
		$this->eventDispatcher->dispatchTyped($passwordEvent);

		$password = $passwordEvent->getPassword();
		if ($password === null) {
			// Fallback: ensure to pass password_policy in any case
			$password = $this->secureRandom->generate(10)
				. $this->secureRandom->generate(1, ISecureRandom::CHAR_UPPER)
				. $this->secureRandom->generate(1, ISecureRandom::CHAR_LOWER)
				. $this->secureRandom->generate(1, ISecureRandom::CHAR_DIGITS)
				. $this->secureRandom->generate(1, ISecureRandom::CHAR_SYMBOLS);
		}
		$generatePasswordResetToken = true;



		//Get available Username
		$strAvailableUsername = $this->getNextAvailableUserName($strSuggestedUserName);

		if($strAvailableUsername === false){
			return "Unable to get available userName from suggested name: " . $strSuggestedUserName;
		}


		//createUser and get the created user as IUser
		$this->user = $this->userManager->createUser($strAvailableUsername, $password);

		//set user displayName
		$this->user->setDisplayName($displayName);

		//set User Email
		$this->user->setEMailAddress($userEmail);

		//Get available Groupname
		$availableGroupName = $this->getNextAvailableGroupName($strSuggestedCompanyName);
		if($availableGroupName===false){
			return false;
		}

		//createGroup + get the created Group as IGroup
		$this->group = $this->groupManager->createGroup($availableGroupName);

		//set groupquota for the created group;
		$this->groupQuotaManager->setGroupQuota($availableGroupName, $groupQuota);

		//assign created user to created group
		$this->group->addUser($this->user);

		//assign created user as subadmin in created croup
		$this->subAdmin->createSubAdmin($this->user, $this->group);

		// Send a mail to the user
		$emailTemplate = $this->newUserMailHelper->generateTemplate($this->user, $generatePasswordResetToken);
		$this->newUserMailHelper->sendMail($this->user, $emailTemplate);

		return $this->group->getGID();
  }

    public function startTest(){
        $this->group = $this->groupManager->createGroup("AlexPartyMembers");
        $this->user = $this->userManager->createUser("Twitch-Girl", "pwVeryVerySafe420");
        $this->group->addUser($this->user);
        $arrUserIds = $this->group->getUsers($this->group);
        echo count($arrUserIds);
    }

	/**
	* The method is called when a customer is canceling the contract. His Account will be deleted.
	* @param string $accountName: the acoount name which will be deleted
	* @return mixed: true on sucess
	*								 string with failure reason on failure.
	*/

	public function deleteAccount($accountName){

		//check for valid account name
		if($this->isNullOrEmpty($accountName) === true){
			return "account name is null or empty";
		}

		//check if account exists
		if($this->groupManager->groupExists($accountName) !== true){
			return "group '" . $accountName . "' doesn't exists!";
		}

		//get the account as igroup object from the string accountName
		$this->group = $this->groupManager->get($accountName);

		//get all the users in the group (=account) as an IUser Array
		$arrUserIds = $this->group->getUsers($this->group);

		//delete all Users in the group
		foreach ($arrUserIds as $user) {
			try {
				$user->delete();
			} catch (\Exception $e) {
				return $e->getMessage();
			}
		}

		//delete the group
		try {
			$this->group->delete();
		} catch (\Exception $e) {
			return $e->getMessage();
		}
		return true;
	}



	/**
	* The method is called when a customer's account shall be disabled. It can be enabled later again.
	* It simply disables all users in the given account name.
	* @param string $accountName: the acoount name which will be disabled
	* @return mixed: true on sucess
	*								 string with failure reason on failure.
	*/

	public function disableAccount($accountName){

		//check for valid account name
		if($this->isNullOrEmpty($accountName) === true){
			return "account name is null or empty";
		}

		//check if group (=account) exists
		if(!$this->groupManager->groupExists($accountName)){
			return "group '" . $accountName . "' doesn't exists!";
		}

		//get the account as IGroup object from the string accountName
		$this->group = $this->groupManager->get($accountName);

		//get all the users in the group (=account) as an IUser Array
		$arrUserIds = $this->group->getUsers($this->group);

		//disable all users in group
		foreach ($arrUserIds as $user) {
			$user->setEnabled(false);
		}
		return true;
	}


	/**
	* The method is called when a customer's account shall be enabled after disabling it before with the method 'disableAccount(...)''
	* It simply enables all users in the given account name.
	* @param string $accountName: the acoount name which will be enabled
	* @return mixed: true on sucess
	*								 string with failure reason on failure.
	*/

	public function enableAccount($accountName){

		//check for valid account name
		if($this->isNullOrEmpty($accountName) === true){
			return "account name is null or empty";
		}

		//check if group (=account) exists
		if(!$this->groupManager->groupExists($accountName)){
			return "group '" . $accountName . "' doesn't exists!";
		}

		//get the account as IGroup object from the string accountName
		$this->group = $this->groupManager->get($accountName);

		//get all the users in the group (=account) as an IUser Array
		$arrUserIds = $this->group->getUsers($this->group);

		//enable all Users in the group
		foreach ($arrUserIds as $user) {
			$user->setEnabled(true);
		}
		return true;
	}


	/**
	* checks if an given arguemnt is null or empty.
	* @param argument: a argument that will be checked.
	* @return boolean: returns true when $argument is NULL or an empty string.
	*									 Otherwhise it returns false.
	**/
	public function isNullOrEmpty($argument){

				// Delete this line if you want space(s) to count as not empty
		    $argument = trim($argument);

		    if(isset($argument) === true && $argument === '') {
		        return true;
		    }
		return false;
	}


	//----------------------------------
	//Methods for OCA\Skeleton\Business;
	//----------------------------------

	public function getAllUserGroupsAsStringArray(){
		$arrUserIGroups = $this->groupManager->search("");
		$strArrUserGroups;
		foreach ($arrUserIGroups as $iGroup) {
			$strArrUserGroups[] = $iGroup->getGID();
		}
		return $strArrUserGroups;
	}


	public function getUserCountInGroup($strGroupID){
		return $this->groupManager->get($strGroupID)->count();
	}


	public function getGroupIdAsString($iGroup){
		return $iGroup->getGID();
	}



	/**
	* checks if an username already exists. If thats the case, its adding a number behind the username, until it finds a free username
	* Fore safety reasons, the loop is canceld after 99999 tries.
	* @param string $baseUserName: the username that will be checked.
	* @return mixed: on sucess it returns a username as string that hasnt been taken yet.
	*								 false on failure.
	**/

	private function getNextAvailableUserName($baseUserName){

		if(!$this->userManager->userExists($baseUserName)){
			return $baseUserName;
		}

		$availableUserName = "";

		for ($intAdd = 2; $intAdd <= 99999; $intAdd++) {

			$availableUserName = $baseUserName . $intAdd;
			if (!$this->userManager->userExists($availableUserName)){

				$intAdd <= 99999;
				return $availableUserName;
			}
		}
		return false;
	}


	/**
	* checks if a groupname already exists. If thats the case, its adding a number behind the groupname, until it finds a free groupname
	* Fore safety reasons, the loop is canceld after 99999 tries.
	* @param string $baseGroupName: the groupname that will be checked.
	* @return mixed: on sucess it returns a groupname as string that hasnt been taken yet.
	*								 false on failure.
	**/
	private function getNextAvailableGroupName($baseGroupName){

		if(!$this->groupManager->groupExists($baseGroupName)){
			return $baseGroupName;
		}

		$availableGroupName = "";

		for ($intAdd=2; $intAdd <= 99999; $intAdd++) {

			$availableGroupName = $baseGroupName . " (" . $intAdd . ")";
			if (!$this->groupManager->groupExists($availableGroupName)){

				$intAdd <= 99999;
				return $availableGroupName;
			}
		}
		return false;
	}



	/**
	* changes the groupquata for a given accountname.
	* @param string $groupName: the groupQuota will be changed for this group
	* @param integer mewGroupQuota: sets the new GroupQuota to this value. [TODO: IN BYTES?]
	* @return mixed: on sucess it returns a groupname as string that hasnt been taken yet.
	* false on failure.
	**/
	public function changeAccountGroupQuota($groupName, $newGroupQuota){
		//no downgrade allowed;
		$currentQuota = $this->groupQuotaManager->getGroupQuota($groupName);
		if($currentQuota > $newGroupQuota){
			return "downgrade not allowed";
		}
		try {
			$this->groupQuotaManager->setGroupQuota($groupName, $newGroupQuota);
		} catch (\Exception $e) {
				return $e->getMessage();
		}
		return true;
	}


	/**
	* Just for testing. Deletes all accounts where the groupname contains the string $containsString
	*
	* @param string $containsString: all accounts matching this string will be deleted.
	* @return mixed: true on sucess
	* you get a 'better think before'-message, when you tried to delete every user, including the super admin.... ;-)
	* because in enpty string is matching with ervery existing username.
	* [TODO: exists a more efficent way? Getting all Users as IUser first seems like overkill...? Didnt found better way.
	* its not that big of a deal, causes this method is only for testing.]
	**/
	public function deleteAllUsersContains($containsString){

		if($containsString === ""){
			return "idiot!!!";
		}

		//get all existing users as an array of IUsers
		$allUsers = $this->userManager->search("");


		//if the username is machting the string, delete it.
		foreach ($allUsers as $user) {
			if(str_contains($user->getUID(), $containsString) === true){

				try {
					$user->delete();

				} catch (\Exception $e) {
					return $e->getMessage();
				}
			}
		}
		return true;
	}
}
