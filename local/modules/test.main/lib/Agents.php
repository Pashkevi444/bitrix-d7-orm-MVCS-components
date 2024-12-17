<?php

/**
 * A class to manage agents for periodic tasks.
 * The agents are self-creating and self-destructing, meaning they schedule themselves to run and automatically
 * remove themselves when they finish their task. This avoids the need for external queues (like RabbitMQ).
 */

use \Exceptions\NoDataException;

class Agents
{
    /**
     * @var int The limit of items to process per iteration. Default is 10.
     */
    private static int $limit = 10;

    /**
     * @var object Logger instance used for logging information and errors.
     */
    private static object $logger;

    /**
     * Initializes the logger if it's not already initialized.
     */
    private static function initLogger(): void
    {
        if (!isset(self::$logger)) {
            self::$logger = \Bitrix\Main\Diag\Logger::create('LoggerAgents');
        }
    }

    /**
     * Agent to update currency rates.
     * This method will be executed periodically.
     *
     * @return string The command to schedule the next execution of this agent.
     */
    public static function agentGetCurrencyRate(): string
    {
        self::initLogger();
        try {
            self::$logger->info('Starting agentGetCurrencyRate');
            $currencyManager = new \CurrencyManager();
            $currencyManager->updateCurrencyRates();
            self::$logger->info('Currency rates updated successfully');
        } catch (\Exception $e) {
            self::$logger->error('Error in agentGetCurrencyRate: ' . $e->getMessage());
        }

        return '\Agents::agentGetCurrencyRate();';
    }

    /**
     * Agent to iteratively update villages.
     *
     * @return string The command to schedule the next execution of this agent.
     */
    public static function agentUpdateVillages(): string
    {
        self::initLogger();
        self::$logger->info('Starting agentUpdateVillages');
        self::scheduleNextAgent('agentUpdateVillages', 'village', 'iblockJKKPId', 1);
        return '\Agents::agentUpdateVillages();';
    }

    /**
     * Agent to iteratively update housing complexes (JK).
     *
     * @return string The command to schedule the next execution of this agent.
     */
    public static function agentUpdateJk(): string
    {
        self::initLogger();
        self::$logger->info('Starting agentUpdateJk');
        self::scheduleNextAgent('agentUpdateJk', 'complex', 'iblockJKKPId', 1);

        return '\Agents::agentUpdateJk();';
    }

    /**
     * Agent to iteratively update objects.
     *
     * @return string The command to schedule the next execution of this agent.
     */
    public static function agentUpdateObjects(): string
    {
        self::initLogger();
        self::$logger->info('Starting agentUpdateObjects');
        self::scheduleNextAgent('agentUpdateObjects', 'objects', 'iblockObjectsId', 1);

        return '\Agents::agentUpdateObjects();';
    }

    /**
     * Agent to iteratively update the prices of objects.
     *
     * @return string The command to schedule the next execution of this agent.
     */
    public static function agentUpdateObjectsPrice(): string
    {
        self::initLogger();
        self::$logger->info('Starting agentUpdateObjectsPrice');
        self::scheduleNextAgent('agentUpdateObjectsPrice', 'objectsPrice', 'iblockObjectsId', 1);

        return '\Agents::agentUpdateObjectsPrice();';
    }

    /**
     * Executes a specific iteration of an agent task.
     * It processes items of a given type, and schedules the next iteration if necessary.
     *
     * @param string $agentName The name of the agent (used for logging and scheduling).
     * @param string $type The type of items being processed (e.g., 'objects', 'village').
     * @param string $iblockKey The key to retrieve the appropriate IBLOCK ID.
     * @param int $iteration The current iteration number.
     *
     * @return string An empty string, as this method will schedule the next iteration.
     */
    public static function executeIteration(
        string $agentName,
        string $type,
        string $iblockKey,
        int $iteration
    ): string
    {
        self::initLogger();
        self::$logger->info("Processing $agentName iteration $iteration");
        self::processItems($type, $iblockKey, $agentName, $iteration);
        return '';
    }

    /**
     * A general method for processing a batch of items.
     * This is used for various types of data (e.g., 'objects', 'village') and processes them in batches.
     *
     * @param string $type The type of data (e.g., 'objects', 'objectsPrice', 'village').
     * @param string $iblockKey The key to retrieve the appropriate IBLOCK ID.
     * @param string $agentName The name of the agent for logging.
     * @param int $currentIteration The current iteration of the batch to process.
     */
    private static function processItems(
        string $type,
        string $iblockKey,
        string $agentName,
        int $currentIteration
    ): void {
        try {
            // Retrieve module options and setup the API client
            $moduleOptionsData = OptionsData::getInstance();
            $apiXoms = new \Xoms\ApiXoms();
            $iblockId = $moduleOptionsData->$iblockKey;

            $isFullUpdate = $moduleOptionsData->isFullUpdate;

            // Calculate start and end pages for batch processing
            $startPage = $currentIteration;
            $endPage = $currentIteration + self::$limit - 1;

            // Process items based on their type
            if ($type === 'objects') {
                $apiXoms->processItemsObjects($iblockId, $startPage, $endPage, $isFullUpdate);
            } elseif ($type === 'objectsPrice') {
                $apiXoms->processItemsObjectsOnlyPrice($iblockId, $startPage, $endPage);
            } else {
                $apiXoms->processItemsBuildings($iblockId, $startPage, $endPage, $type, $isFullUpdate);
            }

            // Log the successful processing of the batch
            self::$logger->info("Processed $type from page $startPage to $endPage");

            // Schedule the next iteration of the agent
            self::scheduleNextAgent($agentName, $type, $iblockKey, $endPage + 1);

        } catch (NoDataException $noDataException) {
            self::$logger->info("All $type items processed: " . $noDataException->getMessage());
        } catch (\Exception $e) {
            self::$logger->error("Error processing $type batch from $startPage to $endPage: " . $e->getMessage());
        }
    }

    /**
     * Schedules the next iteration of an agent.
     * This method ensures that the agent will be executed again at the appropriate time.
     *
     * @param string $agentName The name of the agent to schedule.
     * @param string $type The type of data being processed.
     * @param string $iblockKey The key for the IBLOCK ID.
     * @param int $iteration The iteration number for the next agent run.
     *
     * @return bool Returns true if the agent was successfully scheduled.
     */
    private static function scheduleNextAgent(
        string $agentName,
        string $type,
        string $iblockKey,
        int $iteration
    ): bool
    {
        // Prepare the agent command to be scheduled
        $agentCommand = "Agents::executeIteration('$agentName', '$type', '$iblockKey', $iteration);";
        \CAgent::AddAgent($agentCommand, "test.main", "N", 0, "", "Y");
        return true;
    }
}
