<?php
namespace OCA\AppDirect\Database;

use Exception;
use OCA\AppDirect\Database\QueueEntity;
use OCP\IDBConnection;
use OCP\AppFramework\Db\QBMapper;
use OCP\DB\QueryBuilder\IQueryBuilder;

class QueueMapper extends QBMapper {

    /** @Param IDBConnection $db | Connection to the Queue Database
     */
    public function __construct(IDBConnection $db) {
        parent::__construct($db, 'Queue', QueueEntity::class);
    }

    //---------------------------------------//
    //Already implemented by inheritance:
    //public function update(Entity $entity)
    //public function insert(Entity $entity)
    //public function delete(Entity $entity)
    //---------------------------------------//

    /**
     * Description: Returning the oldest Queue Entity, measured by the timestamp and starting a database
     * transaction to set 'inprogress' to true.
     * @throws Exception
     */
    public function getNext() {
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
            ->from('Queue')
            ->where(
                $qb->expr()->eq('inprogress', $qb->createNamedParameter(0, IQueryBuilder::PARAM_INT))
            )->orderBy('time')->setMaxResults(1);

        $update = $this->db->getQueryBuilder();
        $update->update($this->getTableName())
            ->set('inprogress', $update->createNamedParameter(1, IQueryBuilder::PARAM_INT))
            ->where($update->expr()->eq('id', $update->createParameter('id')))
            ->andWhere($update->expr()->eq('inprogress', $update->createNamedParameter(0, IQueryBuilder::PARAM_INT)));

        $result = $qb->executeQuery();
        $row = $result->fetch();
        $result->closeCursor();

        if ($row) {
            $update->setParameter('id', $row['id']);
            $count = $update->executeStatement();

            if ($count === 0) {
                // Some other worker picked up the job between the select and update, move on to the next one
                return $this->getNext();
            }

            // Process queue item (return entity)
            return $this->mapRowToEntity($row);
        } else {
            // No more jobs in the queue
            throw new Exception("Queue empty");
        }
    }

}
