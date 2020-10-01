<?php

namespace Wamesro\LaravelMagentoVariantGenerator\Http\Controllers;

use App\ConfiguratorItem;
use Illuminate\Http\Request;
use Wamesro\LaravelMagentoVariantGenerator\Http\Resources\ConfigurationItemResource;

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

        $newPath = $request->file->storeAs('patterns/'.$id.'/', $request->file->getClientOriginalName());
        $patterns[] = $newPath;
        $configurationItem->patterns = \GuzzleHttp\json_encode($patterns);
        $configurationItem->save();
    }

}
