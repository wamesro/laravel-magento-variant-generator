<?php

namespace Wamesro\LaravelMagentoVariantGenerator\Http\Controllers;

use App\ConfiguratorItem;
use Illuminate\Http\Request;

class Tool {

    public function create(Request $request) {
        // Patterns
        $newPatternsPath = [];
        foreach ($request->patterns as $pattern) {
            $newPatternsPath[] = $pattern->store('patterns', 'public');
        };

        $configurationItem = new ConfiguratorItem();
        $configurationItem->mockup_ids = $request->mockups;
        $configurationItem->patterns = \GuzzleHttp\json_encode($newPatternsPath);
        $configurationItem->save();

        return response([
            'status' => 'ok',
            'id' => $configurationItem->id
        ]);
    }

    public function get(Request $request) {
        return response([
            'vsetko' => 'ok'
        ]);
    }

}
