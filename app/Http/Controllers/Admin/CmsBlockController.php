<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Page;

class CmsBlockController extends Controller
{
    public function getAvailableBlocks($pageId)
    {
        $page = Page::findOrFail($pageId);
        $type = $page->page_type;
        
        $blocks = config("cms_blocks.mappings.{$type}", []);
        
        return response()->json([
            'success' => true,
            'data' => $blocks
        ]);
    }
}

