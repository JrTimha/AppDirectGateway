<?php
namespace OCA\AppDirect\Database;

use OCP\AppFramework\Db\Entity;

class QueueEntity extends Entity {

    //Variables----------------------|
    //protected $id is already included as default attribute from nextcloud, autoincrement starts with 0
    protected $eventurl;                //Used to get assignment details as string
    protected $inprogress;              //Boolean false = nicht in Bearbeitung | true = in Bearbeitung
    protected $time;                    // unix timestamp@insert as string
    //Variables----------------------|


    public function __construct() {
        // add types in constructor
        $this->addType('time', 'string');
        $this->addType('eventurl', 'string');
    }

}
