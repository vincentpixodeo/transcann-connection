<?php
/**
 * Created by Vincent
 * Email: vincent@pixodeo.net
 */

namespace WMS\Helpers\Logs;

class LogFile
{
    public function __construct(protected string $directory, protected $multipleFiles = false)
    {}

    function write($data, string $fileName): void
    {
        $directory = trim($this->directory, '/\\');

        if (!file_exists($directory)) {
            mkdir($directory);
        }

        if ($this->multipleFiles) {
            foreach ($data as $key => $item) {
                $content = json_encode($item);
                file_put_contents($directory."/{$fileName}-{$key}.json", $content);
            }
        } else {
            $content = json_encode($data);
            file_put_contents($directory."/{$fileName}.json", $content);

        }
    }
}