<?php
/**
 * Created by Vincent
 * Email: vincent@pixodeo.net
 */

namespace WMS\Xtent\DolibarrConvert;

use Exception;
use Throwable;
use WMS\Xtent\Http\Log;

class TranscannSyncException extends Exception
{

    /**
     * @param Throwable $error
     * @param Log[] $logs
     */
    public function __construct(protected Throwable $error, protected array $logs = [])
    {
        $message = $error->getMessage();
        $code = $error->getCode();
        if (empty($message) && $log = $this->getLastLog()) {
            $message = $log->getResponse();
            $code = $log->getResponseCode();
        }
        parent::__construct($message, $code, $error);
    }

    /**
     * @return Log[]
     */
    public function getLogs(): array
    {
        return $this->logs;
    }

    /**
     * @return Log|null
     */
    function getLastLog(): ?Log
    {
        if ($index = array_key_last($this->logs)) {
            return $this->logs[$index];
        }
        return null;
    }

}