<?php

use Bitrix\Main\Diag\FileLogger;
use Bitrix\Main\Diag\LogFormatter;
use Psr\Log\LoggerInterface;

/**
 * Logger class for managing and writing logs to files with different log levels.
 * Implements PSR-3 LoggerInterface.
 */
class Logger implements LoggerInterface
{
    /**
     * @var string The base directory for storing log files.
     */
    private const BASE_LOG_DIR = '/bitrix/logs';

    /**
     * @var int The maximum length of a subdirectory name.
     */
    private const MAX_DIR_NAME_LENGTH = 50;

    /**
     * @var int The maximum number of log files that can be stored in a subdirectory.
     */
    private const MAX_LOG_FILES = 20;

    /**
     * @var FileLogger A Bitrix FileLogger instance for handling the actual file logging.
     */
    private FileLogger $fileLogger;

    /**
     * @var LogFormatter A Bitrix LogFormatter instance to format the log messages.
     */
    private LogFormatter $logFormatter;

    /**
     * Logger constructor.
     *
     * @param string $subDir Name of the subdirectory inside the base log directory where logs will be stored.
     * @param int $maxFileSize Maximum file size for the log file (default is 5MB).
     *
     * @throws \RuntimeException If the subdirectory cannot be created or if the log files exceed the allowed limit.
     * @throws \InvalidArgumentException If the subdirectory name is invalid.
     */
    public function __construct(string $subDir, int $maxFileSize = 5 * 1024 * 1024)
    {
        // Validate and normalize the subdirectory name.
        $this->validateSubDir($subDir);
        $logDir = $_SERVER['DOCUMENT_ROOT'] . self::BASE_LOG_DIR . '/' . $subDir;

        // Create the subdirectory if it doesn't exist.
        if (!is_dir($logDir)) {
            if (!mkdir($logDir, 0755, true) && !is_dir($logDir)) {
                throw new \RuntimeException(sprintf('Failed to create directory: "%s".', $logDir));
            }
        }

        // Validate the number of log files in the directory.
        $this->validateLogFilesLimit($logDir);

        $logFilePath = $logDir . '/logger.log';
        $this->fileLogger = new FileLogger($logFilePath, $maxFileSize);
        $this->logFormatter = new LogFormatter();
    }

    /**
     * Validate the subdirectory name.
     *
     * @param string $subDir The subdirectory name.
     *
     * @throws \InvalidArgumentException If the subdirectory name contains invalid characters or exceeds the maximum length.
     */
    private function validateSubDir(string $subDir): void
    {
        // Check if the subdirectory name contains only valid characters (letters, digits, hyphen, and underscore).
        if (!preg_match('/^[a-zA-Z0-9_-]+$/', $subDir)) {
            throw new \InvalidArgumentException('Invalid subdirectory name. Only letters, digits, "-", and "_" are allowed.');
        }

        // Check if the subdirectory name length exceeds the maximum allowed length.
        if (strlen($subDir) > self::MAX_DIR_NAME_LENGTH) {
            throw new \InvalidArgumentException(sprintf(
                'Subdirectory name is too long. Maximum length: %d characters.',
                self::MAX_DIR_NAME_LENGTH
            ));
        }
    }

    /**
     * Validate the log files limit in the directory.
     *
     * @param string $logDir Path to the log directory.
     *
     * @throws \RuntimeException If the number of log files exceeds the maximum allowed limit.
     */
    private function validateLogFilesLimit(string $logDir): void
    {
        $files = glob($logDir . '/*.log');
        if (count($files) >= self::MAX_LOG_FILES) {
            throw new \RuntimeException(sprintf(
                'Exceeded the log files limit (%d) in the directory: %s',
                self::MAX_LOG_FILES,
                $logDir
            ));
        }
    }

    /**
     * Format the log message with a timestamp, log level, and process ID.
     *
     * @param string $level The log level.
     * @param string $message The log message.
     * @return string The formatted log message.
     */
    private function formatMessage(string $level, string $message): string
    {
        $timestamp = date('Y-m-d H:i:s');
        $pid = getmypid();
        return "[{$timestamp}] [{$level}] [PID: {$pid}] {$message}" . PHP_EOL;
    }

    /**
     * Replace placeholders in the log message with values from the context array.
     *
     * @param string $message The log message.
     * @param array $context The context array.
     * @return string The message with replaced placeholders.
     */
    private function interpolateMessage(string $message, array $context): string
    {
        foreach ($context as $key => $value) {
            $placeholder = '{' . $key . '}';
            if (strpos($message, $placeholder) !== false) {
                $message = str_replace($placeholder, (string)$value, $message);
            }
        }
        return $message;
    }

    /**
     * Main logging method to write messages to the log file.
     *
     * @param string $level The log level.
     * @param string|\Stringable $message The log message.
     * @param array $context Additional context or data for the log entry.
     *
     * @throws \InvalidArgumentException If the log level is invalid.
     */
    public function log($level, $message, array $context = []): void
    {
        $this->validateLogLevel($level);

        // Replace context placeholders in the message.
        $message = $this->interpolateMessage((string)$message, $context);

        // Format the message.
        $formattedMessage = $this->formatMessage($level, $message);
        $formattedMessage = $this->logFormatter->format($formattedMessage, $context);

        // Write the message to the log file.
        $this->fileLogger->log($level, $formattedMessage, $context);
    }

    /**
     * Validate the log level to ensure it is a recognized level.
     *
     * @param string $level The log level.
     *
     * @throws \InvalidArgumentException If the log level is not valid.
     */
    private function validateLogLevel(string $level): void
    {
        $validLevels = [
            'emergency', 'alert', 'critical', 'error',
            'warning', 'notice', 'info', 'debug',
        ];

        if (!in_array($level, $validLevels, true)) {
            throw new \InvalidArgumentException("Unknown log level: $level");
        }
    }

    // Methods for each log level
    public function info($message, array $context = []): void
    {
        $this->log('info', $message, $context);
    }

    public function error($message, array $context = []): void
    {
        $this->log('error', $message, $context);
    }

    public function critical($message, array $context = []): void
    {
        $this->log('critical', $message, $context);
    }

    public function debug($message, array $context = []): void
    {
        $this->log('debug', $message, $context);
    }

    public function warning($message, array $context = []): void
    {
        $this->log('warning', $message, $context);
    }

    public function notice($message, array $context = []): void
    {
        $this->log('notice', $message, $context);
    }

    public function emergency($message, array $context = []): void
    {
        $this->log('emergency', $message, $context);
    }

    public function alert($message, array $context = []): void
    {
        $this->log('alert', $message, $context);
    }
}
