<?php
/**
 * Created by Vincent
 * Email: vincent@pixodeo.net
 */

namespace WMS\Xtent\DolibarrConvert\Contracts;


use WMS\Xtent\Contracts\ObjectDataInterface;

/**
 * @property int row_id
 * @property string ref
 * @property string label
 * @property string description
 * @property string price
 * @see
 */
interface DoSyncWithTranscannInterface
{
    /**
     * Update current Instance with Data from Transcann
     * @param ObjectDataInterface $objectData
     * @param array|null $mapping
     * @return bool
     */
    function updateDataFromTranscann(ObjectDataInterface $objectData, array $mapping = null): bool;

    /**
     * Push current Instance to Transann
     * @param ObjectDataInterface|null $objectData
     * @param array|null $mapping
     * @return bool
     */
    function pushDataToTranscann(ObjectDataInterface $objectData = null, array $mapping = null): bool;

    /**
     * Fetch data from Transcan
     * @param ObjectDataInterface|null $objectData
     * @param array|null $mapping
     * @return bool
     */
    function fetchDataFromTranscann(ObjectDataInterface $objectData = null, array $mapping = null): bool;

    /**
     * Create Mapping Instance
     * @param $data
     * @return array|null
     */
    function createNewMappingInstance($data): ?array;

    /**
     * update Mapping Instance
     * @param array $data
     * @return array|null
     */
    function updateMappingInstance(array $data): ?array;

    /**
     * Get Mapping Instance By Transcann Id
     * @param $id
     * @param bool $createNewIfDontExist
     * @return array|null
     */
    function getMappingInstanceByTranscannId($id, bool $createNewIfDontExist = true): ?array;

    /**
     * Get Mapping Instance by Object Id
     * @param $id
     * @param bool $createNewIfDontExist
     * @return array|null
     */
    function getMappingInstanceByObjectId($id, bool $createNewIfDontExist = true): ?array;
}