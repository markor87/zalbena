<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class IzvestajController extends Controller
{
    /**
     * Get report of unresolved and pending complaints
     */
    public function neresenZalbi(Request $request)
    {
        // Subquery to get minimum datum_prijema_zalbe for each institution
        $minDateSubquery = DB::table('zalbe as z2')
            ->join('podnosioci_zalbi as pz2', 'z2.podnosioci_zalbe', '=', 'pz2.id')
            ->select('pz2.institucija_podnosioca_zalbe', DB::raw('MIN(z2.datum_prijema_zalbe) as min_datum'))
            ->whereIn('z2.status_zalbe', ['Нерешен', 'Упućен на допуну'])
            ->groupBy('pz2.institucija_podnosioca_zalbe');

        $query = DB::table('zalbe as z')
            ->leftJoin('podnosioci_zalbi as pz', 'z.podnosioci_zalbe', '=', 'pz.id')
            ->leftJoin('sifarnik_osnov_zalbe as soz', 'z.osnov_zalbe', '=', 'soz.id')
            ->leftJoinSub($minDateSubquery, 'inst_min', function($join) {
                $join->on('pz.institucija_podnosioca_zalbe', '=', 'inst_min.institucija_podnosioca_zalbe');
            })
            ->select(
                'pz.institucija_podnosioca_zalbe',
                'pz.prezime_podnosioca_zalbe',
                'pz.ime_podnosioca_zalbe',
                'z.prijemni_broj',
                'z.broj_resenja',
                'z.datum_prijema_zalbe',
                'z.datum_resavanja_na_zk',
                'z.datum_isticanja_donosenje',
                'soz.osnov_zalbe',
                'z.status_zalbe',
                'inst_min.min_datum'
            )
            ->whereIn('z.status_zalbe', ['Нерешен', 'Упућен на допуну'])
            ->orderByRaw('inst_min.min_datum IS NULL, inst_min.min_datum ASC')
            ->orderBy('pz.institucija_podnosioca_zalbe', 'asc')
            ->orderBy('z.datum_prijema_zalbe', 'asc')
            ->orderBy('z.prijemni_broj', 'asc');

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('pz.ime_podnosioca_zalbe', 'LIKE', "%{$search}%")
                  ->orWhere('pz.prezime_podnosioca_zalbe', 'LIKE', "%{$search}%")
                  ->orWhere('z.prijemni_broj', 'LIKE', "%{$search}%")
                  ->orWhere('z.broj_resenja', 'LIKE', "%{$search}%");
            });
        }

        $results = $query->get();

        return response()->json($results);
    }

    /**
     * Get query for export
     */
    private function getExportQuery(Request $request)
    {
        // Subquery to get minimum datum_prijema_zalbe for each institution
        $minDateSubquery = DB::table('zalbe as z2')
            ->join('podnosioci_zalbi as pz2', 'z2.podnosioci_zalbe', '=', 'pz2.id')
            ->select('pz2.institucija_podnosioca_zalbe', DB::raw('MIN(z2.datum_prijema_zalbe) as min_datum'))
            ->whereIn('z2.status_zalbe', ['Нерешен', 'Упућен на допуну'])
            ->groupBy('pz2.institucija_podnosioca_zalbe');

        $query = DB::table('zalbe as z')
            ->leftJoin('podnosioci_zalbi as pz', 'z.podnosioci_zalbe', '=', 'pz.id')
            ->leftJoin('sifarnik_osnov_zalbe as soz', 'z.osnov_zalbe', '=', 'soz.id')
            ->leftJoinSub($minDateSubquery, 'inst_min', function($join) {
                $join->on('pz.institucija_podnosioca_zalbe', '=', 'inst_min.institucija_podnosioca_zalbe');
            })
            ->select(
                'pz.institucija_podnosioca_zalbe',
                'pz.prezime_podnosioca_zalbe',
                'pz.ime_podnosioca_zalbe',
                'z.prijemni_broj',
                'z.broj_resenja',
                'z.datum_prijema_zalbe',
                'z.datum_resavanja_na_zk',
                'z.datum_isticanja_donosenje',
                'soz.osnov_zalbe',
                'z.status_zalbe',
                'inst_min.min_datum'
            )
            ->whereIn('z.status_zalbe', ['Нерешен', 'Упућен на допуну'])
            ->orderByRaw('inst_min.min_datum IS NULL, inst_min.min_datum ASC')
            ->orderBy('pz.institucija_podnosioca_zalbe', 'asc')
            ->orderBy('z.datum_prijema_zalbe', 'asc')
            ->orderBy('z.prijemni_broj', 'asc');

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('pz.ime_podnosioca_zalbe', 'LIKE', "%{$search}%")
                  ->orWhere('pz.prezime_podnosioca_zalbe', 'LIKE', "%{$search}%")
                  ->orWhere('z.prijemni_broj', 'LIKE', "%{$search}%")
                  ->orWhere('z.broj_resenja', 'LIKE', "%{$search}%");
            });
        }

        return $query;
    }

    /**
     * Export to Excel
     */
    public function exportExcel(Request $request)
    {
        $data = $this->getExportQuery($request)->get();

        $export = new class($data) implements FromCollection, WithHeadings {
            protected $data;

            public function __construct($data)
            {
                $this->data = $data;
            }

            public function collection()
            {
                return $this->data->map(function($item) {
                    return [
                        'institucija_podnosioca_zalbe' => $item->institucija_podnosioca_zalbe ?? '-',
                        'ime_podnosioca_zalbe' => $item->ime_podnosioca_zalbe ?? '-',
                        'prezime_podnosioca_zalbe' => $item->prezime_podnosioca_zalbe ?? '-',
                        'prijemni_broj' => $item->prijemni_broj,
                        'broj_resenja' => $item->broj_resenja ?? '-',
                        'datum_prijema_zalbe' => $item->datum_prijema_zalbe,
                        'datum_resavanja_na_zk' => $item->datum_resavanja_na_zk ?? '-',
                        'datum_isticanja_donosenje' => $item->datum_isticanja_donosenje ?? '-',
                        'osnov_zalbe' => $item->osnov_zalbe ?? '-',
                    ];
                });
            }

            public function headings(): array
            {
                return [
                    'Институција',
                    'Име',
                    'Презиме',
                    'Пријемни број',
                    'Број решења',
                    'Датум пријема',
                    'Датум решавања',
                    'Датум истицања',
                    'Основ жалбе'
                ];
            }
        };

        return Excel::download($export, 'neresene-zalbe-' . date('Y-m-d') . '.xlsx');
    }

    /**
     * Export to PDF
     */
    public function exportPdf(Request $request)
    {
        $data = $this->getExportQuery($request)->get();

        $pdf = Pdf::loadView('pdf.neresene-zalbe', compact('data'));
        $pdf->setPaper('a4', 'landscape');

        return $pdf->download('neresene-zalbe-' . date('Y-m-d') . '.pdf');
    }

    /**
     * Evidencija zalbi po datumu prijema
     */
    public function poDatamuPrijema(Request $request)
    {
        $query = DB::table('zalbe as z')
            ->join('podnosioci_zalbi as pz', 'z.podnosioci_zalbe', '=', 'pz.id')
            ->select(
                DB::raw("CONCAT(pz.ime_podnosioca_zalbe, ' ', pz.prezime_podnosioca_zalbe) as ime_i_prezime"),
                'z.datum_prijema_zalbe',
                'pz.institucija_podnosioca_zalbe',
                'z.prijemni_broj',
                'z.broj_resenja',
                'z.status_zalbe'
            );

        // Simple search filter
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('pz.ime_podnosioca_zalbe', 'LIKE', "%{$search}%")
                  ->orWhere('pz.prezime_podnosioca_zalbe', 'LIKE', "%{$search}%")
                  ->orWhere('z.prijemni_broj', 'LIKE', "%{$search}%")
                  ->orWhere('z.broj_resenja', 'LIKE', "%{$search}%")
                  ->orWhere('z.status_zalbe', 'LIKE', "%{$search}%")
                  ->orWhere('pz.institucija_podnosioca_zalbe', 'LIKE', "%{$search}%");
            });
        }

        // Decode advanced_filters if it's a JSON string
        $advancedFilters = $request->input('advanced_filters');
        if ($advancedFilters && is_string($advancedFilters)) {
            $advancedFilters = json_decode($advancedFilters, true);
        }

        // Advanced search filters
        if (is_array($advancedFilters) && count($advancedFilters) > 0) {
            foreach ($advancedFilters as $filter) {
                if (!isset($filter['field']) || !isset($filter['operator'])) {
                    continue;
                }

                $field = $filter['field'];
                $operator = $filter['operator'];
                $value = $filter['value'] ?? null;
                $value2 = $filter['value2'] ?? null;

                // Map field names to actual database columns
                $fieldMap = [
                    'ime_i_prezime' => DB::raw("CONCAT(pz.ime_podnosioca_zalbe, ' ', pz.prezime_podnosioca_zalbe)"),
                    'datum_prijema_zalbe' => 'z.datum_prijema_zalbe',
                    'institucija_podnosioca_zalbe' => 'pz.institucija_podnosioca_zalbe',
                    'prijemni_broj' => 'z.prijemni_broj',
                    'broj_resenja' => 'z.broj_resenja',
                    'status_zalbe' => 'z.status_zalbe',
                ];

                $dbField = $fieldMap[$field] ?? $field;

                $this->applyAdvancedFilterPoDatumu($query, $dbField, $operator, $value, $value2);
            }
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'z.datum_prijema_zalbe');
        $sortDirection = $request->input('sort_direction', 'desc');

        // Allowed sortable fields
        $allowedSortFields = [
            'ime_i_prezime',
            'z.datum_prijema_zalbe',
            'pz.institucija_podnosioca_zalbe',
            'z.prijemni_broj',
            'z.broj_resenja',
            'z.status_zalbe'
        ];

        if (in_array($sortBy, $allowedSortFields)) {
            $query->orderBy($sortBy, $sortDirection);
        } else {
            $query->orderBy('z.datum_prijema_zalbe', 'desc');
        }

        $data = $query->paginate(10);

        return response()->json($data);
    }

    /**
     * Apply advanced filter for po datumu prijema report
     */
    private function applyAdvancedFilterPoDatumu($query, $field, $operator, $value, $value2 = null)
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
     * Get export query for po datumu prijema
     */
    private function getPoDatamuPrijemanQuery(Request $request)
    {
        $query = DB::table('zalbe as z')
            ->join('podnosioci_zalbi as pz', 'z.podnosioci_zalbe', '=', 'pz.id')
            ->select(
                DB::raw("CONCAT(pz.ime_podnosioca_zalbe, ' ', pz.prezime_podnosioca_zalbe) as ime_i_prezime"),
                'z.datum_prijema_zalbe',
                'pz.institucija_podnosioca_zalbe',
                'z.prijemni_broj',
                'z.broj_resenja',
                'z.status_zalbe'
            );

        // Simple search filter
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('pz.ime_podnosioca_zalbe', 'LIKE', "%{$search}%")
                  ->orWhere('pz.prezime_podnosioca_zalbe', 'LIKE', "%{$search}%")
                  ->orWhere('z.prijemni_broj', 'LIKE', "%{$search}%")
                  ->orWhere('z.broj_resenja', 'LIKE', "%{$search}%")
                  ->orWhere('z.status_zalbe', 'LIKE', "%{$search}%")
                  ->orWhere('pz.institucija_podnosioca_zalbe', 'LIKE', "%{$search}%");
            });
        }

        // Decode advanced_filters if it's a JSON string
        $advancedFilters = $request->input('advanced_filters');
        if ($advancedFilters && is_string($advancedFilters)) {
            $advancedFilters = json_decode($advancedFilters, true);
        }

        // Advanced search filters
        if (is_array($advancedFilters) && count($advancedFilters) > 0) {
            foreach ($advancedFilters as $filter) {
                if (!isset($filter['field']) || !isset($filter['operator'])) {
                    continue;
                }

                $field = $filter['field'];
                $operator = $filter['operator'];
                $value = $filter['value'] ?? null;
                $value2 = $filter['value2'] ?? null;

                // Map field names to actual database columns
                $fieldMap = [
                    'ime_i_prezime' => DB::raw("CONCAT(pz.ime_podnosioca_zalbe, ' ', pz.prezime_podnosioca_zalbe)"),
                    'datum_prijema_zalbe' => 'z.datum_prijema_zalbe',
                    'institucija_podnosioca_zalbe' => 'pz.institucija_podnosioca_zalbe',
                    'prijemni_broj' => 'z.prijemni_broj',
                    'broj_resenja' => 'z.broj_resenja',
                    'status_zalbe' => 'z.status_zalbe',
                ];

                $dbField = $fieldMap[$field] ?? $field;

                $this->applyAdvancedFilterPoDatumu($query, $dbField, $operator, $value, $value2);
            }
        }

        return $query->orderBy('z.datum_prijema_zalbe', 'desc');
    }

    /**
     * Export po datumu prijema to Excel
     */
    public function exportPoDatamuPrijemanExcel(Request $request)
    {
        set_time_limit(480);

        $data = $this->getPoDatamuPrijemanQuery($request)->get();

        return Excel::download(new class($data) implements FromCollection, WithHeadings {
            private $data;

            public function __construct($data)
            {
                $this->data = $data;
            }

            public function collection()
            {
                return $this->data->map(function($item) {
                    return [
                        'Ime i prezime' => $item->ime_i_prezime,
                        'Datum prijema' => $item->datum_prijema_zalbe ? date('d.m.Y', strtotime($item->datum_prijema_zalbe)) : '-',
                        'Institucija' => $item->institucija_podnosioca_zalbe ?? '-',
                        'Prijemni broj' => $item->prijemni_broj ?? '-',
                        'Broj rešenja' => $item->broj_resenja ?? '-',
                        'Status' => $item->status_zalbe ?? '-',
                    ];
                });
            }

            public function headings(): array
            {
                return [
                    'Ime i prezime',
                    'Datum prijema',
                    'Institucija',
                    'Prijemni broj',
                    'Broj rešenja',
                    'Status'
                ];
            }
        }, 'evidencija-zalbi-po-datumu-prijema-' . date('Y-m-d') . '.xlsx');
    }

    /**
     * Export po datumu prijema to PDF
     */
    public function exportPoDatamuPrijemaPdf(Request $request)
    {
        set_time_limit(480);

        $data = $this->getPoDatamuPrijemanQuery($request)->get();

        $pdf = Pdf::loadView('pdf.po-datumu-prijema', compact('data'));
        $pdf->setPaper('a4', 'landscape');

        return $pdf->download('evidencija-zalbi-po-datumu-prijema-' . date('Y-m-d') . '.pdf');
    }

    /**
     * Tuzbe primljene od Upravnog suda Srbije
     */
    public function tuzbeOdUS(Request $request)
    {
        $query = DB::table('zalbe as z')
            ->join('podnosioci_zalbi as pz', 'z.podnosioci_zalbe', '=', 'pz.id')
            ->join('sifarnik_tip_presude as stp', 'z.tipovi_presude_us', '=', 'stp.id')
            ->select(
                DB::raw("CONCAT(pz.ime_podnosioca_zalbe, ' ', pz.prezime_podnosioca_zalbe) as ime_i_prezime"),
                'pz.institucija_podnosioca_zalbe',
                'z.prijemni_broj',
                'z.datum_prijema_zalbe',
                'z.datum_prijema_tuzbe_od_us',
                'stp.tip_presude'
            )
            ->whereNotNull('z.datum_prijema_tuzbe_od_us');

        // Simple search filter
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('pz.ime_podnosioca_zalbe', 'LIKE', "%{$search}%")
                  ->orWhere('pz.prezime_podnosioca_zalbe', 'LIKE', "%{$search}%")
                  ->orWhere('z.prijemni_broj', 'LIKE', "%{$search}%")
                  ->orWhere('pz.institucija_podnosioca_zalbe', 'LIKE', "%{$search}%")
                  ->orWhere('stp.tip_presude', 'LIKE', "%{$search}%");
            });
        }

        // Decode advanced_filters if it's a JSON string
        $advancedFilters = $request->input('advanced_filters');
        if ($advancedFilters && is_string($advancedFilters)) {
            $advancedFilters = json_decode($advancedFilters, true);
        }

        // Advanced search filters
        if (is_array($advancedFilters) && count($advancedFilters) > 0) {
            foreach ($advancedFilters as $filter) {
                if (!isset($filter['field']) || !isset($filter['operator'])) {
                    continue;
                }

                $field = $filter['field'];
                $operator = $filter['operator'];
                $value = $filter['value'] ?? null;
                $value2 = $filter['value2'] ?? null;

                // Map field names to actual database columns
                $fieldMap = [
                    'ime_i_prezime' => DB::raw("CONCAT(pz.ime_podnosioca_zalbe, ' ', pz.prezime_podnosioca_zalbe)"),
                    'institucija_podnosioca_zalbe' => 'pz.institucija_podnosioca_zalbe',
                    'prijemni_broj' => 'z.prijemni_broj',
                    'datum_prijema_zalbe' => 'z.datum_prijema_zalbe',
                    'datum_prijema_tuzbe_od_us' => 'z.datum_prijema_tuzbe_od_us',
                    'tip_presude' => 'stp.tip_presude',
                ];

                $dbField = $fieldMap[$field] ?? $field;

                $this->applyAdvancedFilterTuzbeOdUS($query, $dbField, $operator, $value, $value2);
            }
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'z.datum_prijema_zalbe');
        $sortDirection = $request->input('sort_direction', 'desc');

        // Allowed sortable fields
        $allowedSortFields = [
            'ime_i_prezime',
            'pz.institucija_podnosioca_zalbe',
            'z.prijemni_broj',
            'z.datum_prijema_zalbe',
            'z.datum_prijema_tuzbe_od_us',
            'stp.tip_presude'
        ];

        if (in_array($sortBy, $allowedSortFields)) {
            $query->orderBy($sortBy, $sortDirection);
        } else {
            $query->orderBy('z.datum_prijema_zalbe', 'desc');
        }

        $data = $query->paginate(10);

        return response()->json($data);
    }

    /**
     * Apply advanced filter for tuzbe od US report
     */
    private function applyAdvancedFilterTuzbeOdUS($query, $field, $operator, $value, $value2 = null)
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
     * Get export query for tuzbe od US
     */
    private function getTuzbeOdUSQuery(Request $request)
    {
        $query = DB::table('zalbe as z')
            ->join('podnosioci_zalbi as pz', 'z.podnosioci_zalbe', '=', 'pz.id')
            ->join('sifarnik_tip_presude as stp', 'z.tipovi_presude_us', '=', 'stp.id')
            ->select(
                DB::raw("CONCAT(pz.ime_podnosioca_zalbe, ' ', pz.prezime_podnosioca_zalbe) as ime_i_prezime"),
                'pz.institucija_podnosioca_zalbe',
                'z.prijemni_broj',
                'z.datum_prijema_zalbe',
                'z.datum_prijema_tuzbe_od_us',
                'stp.tip_presude'
            )
            ->whereNotNull('z.datum_prijema_tuzbe_od_us');

        // Simple search filter
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('pz.ime_podnosioca_zalbe', 'LIKE', "%{$search}%")
                  ->orWhere('pz.prezime_podnosioca_zalbe', 'LIKE', "%{$search}%")
                  ->orWhere('z.prijemni_broj', 'LIKE', "%{$search}%")
                  ->orWhere('pz.institucija_podnosioca_zalbe', 'LIKE', "%{$search}%")
                  ->orWhere('stp.tip_presude', 'LIKE', "%{$search}%");
            });
        }

        // Decode advanced_filters if it's a JSON string
        $advancedFilters = $request->input('advanced_filters');
        if ($advancedFilters && is_string($advancedFilters)) {
            $advancedFilters = json_decode($advancedFilters, true);
        }

        // Advanced search filters
        if (is_array($advancedFilters) && count($advancedFilters) > 0) {
            foreach ($advancedFilters as $filter) {
                if (!isset($filter['field']) || !isset($filter['operator'])) {
                    continue;
                }

                $field = $filter['field'];
                $operator = $filter['operator'];
                $value = $filter['value'] ?? null;
                $value2 = $filter['value2'] ?? null;

                // Map field names to actual database columns
                $fieldMap = [
                    'ime_i_prezime' => DB::raw("CONCAT(pz.ime_podnosioca_zalbe, ' ', pz.prezime_podnosioca_zalbe)"),
                    'institucija_podnosioca_zalbe' => 'pz.institucija_podnosioca_zalbe',
                    'prijemni_broj' => 'z.prijemni_broj',
                    'datum_prijema_zalbe' => 'z.datum_prijema_zalbe',
                    'datum_prijema_tuzbe_od_us' => 'z.datum_prijema_tuzbe_od_us',
                    'tip_presude' => 'stp.tip_presude',
                ];

                $dbField = $fieldMap[$field] ?? $field;

                $this->applyAdvancedFilterTuzbeOdUS($query, $dbField, $operator, $value, $value2);
            }
        }

        return $query->orderBy('z.datum_prijema_zalbe', 'desc');
    }

    /**
     * Export tuzbe od US to Excel
     */
    public function exportTuzbeOdUSExcel(Request $request)
    {
        set_time_limit(480);

        $data = $this->getTuzbeOdUSQuery($request)->get();

        return Excel::download(new class($data) implements FromCollection, WithHeadings {
            private $data;

            public function __construct($data)
            {
                $this->data = $data;
            }

            public function collection()
            {
                return $this->data->map(function($item) {
                    return [
                        'Ime i prezime' => $item->ime_i_prezime,
                        'Institucija' => $item->institucija_podnosioca_zalbe ?? '-',
                        'Prijemni broj' => $item->prijemni_broj ?? '-',
                        'Datum prijema zalbe' => $item->datum_prijema_zalbe ? date('d.m.Y', strtotime($item->datum_prijema_zalbe)) : '-',
                        'Datum prijema tuzbe od US' => $item->datum_prijema_tuzbe_od_us ? date('d.m.Y', strtotime($item->datum_prijema_tuzbe_od_us)) : '-',
                        'Tip presude' => $item->tip_presude ?? '-',
                    ];
                });
            }

            public function headings(): array
            {
                return [
                    'Ime i prezime',
                    'Institucija',
                    'Prijemni broj',
                    'Datum prijema zalbe',
                    'Datum prijema tuzbe od US',
                    'Tip presude'
                ];
            }
        }, 'tuzbe-od-upravnog-suda-' . date('Y-m-d') . '.xlsx');
    }

    /**
     * Export tuzbe od US to PDF
     */
    public function exportTuzbeOdUSPdf(Request $request)
    {
        set_time_limit(480);

        $data = $this->getTuzbeOdUSQuery($request)->get();

        $pdf = Pdf::loadView('pdf.tuzbe-od-us', compact('data'));
        $pdf->setPaper('a4', 'landscape');

        return $pdf->download('tuzbe-od-upravnog-suda-' . date('Y-m-d') . '.pdf');
    }

    /**
     * Datum ekspedicije resenih zalbi
     */
    public function datumEkspedicije(Request $request)
    {
        $query = DB::table('zalbe as z')
            ->join('podnosioci_zalbi as pz', 'z.podnosioci_zalbe', '=', 'pz.id')
            ->select(
                DB::raw("CONCAT(pz.ime_podnosioca_zalbe, ' ', pz.prezime_podnosioca_zalbe) as ime_i_prezime"),
                'pz.institucija_podnosioca_zalbe',
                'z.prijemni_broj',
                'z.broj_resenja',
                'z.datum_ekspedicije_ds_organu'
            )
            ->whereNotNull('z.datum_ekspedicije_ds_organu');

        // Simple search filter
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('pz.ime_podnosioca_zalbe', 'LIKE', "%{$search}%")
                  ->orWhere('pz.prezime_podnosioca_zalbe', 'LIKE', "%{$search}%")
                  ->orWhere('z.prijemni_broj', 'LIKE', "%{$search}%")
                  ->orWhere('z.broj_resenja', 'LIKE', "%{$search}%")
                  ->orWhere('pz.institucija_podnosioca_zalbe', 'LIKE', "%{$search}%");
            });
        }

        // Decode advanced_filters if it's a JSON string
        $advancedFilters = $request->input('advanced_filters');
        if ($advancedFilters && is_string($advancedFilters)) {
            $advancedFilters = json_decode($advancedFilters, true);
        }

        // Advanced search filters
        if (is_array($advancedFilters) && count($advancedFilters) > 0) {
            foreach ($advancedFilters as $filter) {
                if (!isset($filter['field']) || !isset($filter['operator'])) {
                    continue;
                }

                $field = $filter['field'];
                $operator = $filter['operator'];
                $value = $filter['value'] ?? null;
                $value2 = $filter['value2'] ?? null;

                // Map field names to actual database columns
                $fieldMap = [
                    'ime_i_prezime' => DB::raw("CONCAT(pz.ime_podnosioca_zalbe, ' ', pz.prezime_podnosioca_zalbe)"),
                    'institucija_podnosioca_zalbe' => 'pz.institucija_podnosioca_zalbe',
                    'prijemni_broj' => 'z.prijemni_broj',
                    'broj_resenja' => 'z.broj_resenja',
                    'datum_ekspedicije_ds_organu' => 'z.datum_ekspedicije_ds_organu',
                ];

                $dbField = $fieldMap[$field] ?? $field;

                $this->applyAdvancedFilterDatumEkspedicije($query, $dbField, $operator, $value, $value2);
            }
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'z.datum_ekspedicije_ds_organu');
        $sortDirection = $request->input('sort_direction', 'desc');

        // Allowed sortable fields
        $allowedSortFields = [
            'ime_i_prezime',
            'pz.institucija_podnosioca_zalbe',
            'z.prijemni_broj',
            'z.broj_resenja',
            'z.datum_ekspedicije_ds_organu'
        ];

        if (in_array($sortBy, $allowedSortFields)) {
            $query->orderBy($sortBy, $sortDirection);
        } else {
            $query->orderBy('z.datum_ekspedicije_ds_organu', 'desc');
        }

        $data = $query->paginate(10);

        return response()->json($data);
    }

    /**
     * Apply advanced filter for datum ekspedicije report
     */
    private function applyAdvancedFilterDatumEkspedicije($query, $field, $operator, $value, $value2 = null)
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
     * Get export query for datum ekspedicije
     */
    private function getDatumEkspedicijeQuery(Request $request)
    {
        $query = DB::table('zalbe as z')
            ->join('podnosioci_zalbi as pz', 'z.podnosioci_zalbe', '=', 'pz.id')
            ->select(
                DB::raw("CONCAT(pz.ime_podnosioca_zalbe, ' ', pz.prezime_podnosioca_zalbe) as ime_i_prezime"),
                'pz.institucija_podnosioca_zalbe',
                'z.prijemni_broj',
                'z.broj_resenja',
                'z.datum_ekspedicije_ds_organu'
            )
            ->whereNotNull('z.datum_ekspedicije_ds_organu');

        // Simple search filter
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('pz.ime_podnosioca_zalbe', 'LIKE', "%{$search}%")
                  ->orWhere('pz.prezime_podnosioca_zalbe', 'LIKE', "%{$search}%")
                  ->orWhere('z.prijemni_broj', 'LIKE', "%{$search}%")
                  ->orWhere('z.broj_resenja', 'LIKE', "%{$search}%")
                  ->orWhere('pz.institucija_podnosioca_zalbe', 'LIKE', "%{$search}%");
            });
        }

        // Decode advanced_filters if it's a JSON string
        $advancedFilters = $request->input('advanced_filters');
        if ($advancedFilters && is_string($advancedFilters)) {
            $advancedFilters = json_decode($advancedFilters, true);
        }

        // Advanced search filters
        if (is_array($advancedFilters) && count($advancedFilters) > 0) {
            foreach ($advancedFilters as $filter) {
                if (!isset($filter['field']) || !isset($filter['operator'])) {
                    continue;
                }

                $field = $filter['field'];
                $operator = $filter['operator'];
                $value = $filter['value'] ?? null;
                $value2 = $filter['value2'] ?? null;

                // Map field names to actual database columns
                $fieldMap = [
                    'ime_i_prezime' => DB::raw("CONCAT(pz.ime_podnosioca_zalbe, ' ', pz.prezime_podnosioca_zalbe)"),
                    'institucija_podnosioca_zalbe' => 'pz.institucija_podnosioca_zalbe',
                    'prijemni_broj' => 'z.prijemni_broj',
                    'broj_resenja' => 'z.broj_resenja',
                    'datum_ekspedicije_ds_organu' => 'z.datum_ekspedicije_ds_organu',
                ];

                $dbField = $fieldMap[$field] ?? $field;

                $this->applyAdvancedFilterDatumEkspedicije($query, $dbField, $operator, $value, $value2);
            }
        }

        return $query->orderBy('z.datum_ekspedicije_ds_organu', 'desc');
    }

    /**
     * Export datum ekspedicije to Excel
     */
    public function exportDatumEkspedicijeExcel(Request $request)
    {
        $data = $this->getDatumEkspedicijeQuery($request)->get();

        return Excel::download(new class($data) implements FromCollection, WithHeadings {
            private $data;

            public function __construct($data)
            {
                $this->data = $data;
            }

            public function collection()
            {
                return $this->data->map(function($item) {
                    return [
                        'Ime i prezime' => $item->ime_i_prezime,
                        'Institucija' => $item->institucija_podnosioca_zalbe ?? '-',
                        'Prijemni broj' => $item->prijemni_broj ?? '-',
                        'Broj resenja' => $item->broj_resenja ?? '-',
                        'Datum ekspedicije' => $item->datum_ekspedicije_ds_organu ? date('d.m.Y', strtotime($item->datum_ekspedicije_ds_organu)) : '-',
                    ];
                });
            }

            public function headings(): array
            {
                return [
                    'Ime i prezime',
                    'Institucija',
                    'Prijemni broj',
                    'Broj resenja',
                    'Datum ekspedicije'
                ];
            }
        }, 'datum-ekspedicije-resenih-zalbi-' . date('Y-m-d') . '.xlsx');
    }

    /**
     * Export datum ekspedicije to PDF
     */
    public function exportDatumEkspedicijePdf(Request $request)
    {
        $data = $this->getDatumEkspedicijeQuery($request)->get();

        $pdf = Pdf::loadView('pdf.datum-ekspedicije', compact('data'));
        $pdf->setPaper('a4', 'landscape');

        return $pdf->download('datum-ekspedicije-resenih-zalbi-' . date('Y-m-d') . '.pdf');
    }

    /**
     * Ekspedovane tuzbe
     */
    public function ekspedovaneTuzbe(Request $request)
    {
        $query = DB::table('zalbe as z')
            ->join('podnosioci_zalbi as pz', 'z.podnosioci_zalbe', '=', 'pz.id')
            ->select(
                DB::raw("CONCAT(pz.ime_podnosioca_zalbe, ' ', pz.prezime_podnosioca_zalbe) as ime_i_prezime"),
                'pz.institucija_podnosioca_zalbe',
                'z.prijemni_broj',
                'z.datum_prijema_odluke_us',
                'z.datum_prijema_zalbe',
                'z.datum_prijema_tuzbe_od_us',
                'z.datum_ekspedicije_odgovora_zk',
                'z.status_zalbe'
            );

        // Simple search filter
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('pz.ime_podnosioca_zalbe', 'LIKE', "%{$search}%")
                  ->orWhere('pz.prezime_podnosioca_zalbe', 'LIKE', "%{$search}%")
                  ->orWhere('z.prijemni_broj', 'LIKE', "%{$search}%")
                  ->orWhere('z.status_zalbe', 'LIKE', "%{$search}%")
                  ->orWhere('pz.institucija_podnosioca_zalbe', 'LIKE', "%{$search}%");
            });
        }

        // Decode advanced_filters if it's a JSON string
        $advancedFilters = $request->input('advanced_filters');
        if ($advancedFilters && is_string($advancedFilters)) {
            $advancedFilters = json_decode($advancedFilters, true);
        }

        // Advanced search filters
        if (is_array($advancedFilters) && count($advancedFilters) > 0) {
            foreach ($advancedFilters as $filter) {
                if (!isset($filter['field']) || !isset($filter['operator'])) {
                    continue;
                }

                $field = $filter['field'];
                $operator = $filter['operator'];
                $value = $filter['value'] ?? null;
                $value2 = $filter['value2'] ?? null;

                // Map field names to actual database columns
                $fieldMap = [
                    'ime_i_prezime' => DB::raw("CONCAT(pz.ime_podnosioca_zalbe, ' ', pz.prezime_podnosioca_zalbe)"),
                    'institucija_podnosioca_zalbe' => 'pz.institucija_podnosioca_zalbe',
                    'prijemni_broj' => 'z.prijemni_broj',
                    'datum_prijema_odluke_us' => 'z.datum_prijema_odluke_us',
                    'datum_prijema_zalbe' => 'z.datum_prijema_zalbe',
                    'datum_prijema_tuzbe_od_us' => 'z.datum_prijema_tuzbe_od_us',
                    'datum_ekspedicije_odgovora_zk' => 'z.datum_ekspedicije_odgovora_zk',
                    'status_zalbe' => 'z.status_zalbe',
                ];

                $dbField = $fieldMap[$field] ?? $field;

                $this->applyAdvancedFilterEkspedovaneTuzbe($query, $dbField, $operator, $value, $value2);
            }
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'z.datum_ekspedicije_odgovora_zk');
        $sortDirection = $request->input('sort_direction', 'desc');

        // Allowed sortable fields
        $allowedSortFields = [
            'ime_i_prezime',
            'pz.institucija_podnosioca_zalbe',
            'z.prijemni_broj',
            'z.datum_prijema_odluke_us',
            'z.datum_prijema_zalbe',
            'z.datum_prijema_tuzbe_od_us',
            'z.datum_ekspedicije_odgovora_zk',
            'z.status_zalbe'
        ];

        if (in_array($sortBy, $allowedSortFields)) {
            $query->orderBy($sortBy, $sortDirection);
        } else {
            $query->orderBy('z.datum_ekspedicije_odgovora_zk', 'desc');
        }

        $data = $query->paginate(10);

        return response()->json($data);
    }

    /**
     * Apply advanced filter for ekspedovane tuzbe report
     */
    private function applyAdvancedFilterEkspedovaneTuzbe($query, $field, $operator, $value, $value2 = null)
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
     * Get export query for ekspedovane tuzbe
     */
    private function getEkspedovaneTuzbeQuery(Request $request)
    {
        $query = DB::table('zalbe as z')
            ->join('podnosioci_zalbi as pz', 'z.podnosioci_zalbe', '=', 'pz.id')
            ->select(
                DB::raw("CONCAT(pz.ime_podnosioca_zalbe, ' ', pz.prezime_podnosioca_zalbe) as ime_i_prezime"),
                'pz.institucija_podnosioca_zalbe',
                'z.prijemni_broj',
                'z.datum_prijema_odluke_us',
                'z.datum_prijema_zalbe',
                'z.datum_prijema_tuzbe_od_us',
                'z.datum_ekspedicije_odgovora_zk',
                'z.status_zalbe'
            );

        // Simple search filter
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('pz.ime_podnosioca_zalbe', 'LIKE', "%{$search}%")
                  ->orWhere('pz.prezime_podnosioca_zalbe', 'LIKE', "%{$search}%")
                  ->orWhere('z.prijemni_broj', 'LIKE', "%{$search}%")
                  ->orWhere('z.status_zalbe', 'LIKE', "%{$search}%")
                  ->orWhere('pz.institucija_podnosioca_zalbe', 'LIKE', "%{$search}%");
            });
        }

        // Decode advanced_filters if it's a JSON string
        $advancedFilters = $request->input('advanced_filters');
        if ($advancedFilters && is_string($advancedFilters)) {
            $advancedFilters = json_decode($advancedFilters, true);
        }

        // Advanced search filters
        if (is_array($advancedFilters) && count($advancedFilters) > 0) {
            foreach ($advancedFilters as $filter) {
                if (!isset($filter['field']) || !isset($filter['operator'])) {
                    continue;
                }

                $field = $filter['field'];
                $operator = $filter['operator'];
                $value = $filter['value'] ?? null;
                $value2 = $filter['value2'] ?? null;

                // Map field names to actual database columns
                $fieldMap = [
                    'ime_i_prezime' => DB::raw("CONCAT(pz.ime_podnosioca_zalbe, ' ', pz.prezime_podnosioca_zalbe)"),
                    'institucija_podnosioca_zalbe' => 'pz.institucija_podnosioca_zalbe',
                    'prijemni_broj' => 'z.prijemni_broj',
                    'datum_prijema_odluke_us' => 'z.datum_prijema_odluke_us',
                    'datum_prijema_zalbe' => 'z.datum_prijema_zalbe',
                    'datum_prijema_tuzbe_od_us' => 'z.datum_prijema_tuzbe_od_us',
                    'datum_ekspedicije_odgovora_zk' => 'z.datum_ekspedicije_odgovora_zk',
                    'status_zalbe' => 'z.status_zalbe',
                ];

                $dbField = $fieldMap[$field] ?? $field;

                $this->applyAdvancedFilterEkspedovaneTuzbe($query, $dbField, $operator, $value, $value2);
            }
        }

        return $query->orderBy('z.datum_ekspedicije_odgovora_zk', 'desc');
    }

    /**
     * Export ekspedovane tuzbe to Excel
     */
    public function exportEkspedovaneTuzbeExcel(Request $request)
    {
        $data = $this->getEkspedovaneTuzbeQuery($request)->get();

        return Excel::download(new class($data) implements FromCollection, WithHeadings {
            private $data;

            public function __construct($data)
            {
                $this->data = $data;
            }

            public function collection()
            {
                return $this->data->map(function($item) {
                    return [
                        'Ime i prezime' => $item->ime_i_prezime,
                        'Institucija' => $item->institucija_podnosioca_zalbe ?? '-',
                        'Prijemni broj' => $item->prijemni_broj ?? '-',
                        'Datum prijema odluke US' => $item->datum_prijema_odluke_us ? date('d.m.Y', strtotime($item->datum_prijema_odluke_us)) : '-',
                        'Datum prijema zalbe' => $item->datum_prijema_zalbe ? date('d.m.Y', strtotime($item->datum_prijema_zalbe)) : '-',
                        'Datum prijema tuzbe od US' => $item->datum_prijema_tuzbe_od_us ? date('d.m.Y', strtotime($item->datum_prijema_tuzbe_od_us)) : '-',
                        'Datum ekspedicije odgovora ZK' => $item->datum_ekspedicije_odgovora_zk ? date('d.m.Y', strtotime($item->datum_ekspedicije_odgovora_zk)) : '-',
                        'Status' => $item->status_zalbe ?? '-',
                    ];
                });
            }

            public function headings(): array
            {
                return [
                    'Ime i prezime',
                    'Institucija',
                    'Prijemni broj',
                    'Datum prijema odluke US',
                    'Datum prijema zalbe',
                    'Datum prijema tuzbe od US',
                    'Datum ekspedicije odgovora ZK',
                    'Status'
                ];
            }
        }, 'ekspedovane-tuzbe-' . date('Y-m-d') . '.xlsx');
    }

    /**
     * Export ekspedovane tuzbe to PDF
     */
    public function exportEkspedovaneTuzbePdf(Request $request)
    {
        $data = $this->getEkspedovaneTuzbeQuery($request)->get();

        $pdf = Pdf::loadView('pdf.ekspedovane-tuzbe', compact('data'));
        $pdf->setPaper('a4', 'landscape');

        return $pdf->download('ekspedovane-tuzbe-' . date('Y-m-d') . '.pdf');
    }

    /**
     * Odluke suda report
     */
    public function odlukeSuda(Request $request)
    {
        $query = DB::table('zalbe as z')
            ->join('podnosioci_zalbi as pz', 'z.podnosioci_zalbe', '=', 'pz.id')
            ->leftJoin('sifarnik_tip_presude as stp', 'z.tipovi_presude_us', '=', 'stp.id')
            ->select(
                DB::raw("CONCAT(pz.ime_podnosioca_zalbe, ' ', pz.prezime_podnosioca_zalbe) as ime_i_prezime"),
                'pz.institucija_podnosioca_zalbe',
                'z.prijemni_broj',
                'z.datum_prijema_odluke_us',
                'z.datum_prijema_zalbe',
                'z.datum_prijema_tuzbe_od_us',
                'z.status_zalbe',
                'stp.tip_presude'
            )
            ->whereIn('z.tipovi_presude_us', [1, 2, 3, 4, 5, 8]);

        // Simple search
        if ($request->has('search') && $request->search) {
            $searchTerm = '%' . $request->search . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where(DB::raw("CONCAT(pz.ime_podnosioca_zalbe, ' ', pz.prezime_podnosioca_zalbe)"), 'LIKE', $searchTerm)
                    ->orWhere('pz.institucija_podnosioca_zalbe', 'LIKE', $searchTerm)
                    ->orWhere('z.prijemni_broj', 'LIKE', $searchTerm)
                    ->orWhere('z.status_zalbe', 'LIKE', $searchTerm)
                    ->orWhere('stp.tip_presude', 'LIKE', $searchTerm);
            });
        }

        // Advanced filters
        $advancedFilters = $request->input('advanced_filters');
        if ($advancedFilters && is_string($advancedFilters)) {
            $advancedFilters = json_decode($advancedFilters, true);
        }

        if (is_array($advancedFilters) && count($advancedFilters) > 0) {
            $fieldMap = [
                'ime_i_prezime' => DB::raw("CONCAT(pz.ime_podnosioca_zalbe, ' ', pz.prezime_podnosioca_zalbe)"),
                'institucija_podnosioca_zalbe' => 'pz.institucija_podnosioca_zalbe',
                'prijemni_broj' => 'z.prijemni_broj',
                'datum_prijema_odluke_us' => 'z.datum_prijema_odluke_us',
                'datum_prijema_zalbe' => 'z.datum_prijema_zalbe',
                'datum_prijema_tuzbe_od_us' => 'z.datum_prijema_tuzbe_od_us',
                'status_zalbe' => 'z.status_zalbe',
                'tip_presude' => 'stp.tip_presude'
            ];

            foreach ($advancedFilters as $filter) {
                if (isset($filter['field']) && isset($filter['operator'])) {
                    $field = $fieldMap[$filter['field']] ?? null;
                    if ($field) {
                        $value = $filter['value'] ?? null;
                        $value2 = $filter['value2'] ?? null;
                        $this->applyAdvancedFilterOdlukeSuda($query, $field, $filter['operator'], $value, $value2);
                    }
                }
            }
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'z.datum_prijema_odluke_us');
        $sortDirection = $request->input('sort_direction', 'desc');

        // Allowed sortable fields
        $allowedSortFields = [
            'ime_i_prezime',
            'pz.institucija_podnosioca_zalbe',
            'z.prijemni_broj',
            'z.datum_prijema_odluke_us',
            'z.datum_prijema_zalbe',
            'z.datum_prijema_tuzbe_od_us',
            'z.status_zalbe',
            'stp.tip_presude'
        ];

        if (in_array($sortBy, $allowedSortFields)) {
            $query->orderBy($sortBy, $sortDirection);
        } else {
            $query->orderBy('z.datum_prijema_odluke_us', 'desc');
        }

        $data = $query->paginate(10);

        return response()->json($data);
    }

    /**
     * Apply advanced filter for odluke suda
     */
    private function applyAdvancedFilterOdlukeSuda($query, $field, $operator, $value, $value2 = null)
    {
        switch ($operator) {
            case 'equals':
                $query->where($field, '=', $value);
                break;
            case 'not_equals':
                $query->where($field, '!=', $value);
                break;
            case 'contains':
                $query->where($field, 'LIKE', '%' . $value . '%');
                break;
            case 'starts_with':
                $query->where($field, 'LIKE', $value . '%');
                break;
            case 'ends_with':
                $query->where($field, 'LIKE', '%' . $value);
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
     * Get export query for odluke suda
     */
    private function getOdlukeSudaQuery(Request $request)
    {
        $query = DB::table('zalbe as z')
            ->join('podnosioci_zalbi as pz', 'z.podnosioci_zalbe', '=', 'pz.id')
            ->leftJoin('sifarnik_tip_presude as stp', 'z.tipovi_presude_us', '=', 'stp.id')
            ->select(
                DB::raw("CONCAT(pz.ime_podnosioca_zalbe, ' ', pz.prezime_podnosioca_zalbe) as ime_i_prezime"),
                'pz.institucija_podnosioca_zalbe',
                'z.prijemni_broj',
                'z.datum_prijema_odluke_us',
                'z.datum_prijema_zalbe',
                'z.datum_prijema_tuzbe_od_us',
                'z.status_zalbe',
                'stp.tip_presude'
            )
            ->whereIn('z.tipovi_presude_us', [1, 2, 3, 4, 5, 8]);

        // Simple search
        if ($request->has('search') && $request->search) {
            $searchTerm = '%' . $request->search . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where(DB::raw("CONCAT(pz.ime_podnosioca_zalbe, ' ', pz.prezime_podnosioca_zalbe)"), 'LIKE', $searchTerm)
                    ->orWhere('pz.institucija_podnosioca_zalbe', 'LIKE', $searchTerm)
                    ->orWhere('z.prijemni_broj', 'LIKE', $searchTerm)
                    ->orWhere('z.status_zalbe', 'LIKE', $searchTerm)
                    ->orWhere('stp.tip_presude', 'LIKE', $searchTerm);
            });
        }

        // Advanced filters
        $advancedFilters = $request->input('advanced_filters');
        if ($advancedFilters && is_string($advancedFilters)) {
            $advancedFilters = json_decode($advancedFilters, true);
        }

        if (is_array($advancedFilters) && count($advancedFilters) > 0) {
            $fieldMap = [
                'ime_i_prezime' => DB::raw("CONCAT(pz.ime_podnosioca_zalbe, ' ', pz.prezime_podnosioca_zalbe)"),
                'institucija_podnosioca_zalbe' => 'pz.institucija_podnosioca_zalbe',
                'prijemni_broj' => 'z.prijemni_broj',
                'datum_prijema_odluke_us' => 'z.datum_prijema_odluke_us',
                'datum_prijema_zalbe' => 'z.datum_prijema_zalbe',
                'datum_prijema_tuzbe_od_us' => 'z.datum_prijema_tuzbe_od_us',
                'status_zalbe' => 'z.status_zalbe',
                'tip_presude' => 'stp.tip_presude'
            ];

            foreach ($advancedFilters as $filter) {
                if (isset($filter['field']) && isset($filter['operator'])) {
                    $field = $fieldMap[$filter['field']] ?? null;
                    if ($field) {
                        $value = $filter['value'] ?? null;
                        $value2 = $filter['value2'] ?? null;
                        $this->applyAdvancedFilterOdlukeSuda($query, $field, $filter['operator'], $value, $value2);
                    }
                }
            }
        }

        return $query->orderBy('z.datum_prijema_odluke_us', 'desc');
    }

    /**
     * Export odluke suda to Excel
     */
    public function exportOdlukeSudaExcel(Request $request)
    {
        $data = $this->getOdlukeSudaQuery($request)->get();

        return Excel::download(new class($data) implements FromCollection, WithHeadings {
            protected $data;

            public function __construct($data)
            {
                $this->data = $data;
            }

            public function collection()
            {
                return $this->data->map(function ($item) {
                    return [
                        'Име и презиме' => $item->ime_i_prezime ?? '',
                        'Институција' => $item->institucija_podnosioca_zalbe ?? '',
                        'Пријемни број' => $item->prijemni_broj ?? '',
                        'Датум пријема одлуке УС' => $item->datum_prijema_odluke_us ? date('d.m.Y', strtotime($item->datum_prijema_odluke_us)) : '',
                        'Датум пријема жалбе' => $item->datum_prijema_zalbe ? date('d.m.Y', strtotime($item->datum_prijema_zalbe)) : '',
                        'Датум пријема тужбе од УС' => $item->datum_prijema_tuzbe_od_us ? date('d.m.Y', strtotime($item->datum_prijema_tuzbe_od_us)) : '',
                        'Статус' => $item->status_zalbe ?? '',
                        'Тип пресуде' => $item->tip_presude ?? ''
                    ];
                });
            }

            public function headings(): array
            {
                return [
                    'Име и презиме',
                    'Институција',
                    'Пријемни број',
                    'Датум пријема одлуке УС',
                    'Датум пријема жалбе',
                    'Датум пријема тужбе од УС',
                    'Статус',
                    'Тип пресуде'
                ];
            }
        }, 'odluke-suda-' . date('Y-m-d') . '.xlsx');
    }

    /**
     * Export odluke suda to PDF
     */
    public function exportOdlukeSudaPdf(Request $request)
    {
        $data = $this->getOdlukeSudaQuery($request)->get();

        $pdf = Pdf::loadView('pdf.odluke-suda', compact('data'));
        $pdf->setPaper('a4', 'landscape');

        return $pdf->download('odluke-suda-' . date('Y-m-d') . '.pdf');
    }

    /**
     * Upravni sporovi u toku report
     */
    public function upravniSporoviUToku(Request $request)
    {
        $data = $this->getUpravniSporoviUTokuQuery();
        return response()->json($data);
    }

    /**
     * Get query for upravni sporovi u toku
     */
    private function getUpravniSporoviUTokuQuery()
    {
        $query = DB::select("
            WITH grupisano AS (
                SELECT
                    CASE
                        WHEN pz.institucija_podnosioca_zalbe LIKE '%унутрашњи%' THEN pz.institucija_podnosioca_zalbe
                        WHEN pz.institucija_podnosioca_zalbe LIKE '%ореска упр%' THEN pz.institucija_podnosioca_zalbe
                        WHEN pz.institucija_podnosioca_zalbe LIKE '%геод%' THEN pz.institucija_podnosioca_zalbe
                        WHEN pz.institucija_podnosioca_zalbe LIKE '%санкц%' THEN pz.institucija_podnosioca_zalbe
                        ELSE 'Остали'
                    END AS institucija,
                    COUNT(z.id) AS broj_zalbi,
                    SUM(CASE
                          WHEN pz.institucija_podnosioca_zalbe LIKE '%унутрашњи%'
                               AND pz.id = 54
                          THEN 1
                          ELSE 0
                        END) AS broj_id54,
                    CASE
                        WHEN pz.institucija_podnosioca_zalbe LIKE '%унутрашњи%' THEN 1
                        WHEN pz.institucija_podnosioca_zalbe LIKE '%ореска упр%' THEN 2
                        WHEN pz.institucija_podnosioca_zalbe LIKE '%геод%' THEN 3
                        WHEN pz.institucija_podnosioca_zalbe LIKE '%санкц%' THEN 4
                        ELSE 5
                    END AS ord
                FROM zalbena.zalbe z
                JOIN zalbena.podnosioci_zalbi pz ON z.podnosioci_zalbe = pz.id
                WHERE z.tipovi_presude_us = 3
                GROUP BY
                    CASE
                        WHEN pz.institucija_podnosioca_zalbe LIKE '%унутрашњи%' THEN pz.institucija_podnosioca_zalbe
                        WHEN pz.institucija_podnosioca_zalbe LIKE '%ореска упр%' THEN pz.institucija_podnosioca_zalbe
                        WHEN pz.institucija_podnosioca_zalbe LIKE '%геод%' THEN pz.institucija_podnosioca_zalbe
                        WHEN pz.institucija_podnosioca_zalbe LIKE '%санкц%' THEN pz.institucija_podnosioca_zalbe
                        ELSE 'Остали'
                    END,
                    CASE
                        WHEN pz.institucija_podnosioca_zalbe LIKE '%унутрашњи%' THEN 1
                        WHEN pz.institucija_podnosioca_zalbe LIKE '%ореска упр%' THEN 2
                        WHEN pz.institucija_podnosioca_zalbe LIKE '%геод%' THEN 3
                        WHEN pz.institucija_podnosioca_zalbe LIKE '%санкц%' THEN 4
                        ELSE 5
                    END
            )
            SELECT institucija, broj_zalbi, broj_id54
            FROM (
                SELECT institucija, broj_zalbi, broj_id54, ord
                FROM grupisano
                UNION ALL
                SELECT 'Укупно', SUM(broj_zalbi), SUM(broj_id54), 6
                FROM grupisano
            ) AS rezultat
            ORDER BY ord
        ");

        return collect($query);
    }

    /**
     * Export upravni sporovi u toku to Excel
     */
    public function exportUpravniSporoviUTokuExcel(Request $request)
    {
        $data = $this->getUpravniSporoviUTokuQuery();

        return Excel::download(new class($data) implements FromCollection, WithHeadings {
            protected $data;

            public function __construct($data)
            {
                $this->data = $data;
            }

            public function collection()
            {
                return $this->data->map(function ($item) {
                    return [
                        'Институција' => $item->institucija ?? '',
                        'Број предмета' => $item->broj_zalbi ?? '0',
                        'Јасмина Михаиловић' => $item->broj_id54 ?? '0'
                    ];
                });
            }

            public function headings(): array
            {
                return [
                    'Институција',
                    'Број предмета',
                    'Јасмина Михаиловић'
                ];
            }
        }, 'upravni-sporovi-u-toku-' . date('Y-m-d') . '.xlsx');
    }

    /**
     * Export upravni sporovi u toku to PDF
     */
    public function exportUpravniSporoviUTokuPdf(Request $request)
    {
        $data = $this->getUpravniSporoviUTokuQuery();

        $pdf = Pdf::loadView('pdf.upravni-sporovi-u-toku', compact('data'));
        $pdf->setPaper('a4', 'landscape');

        return $pdf->download('upravni-sporovi-u-toku-' . date('Y-m-d') . '.pdf');
    }

    /**
     * Upravni sporovi po godinama report
     */
    public function upravniSporoviPoGodinama(Request $request)
    {
        $data = $this->getUpravniSporoviPoGodinamaQuery();
        return response()->json($data);
    }

    /**
     * Get query for upravni sporovi po godinama
     */
    private function getUpravniSporoviPoGodinamaQuery()
    {
        $query = DB::select("
            WITH godine_temp AS (
                SELECT
                    YEAR(z.datum_prijema_zalbe) AS god_num,
                    z.id
                FROM zalbena.zalbe z
                WHERE z.tipovi_presude_us = 3
            ),
            grupisano AS (
                SELECT
                    CAST(god_num AS CHAR) AS godina,
                    COUNT(id) AS broj_zalbi,
                    god_num AS ord
                FROM godine_temp
                GROUP BY god_num
            )
            SELECT godina, broj_zalbi
            FROM (
                SELECT godina, broj_zalbi, ord
                FROM grupisano
                UNION ALL
                SELECT 'Укупно', SUM(broj_zalbi), 9999 AS ord
                FROM grupisano
            ) AS rezultat
            ORDER BY ord
        ");

        return collect($query);
    }

    /**
     * Export upravni sporovi po godinama to Excel
     */
    public function exportUpravniSporoviPoGodinamaExcel(Request $request)
    {
        $data = $this->getUpravniSporoviPoGodinamaQuery();

        return Excel::download(new class($data) implements FromCollection, WithHeadings {
            protected $data;

            public function __construct($data)
            {
                $this->data = $data;
            }

            public function collection()
            {
                return $this->data->map(function ($item) {
                    return [
                        'Година' => $item->godina ?? '',
                        'Број предмета' => $item->broj_zalbi ?? '0'
                    ];
                });
            }

            public function headings(): array
            {
                return [
                    'Година',
                    'Број предмета'
                ];
            }
        }, 'upravni-sporovi-po-godinama-' . date('Y-m-d') . '.xlsx');
    }

    /**
     * Export upravni sporovi po godinama to PDF
     */
    public function exportUpravniSporoviPoGodinamaPdf(Request $request)
    {
        $data = $this->getUpravniSporoviPoGodinamaQuery();

        $pdf = Pdf::loadView('pdf.upravni-sporovi-po-godinama', compact('data'));
        $pdf->setPaper('a4', 'landscape');

        return $pdf->download('upravni-sporovi-po-godinama-' . date('Y-m-d') . '.pdf');
    }

    /**
     * Export zalbe to PDF (optimized for large datasets)
     */
    public function exportZalbePdf(Request $request)
    {
        set_time_limit(480); // 8 minutes
        ini_set('memory_limit', '1024M'); // Increase memory

        $data = $this->getZalbeExportQuery($request)->get();

        $pdf = Pdf::loadView('pdf.zalbe', compact('data'));
        $pdf->setPaper('a4', 'landscape');
        $pdf->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => false,
            'dpi' => 96, // Lower DPI for faster rendering
            'defaultFont' => 'DejaVu Sans',
        ]);

        return $pdf->download('zalbe-' . date('Y-m-d') . '.pdf');
    }

    /**
     * Export zalbe to Excel (extended dataset)
     */
    public function exportZalbeExcel(Request $request)
    {
        set_time_limit(480); // 8 minutes

        $data = $this->getZalbeExportQueryExtended($request)->get();

        return Excel::download(new class($data) implements FromCollection, WithHeadings {
            protected $data;

            public function __construct($data)
            {
                $this->data = $data;
            }

            public function collection()
            {
                return $this->data->map(function($item) {
                    return [
                        'Пријемни број' => $item->prijemni_broj ?? '-',
                        'Датум пријема' => $item->datum_prijema_zalbe ? date('d.m.Y', strtotime($item->datum_prijema_zalbe)) : '-',
                        'Број решења' => $item->broj_resenja ?? '-',
                        'Статус' => $item->status_zalbe ?? '-',
                        'Име подносиоца' => $item->ime_podnosioca_zalbe ?? '-',
                        'Презиме подносиоца' => $item->prezime_podnosioca_zalbe ?? '-',
                        'ЈМБГ' => $item->jmbg_podnosioca_zalbe ?? '-',
                        'Институција' => $item->institucija_podnosioca_zalbe ?? '-',
                        'Основ жалбе' => $item->osnov_zalbe ?? '-',
                        'Тип решења' => $item->tip_resenja ?? '-',
                        'Тип решења напомена' => $item->tip_resenja_napomena ?? '-',
                        'Датум враћања на допуну' => $item->datum_vracanja_na_dopunu ? date('d.m.Y', strtotime($item->datum_vracanja_na_dopunu)) : '-',
                        'Датум пријема допуне' => $item->datum_prijema_dopune ? date('d.m.Y', strtotime($item->datum_prijema_dopune)) : '-',
                        'Датум предаје комисији' => $item->datum_predaje_komisiji ? date('d.m.Y', strtotime($item->datum_predaje_komisiji)) : '-',
                        'Датум решавања на ЗК' => $item->datum_resavanja_na_zk ? date('d.m.Y', strtotime($item->datum_resavanja_na_zk)) : '-',
                        'Датум експедиције ДС органу' => $item->datum_ekspedicije_ds_organu ? date('d.m.Y', strtotime($item->datum_ekspedicije_ds_organu)) : '-',
                        'Датум истицања доношење' => $item->datum_isticanja_donosenje ? date('d.m.Y', strtotime($item->datum_isticanja_donosenje)) : '-',
                        'Рок за допуну' => $item->rok_za_dopunu ? date('d.m.Y', strtotime($item->rok_za_dopunu)) : '-',
                        'Комисија ЖКВ' => $item->komisija_zkv ?? '-',
                        'Чланови комисије 1' => $item->clanovi_komisije1 ?? '-',
                        'Чланови комисије 2' => $item->clanovi_komisije2 ?? '-',
                        'Накнада' => $item->naknada ?? '-',
                        'Број одлуке УС' => $item->broj_odluke_us ?? '-',
                        'Датум доношења одлуке УС' => $item->datum_donosenja_odluke_us ? date('d.m.Y', strtotime($item->datum_donosenja_odluke_us)) : '-',
                        'Датум пријема тужбе од УС' => $item->datum_prijema_tuzbe_od_us ? date('d.m.Y', strtotime($item->datum_prijema_tuzbe_od_us)) : '-',
                        'Датум експедиције одговора ЗК' => $item->datum_ekspedicije_odgovora_zk ? date('d.m.Y', strtotime($item->datum_ekspedicije_odgovora_zk)) : '-',
                        'Датум пријема одлуке УС' => $item->datum_prijema_odluke_us ? date('d.m.Y', strtotime($item->datum_prijema_odluke_us)) : '-',
                        'Број решења ЗК по пресуди УС' => $item->broj_resenja_zk_po_presudi_us ?? '-',
                        'Датум решења ЗК по пресуди УС' => $item->datum_resenja_zk_po_presudi_us ? date('d.m.Y', strtotime($item->datum_resenja_zk_po_presudi_us)) : '-',
                        'Достaвница' => $item->dostavnica ? date('d.m.Y', strtotime($item->dostavnica)) : '-',
                        'Тип пресуде УС' => $item->tip_presude ?? '-',
                        'Напомена' => $item->napomena ?? '-',
                    ];
                });
            }

            public function headings(): array
            {
                return [
                    'Пријемни број',
                    'Датум пријема',
                    'Број решења',
                    'Статус',
                    'Име подносиоца',
                    'Презиме подносиоца',
                    'ЈМБГ',
                    'Институција',
                    'Основ жалбе',
                    'Тип решења',
                    'Тип решења напомена',
                    'Датум враћања на допуну',
                    'Датум пријема допуне',
                    'Датум предаје комисији',
                    'Датум решавања на ЗК',
                    'Датум експедиције ДС органу',
                    'Датум истицања доношење',
                    'Рок за допуну',
                    'Комисија ЗКВ',
                    'Чланови комисије 1',
                    'Чланови комисије 2',
                    'Накнада',
                    'Број одлуке УС',
                    'Датум доношења одлуке УС',
                    'Датум пријема тужбе од УС',
                    'Датум експедиције одговора ЗК',
                    'Датум пријема одлуке УС',
                    'Број решења ЗК по пресуди УС',
                    'Датум решења ЗК по пресуди УС',
                    'Доставница',
                    'Тип пресуде УС',
                    'Напомена',
                ];
            }
        }, 'zalbe-' . date('Y-m-d') . '.xlsx');
    }

    /**
     * Get query for zalbe export
     */
    private function getZalbeExportQuery(Request $request)
    {
        // Subquery to get minimum datum_prijema_zalbe for each institution
        $minDateSubquery = DB::table('zalbe as z2')
            ->join('podnosioci_zalbi as pz2', 'z2.podnosioci_zalbe', '=', 'pz2.id')
            ->select('pz2.institucija_podnosioca_zalbe', DB::raw('MIN(z2.datum_prijema_zalbe) as min_datum'))
            ->groupBy('pz2.institucija_podnosioca_zalbe');

        $query = DB::table('zalbe as z')
            ->leftJoin('podnosioci_zalbi as pz', 'z.podnosioci_zalbe', '=', 'pz.id')
            ->leftJoin('sifarnik_osnov_zalbe as soz', 'z.osnov_zalbe', '=', 'soz.id')
            ->leftJoin('sifarnik_tipovi_resenja as str', 'z.tipovi_resenja', '=', 'str.id')
            ->leftJoinSub($minDateSubquery, 'inst_min', function($join) {
                $join->on('pz.institucija_podnosioca_zalbe', '=', 'inst_min.institucija_podnosioca_zalbe');
            })
            ->select(
                'z.datum_prijema_zalbe',
                'pz.institucija_podnosioca_zalbe',
                'soz.osnov_zalbe',
                'str.tip_resenja',
                'z.status_zalbe',
                'inst_min.min_datum'
            );

        // Simple search filter
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('z.prijemni_broj', 'LIKE', "%{$search}%")
                  ->orWhere('z.broj_resenja', 'LIKE', "%{$search}%")
                  ->orWhere('pz.institucija_podnosioca_zalbe', 'LIKE', "%{$search}%")
                  ->orWhere('z.status_zalbe', 'LIKE', "%{$search}%");
            });
        }

        // Decode advanced_filters if it's a JSON string
        $advancedFilters = $request->input('advanced_filters');
        if ($advancedFilters && is_string($advancedFilters)) {
            $advancedFilters = json_decode($advancedFilters, true);
        }

        // Advanced search filters
        if (is_array($advancedFilters) && count($advancedFilters) > 0) {
            foreach ($advancedFilters as $filter) {
                if (!isset($filter['field']) || !isset($filter['operator'])) {
                    continue;
                }

                $field = $filter['field'];
                $operator = $filter['operator'];
                $value = $filter['value'] ?? null;
                $value2 = $filter['value2'] ?? null;

                // Map field names to actual database columns
                $fieldMap = [
                    'datum_prijema_zalbe' => 'z.datum_prijema_zalbe',
                    'institucija' => 'pz.institucija_podnosioca_zalbe',
                    'osnov_zalbe' => 'soz.osnov_zalbe',
                    'tipovi_resenja' => 'str.tip_resenja',
                    'status_zalbe' => 'z.status_zalbe',
                ];

                $dbField = $fieldMap[$field] ?? null;

                if ($dbField) {
                    $this->applyAdvancedFilterZalbe($query, $dbField, $operator, $value, $value2);
                }
            }
        }

        return $query->orderBy('inst_min.min_datum', 'asc')
                    ->orderBy('pz.institucija_podnosioca_zalbe', 'asc')
                    ->orderBy('z.datum_prijema_zalbe', 'asc');
    }

    /**
     * Apply advanced filter for zalbe report
     */
    private function applyAdvancedFilterZalbe($query, $field, $operator, $value, $value2 = null)
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
     * Get extended query for zalbe export (Excel with all fields)
     */
    private function getZalbeExportQueryExtended(Request $request)
    {
        $query = DB::table('zalbe as z')
            ->leftJoin('podnosioci_zalbi as pz', 'z.podnosioci_zalbe', '=', 'pz.id')
            ->leftJoin('sifarnik_osnov_zalbe as soz', 'z.osnov_zalbe', '=', 'soz.id')
            ->leftJoin('sifarnik_tipovi_resenja as str', 'z.tipovi_resenja', '=', 'str.id')
            ->leftJoin('sifarnik_tip_presude as stp', 'z.tipovi_presude_us', '=', 'stp.id')
            ->select(
                // Osnovni podaci žalbe
                'z.prijemni_broj',
                'z.datum_prijema_zalbe',
                'z.broj_resenja',
                'z.status_zalbe',

                // Podnosilac
                'pz.ime_podnosioca_zalbe',
                'pz.prezime_podnosioca_zalbe',
                'pz.jmbg_podnosioca_zalbe',
                'pz.institucija_podnosioca_zalbe',

                // Šifarnici
                'soz.osnov_zalbe',
                'str.tip_resenja',
                'stp.tip_presude',

                // Datumi
                'z.datum_vracanja_na_dopunu',
                'z.datum_prijema_dopune',
                'z.datum_predaje_komisiji',
                'z.datum_resavanja_na_zk',
                'z.datum_ekspedicije_ds_organu',
                'z.datum_isticanja_donosenje',
                'z.rok_za_dopunu',
                'z.datum_donosenja_odluke_us',
                'z.datum_prijema_tuzbe_od_us',
                'z.datum_ekspedicije_odgovora_zk',
                'z.datum_prijema_odluke_us',
                'z.datum_resenja_zk_po_presudi_us',
                'z.dostavnica',

                // Dodatni podaci
                'z.tip_resenja_napomena',
                'z.napomena',
                'z.komisije_zkv as komisija_zkv',
                'z.clanovi_komisije1',
                'z.clanovi_komisije2',
                'z.naknada',
                'z.broj_odluke_us',
                'z.broj_resenja_zk_po_presudi_us'
            );

        // Simple search filter
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('z.prijemni_broj', 'LIKE', "%{$search}%")
                  ->orWhere('z.broj_resenja', 'LIKE', "%{$search}%")
                  ->orWhere('pz.ime_podnosioca_zalbe', 'LIKE', "%{$search}%")
                  ->orWhere('pz.prezime_podnosioca_zalbe', 'LIKE', "%{$search}%")
                  ->orWhere('pz.institucija_podnosioca_zalbe', 'LIKE', "%{$search}%")
                  ->orWhere('z.status_zalbe', 'LIKE', "%{$search}%");
            });
        }

        // Podnosilac filter
        if ($request->has('podnosilac_id') && $request->podnosilac_id) {
            $query->where('z.podnosioci_zalbe', $request->podnosilac_id);
        }

        // Advanced search filters
        $advancedFilters = $request->input('advanced_filters');
        if ($advancedFilters && is_string($advancedFilters)) {
            $advancedFilters = json_decode($advancedFilters, true);
        }

        if (is_array($advancedFilters) && count($advancedFilters) > 0) {
            foreach ($advancedFilters as $filter) {
                if (!isset($filter['field']) || !isset($filter['operator'])) {
                    continue;
                }

                $field = $filter['field'];
                $operator = $filter['operator'];
                $value = $filter['value'] ?? null;
                $value2 = $filter['value2'] ?? null;

                // Map field names to actual database columns
                $fieldMap = [
                    'podnosioci_zalbe' => DB::raw("CONCAT(pz.ime_podnosioca_zalbe, ' ', pz.prezime_podnosioca_zalbe)"),
                    'prijemni_broj' => 'z.prijemni_broj',
                    'broj_resenja' => 'z.broj_resenja',
                    'datum_prijema_zalbe' => 'z.datum_prijema_zalbe',
                    'datum_resavanja' => 'z.datum_resavanja_na_zk',
                    'status_zalbe' => 'z.status_zalbe',
                    'institucija' => 'pz.institucija_podnosioca_zalbe',
                    'osnov_zalbe' => 'soz.osnov_zalbe',
                    'tipovi_resenja' => 'str.tip_resenja',
                ];

                $dbField = $fieldMap[$field] ?? null;

                if ($dbField) {
                    // Reuse the same filter logic
                    $this->applyAdvancedFilterZalbe($query, $dbField, $operator, $value, $value2);
                }
            }
        }

        return $query->orderBy('z.datum_prijema_zalbe', 'desc');
    }
}
