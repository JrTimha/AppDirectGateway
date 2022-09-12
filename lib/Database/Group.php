<?php
namespace OCA\AppDirect\Database;

use JsonSerializable;
use OCP\AppFramework\Db\Entity;

class Group extends Entity implements JsonSerializable {

    //Variables----------------------|
    //protected $id is already included as default attribute from nextcloud, autoincrement starts with 0
    protected $group;               //Unic group name
    protected $admin;               //Group Admin name
    protected $billingdate;         //Date of the new subscription month
    protected $editioncode;         //Subsription model S, M, L...
    protected $usercount;           //Limit of the allowed users, depends on subscription
    protected $storage;             //Storage limitation
    protected $inprocessing;        //True if BillingDate is currently updating
    protected $creationdate;         //exact time where the group was created
    //Variables----------------------|


    public function __construct() {
        $this->addType('id','integer');
        $this->addType('billingdate','integer');
        $this->addType('usercount','integer');
        $this->addType('storage','integer');
    }

    /**
     * Description: Returning the Group Entity as a JSON
     */
    public function jsonSerialize(): array {
        return [
            'group' => $this->group,
            'admin' => $this->admin,
            'billingdate' => $this->billingdate,
            'creationdate' => $this->creationdate,
            'editioncode' => $this->editioncode,
            'usercount' => $this->usercount,
            'storage' => $this->storage,
            'inprocessing' => $this->inprocessing
        ];
    }
}
