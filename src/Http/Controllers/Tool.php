<?php

namespace Wamesro\LaravelMagentoVariantGenerator\Http\Controllers;

use App\ConfiguratorItem;
use App\ConfiguratorItemVariant;
use App\Http\Resources\ProductResource;
use App\Mockup;
use App\MockupColor;
use App\Product;
use ClassicO\NovaMediaLibrary\API;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Wamesro\LaravelMagentoVariantGenerator\Http\Resources\ConfigurationItemResource;
use Wamesro\LaravelMagentoVariantGenerator\Http\Resources\ConfigurationItemVariantResource;

class Tool {

    public function create(Request $request) {
        $configurationItem = new ConfiguratorItem();
        $configurationItem->mockup_ids = $request->mockups;
        $configurationItem->save();

        return response([
            'status' => 200,
            'id' => $configurationItem->id
        ]);
    }

    public function get($id) {
        return new ConfigurationItemResource(ConfiguratorItem::find($id));
    }

    public function appendPatterns($id, Request $request) {
        $configurationItem = ConfiguratorItem::find($id);

        if ($configurationItem->patterns == null) {
            $patterns = [];
        } else {
            $patterns = \GuzzleHttp\json_decode($configurationItem->patterns);
        }


        $filename = Str::slug(pathinfo($request->file->getClientOriginalName(), PATHINFO_FILENAME), '-');
        $extension = pathinfo($request->file->getClientOriginalName(), PATHINFO_EXTENSION);
        $filename .= '.' . $extension;

        $newPath = $request->file->storeAs('patterns/'.$id.'/', $filename);
        $patterns[] = $newPath;
        $configurationItem->patterns = \GuzzleHttp\json_encode($patterns);
        $configurationItem->save();
    }

    public function deletePatterns($id, Request $request) {
        $configurationItem = ConfiguratorItem::find($id);

        if ($configurationItem->patterns == null) {
            $patterns = [];
        } else {
            $patterns = \GuzzleHttp\json_decode($configurationItem->patterns);
        }

        foreach ($patterns as $key => $pattern) {
            $filename = Str::slug(pathinfo($request->filename, PATHINFO_FILENAME), '-');
            $extension = pathinfo($request->filename, PATHINFO_EXTENSION);
            $filename .= '.' . $extension;
            if (basename($pattern) == $filename) unset($patterns[$key]);
        }

        if (empty($patterns)) {
            $patterns = null;
        } else {
            $patterns = \GuzzleHttp\json_encode(array_values($patterns));
        }

        $configurationItem->patterns = $patterns;
        $configurationItem->save();

        return response([
            'status' => 200,
            'id' => $configurationItem->id
        ]);
    }

    public function getPatterns($id) {
        $configurationItem = ConfiguratorItem::find($id);

        if ($configurationItem->patterns == null) {
            $patterns = [];
        } else {
            $patterns = \GuzzleHttp\json_decode($configurationItem->patterns);
        }

        $links = [];
        foreach ($patterns as $pattern) {
            $url = Storage::url($pattern);
            $extension = pathinfo($url, PATHINFO_EXTENSION);
            $links[] = [
                'url' => Storage::url($pattern),
                'mimetype' => $extension == 'png' ? 'image/png' : 'image/jpeg',
                'size' => Storage::size($pattern),
                'name' => basename($pattern)
            ];
        }

        return response($links);
    }


    public function createVariants($id, Request $request) {
        $configurationItem = ConfiguratorItem::find($id);
        $mockupIds = explode(',', $configurationItem->mockup_ids);

        foreach ($mockupIds as $mockupId) {
            $mockupColors = MockupColor::all()->where('mockup_id', $mockupId);
            foreach ($mockupColors as $mockupColor) {
                $exist = ConfiguratorItemVariant::where(['configurator_item_id' => $configurationItem->id,'mockup_id' => $mockupId, 'color_id' => $mockupColor->id])->first();
                if ($exist) continue;

                $configurationItemVariant = new ConfiguratorItemVariant();
                $configurationItemVariant->configurator_item_id = $configurationItem->id;
                $configurationItemVariant->mockup_id = $mockupId;
                $configurationItemVariant->color_id = $mockupColor->id;
                if ($configurationItem->patterns == null) {
                    $patterns = [];
                } else {
                    $patterns = \GuzzleHttp\json_decode($configurationItem->patterns);
                }
                $configurationItemVariant->pattern = $patterns[0];
                $configurationItemVariant->save();
            }
        }

        return response([
            'status' => 200,
            'id' => $configurationItem->id
        ]);
    }

    public function getVariants($id) {
        return ConfigurationItemVariantResource::collection(ConfiguratorItemVariant::all()->where('configurator_item_id', $id));
    }

    public function getVariantsProducts($id) {
        $configurationItem = ConfiguratorItem::find($id);
        $mockupIds = explode(',', $configurationItem->mockup_ids);

        $productIds = Mockup::whereIn('id', $mockupIds)->pluck('product_id', 'id');

        $productsPivot = [];
        foreach ($productIds as $mockupId => $productId) {
            $productsPivot[] = [
                'product_id' => $productId,
                'mockup_id' => $mockupId,
            ];
        }

        return response(
            [
                'mockups' => $productsPivot,
                'products' => ProductResource::collection(Product::all()->whereIn('id', $productIds))
            ]
        );
    }

    public function changePattern($variantId, Request $request) {
        $request->validate([
            'patternBasename' => 'string'
        ]);

        $variant = ConfiguratorItemVariant::find($variantId);
        $patterns = json_decode($variant->configuratorItem->patterns, true);
        foreach ($patterns as $key => $pattern) {
            if (basename($pattern) == $request->patternBasename) {
                $variant->pattern = $pattern;
                $variant->save();
            }
        }
    }

    public function getPlacePatternsData($id) {
        $variants = ConfiguratorItemVariant::where(['configurator_item_id' => $id, 'enabled' => 1])->get();

        $mockups = [];
        foreach ($variants as $id => $variant) {
            $data = $variant->toArray();
            $data['background'] = API::getFiles(MockupColor::find($variant->color_id)->image);
            $data['pattern'] =  Storage::url($variant->pattern);
            $data['title'] = $variant->mockup->product->title . ' | ' .$variant->mockup->title;
            $mockups[$variant->mockup_id] = $data;
        }

        return response($mockups);
    }

    public function setPatternPosition($id, Request $request) {
        $mockupId = $request->mockup_id;
        unset($request->mockup_id);
        ConfiguratorItemVariant::where(['configurator_item_id' => $id, 'mockup_id' => $mockupId])->update($request->all());
    }

}
