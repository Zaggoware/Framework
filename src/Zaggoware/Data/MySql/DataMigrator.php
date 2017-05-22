<?php

namespace Zaggoware\Data\MySql;

use Zaggoware\Data\DbConfig;
use Zaggoware\Data\Migrations\IDataMigrator;
use Zaggoware\Data\Migrations\MigrateResult;
use Zaggoware\Exceptions\MigrationException;

class DataMigrator implements IDataMigrator {
    /** @var DbConfig */
    private $dbConfig;

    /** @var string */
    private $historyTableName = "__migration_history";

    /** @var string */
    private $migrationsPath = null;

    /** @var bool */
    private $migrationsPathSet = false;

    /** @var bool */
    private $transactionPerScript = false;

    /** @var bool */
    private $consoleLog = false;

    /** @var SimpleMySqlConnection */
    private $con;

    /**
     * DataMigrator constructor.
     * @param DbConfig $dbConfig
     */
    public function __construct(DbConfig $dbConfig) {
        $this->dbConfig = $dbConfig;
    }

    /**
     * @param $tableName
     * @return $this
     */
    public function setHistoryTableName($tableName) {
        $this->historyTableName = $this->con->escape($tableName);

        return $this;
    }

    /**
     * @param $path
     * @return $this
     */
    public function setMigrationsPath($path) {
        $this->migrationsPath = $path;
        $this->migrationsPathSet = true;

        return $this;
    }

    /**
     * @return $this
     */
    public function enableTransactionPerScript() {
        $this->transactionPerScript = true;

        return $this;
    }

    /**
     * @return $this
     */
    public function enableConsoleLog() {
        $this->consoleLog = true;

        return $this;
    }

    /**
     * @return MigrateResult
     * @throws MigrationException
     */
    public function performUpgrade() {
        if ($this->consoleLog) {
            echo "Performing database upgrade...\r\nChecking migration history...\r\n";
        }

        if ($this->dbConfig === null) {
            return new MigrateResult(false, "Please provide a database configuration with 'setDbConfig'.");
        }

        if ($this->migrationsPath === null || !is_dir($this->migrationsPath)) {
            return new MigrateResult(
                false,
                $this->migrationsPathSet
                    ? "Migrations path '{$this->migrationsPath}' could not be found."
                    : "Please provide a migrations path with 'setMigrationsPath'.");
        }


        $this->con = new SimpleMySqlConnection();
        try {
            $this->con->connect($this->dbConfig);

            if ($this->consoleLog) {
                echo "Connected to database: {$this->dbConfig->getDatabaseName()}.\r\n";
            }

            $createResult = $this->tryCreateTable();
            $migrationHistory = $createResult === false ? $this->getMigrationHistory() : array();

            $files = array_diff(scandir($this->migrationsPath), array('..', '.'));
            sort($files, SORT_STRING | SORT_ASC);

            if ($this->consoleLog) {
                echo "\r\nExisting migration history:\r\n";

                if (empty($migrationHistory)) {
                    echo "No existing migration history found.\r\n";
                } else {
                    echo join(", ", array_column($migrationHistory, "id")) . "\r\n";
                }

                echo "\r\n";
                echo "Script files:\r\n";
                echo join(", ", $files) . "\r\n\r\n";
            }

            // Checking existing migrations
            foreach ($migrationHistory as $entry) {
                $entryId = strtolower($entry['id']);
                $file = $files[0];
                $this->checkFileName($file);
                $name = $this->getNameWithoutExtension($file);

                if ($name !== $entryId) {
                    throw new MigrationException(
                        "Migration history mis-match. Found: {$entryId} in database, found: {$name} in migrations path.");
                }

                array_shift($files);
            }

            if (count($files) === 0) {
                if ($this->consoleLog) {
                    echo "No pending migrations.\r\n";
                }

                return new MigrateResult(true);
            }

            if ($this->consoleLog) {
                echo "New migration scripts found: " . join(", ", $files) . ".\r\n";
                echo "Executing migration scripts...\r\n";
            }

            // New migration scripts
            foreach ($files as $file) {
                $this->checkFileName($file);
                $name = $this->getNameWithoutExtension($file);

                if ($this->consoleLog) {
                    echo "- {$name}";
                }

                $fullQuery = $this->getFileContents($this->migrationsPath . "/" . $file);
                $queries = explode(";", $fullQuery);

                if ($this->consoleLog) {
                    echo " (Found " . count($queries) . " quer" . (count($queries) === 1 ? "y" : "ies") . ")\r\n";
                }

                if ($this->transactionPerScript) {
                    $this->con->setAutocommit(false);
                    $this->con->beginTransaction();
                }

                $result = $this->con->queryMultiple($fullQuery);
                if ($result === false && $this->con->getErrorNumber() != 0) {
                    throw new MigrationException($this->con->getError());
                }

                $this->con->query("
                    INSERT INTO `{$this->historyTableName}` (`id`)
                    VALUES ('{$this->con->escape($name)}')
                ");

                if ($this->transactionPerScript) {
                    $this->con->commit();
                }
            }

            if ($this->consoleLog) {
                echo "\r\nDone. Database is up-to-date.\r\n";
            }

            return new MigrateResult(true);
        }
        catch (\Exception $e) {
            $rollbackResult = $this->con->rollback();

            if ($this->consoleLog) {
                echo "RollbackResult: " . ($rollbackResult ? "Succeeded" : "Failed") . ".\r\n";
            }

            throw new MigrationException(
                "One or more errors occurred during the migration process: ". $e->getMessage(),
                $e->getCode(),
                $e);
        }
        finally {
            $this->con->close();
        }
    }

    private function tryCreateTable() {
        $result = $this->con->query("SHOW TABLES LIKE '{$this->historyTableName}'");
        if (!$result || $result->getRowCount() !== 1) {
            if ($this->consoleLog) {
                echo "Creating table '{$this->historyTableName}'...\r\n";
            }

            $this->con->query("
                CREATE TABLE `{$this->historyTableName}` (
                  `id` varchar(255) NOT NULL,
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
                ");

            return true;
        }

        return false;
    }

    private function getMigrationHistory() {
        $result = $this->con->query("SELECT `id` FROM `{$this->historyTableName}` ORDER BY `id` ASC");
        if (!$result || $result->getRowCount() === 0) {
            return array();
        }

        return $result->fetchAllAssoc();
    }

    private function checkFileName($fileName) {
        if (strtolower(substr($fileName, strlen($fileName) - 3)) !== "sql") {
            throw new \Exception("Unsupported migration file found: {$fileName}.");
        }
    }

    private function getNameWithoutExtension($fileName) {
        return strtolower(substr($fileName, 0, strlen($fileName) - 4));
    }

    private function getFileContents($fileName) {
        $contents = file_get_contents($fileName);

        return mb_convert_encoding($contents, 'UTF-8',
            mb_detect_encoding($contents, 'UTF-8, ISO-8859-1', true));
    }
}