<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $roles = Role::paginate(10); // 10 data perhalaman
        return response()->json([
            'success' => true,
            'message' => 'List Data Role',
            'data' => $roles,
            'code' => 200
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $message_error = [
            'roles_name.required' => 'Nama Role wajib isi',
            'roles_name.unique' => 'Nama Role sudah ada'
        ];

        $request->validate([
            'roles_neme' => 'required|unique:roles'
        ], $message_error);

        $role = Role::create([
            'roles_name' => $request->roles_name
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data Role berhasil ditambahkan',
            'data' => $role,
            'code' => 201
        ]);

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
        $role = Role::find($id);
        if (!$role) {
            return response()->json([
                'success' => false,
                'message' => 'Data Role tidak ditemukan',
                'data' => null,
                'code' => 404
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Data Role ditemukan',
            'data' => $role,
            'code' => 200
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
        try{
            $role = Role::findOrFail($id);
            $request->validate([
                'roles_name' => 'required|unique:roles,roles_name,' . $id
            ], [
                'roles_name.required' => 'Nama Role wajib isi',
                'roles_name.unique' => 'Nama Role sudah ada'
            ]);
            $role->update([
                'roles_name' => $request->roles_name
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Data Role berhasil diupdate',
                'data' => $role,
                'code' => 200
            ]);
        } catch (\Exception $th) {
            return response()->json([
                'success' => false,
                'message' => 'Data Role tidak ditemukan ' . $th->getMessage(),
                'data' => null,
                'code' => 404
            ]);

        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
        try {
            $role = Role::findOrFail($id);
            $role->delete();

            return response()->json([
                'success' => true,
                'message' => 'Data Role berhasil dihapus',
                'data' => null,
                'code' => 200
            ]);
        } catch (\Exception $th) {
            return response()->json([
                'success' => false,
                'message' => 'Data Role tidak ditemukan ' . $th->getMessage(),
                'data' => null,
                'code' => 404
            ]);
        }
    }
}
