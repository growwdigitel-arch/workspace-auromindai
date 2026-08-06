<?php

namespace Modules\AssetManagement\Http\Controllers;

use App\Helper\Reply;
use App\Http\Controllers\AccountBaseController;
use Illuminate\Http\Request;
use Modules\AssetManagement\Entities\Asset;
use App\Models\User;

class AssetManagementController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'Asset Management';
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->assets = Asset::with('user')->get();
        return view('assetmanagement::index', $this->data);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->employees = User::allEmployees();
        return view('assetmanagement::create', $this->data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'serial_number' => 'required|unique:assets,serial_number',
            'type' => 'required',
            'status' => 'required',
        ]);

        $asset = new Asset();
        $asset->name = $request->name;
        $asset->serial_number = $request->serial_number;
        $asset->type = $request->type;
        $asset->status = $request->status;
        $asset->user_id = $request->user_id;
        $asset->value = $request->value;
        $asset->description = $request->description;
        $asset->save();

        return Reply::success('Asset added successfully.');
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        return view('assetmanagement::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $this->asset = Asset::findOrFail($id);
        $this->employees = User::allEmployees();
        return view('assetmanagement::edit', $this->data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required',
            'serial_number' => 'required|unique:assets,serial_number,' . $id,
            'type' => 'required',
            'status' => 'required',
        ]);

        $asset = Asset::findOrFail($id);
        $asset->name = $request->name;
        $asset->serial_number = $request->serial_number;
        $asset->type = $request->type;
        $asset->status = $request->status;
        $asset->user_id = $request->user_id;
        $asset->value = $request->value;
        $asset->description = $request->description;
        $asset->save();

        return Reply::success('Asset updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        Asset::destroy($id);
        return Reply::success('Asset deleted successfully.');
    }
}
