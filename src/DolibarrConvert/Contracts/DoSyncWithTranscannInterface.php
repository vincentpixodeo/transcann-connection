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
     */
    function updateDataFromTranscann();

    /**
     * Push current Instance to Transann
     */
    function pushDataToTranscann();

    /**
     * get Mapping Instance
     * @param array $data
     * @return CanSaveDataInterface&ObjectDataInterface
     */
    function getMappingInstance(array $data = []): ObjectDataInterface&CanSaveDataInterface;
}