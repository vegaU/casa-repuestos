<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:255'],
        ]);

        $user = User::query()->where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Las credenciales proporcionadas son incorrectas.'],
            ]);
        }

        if (! $user->is_super_admin && ! $user->tenants()->wherePivot('is_active', true)->exists()) {
            throw ValidationException::withMessages([
                'email' => ['El usuario no tiene una empresa activa asignada.'],
            ]);
        }

        $abilities = $user->is_super_admin ? ['*'] : ['tenant-access'];
        $token = $user->createToken($credentials['device_name'] ?? 'api-client', $abilities);

        return response()->json([
            'token' => $token->plainTextToken,
            'token_type' => 'Bearer',
            'user' => $this->userData($user),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json(status: 204);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->load('tenants');

        return response()->json([
            'user' => $this->userData($user),
            'tenants' => $user->tenants->map(fn ($tenant) => [
                'id' => $tenant->id,
                'name' => $tenant->name,
                'role' => $tenant->pivot->role,
            ])->values(),
        ]);
    }

    public function changePassword(Request $request): JsonResponse
    {
        $data = $request->validate([
            'password' => ['required', 'string', 'min:10', 'confirmed'],
        ]);
        $request->user()->update(['password' => Hash::make($data['password']), 'must_change_password' => false]);
        return response()->json(['data' => $this->userData($request->user()->fresh())]);
    }

    private function userData(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'is_super_admin' => $user->is_super_admin,
            'must_change_password' => $user->must_change_password,
        ];
    }
}
