<?php
/**
 * Created by Vincent
 * Email: vincent@pixodeo.net
 */

namespace WMS\Xtent\Apis\GenericService;

use WMS\Xtent\Contracts\RequestActionInterface;
use WMS\Xtent\Http\HttpAuthRequest;

class Retrieve extends HttpAuthRequest implements RequestActionInterface
{
	public function execute(...$arguments): bool 
	{
		$data = [
			"filters" => [
				"IsPrimitive"=> true,
				"UIFlag"=> 0,
				"JoinType"=> 0,
				"ParentPropertyName"=> 0,
				"PrimitiveFilter" => [
					"PropertyName" => "WarehousePartyCategory",
					"TypeOfFilter" => 0,
					"Values#System.Int64" => 2
				]
			]
		]; 
		return parent::execute($data);
	}
}