<?php
/**
 * Created by Vincent
 * Email: vincent@pixodeo.net
 */

namespace WMS\Apis;

use WMS\Contracts\RequestActionInterface;
use WMS\Http\HttpAuthRequest;

class Item extends HttpAuthRequest implements RequestActionInterface
{

    public function delete(): bool
    {
        $this->uri = 'Item/?token={TOKEN}&metaId={METAID}&id={IDS}';
        $this->_method = self::METHOD_DELETE;
        return $this->execute();
    }
    public function post(): bool
    {
        $this->uri = 'Item/?token={TOKEN}';
        $this->_method = self::METHOD_POST;
        return $this->execute();
    }
    public function put(): bool
    {
        $this->uri = 'Item/?token={TOKEN}';
        $this->_method = self::METHOD_PUT;
        return $this->execute();
    }
}