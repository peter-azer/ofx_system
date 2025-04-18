<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Lead;
use App\Models\FollowUp;
use App\Models\Offer;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

class LeadsController extends Controller
{

    use AuthorizesRequests;

    public function __construct()
    {
        $this->authorize('access-sales');
    }



    public function index()
    {
        return response()->json(['message' => 'Welcome to the Sales Dashboard!']);
    }

    /**
     * Create a lead
     */


    public function checkFollowUp($id)
    {
        // Get the authenticated user's ID
        $userId = auth()->user()->id;


        $lead = Lead::find($id);

        if (!$lead) {
            return response()->json([
                'message' => 'Lead not found.'
            ], 404);
        }


        if ($lead->sales_id !== $userId) {
            return response()->json([
                'message' => 'You are not authorized to check follow-ups for this lead.'
            ], 403);
        }


        $lastFollowUp = FollowUp::where('lead_id', $lead->id)
            ->latest('created_at')
            ->first();

        if ($lastFollowUp && Carbon::parse($lastFollowUp->created_at)->addDays(10)->isFuture()) {
            return response()->json([
                'message' => 'You cannot add a follow-up for this lead until 10 days have passed since the last follow-up.',
                'can_follow_up' => false
            ], 200);
        }

        // Follow-up can be added
        return response()->json([
            'message' => 'You can add a follow-up for this lead.',
            'can_follow_up' => true
        ], 200);
    }


    public function create(Request $request)
    {
        $request->validate([
            'company_name' => 'required|string|max:255',
            'client_name' => 'required|string|max:255',
            'email' => 'email|nullable',
            // 'phone' => 'required|array|min:1',
            // 'phone.*' => 'string|max:15',
            'from_where' => 'required|string|max:255',
        ]);


        $existingLead = Lead::whereJsonContains('phone', $request->phone)->first();

        if ($existingLead) {

            $lastFollowUp = FollowUp::where('lead_id', $existingLead->id)
                ->latest('followed_date')
                ->first();


            if ($lastFollowUp && Carbon::parse($lastFollowUp->followed_date)->addDays(10)->isFuture()) {
                return response()->json([
                    'message' => 'This lead already exists and is assigned to another salesperson.',
                    'sales_id' => $existingLead->sales_id,
                    'existing_lead' => $existingLead
                ], 400);
            }
        }


        $lead = Lead::create([
            'sales_id' => auth()->user()->id,
            'company_name' => $request->company_name,
            'client_name' => $request->client_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'from_where' => $request->from_where,
            'status' => 'new',
        ]);

        return response()->json([
            'message' => 'Lead created successfully',
            'lead' => $lead
        ], 201);
    }


    /**
     * Update lead status
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|string|max:255|in:new,in_progress,closed,contracted',
        ]);

        $lead = Lead::find($id);

        if (!$lead) {
            return response()->json(['message' => 'Lead not found'], 404);
        }

        $lead->status = $request->status;
        $lead->save();

        return response()->json(['message' => 'Status updated successfully', 'lead' => $lead], 200);
    }

    public function assignLead(Request $request, $id){
        $lead = Lead::findOrFail($id);
        if (!$lead) {
            return response()->json(['message' => 'Lead not found'], 404);
        }
        $request->validate([
            'sales_id' => 'required|exists:users,id',
        ]);
        $lead->sales_id = $request->sales_id;
        $lead->save();
        return response()->json(['message' => 'Lead assigned successfully', 'lead' => $lead], 200);
    }
    /**
     * Add a follow-up
     */
    public function addFollowUp(Request $request)
    {
        $request->validate([
            'lead_id' => 'required|exists:leads,id',
            'details' => 'required|string|max:255',
            'followed_date' => 'required|date',
            'status' => 'required|in:un-qualified,qualified,cold-lead,hot-lead',
        ]);

        $followUp = FollowUp::create([
            'lead_id' => $request->lead_id,
            'details' => $request->details,
            'followed_date' => $request->followed_date,
            'status' => $request->status
        ]);

        return response()->json(['message' => 'Follow-up added successfully', 'followup' => $followUp], 201);
    }

