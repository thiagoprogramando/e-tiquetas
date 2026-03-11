<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UserController extends Controller {
    
    public function show ($uuid) {

        $user = User::where('uuid', $uuid)->first();
        if (!$user) {
            return redirect()->back()->with('infor', 'Conta não encontrada/disponível, verifique os dados e tente novamente!');
        }

        return view('app.User.show', [
            'user' => $user
        ]);
    }

    public function store (Request $request) {

        $user = new User();
        $user->uuid      = Str::uuid();
        $user->name      = $request->name;
        $user->email     = $request->email;
        $user->cpfcnpj   = preg_replace('/\D/', '', $request->cpfcnpj);
        $user->password  = bcrypt(preg_replace('/\D/', '', $request->cpfcnpj));
        $user->parent_id = Auth::user()->id;
        if ($user->save()) {
            return redirect()->back()->with('success', 'Conta cadastrada com sucesso, a primeira senha são os números do CPF/CNPJ sem pontuação!');
        }

        return redirect()->back()->with('infor', 'Não foi possível cadastrar a Conta, verifique os dados e tente novamente!');
    }

    public function update (Request $request, $uuid) {

        $user = User::where('uuid', $request->uuid)->first();
        if (!$user) {
            return redirect()->back()->with('infor', 'Conta não encontrada/disponível, verifique os dados e tente novamente!');
        }

        if ($request->has('name')) {
            $user->name = $request->name;
        }
        if ($request->has('email')) {
            $user->email = $request->email;
        }
        if ($request->has('cpfcnpj')) {
            $user->cpfcnpj = preg_replace('/[\.\-\/]/', '', $request->cpfcnpj);
        }

        if ($request->has('password')) {
            
            if ($request->password !== $request->confirm_password) {
                return redirect()->back()->with('infor', 'As senhas não conferem, verifique os dados e tente novamente!');
            }

            $user->password = bcrypt($request->password);
        }

        if (!empty($request->photo)) {

            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }

            $file        = $request->file('photo');
            $filename    = Str::uuid() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('profile-images', $filename, 'public');
            $user->avatar = 'profile-images/' . $filename;
        }
        if ($user->save()) {
            return redirect()->back()->with('success', 'Conta atualizada com sucesso!');
        }

        return redirect()->back()->with('infor', 'Não foi possível atualiza a Conta, verifique os dados e tente novamente!');
    }

    public function destroy (Request $request) {

        if (Hash::check($request->password, Auth::user()->password)) {
            
            $user = User::where('uuid', $request->uuid)->first();
            if ($user && $user->delete()) {
                return redirect()->back()->with('success', 'Conta excluída com sucesso!');
            }

            return redirect()->back()->with('infor', 'Conta não encontrada/disponível, verifique os dados e tente novamente!');
        }

        return redirect()->back()->with('infor', 'Senha/Autorização inválida, verifique os dados e tente novamente!');
    }
}
