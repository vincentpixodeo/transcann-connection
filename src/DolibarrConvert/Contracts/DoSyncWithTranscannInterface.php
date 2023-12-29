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
     * Fetch And Update current Instance with Data from Transcann
     * @param array $data
     * @return bool
     */
    function updateDataFromTranscann(array $data = []): bool;

    /**
     * Push current Instance to Transann
     * @param array $data
     * @return bool
     */
    function pushDataToTranscann(array $data = []): bool;

    /**
     * get Mapping Instance
     * @param array $data
     * @return CanSaveDataInterface&ObjectDataInterface
     */
    function getMappingInstance(array $data = []): ObjectDataInterface&CanSaveDataInterface;
}