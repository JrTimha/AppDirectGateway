<?php
namespace OCA\AppDirect\Command;

use Exception;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\BackgroundJob\TimedJob;
use OCP\AppFramework\QueryException;
use OCP\IConfig;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use OCA\AppDirect\Config;
use OCA\AppDirect\Logger;
use OCA\AppDirect\Database\Group;
use OCA\AppDirect\Service\DBGroupService;
use OCA\AppDirect\NextCloud\AccountManager;
use OCA\AppDirect\Service\AppDirectService;


/** Execute Command with: ./occ appdirect:BillingRulesCheck
 * On Test-Instance: docker exec -ti --user www-data nextcloud-app /var/www/html/occ appdirect:BillingRulesCheck
 * Documentation of this Framework:
 * https://symfony.com/doc/current/console.html
 */

class BillingRulesCheck extends Command
{
    //-------------------------------------|
    //Variables used in this Command Class:
    protected IConfig $config;
    protected $appName;
    protected DBGroupService $groupService;
    protected AccountManager $ncAccAPI;
    protected AppDirectService $adService;
    protected Logger $ncLogger;
    protected static $defaultDescription = 'BackgroundJob for User Count Detection';
    //-------------------------------------|

    /* @Param DBGroupService $groupService | Is used to interact with the Group Database.
     * @Param AccountManager $ncAccAPI | Using the Nextcloud API.
     * @Param AppDirectService $adService | Communication with REST API.
     * @Param $appName | Name of the Nextcloud App
     * @Param IConfig $config | Configuration, not used but essential variable.
     */
    public function __construct($appName, IConfig $config, DBGroupService $groupService, AccountManager $ncAccAPI, AppDirectService $adService, Logger $logger)
    {
        parent::__construct();
        $this->config = $config;
        $this->appName = $appName;
        $this->groupService = $groupService;
        $this->ncAccAPI = $ncAccAPI;
        $this->adService = $adService;
        $this->ncLogger = $logger;
    }

    protected function configure(): void
    {
        $this
            // the command help shown when running the command with the "--help" option
            ->setHelp('This command is checking the user count of from groups before subscription end')
            ->setName('appdirect:BillingRulesCheck');
    }

    /* @Param OutputInterface $output | Is used to write messages in the console.
     * @Param InputInterface $input | Default, none input required for this command.
     * Description: Entry Point for the execution of the command which is executed via unix console. Starts and ends
     * the process, can't be executed multiple times together!
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
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
     * Description: Processing through a selected set of Groups with expiring subscriptions (<3 days) and
     * checking if the user count is higher than the limit of the subscription model,
     * stops if SQL-Statement can't return more entities.
     */
    private function startProcessing(OutputInterface $output)
    {
        $output->writeln('LOG: Starting the Billing Rules Check.');
        while (true) {
            try {
                $entityOut = $this->groupService->getExpiring();
            } catch (Exception $e) {
                $output->writeln('LOG: Processing completed, all expiring groups checked.');
                return;
            }
            $userCount = $this->ncAccAPI->getUserCountInGroup($entityOut->getGroup());
            //Logging
            $this->ncLogger->info('Billing Message:',
                ['Group'=>$entityOut->getGroup(),
                    'User-Count' => $entityOut->getUsercount(),
                    'Edition' => $entityOut->getEditioncode(),
                    'Last Billing' => $entityOut->getBillingdate(),
                    'Status' => "Updated to next month."
                ]);
            //Console Tests---------------------------------|
            $output->writeln($entityOut->getGroup());
            $output->writeln($entityOut->getAdmin());
            $output->writeln($entityOut->getBillingdate());
            $output->writeln($entityOut->getEditioncode());
            $output->writeln($entityOut->getUsercount());
            $output->writeln($entityOut->getInprocessing());
            //Console Tests---------------------------------|
            if($userCount > $entityOut->getUsercount()){
                $output->writeln('Sending update for group: ');
                $output->writeln($entityOut->getGroup());
                $userDifference = $userCount - $entityOut->getUsercount();
                $this->adService->sendMeteredUsageData($entityOut->getGroup(), $userDifference);
                $this->ncLogger->info('Billing send for:',
                    ['Group'=>$entityOut->getGroup(),
                        'User-Count' => $entityOut->getUsercount(),
                        'Edition' => $entityOut->getEditioncode(),
                        'Last Billing' => $entityOut->getBillingdate(),
                        'Status' => "Send information to AppDirect."
                    ]);
            }
            $this->groupService->updateBillingInformation($entityOut);
        }
    }

    private function setPID()
    {
        $this->config->setAppValue($this->appName, 'pid', posix_getpid());
    }

    private function clearPID()
    {
        $this->config->deleteAppValue($this->appName, 'pid');
    }

    private function getPID()
    {
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
