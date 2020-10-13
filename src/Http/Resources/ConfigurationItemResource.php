<?php

namespace Wamesro\LaravelMagentoVariantGenerator\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ConfigurationItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return parent::toArray($request);
    }
}
