<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DemoController extends Controller
{
    public function index()
    {
        return view('dashboard');
    }

    public function data(Request $request)
    {
        return response()->json(['data' => []]);
    }

    public function store(Request $request)
    {
        return response()->json(['message' => 'Creado'], 201);
    }

    public function update(Request $request, $id)
    {
        return response()->json(['message' => 'Actualizado']);
    }

    public function destroy($id)
    {
        return response()->json(['message' => 'Eliminado']);
    }
}