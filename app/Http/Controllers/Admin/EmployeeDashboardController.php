<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;

class EmployeeDashboardController extends Controller
{
    public function index(): RedirectResponse
    {
        return redirect()->route('employee.invoices.index');
    }
}
