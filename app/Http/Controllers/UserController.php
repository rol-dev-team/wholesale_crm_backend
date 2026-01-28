<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\UserSupervisorMapping;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{

    /**
     * GET /api/users
     * Pagination + Search
     */
   public function index(Request $request)
{
    $perPage = (int) $request->get('per_page', 10);
    $search  = $request->get('search');

    $query = User::query();

    if ($search) {
        $query->where(function ($q) use ($search) {
            $q->where('fullname', 'like', "%{$search}%")
              ->orWhere('username', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%");
        });
    }

    $users = $query
        ->orderBy('id', 'desc')
        ->paginate($perPage);

    return response()->json([
        'status' => true,
        'message' => 'User list',
        'data' => $users->items(), // ONLY DATA
        'meta' => [
            'current_page' => $users->currentPage(),
            'per_page'     => $users->perPage(),
            'total'        => $users->total(),
            'last_page'    => $users->lastPage(),
            'from'         => $users->firstItem(),
            'to'           => $users->lastItem(),
        ]
    ]);
}


    /**
     * POST /api/users
     */
    public function store(Request $request)
{
    DB::beginTransaction();

    try {
        $validated = $request->validate([
            'fullname' => 'required|string|max:255',
            'username' => 'required|string|unique:users',
            'email' => 'required|email|unique:users',
            'phone' => 'nullable|string',
            'password' => 'required|min:6',
            'role' => 'required|in:super_admin,admin,supervisor,kam,management',
            'default_kam_id' => 'required|integer',
            'status' => 'nullable|in:active,inactive,blocked',
            'supervisor_ids' => 'required'
        ]);

        $validated['password'] = Hash::make($validated['password']);

        // Create user
        $user = User::create($validated);

        /** -------------------------------
         * Supervisor Mapping Logic
         * --------------------------------
         */
        if ($request->supervisor_ids === 'all') {
            // ALL supervisors
            UserSupervisorMapping::create([
                'user_id' => $user->id,
                'supervisor_id' => 0
            ]);
        } else {
            // Multiple supervisors
            foreach ($request->supervisor_ids as $supervisorId) {
                UserSupervisorMapping::create([
                    'user_id' => $user->id,
                    'supervisor_id' => $supervisorId
                ]);
            }
        }

        DB::commit();

        return response()->json([
            'status' => true,
            'message' => 'User created successfully',
            'data' => $user
        ], 201);

    } catch (\Exception $e) {
        DB::rollBack();

        return response()->json([
            'status' => false,
            'message' => $e->getMessage()
        ], 500);
    }
}


    /**
     * GET /api/users/{id}
     */
    public function show($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not found'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $user
        ]);
    }

    /**
     * PUT /api/users/{id}
     */
    public function update(Request $request, $id)
{
    DB::beginTransaction();

    try {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not found'
            ], 404);
        }

        $validated = $request->validate([
            'fullname' => 'sometimes|required|string|max:255',
            'username' => 'sometimes|unique:users,username,' . $id,
            'email' => 'sometimes|email|unique:users,email,' . $id,
            'phone' => 'nullable|string',
            'password' => 'nullable|min:6',
            'role' => 'sometimes|in:super_admin,admin,supervisor,kam,management',
            'default_kam_id' => 'nullable|exists:users,id',
            'status' => 'sometimes|in:active,inactive,blocked',
            'supervisor_ids' => 'nullable'
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        /** -------------------------------
         * Supervisor Mapping Update
         * --------------------------------
         */
        if ($request->has('supervisor_ids')) {

            // Remove old mappings
            UserSupervisorMapping::where('user_id', $user->id)->delete();

            if ($request->supervisor_ids === 'all') {
                UserSupervisorMapping::create([
                    'user_id' => $user->id,
                    'supervisor_id' => null
                ]);
            } else {
                foreach ($request->supervisor_ids as $supervisorId) {
                    UserSupervisorMapping::create([
                        'user_id' => $user->id,
                        'supervisor_id' => $supervisorId
                    ]);
                }
            }
        }

        DB::commit();

        return response()->json([
            'status' => true,
            'message' => 'User updated successfully',
            'data' => $user
        ]);

    } catch (\Exception $e) {
        DB::rollBack();

        return response()->json([
            'status' => false,
            'message' => $e->getMessage()
        ], 500);
    }
}


    /**
     * DELETE /api/users/{id}
     */
    public function destroy($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not found'
            ], 404);
        }

        $user->delete();

        return response()->json([
            'status' => true,
            'message' => 'User deleted successfully'
        ]);
    }
}
