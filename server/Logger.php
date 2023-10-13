<?php

namespace server;
use Exception;

ini_set('allow_url_fopen', 1);
class Logger
{
    protected static $log_file;
    protected static $file;

    protected static $options = [
        'dateFormat' => 'dmY',
        'logFormat' => 'H:i:s d.m.Y'
    ];
    private static $instance;

    /**
     * @throws Exception
     */
    public static function createLogFile()
    {
        $time = date(static::$options['dateFormat']);
        static::$log_file = __DIR__ . "/logs/log_{$time}.txt";
        if (!file_exists(__DIR__ . '/logs')) {
            mkdir(__DIR__ . '/logs', 0777, true);
        }
        if (!file_exists(static::$log_file)) {
            fopen(static::$log_file, 'w') or exit("Не могу создать file!");
        }
        if (!is_writable(static::$log_file)) {
            throw new Exception("Не могу записать в file, проверьте права", 1);
        }
    }

    public static function setOptions($options = [])
    {
        static::$options = array_merge(static::$options, $options);
    }

    public static function info($message, array $context = [])
    {
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 1);
        static::writeLog([
            'message' => $message, 'backtrace' => $backtrace, 'level' => 'INFO', 'context' => $context
        ]);
    }

    public static function notice($message, array $context = [])
    {
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 1);
        static::writeLog([
            'message' => $message, 'backtrace' => $backtrace, 'level' => 'NOTICE', 'context' => $context
        ]);
    }

    public static function debug($message, array $context = [])
    {
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 1);
        static::writeLog([
            'message' => $message, 'backtrace' => $backtrace, 'level' => 'DEBUG', 'context' => $context
        ]);
    }

    public static function warning($message, array $context = [])
    {
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 1);
        static::writeLog([
            'message' => $message, 'backtrace' => $backtrace, 'level' => 'WARNING', 'context' => $context
        ]);
    }

    public static function error($message, array $context = [])
    {
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 1);
        static::writeLog([
            'message' => $message, 'backtrace' => $backtrace, 'level' => 'ERROR', 'context' => $context
        ]);
    }

    public static function fatal($message, array $context = [])
    {
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 1);
        static::writeLog([
            'message' => $message, 'backtrace' => $backtrace, 'level' => 'FATAL', 'context' => $context
        ]);
    }

    public static function writeLog($args = [])
    {
        static::createLogFile();
        if (!is_resource(static::$file)) {
            static::openLog();
        }
        $time = date(static::$options['logFormat']);
        $context = json_encode($args['context']);
        $caller = array_shift($args['backtrace']);
        $btLine = $caller['line'];
        $btPath = $caller['file'];
        $path = static::absToRelPath($btPath);
        $timeLog = is_null($time) ? "[N/A] " : "[{$time}] ";
        $pathLog = is_null($path) ? "[N/A] " : "[{$path}] ";
        $lineLog = is_null($btLine) ? "[N/A] " : "[{$btLine}] ";
        $levelLog = is_null($args['level']) ? "[N/A]" : "[{$args['level']}]";
        $messageLog = is_null($args['message']) ? "N/A" : "{$args['message']}";
        $contextLog = empty($args['context']) ? "" : "{$context}";
        fwrite(static::$file, "{$timeLog}{$pathLog}{$lineLog}: {$levelLog} - {$messageLog} {$contextLog}" . PHP_EOL);
        static::closeFile();
    }

    private static function openLog()
    {
        $openFile = static::$log_file;
        static::$file = fopen($openFile, 'a') or exit ("Не могу открыть $openFile!");
    }

    public static function closeFile()
    {
        if (static::$file) {
            fclose(static::$file);
        }
    }

    public static function absToRelPath($pathToConvert)
    {
        $pathAbs = str_replace(['/', '\\'], '/', $pathToConvert);
        $documentRoot = str_replace(['/', '\\'], '/', $_SERVER['DOCUMENT_ROOT']);
        return $_SERVER['SERVER_NAME'] . str_replace($documentRoot, '', $pathAbs);
    }

    protected function __construct()
    {
    }

    protected function __clone()
    {
    }

    /**
     * @throws Exception
     */
    public function __wakeup()
    {
        throw new \Exception("Cannot unserialize a class");
    }

    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __destruct()
    {
    }

}