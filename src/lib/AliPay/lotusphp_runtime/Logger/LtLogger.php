<?php

namespace fk\pay\lib\AliPay\lotusphp_runtime\Logger;

class LtLogger
{
    public $conf = [
        'log_file' => ''
    ];

    private $_handle;

    protected function getHandle()
    {
        if (null === $this->_handle) {
            if (empty($this->conf['log_file'])) {
                trigger_error('no log file specified.');
            }
            $log_path = dirname($this->conf['log_file']);
            if (!is_dir($log_path)) mkdir($log_path, 0777, true);
            $this->_handle = fopen($this->conf['log_file'], 'a');
        }
        return $this->_handle;
    }

    public function log($logData)
    {
        if (!$logData) return false;
        fwrite($handle = $this->getHandle(), print_r($logData, true) . PHP_EOL);
        fclose($handle);
    }
}