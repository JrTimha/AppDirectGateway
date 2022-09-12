<?php
namespace OCA\AppDirect\Command;

use Exception;
use OCA\AppDirect\AppDirect\EventGoneException;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\BackgroundJob\TimedJob;
use OCP\AppFramework\QueryException;
use OCP\IConfig;
use OCA\AppDirect\Config;
use OCA\AppDirect\Logger;
use OCA\AppDirect\Service\DBQueueService;
use OCA\AppDirect\Database\QueueEntity;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use OCA\AppDirect\Service\EventProcessingService;


/** Execute Command with: ./occ appdirect:JobQueueWorker
 * On Test-Instance: docker exec -ti --user www-data nextcloud-app /var/www/html/occ appdirect:JobQueueWorker
 * Documentation of this Framework:
 * https://symfony.com/doc/current/console.html
 */

class JobQueue extends Command {

    //-------------------------------------|
    //Variables used in this Command Class:
    protected IConfig $config;
    protected $appName;
    protected DBQueueService $service;
    protected EventProcessingService $eventService;
    protected Logger $ncLogger;
    protected static $defaultDescription = 'BackgroundJob for JobAssignments';
    //-------------------------------------|


    /* @Param DBGroupService $groupService | Is used to interact with the Group Database.
     * @Param $appName | Name of the Nextcloud App
     * @Param IConfig $config | Configuration, not used but esential variable.
     * @Param EventProcessingService $eventService | To send EventURL's to the REST API.
     */
    public function __construct($appName, IConfig $config, DBQueueService $service, EventProcessingService $eventService, Logger $ncLog) {
        parent::__construct();
        $this->config = $config;
        $this->appName = $appName;
        $this->service = $service;
        $this->eventService = $eventService;
        $this->ncLogger = $ncLog;
    }


    protected function configure(): void
    {
        $this
            // the command help shown when running the command with the "--help" option
            ->setHelp('This command allows you to create a Group.')
            ->setName('appdirect:JobQueueWorker');
    }


    /* @Param OutputInterface $output | Is used to write messages in the console.
     * @Param InputInterface $input | Default, none input required for this command.
     * Description: Entry Point for the execution of the command which is executed via unix console. Starts and ends
     * the process, can't be executed multiple times together!
     */
    protected function execute(InputInterface $input, OutputInterface $output) {

        if ($this->checkAlreadyRunning()) {
            $output->writeln('Command is already running.');
            return 2;
        }
        $this->setPID();
        $this->startProcessing($output);
        $this->clearPID();
        return 0;
    }

    /* @Param OutputInterface $output - Is used to write messages in the console.
     * Description: Processing through the entire Queue Database and sending the EventURL to the RestAPI, after a true
     * is returned from the RestAPI, the Queue Entity will be deleted.
     */
    private function startProcessing(OutputInterface $output) {
        $output->writeln('LOG: Starting the JobQueue.');
        while (true){
            try {
                $entityOut = $this->service->pullAssignment();
            } catch (Exception $e) {
                $output->writeln("Queue is empty, stopping Worker.");
                return;
            }
            //Console Tests---------------------------------|
            $output->writeln($entityOut->getId());
            $output->writeln($entityOut->getEventurl());
            $output->writeln($entityOut->getTime());
            $output->writeln($entityOut->getInprogress());
            //Console Tests---------------------------------|
            try {
                $this->eventService->processEvent($entityOut->getEventurl());
                $output->write('Assignment deleted: ');
                $output->writeln($entityOut->getId());
                $this->ncLogger->info('Events:',
                    ['ID'=>$entityOut->getId(),
                        'EventURL' => $entityOut->getEventurl(),
                        'Time' => $entityOut->getTime(),
                        'Status' => "Job done."
                    ]);
                $this->service->deleteAssignment($entityOut);
            } catch (EventGoneException $e) {
                //Delete Event
                $this->ncLogger->error('EventMissing:',
                    ['ID'=>$entityOut->getId(),
                        'EventURL' => $entityOut->getEventurl(),
                        'Time' => $entityOut->getTime(),
                        'Status' => "Event Gone.",
                        'Exception' => [
                            'Message' => $e->getMessage(),
                            'Trace' => $e->getTraceAsString()
                        ],
                    ]);
                $this->service->deleteAssignment($entityOut);
                $output->writeln("Duplicate deleted.");
            } catch (Exception $e) {
                $this->ncLogger->error('Undefined Error:',
                    ['ID'=>$entityOut->getId(),
                        'EventURL' => $entityOut->getEventurl(),
                        'Time' => $entityOut->getTime(),
                        'Status' => "Unexpected error.",
                        'Exception' => [
                            'Message' => $e->getMessage(),
                            'Trace' => $e->getTraceAsString()
                        ],
                    ]);
                $this->service->rollbackEntity($entityOut);
                $output->writeln("Networking Error, stopping Queue.");
                return;
            }
        }
    }

    private function setPID() {
        $this->config->setAppValue($this->appName, 'pid', posix_getpid());
    }

    private function clearPID() {
        $this->config->deleteAppValue($this->appName, 'pid');
    }

    private function getPID() {
        return (int)$this->config->getAppValue($this->appName, 'pid', -1);
    }


    /**
     * Description: Checking if the process is already running, if true this function returns true, else false
     */
    private function checkAlreadyRunning(): bool
    {
        $pid = $this->getPID();

        // No PID set so just continue
        if ($pid === -1) {
            return false;
        }

        // Get the gid of non running processes so continue
        if (posix_getpgid($pid) === false) {
            return false;
        }

        // Seems there is already a running process generating previews
        return true;
    }
}
