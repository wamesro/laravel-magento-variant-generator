<?php

namespace Wamesro\LaravelMagentoVariantGenerator\Http\Resources;

use App\Color;
use App\ConfiguratorItem;
use App\Mockup;
use App\MockupColor;
use ClassicO\NovaMediaLibrary\API;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ConfigurationItemVariantResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {

        $mockupColor = MockupColor::find($this->color_id);
        $patterns = ConfiguratorItem::find($this->configurator_item_id)->patterns;
        if ($patterns) {
            $patterns = \GuzzleHttp\json_decode($patterns);
            foreach ($patterns as $key => $pattern) {
                $patterns[$key] = [
                    'basename' => basename($pattern),
                    'url' => Storage::url($pattern)
                ];
            }
        } else {
            $patterns = [];
        }
        return [
            // Base
            'configurator_item_id' => $this->configurator_item_id,
            'mockup_id' => $this->mockup_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            // Custom
            'id' => $this->id,
            'enabled' => $this->enabled === 1 ? 1 : 0,
            'image' => API::getFiles($mockupColor->image, $imgSize = 'thumb'),
            'color' => Color::find($mockupColor->color_id)->name,
            'final_image' => $this->final_image ? API::getFiles($this->final_image) : null,
            'pattern' => [
                'basename' => basename($this->pattern),
                'url' => Storage::url($this->pattern)
            ],
            'patterns' => $patterns
        ];
    }
}
