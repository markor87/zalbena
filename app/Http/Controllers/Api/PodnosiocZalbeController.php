<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PodnosiocZalbe;
use Illuminate\Http\Request;

class PodnosiocZalbeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = PodnosiocZalbe::query();

        // Search filter
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('ime_podnosioca_zalbe', 'LIKE', "%{$search}%")
                  ->orWhere('prezime_podnosioca_zalbe', 'LIKE', "%{$search}%")
                  ->orWhere('jmbg_podnosioca_zalbe', 'LIKE', "%{$search}%");
            });
        }

        // Institution filter
        if ($request->has('institucija') && $request->institucija) {
            $query->where('institucija_podnosioca_zalbe', $request->institucija);
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'datum_unosa');
        $sortDirection = $request->input('sort_direction', 'desc');

        // Allowed sortable fields
        $allowedSortFields = [
            'ime_podnosioca_zalbe',
            'prezime_podnosioca_zalbe',
            'jmbg_podnosioca_zalbe',
            'institucija_podnosioca_zalbe',
            'datum_unosa'
        ];

        if (in_array($sortBy, $allowedSortFields)) {
            $query->orderBy($sortBy, $sortDirection);
        } else {
            $query->orderBy('datum_unosa', 'desc');
        }

        $podnosioci = $query->paginate(10);
        return response()->json($podnosioci);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'ime_podnosioca_zalbe' => 'required|string|max:255',
            'prezime_podnosioca_zalbe' => 'required|string|max:255',
            'jmbg_podnosioca_zalbe' => 'nullable|string|max:13',
            'institucija_podnosioca_zalbe' => 'nullable|string|max:255',
            'napomena' => 'nullable|string',
        ]);

        $podnosilac = PodnosiocZalbe::create($validated);

        return response()->json($podnosilac, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $podnosilac = PodnosiocZalbe::findOrFail($id);
        return response()->json($podnosilac);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $podnosilac = PodnosiocZalbe::findOrFail($id);

        $validated = $request->validate([
            'ime_podnosioca_zalbe' => 'sometimes|required|string|max:255',
            'prezime_podnosioca_zalbe' => 'sometimes|required|string|max:255',
            'jmbg_podnosioca_zalbe' => 'nullable|string|max:13',
            'institucija_podnosioca_zalbe' => 'nullable|string|max:255',
            'napomena' => 'nullable|string',
        ]);

        $podnosilac->update($validated);

        return response()->json($podnosilac);
    }

    /**
     * Search for podnosioci (for v-select dropdown)
     */
    public function search(Request $request)
    {
        $query = PodnosiocZalbe::query();

        if ($request->has('q') && $request->q) {
            $search = $request->q;
            $query->where(function($q) use ($search) {
                $q->where('ime_podnosioca_zalbe', 'LIKE', "%{$search}%")
                  ->orWhere('prezime_podnosioca_zalbe', 'LIKE', "%{$search}%")
                  ->orWhere('jmbg_podnosioca_zalbe', 'LIKE', "%{$search}%")
                  ->orWhereRaw("CONCAT(ime_podnosioca_zalbe, ' ', prezime_podnosioca_zalbe) LIKE ?", ["%{$search}%"]);
            });
        }

        // Return top 50 results for dropdown
        $podnosioci = $query->orderBy('ime_podnosioca_zalbe')
                            ->orderBy('prezime_podnosioca_zalbe')
                            ->limit(50)
                            ->get();

        return response()->json($podnosioci);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $podnosilac = PodnosiocZalbe::findOrFail($id);

        // Check if podnosilac has any zalbe
        $zalbeCount = \App\Models\Zalba::where('podnosioci_zalbe', $id)->count();

        if ($zalbeCount > 0) {
            return response()->json([
                'message' => "Не можете обрисати подносиоца који има {$zalbeCount} активних жалби. Прво обришите све жалбе овог подносиоца."
            ], 422); // 422 Unprocessable Entity
        }

        $podnosilac->delete();

        return response()->json(['message' => 'Подносилац успешно обрисан.']);
    }
}
