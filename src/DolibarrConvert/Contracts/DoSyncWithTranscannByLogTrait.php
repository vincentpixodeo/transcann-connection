<?php
/**
 * Created by Vincent
 * Email: vincent@pixodeo.net
 */

namespace WMS\Xtent\DolibarrConvert\Contracts;

use WMS\Xtent\Contracts\ObjectDataInterface;
use WMS\Xtent\Helpers\Logs\LogFile;
use WMS\Xtent\WmsXtentService;


trait DoSyncWithTranscannByLogTrait
{
    protected $loggers = [];
    protected $itemName = 'item';

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
        if (empty($this->loggers[$tableName])) {
            $this->loggers[$tableName] = new LogFile($this->getPathLog($tableName), false);
        }
        return $this->loggers[$tableName];
    }

    public function save(array $data = [])
    {
        $path = $this->getPathLog($this->mainTable);
        $fileName = $this->itemName . '-' . $this->rowid;
        $content = $this->toArray();
        if (file_exists($path . '/' . $fileName)) {
            $content = array_merge(json_decode(file_get_contents($path . '/' . $fileName)), $content);
        }
        $this->getLog($this->mainTable)->write(array_merge($content, $data), $fileName);
        return true;
    }

    function updateDataFromTranscann(ObjectDataInterface $objectData, array $mapping = null): bool
    {
        is_null($mapping) && $mapping = $this->getMappingInstanceByTranscannId($objectData->Id);

        /* Action save data from Transcann*/
        if ($mapping) {
            $dataSave = $this->createFromTranscan($objectData)->toArray();
            $this->updateMappingInstance(array_merge($mapping, ['transcann_id' => $objectData->Id]));
            return $this->save($dataSave);
        }
        return false;
    }

    function pushDataToTranscann(self|ObjectDataInterface $objectData = null, array $mapping = null): bool
    {
        is_null($objectData) && $objectData = $this;
        is_null($mapping) && $mapping = $this->getMappingInstanceByObjectId($objectData->rowid);

        /* Action push data to Transcann*/
        if ($mapping) {
            $dataSend = $objectData->convertToTranscan();
        }
        return false;
    }

    function fetchDataFromTranscann(self|ObjectDataInterface $objectData = null, array $mapping = null): bool
    {
        is_null($objectData) && $objectData = $this;
        is_null($mapping) && $mapping = $this->getMappingInstanceByObjectId($objectData->rowid);

        /* Action fetch data to Transcann*/
        if ($mapping) {
            /** @var ObjectDataInterface $transcanInstance */
            $dataSave = $this->updateDataFromTranscann($transcanInstance);
        }
        return false;
    }

    function createNewMappingInstance($data): ?array
    {
        $this->getLog("mapping_" . $this->mainTable)->write($data, $data['fileName']);
        /*Check to see if there is a map*/

        /*Create new mapping when dont exist*/

        /*return mapping data*/
        return $data;
    }

    function updateMappingInstance(array $data): ?array
    {

        $fileName = $data['fileName'];
        $newFileName = $fileName;
        if ($obId = $data['fk_object_id']) {
            $newFileName = preg_replace('/((?:temp_)?\d+)-(\d+)/', $obId . '-$2', $newFileName);
        }
        if ($transcannId = $data['transcann_id']) {
            $newFileName = preg_replace('/(\d+)-((?:temp_)?\d+)/', '$1-' . $transcannId, $newFileName);
        }
        $path = $this->getPathLog("mapping_" . $this->mainTable);

        if (file_exists($path . '/' . $fileName . '.json')) {
            if ($fileName != $newFileName) {
                rename($path . '/' . $fileName . '.json', $path . '/' . $newFileName . '.json');
            }

            $data['fileName'] = $newFileName;
            $data['updatedAt'] = time();
            $this->getLog("mapping_" . $this->mainTable)->write($data, $data['fileName']);
        }
        return $data;
    }

    function getMappingInstanceByTranscannId($id, bool $createNewIfDontExist = true): ?array
    {
        $fileName = $this->itemName . "-" . $this->rowid . "-$id";
        $files = glob($this->getPathLog("mapping_" . $this->mainTable) . '/' . $fileName . ".json");
        if ($files) {
            $content = file_get_contents($files[0]);
            return json_decode($content, true);
        }

        return $this->getMappingInstanceByObjectId($this->rowid);
    }

    function getMappingInstanceByObjectId($id, bool $createNewIfDontExist = true): ?array
    {
        $files = glob($this->getPathLog("mapping_" . $this->mainTable) . '/' . $this->itemName . "-$id-*.json");
        if ($files) {
            $content = file_get_contents($files[0]);
            return json_decode($content, true);
        }

        if ($createNewIfDontExist) {
            return $this->createNewMappingInstance([
                'fk_object_id' => $id,
                'fileName' => $this->itemName . "-$id-temp_$id",
                'createdAt' => time()
            ]);
        }
        return null;
    }
}