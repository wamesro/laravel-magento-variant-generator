<?php

namespace Wamesro\LaravelMagentoVariantGenerator\Http\Controllers;

use App\ConfiguratorItem;
use App\ConfiguratorItemVariant;
use App\Exports\MagentoProductsExport;
use App\Http\Resources\ProductResource;
use App\Mockup;
use App\MockupColor;
use App\Product;
use ClassicO\NovaMediaLibrary\API;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;
use Intervention\Image\ImageManager;
use Maatwebsite\Excel\Facades\Excel;
use Wamesro\LaravelMagentoVariantGenerator\Http\Resources\ConfigurationItemResource;
use Wamesro\LaravelMagentoVariantGenerator\Http\Resources\ConfigurationItemVariantResource;

class Tool {

    public function create(Request $request) {
        $configurationItem = new ConfiguratorItem();
        $configurationItem->mockup_ids = $request->mockups;
        $configurationItem->state = ConfiguratorItem::STATE_CREATED;
        $configurationItem->save();

        return response([
            'status' => 200,
            'id' => $configurationItem->id
        ]);
    }

    public function delete($id) {
        $item = ConfiguratorItem::find($id);
        $item->state = ConfiguratorItem::STATE_DELETED;
        $item->save();

        return response([
            'status' => 200,
            'id' => $id
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
        $configurationItem->state = ConfiguratorItem::STATE_PATTERNS;
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

        $configurationItem->state = ConfiguratorItem::STATE_VARIANTS;
        $configurationItem->save();

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

        $configurationItem = ConfiguratorItem::find($id);
        $configurationItem->state = ConfiguratorItem::STATE_EDIT_PATTERNS;
        $configurationItem->save();

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
        $data = $request->all();
        unset($data['mockup_id']);
        ConfiguratorItemVariant::where(['configurator_item_id' => $id, 'mockup_id' => $mockupId])->update(['position' => \GuzzleHttp\json_encode($data)]);
    }

    public function getVariantSiblings($variantId) {
         $variant = ConfiguratorItemVariant::find($variantId);
         $siblings = ConfiguratorItemVariant::where(['configurator_item_id' => $variant->configurator_item_id, 'mockup_id' => $variant->mockup_id, 'enabled' => 1])->get();


         foreach ($siblings as $key => $sibling) {
            $mockupColor = MockupColor::find($sibling->color_id);
            $siblings[$key]['title'] = $mockupColor->color->name;
            $siblings[$key]['background'] = API::getFiles($mockupColor->image);
            $siblings[$key]['pattern'] =  Storage::url($sibling->pattern);
         }
         return response($siblings);
    }

    public function getStates() {
        $configuratorItems = ConfiguratorItem::where('state', '>', 0)->get();

        $data = [];
        foreach ($configuratorItems as $configuratorItem) {
            $mockups = Mockup::find(explode(',', $configuratorItem->mockup_ids));

            $products = [];
            foreach ($mockups as $mockup) {
                $products[] = $mockup->product->title . ' | ' . $mockup->title;
            }

            $patterns = [];
            if ($configuratorItem->patterns) {
                foreach (\GuzzleHttp\json_decode($configuratorItem->patterns) as $pattern) {
                    $patterns[] = Storage::url($pattern);
                }
            }


            if ($configuratorItem->state == ConfiguratorItem::STATE_CREATED) {
                $data[] = [
                    'id' => $configuratorItem->id,
                    'products' => $products,
                    'state' => 'STATE_CREATED',
                    'url' => '/laravel-magento-variant-generator-upload/'.$configuratorItem->id,
                    'patterns' => $patterns
                ];
            }
            if ($configuratorItem->state == ConfiguratorItem::STATE_PATTERNS) {
                $data[] = [
                    'id' => $configuratorItem->id,
                    'products' => $products,
                    'state' => 'STATE_PATTERNS',
                    'url' => '/laravel-magento-variant-generator-upload/'.$configuratorItem->id,
                    'patterns' => $patterns
                ];
            }
            if ($configuratorItem->state == ConfiguratorItem::STATE_VARIANTS) {
                $data[] = [
                    'id' => $configuratorItem->id,
                    'products' => $products,
                    'state' => 'STATE_VARIANTS',
                    'url' => '/laravel-magento-variant-generator-variants/'.$configuratorItem->id,
                    'patterns' => $patterns
                ];
            }
            if ($configuratorItem->state == ConfiguratorItem::STATE_EDIT_PATTERNS) {
                $data[] = [
                    'id' => $configuratorItem->id,
                    'products' => $products,
                    'state' => 'STATE_EDIT_PATTERNS',
                    'url' => '/laravel-magento-variant-generator-place-patterns/'.$configuratorItem->id,
                    'patterns' => $patterns
                ];
            }
        }
        return response($data);
    }

    public function exportReady($id) {
        $item = ConfiguratorItem::find($id);

        if ($item->state == ConfiguratorItem::STATE_EXPORT_PROCESS) {
            return response([
                'status' => ConfiguratorItem::STATE_EXPORT_PROCESS,
                'id' => $item->id,
                'variants' => ConfigurationItemVariantResource::collection(ConfiguratorItemVariant::where('configurator_item_id', $item->id)->get())
            ]);
        }

        if ($item->state == ConfiguratorItem::STATE_EXPORT_SUCCESS) {
            return response([
                'status' => ConfiguratorItem::STATE_EXPORT_SUCCESS,
                'id' => $item->id,
                'variants' => ConfigurationItemVariantResource::collection(ConfiguratorItemVariant::where('configurator_item_id', $item->id)->get())
            ]);
        }

        $variants = ConfiguratorItemVariant::where('configurator_item_id', $item->id)->pluck('id','id');
        foreach ($variants as $variant) {
            $this->resultImage($variant);
        }

        $variants = ConfiguratorItemVariant::where('configurator_item_id', $item->id)->get();

        $item->state = ConfiguratorItem::STATE_EXPORT_READY;
        $item->save();

        return response([
            'status' => ConfiguratorItem::STATE_EXPORT_READY,
            'id' => $item->id,
            'variants' => ConfigurationItemVariantResource::collection($variants)
        ]);
    }

    public function resultImage($variantId) {
        $variant = ConfiguratorItemVariant::find($variantId);

        $background = Storage::disk('public')->get(API::getFiles(MockupColor::find($variant->color_id)->image, null, true)->path);
        $pattern = Storage::disk('public')->get($variant->pattern);

        $img = Image::make($background)
            ->resize(566*2, 566*2);

        $position = json_decode($variant->position, true);

        $scaleX = $position['scaleX'];
        $scaleY = $position['scaleY'];
        $top = $position['top'];
        $left = $position['left'];
        $angle = $position['angle'];

        if ($angle > 0 && $angle <= 90) {
            $left = explode(',', $position['lineCoords_bl'])[0];
        }

        if ($angle > 90 && $angle <= 180) {
            $top = explode(',', $position['lineCoords_bl'])[1];
            $left = $position['lineCoords_br'];
        }

        if ($angle > 180 && $angle <= 270) {
            $top = explode(',', $position['lineCoords_br'])[1];
            $left = explode(',', $position['lineCoords_tr'])[0];
        }

        if ($angle > 270 && $angle <= 360) {
            $top = explode(',', $position['lineCoords_tr'])[1];
            $left = $position['left'];
        }

        $angle = -$angle;

        $patternImage = Image::make($pattern)->resize(intval($scaleX)*2, intval($scaleY)*2)->rotate($angle);
        $img->insert($patternImage, 'top-left', intval($left)*2, intval($top)*2);

        $imagePath = 'patterns/'.$variant->configurator_item_id.'/'.basename($variant->pattern).'-final-'.time().'.png';

        $img->save(storage_path('app/public/'.$imagePath));

        $image = API::upload($img->basePath(), '/products/');

        Storage::delete($imagePath);

        $variant->final_image = $image->id;
        $variant->save();
    }

    public function getCsv($id) {
        $name = 'export-'.date('d. m. Y. H:i:s').'.csv';
        $item = ConfiguratorItem::find($id);
        return Excel::download(new MagentoProductsExport($item), $name);
    }


}
