<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SaleController extends Controller
{
    /**
     * Display a listing of sales
     */
    public function index()
    {
        // Redirect to the existing PHP sales page
        return redirect('/admin/sales.php');
    }

    /**
     * Show the form for creating a new sale
     */
    public function create()
    {
        // Redirect to the existing PHP create sale page
        return redirect('/admin/create_sale.php');
    }

    /**
     * Store a newly created sale
     */
    public function store(Request $request)
    {
        // Redirect to the existing PHP create sale page with POST data
        return redirect('/admin/create_sale.php');
    }

    /**
     * Display the specified sale
     */
    public function show($id)
    {
        // Redirect to the existing PHP view sale page
        return redirect("/admin/view_sale.php?id={$id}");
    }

    /**
     * Show the form for editing the specified sale
     */
    public function edit($id)
    {
        // Redirect to the existing PHP edit sale page
        return redirect("/admin/edit_sale.php?id={$id}");
    }

    /**
     * Update the specified sale
     */
    public function update(Request $request, $id)
    {
        // Redirect to the existing PHP edit sale page
        return redirect("/admin/edit_sale.php?id={$id}");
    }

    /**
     * Remove the specified sale
     */
    public function destroy($id)
    {
        // Redirect to the existing PHP delete sale page
        return redirect("/admin/delete_sale.php?id={$id}");
    }
} 