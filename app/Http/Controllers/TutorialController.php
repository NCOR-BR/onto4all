<?php

namespace App\Http\Controllers;

use App\Models\Tutorial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class TutorialController extends Controller
{
    /**
     * Construtor - aplicar middleware de autenticação
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('can:canEditTutorial')->only(['editor', 'save']);
    }

    /**
     * Exibir o tutorial público (para todos os usuários)
     */
    public function index()
    {
        $tutorial = Tutorial::getCurrentTutorial();
        return view('tutorial', compact('tutorial'));
    }

    /**
     * Exibir o editor de tutorial (apenas para usuários com permissão)
     */
    public function editor()
    {
        // Verificar se o usuário tem permissão
        if (!Auth::user()->can_edit_tutorial) {
            abort(403, 'Você não tem permissão para editar o tutorial.');
        }

        $currentTutorial = Tutorial::getCurrentTutorial();
        return view('tutorials.editor', compact('currentTutorial'));
    }

    /**
     * Salvar nova versão do tutorial
     */
    public function save(Request $request)
    {
        // Verificar se o usuário tem permissão
        if (!Auth::user()->can_edit_tutorial) {
            return response()->json([
                'success' => false,
                'errors' => ['Você não tem permissão para editar o tutorial.']
            ], 403);
        }

        $validator = Validator::make($request->all(), [
           'content' => 'required|string',
           'html_content' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            $tutorial = Tutorial::create([
                'content' => $request->input('content'),
                'html_content' => $request->input('html_content'),
                'updated_by' => Auth::id(),
            ]);

            return response()->json([
                'success' => true,
                'tutorial' => $tutorial->load('updatedByUser'),
                'message' => 'Tutorial salvo com sucesso!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage()
            ], 500);
        }
    }
}
