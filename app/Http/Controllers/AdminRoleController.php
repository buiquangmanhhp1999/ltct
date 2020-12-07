<?php

namespace App\Http\Controllers;

use App\Permission;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Role;
use App\Traits\DeleteModelTrait;
use App\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use PHPUnit\Exception;
use Illuminate\Support\Facades\DB;

class AdminRoleController extends Controller
{
    private $role;
    private $permission;
    public function __construct(Role $role, Permission $permission)
    {
        $this->role = $role;
        $this->permission = $permission;
    }

    public function index()
    {
        $roles = $this->role->paginate(10);
        return view('role.index', compact('roles'));

    }

    public function create()
    {
        $permissionsParent = $this->permission->where('parent_id', 0)->get();
        return view('role.add', compact('permissionsParent'));
    }

    public function store(Request $request)
    {
        $role = $this->role->create([
            'name' => $request->name,
            'display_name' => $request->display_name
        ]);

        $role->permissions()->attach($request->permission_id);
        return redirect()->route('roles.index');


    }
    public function edit($id)
    {
        $permissionsParent = $this->permission->where('parent_id', 0)->get();
        $role = $this->role->find($id);
        $pemissionsChecked = $role->permissions;
        return view('role.edit', compact('permissionsParent', 'role', 'pemissionsChecked'));
    }

    public function update(Request $request, $id)
    {
        $role = $this->role->find($id);
        $role->update([
            'name' => $request->name,
            'display_name' => $request->display_name
        ]);
        $role->permissions()->sync($request->permission_id);
        return redirect()->route('roles.index');
    }
    public function delete($id)
    {
        $this->role->find($id)->delete();
        return redirect()->route('roles.index');
    }

}
