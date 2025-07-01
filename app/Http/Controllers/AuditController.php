<?php

namespace App\Http\Controllers;

use App\Models\Audit;
use App\Models\User; 
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditController extends Controller
{
    
    public function index(Request $request): View
    {
       
        $eventTypes = Audit::distinct('event')->pluck('event');

        $auditableTypes = Audit::distinct('auditable_type')
                                ->pluck('auditable_type')
                                ->map(fn($type) => str_replace('App\\Models\\', '', $type));

        $auditingUsers = User::whereIn('id', Audit::distinct('user_id')->pluck('user_id'))
                               ->orderBy('name')
                               ->get();

        $auditsQuery = Audit::with(['user', 'auditable']);

        if ($request->has('event_type') && $request->input('event_type') !== '' && $request->input('event_type')) {

            $auditsQuery->where('event', $request->input('event_type'));
        }

        if ($request->has('auditable_type') && $request->input('auditable_type') !== '' && $request->input('auditable_type')) {
            
            $auditsQuery->where('auditable_type', 'App\\Models\\' . $request->input('auditable_type'));
        }

        if ($request->has('user_id') && $request->input('user_id') !== '' && $request->input('user_id')) {

            dd(32423, $request->input('user_id'));
            $auditsQuery->where('user_id', $request->input('user_id'));
        }

         if ($request->has('from_date') && !empty($request->input('from_date')) && $request->input('from_date')) {
            try {
                $fromDate = Carbon::parse($request->input('from_date'))->startOfDay();
                $auditsQuery->whereDate('created_at', '>=', $fromDate);
            } catch (\Exception $e) {

                \Log::warning("Invalid 'from_date' provided: " . $request->input('from_date') . " - " . $e->getMessage());
            }
        }

        if ($request->has('to_date') && !empty($request->input('to_date')) && $request->input('to_date')) {
            try {
                $toDate = Carbon::parse($request->input('to_date'))->endOfDay();
                $auditsQuery->whereDate('created_at', '<=', $toDate);
            } catch (\Exception $e) {
                \Log::warning("Invalid 'to_date' provided: " . $request->input('to_date') . " - " . $e->getMessage());
            }
        }

        if ($request->has('search') && $request->input('search') !== '' && $request->input('search') ) {
            $search = '%' . $request->input('search') . '%';
            $auditsQuery->where(function($query) use ($search) {
                $query->where('old_values', 'like', $search)
                      ->orWhere('new_values', 'like', $search)
                      ->orWhere('url', 'like', $search)
                      ->orWhere('user_agent', 'like', $search)
                      ->orWhere('ip_address', 'like', $search)
                      ->orWhere('custom_details', 'like', $search);
            });
        }

        $audits = $auditsQuery->orderByDesc('created_at')->paginate(20);

        $audits->appends($request->except('page'));

        return view('audits.index', compact('audits', 'eventTypes', 'auditableTypes', 'auditingUsers'));
    }
    
    public function show(Audit $audit): View
    {
        $audit->load(['user', 'auditable']);

        return view('audits.show', compact('audit'));
    }
}