<?php
/**
 * Created by Vincent
 * Email: vincent@pixodeo.net
 */

namespace WMS\Xtent\Contracts;

trait HasLoadByKeysFunction
{
    function load(string $key): bool
    {
        return $this->execute([
            'keys' => [$key]
        ]);
    }

    function validate(array $data): bool
    {
        if (empty($data['keys'])) {
            $this->addError('The keys is required');
        } elseif (!is_array($data['keys'])) {
            $this->addError('The keys must be a array');
        } elseif (empty($data['keys'])) {
            $this->addError('The keys must have any a value');
        }
        return parent::validate($data);
    }

    abstract function execute(...$arguments): bool;
}