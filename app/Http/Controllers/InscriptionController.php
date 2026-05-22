<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Inscription;
use App\Imports\EtudiantsImport;
use Maatwebsite\Excel\Facades\Excel;

class InscriptionController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'classe_id'         => 'sometimes|integer|exists:classes,id',
            'annee_scolaire_id' => 'sometimes|integer|exists:annees_scolaires,id',
        ]);

        $query = Inscription::with(['etudiant', 'classe', 'anneeScolaire']);

        if ($request->filled('classe_id')) {
            $query->where('classe_id', $request->classe_id);
        }
        if ($request->filled('annee_scolaire_id')) {
            $query->where('annee_scolaire_id', $request->annee_scolaire_id);
        }

        return response()->json(['data' => $query->get()], 200);
    }

    public function importer(Request $request)
    {
        $request->validate([
            'fichier'           => 'required|file|mimes:xlsx,xls,csv',
            'classe_id'         => 'required|integer|exists:classes,id',
            'annee_scolaire_id' => 'required|integer|exists:annees_scolaires,id',
        ]);

        $import = new EtudiantsImport(
            $request->classe_id,
            $request->annee_scolaire_id
        );

        Excel::import($import, $request->file('fichier'));

        $erreurs = $import->errors();

        return response()->json([
            'message' => 'Importation terminée.',
            'erreurs' => $erreurs->count() > 0 ? $erreurs->map(fn($e) => $e->getMessage()) : [],
        ], 200);
    }

    public function destroy(int $id)
    {
        $inscription = Inscription::findOrFail($id);
        $inscription->delete();
        return response()->json(['message' => 'Inscription supprimée.'], 200);
    }
}
