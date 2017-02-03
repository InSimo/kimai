<?php

/**
 * Responsible for logging db changes to the logfile.
 */
class Kimai_DBLogger
{
    private static $instance = null;
    private $file;

    /**
     * Create a new logger instance.
     */
    private function __construct()
    {
        $d = date("Ymd", time());
        $kimai_log_dir = getenv('KIMAI_LOG_DIR');
        $this->file = fopen($kimai_log_dir . '/kimai-dbchanges-'.$d.'.jsonl', "a");
    }

    /**
     * Close the file if the instance is destroyed.
     */
    public function __destruct()
    {
        fclose($this->file);
    }

    /**
     * Initialize the logger.
     *
     * @author sl
     */
    public static function init()
    {
        if (self::$instance == null) {
            self::$instance = new Kimai_DBLogger();
        }
    }

    /**
     * Write a line to the logfile.
     *
     * @param string $line line to log
     * @author sl
     */
    public function log($line)
    {
        fputs($this->file, $line . "\n");
    }

    /**
     * Simple static method to log lines to the logfile.
     *
     * @param string $value message
     * @author ja
     */
    public static function logjson($value)
    {
        Kimai_DBLogger::init();
        self::$instance->log(json_encode($value, JSON_UNESCAPED_SLASHES));
    }

    /**
     * Write a line to the logfile.
     *
     * @param string $line line to log
     * @author ja
     */
    public static function logpatch($op,$user,$kind,$id,$value,$oldvalue)
    {
        $d = date("[d.m.Y H:i:s]", time());
        $url = $_SERVER['REQUEST_URI'];
        $jpatch = array();
        $jpatch['date'] = $d;
        $jpatch['url'] = $url;
        if (isset($user)) {
            $jpatch['author'] = $user;
        }
        $jpatch['op'] = $op;
        $jpatch['path'] = '/'.$kind.'/'.$id;
        if(isset($value)) {
            $jpatch['value'] = $value;
        }
        if(isset($oldvalue)) {
            $jpatch['oldvalue'] = $oldvalue;
        }
        $jpatch['value'] = $value;
        $jpatch['op'] = $op;

        Kimai_DBLogger::logjson($jpatch);
    }

    public static function log_create($user,$kind,$id,$value)
    {
        Kimai_DBLogger::logpatch('add',$user,$kind,$id,$value,NULL);
    }

    public static function log_edit($user,$kind,$id,$value,$oldvalue)
    {
        Kimai_DBLogger::logpatch('replace',$user,$kind,$id,$value,$oldvalue);
    }

    public static function log_delete($user,$kind,$id,$oldvalue)
    {
        Kimai_DBLogger::logpatch('remove',$user,$kind,$id,NULL,$oldvalue);
    }
}
