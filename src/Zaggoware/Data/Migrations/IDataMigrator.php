<?php

namespace Zaggoware\Data\Migrations;

use Zaggoware\Data\DbConfig;

interface IDataMigrator {
    function __construct(DbConfig $dbConfig);

    /**
     * @param string $tableName
     * @return IDataMigrator
     */
    function setHistoryTableName($tableName);

    /**
     * @param string $path
     * @return IDataMigrator
     */
    function setMigrationsPath($path);

    /**
     * @return IDataMigrator
     */
    function enableTransactionPerScript();

    /**
     * @return IDataMigrator
     */
    function enableConsoleLog();

    /**
     * @return MigrateResult
     */
    function performUpgrade();
}