<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    /**
     * Display a listing of users
     */
    public function index()
    {
        // Redirect to the existing PHP users page
        return redirect('/admin/users.php');
    }

    /**
     * Show the form for creating a new user
     */
    public function create()
    {
        // Redirect to the existing PHP create user page
        return redirect('/admin/create_admin_user.php');
    }

    /**
     * Store a newly created user
     */
    public function store(Request $request)
    {
        // Redirect to the existing PHP create user page
        return redirect('/admin/create_admin_user.php');
    }

    /**
     * Display the specified user
     */
    public function show($id)
    {
        // Redirect to the existing PHP users page
        return redirect('/admin/users.php');
    }

    /**
     * Show the form for editing the specified user
     */
    public function edit($id)
    {
        // Redirect to the existing PHP users page
        return redirect('/admin/users.php');
    }

    /**
     * Update the specified user
     */
    public function update(Request $request, $id)
    {
        // Redirect to the existing PHP users page
        return redirect('/admin/users.php');
    }

    /**
     * Remove the specified user
     */
    public function destroy($id)
    {
        // Redirect to the existing PHP users page
        return redirect('/admin/users.php');
    }
} 