<?php
declare(strict_types=1);

namespace OCA\AppDirect\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\Migration\SimpleMigrationStep;
use OCP\Migration\IOutput;
use OCP\DB\Types;
use OCP\IDBConnection;
use OCA\Theming\ThemingDefaults;
use OCA\Theming\ImageManager;
use OCP\IConfig;
use OCP\ICacheFactory;

/** Execute Migration Script with: docker exec -ti --user www-data nextcloud-app /var/www/html/occ migrations:execute appdirect 000004Date20220509113010
 * Naming from VersionXXYYZZDateYYYYMMDDHHSSAA.php
 */
class Version000004Date20220509113010 extends SimpleMigrationStep {

    //Variables-----------------------|
    private IDBConnection $connection; //Connecting to the Nextcloud Database
    private ThemingDefaults $theming;
    private ImageManager $imageManager;
    private $config;
    private $cacheFactory;
    //Variables-----------------------|

    /**
     * @param IDBConnection $connection
     */
    public function __construct(IDBConnection $connection, ThemingDefaults $theming, ImageManager $imageManager, IConfig $config, ICacheFactory $cacheFactory) {
        $this->connection = $connection;
        $this->theming = $theming;
        $this->imageManager = $imageManager;
        $this->config = $config;
        $this->cacheFactory = $cacheFactory;
    }

    /**
     * @param IOutput $output
     * @param Closure $schemaClosure The `\Closure` returns a `ISchemaWrapper`
     * @param array $options
     * @return null|ISchemaWrapper
     * Description: Entry Point of the Migration Script, first it deletes the Table-Schema, then building them new
     */
    public function changeSchema(IOutput $output, Closure $schemaClosure, array $options
    ): ISchemaWrapper {
        /** @var ISchemaWrapper $schema */
        $schema = $schemaClosure();
        //Creating the different databases:
        $this->createQueue($schema);
        $this->createGroup($schema);
        $this->setTheming();
        $this->setPasswordPolicy();
        return $schema;
    }

    /** Description: Creating the Queue Database with the attributes ID, EventURL, InProgress, time.
     * ID as primary key and index.
     */
    private function createQueue(ISchemaWrapper $schema){
        if ($schema->hasTable('Queue')) {
            return;
        }
        $table = $schema->createTable('Queue');
        $table->addColumn('id', 'integer', [
            'autoincrement' => true,
            'notnull' => true,
        ]);
        $table->addColumn('eventurl', 'string', [
            'notnull' => true,
            'length' => 128
        ]);
        $table->addColumn('inprogress', 'boolean', [
            'notnull' => true
        ]);
        $table->addColumn('time', 'string', [
            'notnull' => true
        ]);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['time'], 'time_index');
        $table->addIndex(['inprogress'], 'progress_index');

    }


    /** Description: Creating the Group Database with the attributes ID, Group, Admin, BillingDate, EditionCode,
     * UserCount, Storage, InProcessing.
     * ID as primary key and index.
     */
    private function createGroup(ISchemaWrapper $schema){
        if (!$schema->hasTable('Group')) {
            $table = $schema->createTable('Group');
            $table->addColumn('id', 'integer', [
                'autoincrement' => true,
                'notnull' => true
            ]);
            $table->addColumn('group', 'string', [
                'notnull' => true,
                'length' => 100
            ]);
            $table->addColumn('admin', 'string', [
                'notnull' => true,
                'length' => 100
            ]);
            $table->addColumn('billingdate', 'integer', [
                'notnull' => true
            ]);
            $table->addColumn('creationdate', 'integer', [
                'notnull' => true
            ]);
            $table->addColumn('editioncode', 'string', [
                'notnull' => true,
                'length' => 15
            ]);
            $table->addColumn('usercount', 'integer', [
                'notnull' => true
            ]);
            $table->addColumn('storage', 'integer', [
                'notnull' => true
            ]);
            $table->addColumn('inprocessing', 'boolean', [
                'notnull' => true
            ]);
            $table->setPrimaryKey(['id']);
            $table->addIndex(['billingdate'], 'billingdate_index');
            $table->addIndex(['inprocessing'], 'inprocessing_index');
        }
    }

	private $cssStyle = <<<EOF
    @font-face {  
        font-family: 'TeleNeoWeb';
        font-style: normal;
        font-weight: normal;
        src: local('TeleNeoWeb-Regular'), url("/apps/appdirect/assets/fonts/TeleNeoWeb-Regular.woff2") format("woff2");
    }

    :root {
        --color-primary-light: whitesmoke;
        --font-face: TeleNeoWeb, -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Oxygen-Sans, Cantarell, Ubuntu, Helvetica Neue, Arial, Noto Color Emoji, sans-serif, Apple Color Emoji, Segoe UI Emoji, Segoe UI Symbol;
    }

    .development-notice {
        text-align: center;
        display: none;
    }
    EOF;


    private function setTheming(){
        $this->config->setAppValue("theming", "name", "MagentaCLOUD Business");
        $this->config->setAppValue("theming", "url", "https://magentacloud-business.de");
        $this->config->setAppValue("theming", "slogan", "Ihr sicherer Cloud Speicher");
        $this->config->setAppValue("theming", "color", "#E20074");
        $this->config->setAppValue("theming", "imprintUrl", "https://www.telekom.de/pflichtangaben");
        $this->config->setAppValue("theming", "privacyUrl", "https://www.telekom.de/ueber-das-unternehmen/datenschutz#fragen-und-antworten");

        $this->config->setAppValue("theming_customcss", "customcss", $this->cssStyle);
        
        $mime = $this->imageManager->updateImage("background", __DIR__ . "/../../assets/background.png");
        $this->config->setAppValue("theming", "backgroundMime", $mime);
        $mime = $this->imageManager->updateImage("logo", __DIR__ . "/../../assets/T_logo_rgb_white.png");
        $this->config->setAppValue("theming", "logoMime", $mime);
        $mime = $this->imageManager->updateImage("logoheader", __DIR__ . "/../../assets/T_logo_rgb_white.png");
        $this->config->setAppValue("theming", "logoheaderMime", $mime);
        $mime = $this->imageManager->updateImage("favicon", __DIR__ . "/../../assets/T_favicon.ico");
        $this->config->setAppValue("theming", "faviconMime", $mime);

        $cacheBusterKey = (int)$this->config->getAppValue('theming', 'cachebuster', '0');
		$this->config->setAppValue('theming', 'cachebuster', (string)($cacheBusterKey + 1));

        $this->cacheFactory->createDistributed('theming-')->clear();
		$this->cacheFactory->createDistributed('imagePath')->clear();
    }

    private function setPasswordPolicy() {
        $this->config->setAppValue("password_policy", "minLength", 12);
        $this->config->setAppValue("password_policy", "historySize", 1);
        $this->config->setAppValue("password_policy", "expiration", 365);
        $this->config->setAppValue("password_policy", "maximumLoginAttempts", 100);
        $this->config->setAppValue("password_policy", "enforceUpperLowerCase", true);
        $this->config->setAppValue("password_policy", "enforceNumericCharacters", true);

        $this->config->setAppValue('core', 'shareapi_restrict_user_enumeration_to_group', 'yes');
    }
}
