<?php
namespace OCA\AppDirect\Service;
use OCA\AppDirect\Database\QueueMapper;
use OCA\AppDirect\Database\QueueEntity;

class DBQueueService
{
    //Variables----------------------|
    private QueueMapper $queueMapper;
    //Variables----------------------|

    /** @Param QueueMapper $queueMapper | Used to interact with the Queue database
     */
    public function __construct(QueueMapper $queueMapper)
    {
        $this->queueMapper = $queueMapper;
    }

    /** @Param string $event | An eventURL
     * Description: Creating a new Queue Entitiy and inserting it in the Queue Database
     */
    public function pushAssignment($event){
        $assignment = new QueueEntity();
        $assignment->setEventurl($event);
        $assignment->setInprogress(0);
        $assignment->setTime(time());
        $this->queueMapper->insert($assignment);
    }

    /**
     * Description: Returning the oldest Queue Entity.
     */
    public function pullAssignment(){
        return $this->queueMapper->getNext();
    }

    /** @Param QueueEntity $entity
     * Description: Deleting the given Queue Entity in the Queue Database.
     */
    public function deleteAssignment(QueueEntity $entity){
        $this->queueMapper->delete($entity);
    }

    /** @Param QueueEntity $entity
     * Description: Set inProgress to false again.
     */
    public function rollbackEntity(QueueEntity $entity){
        $entity->setInprogress(0);
        $this->queueMapper->update($entity);
    }

}