<?php

namespace App\Http\Controllers;

use App\Models\PlatformUser;
use App\Http\Requests\StorePlatformUserRequest;
use App\Http\Requests\UpdatePlatformUserRequest;

class PlatformUserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
    public function store(StorePlatformUserRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(PlatformUser $platformUser)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PlatformUser $platformUser)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePlatformUserRequest $request, PlatformUser $platformUser)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PlatformUser $platformUser)
    {
        //
    }
}
