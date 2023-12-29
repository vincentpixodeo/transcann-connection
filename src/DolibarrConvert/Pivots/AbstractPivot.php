<?php
/**
 * Created by Vincent
 * Email: vincent@pixodeo.net
 */

namespace WMS\Xtent\DolibarrConvert\Pivots;

use WMS\Xtent\Contracts\AbstractObjectData;
use WMS\Xtent\Contracts\ObjectDataInterface;
use WMS\Xtent\DolibarrConvert\Contracts\CanSaveDataByLogTrait;
use WMS\Xtent\DolibarrConvert\Contracts\CanSaveDataInterface;

/**
 * @property int fk_object_id
 * @property string transcann_id
 * @property string transcan_meta_id
 * @property string transcan_payload
 */
abstract class AbstractPivot extends AbstractObjectData implements ObjectDataInterface, CanSaveDataInterface
{
    use CanSaveDataByLogTrait;

    function fetch(...$agruments): ?static
    {
        $objectId = $agruments[0] ?? $this->fk_object_id;
        $newInstance = $objectId != $this->fk_object_id;

        $transcannId = $agruments[1] ?? null;

        if (empty($objectId)) {
            return null;
        }

        if ($objectId) {
            $files = glob($this->getPathLog($this->getMainTable()) . '/' . $this->itemName . "-$objectId-*.json");
        } elseif ($transcannId) {
            $files = glob($this->getPathLog($this->getMainTable()) . '/' . $this->itemName . "-*-$transcannId.json");
        }
        if ($files) {
            $data = json_decode(file_get_contents($files[0]), true);

            return $newInstance ? new static($data) : $this->addData($data);
        }

        $initData = [
            'fileName' => $this->itemName . "-$objectId-temp_$objectId",
            'fk_object_id' => $objectId,
            'createdAt' => time()
        ];

        $instance = $newInstance ? new static($initData) : $this->addData($initData);
        $instance->save();
        return $instance;
    }

    function save(array $data = []): bool
    {
        $this->addData($data);
        $fileName = $this->getData('fileName');
        $newFileName = $fileName;
        if (!$this->fk_object_id) {
            return false;
        }
        if ($this->transcann_id) {
            $newFileName = preg_replace("/({$this->fk_object_id})-(temp_\d+)/", '$1-' . $this->transcann_id, $newFileName);
        }
        $path = $this->getPathLog($this->getMainTable());

        if (file_exists($path . '/' . $fileName . '.json')) {
            if ($fileName != $newFileName) {
                rename($path . '/' . $fileName . '.json', $path . '/' . $newFileName . '.json');
                $this->addData(['fileName' => $newFileName]);
            }
        }
        $this->addData(['updatedAt' => time()]);
        $this->getLog($this->getMainTable())->write($this->toArray(), $this->getData('fileName'));
        return true;
    }
}