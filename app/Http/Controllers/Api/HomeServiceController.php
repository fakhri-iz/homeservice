<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\HomeServiceApiResource;
use App\Models\HomeService;
use Illuminate\Http\Request;

class HomeServiceController extends Controller
{
    //
    public function index(Request $request)
    {
        $homeServices = HomeService::with((['category']));

        if ($request->has('category_id')) {
            $homeServices->where('category_id', $request->input('category_id'));
        }
        if ($request->has('is_popular')) {
            $homeServices->where('is_popular', $request->input('is_popular'));
        }
        if ($request->has('limit')) {
            $homeServices->limit($request->input('limit'));
        }

        return HomeServiceApiResource::collection($homeServices->get());
    }

    //model binding
    public function show(HomeService $homeService)
    {
        //eager load to handle n+1 query problem
        $homeService->load(['category', 'benefits', 'testimonials']);

        return new HomeServiceApiResource($homeService);
    }
}
