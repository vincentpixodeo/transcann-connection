<?php
/**
 * Created by Vincent
 * Email: vincent@pixodeo.net
 */

namespace WMS\Xtent\DolibarrConvert\Contracts;

use WMS\Xtent\Helpers\Logs\LogFile;
use WMS\Xtent\WmsXtentService;

trait CanSaveDataByLogTrait
{
    protected static $loggers = [];
    protected $itemName = 'item';

    /**
     * get Main Table
     * @return string
     */
    abstract protected function getMainTable(): string;

    function fetch($id = null, $field = null): ?static
    {
        $primaryKey = $this->getPrimaryKey();
        $rowId = $id ?? $this->{$primaryKey} ?? null;

        $file = $this->getPathLog($this->getMainTable()) . '/' . $this->itemName . "-$rowId.json";

        if (file_exists($file)) {
            $content = json_decode(file_get_contents($file), true);
            return $this->addData($content);
        }
        return null;
    }

    public function save(array $data = []): bool
    {
        $primaryKey = $this->getPrimaryKey();
        $this->addData($data);
        $path = $this->getPathLog($this->getMainTable());
        $fileName = $this->itemName . '-' . $this->{$primaryKey};
        if (file_exists($path . '/' . $fileName)) {
            $this->addData(json_decode(file_get_contents($path . '/' . $fileName)));
        }
        $this->getLog($this->getMainTable())->write($this->toArray(), $fileName);
        return true;
    }

    protected function getPathLog(string $tableName = null): string
    {
        if (empty($tableName)) {
            throw new \Exception("\$tableName is empty");
        }
        return WmsXtentService::instance()->storagePath('databases/' . $tableName);
    }

    protected function getLog(string $tableName = null): LogFile
    {
        if (empty($tableName)) {
            throw new \Exception("\$tableName is empty");
        }
        if (empty(static::$loggers[$tableName])) {
            static::$loggers[$tableName] = new LogFile($this->getPathLog($tableName), false);
        }
        return static::$loggers[$tableName];
    }
}