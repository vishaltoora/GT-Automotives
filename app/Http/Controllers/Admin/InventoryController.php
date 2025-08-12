<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InventoryController extends Controller
{
    /**
     * Display a listing of inventory items
     */
    public function index()
    {
        // Redirect to the existing PHP inventory page
        return redirect('/admin/inventory.php');
    }

    /**
     * Show the form for creating a new inventory item
     */
    public function create()
    {
        // Redirect to the existing PHP add product page
        return redirect('/admin/add_product.php');
    }

    /**
     * Store a newly created inventory item
     */
    public function store(Request $request)
    {
        // Redirect to the existing PHP add product page
        return redirect('/admin/add_product.php');
    }

    /**
     * Display the specified inventory item
     */
    public function show($id)
    {
        // Redirect to the existing PHP view product page
        return redirect("/admin/view_product.php?id={$id}");
    }

    /**
     * Show the form for editing the specified inventory item
     */
    public function edit($id)
    {
        // Redirect to the existing PHP edit product page
        return redirect("/admin/edit_product.php?id={$id}");
    }

    /**
     * Update the specified inventory item
     */
    public function update(Request $request, $id)
    {
        // Redirect to the existing PHP edit product page
        return redirect("/admin/edit_product.php?id={$id}");
    }

    /**
     * Remove the specified inventory item
     */
    public function destroy($id)
    {
        // Redirect to the existing PHP delete product page
        return redirect("/admin/delete_product.php?id={$id}");
    }
} 