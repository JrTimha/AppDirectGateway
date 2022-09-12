<?php
namespace OCA\AppDirect\Database;

use DateInterval;
use DateTime;
use Exception;
use OCP\IDBConnection;
use OCP\AppFramework\Db\QBMapper;
use OCP\DB\QueryBuilder\IQueryBuilder;

class GroupMapper extends QBMapper {


    /** @Param IDBConnection $db | Connection to the Group Database
     */
    public function __construct(IDBConnection $db) {
        parent::__construct($db, 'Group', Group::class);
    }

    //---------------------------------------//
    //Already implemented by inheritance:
    //public function update(Entity $entity)
    //public function insert(Entity $entity)
    //public function delete(Entity $entity)
    //---------------------------------------//

    /** @Param string $db | Name of the Group-Entity
     * Description: Finding the Entity that has the given group name, returning the Entity
     */
    public function findGroup(string $group) {
        $qb = $this->db->getQueryBuilder();

        $qb->select('*')
            ->from($this->getTableName())
            ->where(
                $qb->expr()->eq('group', $qb->createNamedParameter($group))
            );
        return $this->findEntity($qb);
    }

    /** Description: Returns a Group Entity with an expiring billing date withing the next 3 days and where the
     *   attribute 'inprocessing' is false. Starting a database transaction for the selected entity to set inprocessing
     *   to true.
     * @throws Exception
     */
    public function findExpiring() {
        $curTime = time();
        $curTime -= ($curTime % 86400); // 24 * 60 * 60 = 86400
        $curTime += 3 * 86400;
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
            ->from($this->getTableName())
            ->where($qb->expr()->lte('billingdate', $qb->createNamedParameter($curTime, IQueryBuilder::PARAM_INT)))
            ->andWhere($qb->expr()->eq('inprocessing', $qb->createNamedParameter(0, IQueryBuilder::PARAM_INT)))
            ->setMaxResults(1);

        $update = $this->db->getQueryBuilder();
        $update->update($this->getTableName())
            ->set('inprocessing', $update->createNamedParameter(1, IQueryBuilder::PARAM_INT))
            ->where($update->expr()->eq('id', $update->createParameter('id')))
            ->andWhere($update->expr()->eq('inprocessing', $update->createNamedParameter(0, IQueryBuilder::PARAM_INT)));


        $result = $qb->executeQuery();
        $row = $result->fetch();
        $result->closeCursor();

        if ($row) {
            $update->setParameter('id', $row['id']);
            $count = $update->executeStatement();

            if ($count === 0) {
                // Some other worker picked up the job between the select and update, move on to the next one
                return $this->findExpiring();
            }
            // Process queue item (return entity)
            return $this->mapRowToEntity($row);
        } else {
            // No more jobs in the queue
            throw new Exception("Queue empty");
        }
    }


    /**  @Param Group $grp | A group entity
     *   Description: Updating the billing date to the next month and setting 'inprocessing' to false again.
     */
    public function updateBilling(Group $grp)
    {
        $newBillingDate = $this->calcBilling($grp->getCreationdate(), $grp->getBillingdate());
        $this->db->beginTransaction();
        try {
            $grp->setInprocessing(0);
            $grp->setBillingdate($newBillingDate);
            $this->update($grp);
            $this->db->commit();
        }catch (Exception $e){
            $this->db->rollBack();
        }
    }

    /**  @Param int $creationDate | Date of group creation
     *   @Param int $billingDate | Date of the monthly billing
     *   Description: Calculates the new billing date for the next month.
     */
    function calcBilling(int $creationDate, int $billingDate): int
    {
        $formattedCreation = date("Y-m-d", $creationDate);
        $lastDay = new DateTime($formattedCreation);
        $formattedBilling = date("Y-m-d", $billingDate);
        $newDate = "";
        if($formattedCreation == $lastDay->format('Y-m-t')){
            $newDate = $this->getCalculatedDate($formattedBilling, 1);
        }else if (1==date("n", $billingDate)){
            if((int)date("d", $billingDate) > 28){
                $newDate = $this->getCalculatedDate($formattedBilling, 1);
            }
        }else{
            $newDate = date("Y-m-d", strtotime("+1 month",$billingDate));
        }
        return strtotime($newDate);
    }

    function add_months($months, DateTime $dateObject): DateInterval
    {
        $next = new DateTime($dateObject->format('Y-m-d'));
        $next->modify('last day of +'.$months.' month');

        if($dateObject->format('d') > $next->format('d')) {
            return $dateObject->diff($next);
        } else {
            return new DateInterval('P'.$months.'M');
        }
    }

    function getCalculatedDate($d1, $months): string
    {
        $date = new DateTime($d1);
        // call second function to add the months
        $newDate = $date->add($this->add_months($months, $date));
        //formats final date to m/d/Y form
        $dateReturned = $newDate->format('Y/m/d');
        return $dateReturned;
    }
}
