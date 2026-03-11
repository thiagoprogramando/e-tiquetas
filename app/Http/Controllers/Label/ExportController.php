<?php

namespace App\Http\Controllers\Label;

use App\Http\Controllers\Controller;
use App\Models\Data;
use App\Models\Label;
use App\Models\Layout;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExportController extends Controller {
    
    public function index (Request $request) {

        $data = Data::query()->where(function ($q) {
            $q->where('company_id', Auth::user()->company_id)
              ->orWhere('created_by', Auth::id());
        });

        $layouts = Layout::query()->where(function ($q) {
            $q->where('company_id', Auth::user()->company_id)
              ->orWhere('created_by', Auth::id());
        });

        $labels = Label::query()->where(function ($q) {
            $q->where('company_id', Auth::user()->company_id)
              ->orWhere('created_by', Auth::id());
        });

        return view('app.Label.index', [
            'data'          => $data->get(),
            'layouts'       => $layouts->get(),
            'labels'        => $labels->orderBy('created_at', 'desc')->paginate(30),
            'layoutSelect'  => $request->layout,
            'dataSelect'    => $request->data,
        ]);
    }

    public function destroy (Request $request) {

        $label = Label::where('uuid', $request->uuid)->first();
        if ($label && $label->delete()) {
            return redirect()->back()->with('success', 'Remessa excluída com sucesso!');
        }

        return redirect()->back()->with('infor', 'Remessa não encontrada/disponível, verifique os dados e tente novamente!');
    }
}
