<?php
namespace OCA\AppDirect\Service;
use OCA\AppDirect\Database\GroupMapper;
use OCA\AppDirect\Database\Group;

class DBGroupService
{
    //Variables----------------------|
    private GroupMapper $groupMapper;
    //Variables----------------------|

    /** @Param GroupMapper $groupMapper | Used to interact with the group database
     */
    public function __construct(GroupMapper $groupMapper)
    {
        $this->groupMapper = $groupMapper;
    }

    /** @Param $groupname, @Param $admin, @Param $editionCode, @Param $userCount, @Param $storage -> Used to create a group
     * Description: Creating a new Group and inserting this Entity to the database
     */
    public function createGroup(string $groupname, string $admin, string $editionCode, int $userCount, int $storage){
        $curTime = time();
        $curTime -= ($curTime % 86400); // 24 * 60 * 60 = 86400
        $billingDate = $this->groupMapper->calcBilling($curTime, $curTime);
        $group = new Group();
        $group->setGroup($groupname);
        $group->setAdmin($admin);
        $group->setBillingdate($billingDate);
        $group->setCreationdate($curTime);
        $group->setEditioncode($editionCode);
        $group->setUsercount($userCount);
        $group->setStorage($storage);
        $group->setInprocessing(0);
        $this->groupMapper->insert($group);
    }

    /** @Param Group $entity
     * Description: Deleting the given Entity
     */
    public function deleteGroup(Group $entity){
        $this->groupMapper->delete($entity);
    }

    /** @Param Group $entity
     * Description: Updating the attributes of the given Entity
     */
    public function updateGroup(Group $entity){
        $this->groupMapper->update($entity);
    }

    /**
     * Description: Returns a Group Entity with an billing date that is expiring within the next 3 days
     */
    public function getExpiring(){
        return $this->groupMapper->findExpiring();
    }

    /** @Param string $group
     * Description: Returns a Group Entity that is found with the given string.
     */
    public function getGroup(string $group){
        return $this->groupMapper->findGroup($group);
    }

    /** @Param Group $grp
     * Description: Updating the billing date of the given group entity to the next monthly cycle
     */
    public function updateBillingInformation(Group $grp){
        $this->groupMapper->updateBilling($grp);
    }
}
