<?php
/**
 * Created by Vincent
 * Email: vincent@pixodeo.net
 */

namespace WMS\Xtent\DolibarrConvert;

use WMS\Xtent\Contracts\AbstractObjectData;
use WMS\Xtent\Contracts\ObjectDataInterface;
use WMS\Xtent\DolibarrConvert\Contracts\CanSaveDataInterface;
use WMS\Xtent\DolibarrConvert\Contracts\ConvertTranscanInteface;
use WMS\Xtent\DolibarrConvert\Contracts\ConvertTranscanTrait;
use WMS\Xtent\DolibarrConvert\Contracts\DoSyncWithTranscannInterface;
use WMS\Xtent\DolibarrConvert\Contracts\DoSyncWithTranscannTrait;

abstract class Model extends AbstractObjectData implements ConvertTranscanInteface, ObjectDataInterface, DoSyncWithTranscannInterface, CanSaveDataInterface
{
    use ConvertTranscanTrait;
    use DoSyncWithTranscannTrait;

    public function __construct(array $data = [])
    {
        static::boot();
        parent::__construct($data);
    }


    static protected function boot(): void
    {
    }

}