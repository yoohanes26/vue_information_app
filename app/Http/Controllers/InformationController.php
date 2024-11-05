<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Helper\ApiFormat;
use App\Models\AsmsInformation;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InformationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $information = AsmsInformation::searchInformation($request)->paginate(100);

        $format = ApiFormat::transform($information);

//        return $format;
//
//        dd($information, json_encode($format));

        return Inertia::render('Information/Index', [
//            'informations' => $format
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