    /**
     * Add an offer
     */
    public function addOffer(Request $request)
    {
        $request->validate([
            'lead_id' => 'required|exists:leads,id',
            'offer' => 'required|file|mimes:pdf,jpeg,png|max:2048',
            'description' => 'required|string',
            'valid_until' => 'required|date'
        ]);

        if ($request->hasFile('offer') && $request->file('offer')->isValid()) {

            $filePath = URL::to(Storage::url($request->file('offer')->store('offers', 'public')));

            $offer = Offer::create([
                'lead_id' => $request->lead_id,
                'offer_path' => $filePath,
                'description' => $request->description,
                'valid_until' => $request->valid_until,
            ]);


            return response()->json(['message' => 'Offer added successfully', 'offer' => $offer], 201);
        } else {

            return response()->json(['error' => 'No valid file uploaded'], 400);
        }
    }


    public function getLeadsWithDetails()
    {
        $user = auth()->user();


        $leads = Lead::where('sales_id', $user->id)
            ->with(['followups', 'offers', 'notes'])
            ->get();

        return $leads;
    }

    public function getAllLeads()
    {
        $leads = Lead::with(['followups', 'offers', 'notes', 'sales'])->get();
        
        return $leads;
    }

    public function getTeamLeads()
    {
        $manager = auth()->user();

        // Retrieve the manager's teams and their associated users and leads
        $teams = $manager->teams()->with(['users.leads', 'teamLeader', 'users.leads.followups', 'users.leads.offers'])->get();

        // Flatten the structure to leads with team and sales details
        $response = $teams->flatMap(function ($team) {
            return $team->users->flatMap(function ($user) use ($team) {
                return $user->leads->map(function ($lead) use ($team, $user) {
                    return [
                        'lead_id' => $lead->id,
                        'company_name' => $lead->company_name,
                        'client_name' => $lead->client_name,
                        'phone' => $lead->phone,
                        'status' => $lead->status,
                        'created_at' => $lead->created_at,
                        'details' => [
                            'followups' => $lead->followups,
                            'offers' => $lead->offers,
                            'notes' => $lead->notes,
                        ],
                        'team' => [
                            'team_name' => $team->name,
                            'team_leader' => $team->teamleader_id,
                            'service_id' => $team->service_id,
                            'branch' => $team->branch,
                        ],
                        'sales_person' => [
                            'name' => $user->name,
                            'email' => $user->email,
                        ],
                    ];
                });
            });
        });

        return response()->json([
            'status' => 'success',
            'data' => $response,
        ]);
    }

    // get leads for teamleaders
    public function getTeamLeadsForTeamLeader()
    {
        $user = Auth::user();
        $teamId = $user->leader->id;

        return Lead::with(['salesEmployee', 'followups', 'offers']) // Load all defined relationships dynamically
            ->whereHas('salesEmployee', function ($query) use ($teamId) {
                $query->where('team_id', $teamId);
            })->get();
    }
    // ==========================================================================


    public function filterLeadsByStatus(Request $request)
    {
        $user = auth()->user();

        // Validate status input
        $validated = $request->validate([
            'status' => 'required|in:new,inprogress,closed,deals',
        ]);

        $status = $validated['status'];

        // Fetch leads filtered by status
        $leads = Lead::where('sales_id', $user->id)
            ->where('status', $status)
            ->with(['followups', 'offers', 'notes'])
            ->get();

        return response()->json($leads);
    }


    public function filterallLeadsByStatus(Request $request)
    {

        $validated = $request->validate([
            'status' => 'required|in:new,inprogress,closed,deals',
        ]);

        $status = $validated['status'];

        // Fetch leads filtered by status
        $leads = Lead::where('status', $status)
            ->with(['followups', 'offers', 'notes', 'sales'])
            ->get();

        return response()->json($leads);
    }

    public function filterfollowupsByStatus(Request $request, $leadid)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'status' => 'required|in:un-qualified,qualified,cold-lead,hot-lead',
        ]);

        $status = $validated['status'];


        $leads = FollowUp::where('lead_id', $leadid)
            ->where('status', $status)
            ->with(['lead', 'notes'])
            ->get();

        return response()->json($leads);
    }

    public function allfollowups($leadid)
    {


        $leads = FollowUp::where('lead_id', $leadid)->get();

        return response()->json($leads);
    }


    public function alloffers($leadid)
    {

        $leads = Offer::where('lead_id', $leadid)->get();

        return response()->json($leads);
    }

    public function filterallfollowupsByStatus(Request $request, $leadid)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'status' => 'required|in:Un-qualified,qualified,cold-lead,hot-lead',
        ]);

        $status = $validated['status'];


        $leads = FollowUp::where('lead_id', $leadid)
            ->where('status', $status)
            ->with(['lead', 'notes'])
            ->get();

        return response()->json($leads);
    }
}
