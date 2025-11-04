<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Zalba;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ZalbaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Zalba::with(['podnosilac', 'tipResenja', 'tipPresude', 'osnovZalbe']);

        // Quick search filter
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('prijemni_broj', 'LIKE', "%{$search}%")
                  ->orWhere('broj_resenja', 'LIKE', "%{$search}%");
            });
        }

        // Filter by specific podnosilac
        if ($request->has('podnosilac_id') && $request->podnosilac_id) {
            $query->where('zalbe.podnosioci_zalbe', $request->podnosilac_id);
        }

        // Advanced search filters
        if ($request->has('advanced_filters') && is_array($request->advanced_filters)) {
            foreach ($request->advanced_filters as $filter) {
                if (!isset($filter['field']) || !isset($filter['operator'])) {
                    continue;
                }

                $field = $filter['field'];
                $operator = $filter['operator'];
                $value = $filter['value'] ?? null;
                $value2 = $filter['value2'] ?? null;

                $this->applyAdvancedFilter($query, $field, $operator, $value, $value2);
            }
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'datum_unosa');
        $sortDirection = $request->input('sort_direction', 'desc');

        // Allowed sortable fields (security: prevent SQL injection)
        $allowedSortFields = [
            'prijemni_broj',
            'broj_resenja',
            'datum_prijema_zalbe',
            'datum_resavanja',
            'datum_unosa',
            'status_zalbe',
            'broj_presude',
            'datum_ekspedicije'
        ];

        if (in_array($sortBy, $allowedSortFields)) {
            $query->orderBy($sortBy, $sortDirection);
        } else {
            $query->orderBy('datum_unosa', 'desc');
        }

        $zalbe = $query->paginate(10);

        return response()->json($zalbe);
    }

    /**
     * Apply advanced filter based on operator
     */
    private function applyAdvancedFilter($query, $field, $operator, $value, $value2 = null)
    {
        switch ($operator) {
            case 'equals':
                $query->where($field, '=', $value);
                break;

            case 'not_equals':
                $query->where($field, '!=', $value);
                break;

            case 'contains':
                $query->where($field, 'LIKE', "%{$value}%");
                break;

            case 'starts_with':
                $query->where($field, 'LIKE', "{$value}%");
                break;

            case 'ends_with':
                $query->where($field, 'LIKE', "%{$value}");
                break;

            case 'greater_than':
                $query->where($field, '>', $value);
                break;

            case 'less_than':
                $query->where($field, '<', $value);
                break;

            case 'greater_or_equal':
                $query->where($field, '>=', $value);
                break;

            case 'less_or_equal':
                $query->where($field, '<=', $value);
                break;

            case 'between':
                if ($value && $value2) {
                    $query->whereBetween($field, [$value, $value2]);
                }
                break;

            case 'is_null':
                $query->whereNull($field);
                break;

            case 'is_not_null':
                $query->whereNotNull($field);
                break;
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'prijemni_broj' => 'required|string|max:255',
            'datum_prijema_zalbe' => 'required|date',
            'broj_resenja' => 'nullable|string|max:255',
            'osnov_zalbe' => 'nullable|integer',
            'datum_vracanja_na_dopunu' => 'nullable|date',
            'datum_prijema_dopune' => 'nullable|date',
            'datum_predaje_komisiji' => 'nullable|date',
            'datum_resavanja_na_zk' => 'nullable|date',
            'datum_ekspedicije_ds_organu' => 'nullable|date',
            'podnosioci_zalbe' => 'nullable|integer',
            'institucija' => 'nullable|string|max:255',
            'tipovi_resenja' => 'nullable|integer',
            'tip_resenja_napomena' => 'nullable|string',
            'napomena' => 'nullable|string',
            'komisije_zkv' => 'nullable|integer',
            'datum_isticanja_donosenje' => 'nullable|date',
            'status_zalbe' => 'nullable|string|max:255',
            'rok_za_dopunu' => 'nullable|date',
            'broj_odluke_us' => 'nullable|string|max:255',
            'broj_resenja_zk_po_presudi_us' => 'nullable|string|max:255',
            'datum_donosenja_odluke_us' => 'nullable|date',
            'datum_prijema_tuzbe_od_us' => 'nullable|date',
            'datum_ekspedicije_odgovora_zk' => 'nullable|date',
            'datum_prijema_odluke_us' => 'nullable|date',
            'datum_resenja_zk_po_presudi_us' => 'nullable|date',
            'dostavnica' => 'nullable|date',
            'clanovi_komisije1' => 'nullable|string',
            'clanovi_komisije2' => 'nullable|string',
            'naknada' => 'nullable|numeric',
            'izvestilac_sa_zalbama' => 'nullable|integer',
            'tipovi_presude_us' => 'nullable|integer',
        ]);

        $zalba = Zalba::create($validated);

        // Load relationships before returning
        $zalba->load(['podnosilac', 'tipResenja', 'tipPresude', 'osnovZalbe']);

        return response()->json($zalba, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $zalba = Zalba::with(['podnosilac', 'tipResenja', 'tipPresude', 'osnovZalbe'])
            ->findOrFail($id);

        return response()->json($zalba);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $zalba = Zalba::findOrFail($id);

        $validated = $request->validate([
            'prijemni_broj' => 'sometimes|required|string|max:255',
            'datum_prijema_zalbe' => 'sometimes|required|date',
            'broj_resenja' => 'nullable|string|max:255',
            'osnov_zalbe' => 'nullable|integer',
            'datum_vracanja_na_dopunu' => 'nullable|date',
            'datum_prijema_dopune' => 'nullable|date',
            'datum_predaje_komisiji' => 'nullable|date',
            'datum_resavanja_na_zk' => 'nullable|date',
            'datum_ekspedicije_ds_organu' => 'nullable|date',
            'podnosioci_zalbe' => 'nullable|integer',
            'institucija' => 'nullable|string|max:255',
            'tipovi_resenja' => 'nullable|integer',
            'tip_resenja_napomena' => 'nullable|string',
            'napomena' => 'nullable|string',
            'komisije_zkv' => 'nullable|integer',
            'datum_isticanja_donosenje' => 'nullable|date',
            'status_zalbe' => 'nullable|string|max:255',
            'rok_za_dopunu' => 'nullable|date',
            'broj_odluke_us' => 'nullable|string|max:255',
            'broj_resenja_zk_po_presudi_us' => 'nullable|string|max:255',
            'datum_donosenja_odluke_us' => 'nullable|date',
            'datum_prijema_tuzbe_od_us' => 'nullable|date',
            'datum_ekspedicije_odgovora_zk' => 'nullable|date',
            'datum_prijema_odluke_us' => 'nullable|date',
            'datum_resenja_zk_po_presudi_us' => 'nullable|date',
            'dostavnica' => 'nullable|date',
            'clanovi_komisije1' => 'nullable|string',
            'clanovi_komisije2' => 'nullable|string',
            'naknada' => 'nullable|numeric',
            'izvestilac_sa_zalbama' => 'nullable|integer',
            'tipovi_presude_us' => 'nullable|integer',
        ]);

        $zalba->update($validated);

        // Refresh and load relationships before returning
        $zalba->refresh()->load(['podnosilac', 'tipResenja', 'tipPresude', 'osnovZalbe']);

        return response()->json($zalba);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $zalba = Zalba::findOrFail($id);
        $zalba->delete();

        return response()->json(['message' => 'Žalba uspešno obrisana.']);
    }
}
