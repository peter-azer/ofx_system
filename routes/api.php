<?php
//ress
use App\Http\Controllers\BonusController;
use App\Http\Controllers\CollectionController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\LayoutController;
use App\Http\Controllers\Leads_adminController;
use App\Http\Controllers\LeadsController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\Owner\OwnerDashboardController;
use App\Http\Controllers\SalaryController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\userDashboardController;
use App\Http\Controllers\UserFilterationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Permission;
use App\Http\Controllers\ContractController;
use App\Http\Controllers\TeamController;
use Spatie\Permission\Http\Middleware\RoleMiddleware;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\LiabilityController;
use App\Http\Controllers\PriceListController;
use App\Http\Controllers\ManagerController;
use App\Http\Controllers\SubServieceController;

// =====================================================
use App\Http\Controllers\Auth\AuthController;



Route::get('/user', function (Request $request) {
    $user = $request->user();
    return response()->json([
        "user" => $user,
        "team" => $user->team,
        "role" => $user->getRoleNames()
    ]);
})->middleware('auth:sanctum');

Route::post('/login', [AuthController::class, 'login']);

Route::middleware(['auth:sanctum', 'role:owner'])->group(function(){
    // add new user
    Route::post('/add_employee', [AuthController::class, 'register']);
    // update users data
    Route::put('/users/{id}', [EmployeeController::class, 'update']);
    // get all users data
    Route::get('/users', [EmployeeController::class, 'index']);
    // delete users from the system permanently
    Route::delete('/user/{id}', [EmployeeController::class, 'destroy']);
    // soft delete any users not totally
    Route::delete('/user/{id}', [EmployeeController::class, 'softDeleteUser']);
    // restore deleted users
    Route::delete('/user/{id}', [EmployeeController::class, 'restoreUser']);
    // display all team leaders
    Route::get('/team/leaders', [EmployeeController::class, 'teamLeaders']);
});

// Group of routes for user management, protected by 'auth:sanctum' middleware
Route::middleware('auth:sanctum')->group(function () {
    // Route::post('/edit_employee/{id}', [EmployeeController::class, 'update']);

    Route::get('/roles', [EmployeeController::class, 'getAllRoles']);
    Route::get('/Permissions', [EmployeeController::class, 'getAllPermissions']);
});

// Group of routes for manager management, protected by 'auth:sanctum' middleware and prefixed with 'managers'
Route::middleware('auth:sanctum')->prefix('managers')->group(function () {
    Route::post('/add', [ManagerController::class, 'addManagerWithTeams']);
    Route::get('/', [ManagerController::class, 'getAllManagersWithTeams']);
    Route::get('/{id}', [ManagerController::class, 'getManagerById']);
    Route::delete('/{id}', [ManagerController::class, 'deleteManager']);
});

// Routes for contracts and collections
Route::get('/contracts', [ManagerController::class, 'getTeamContracts']);
Route::get('/collection', [ManagerController::class, 'getTeamcollection']);

// Additional user management routes, protected by 'auth:sanctum' middleware
Route::middleware('auth:sanctum')->group(function () {
    Route::delete('/users/{id}/delete', [EmployeeController::class, 'softDeleteUser']);
    Route::patch('/users/{id}/restore', [EmployeeController::class, 'restoreUser']);
    Route::get('/users/birth-month', [EmployeeController::class, 'getUsersByBirthMonth']);
});

// Route for adding a role, protected by 'auth:sanctum' middleware
Route::middleware('auth:sanctum')->group(function () {
    Route::post('add/role', [EmployeeController::class, 'addRole']);
});

// Group of routes for leads management, protected by 'auth:sanctum' middleware
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/leads/create', [LeadsController::class, 'create']);
    Route::get('/leads/{id}/check-followup', [LeadsController::class, 'checkFollowUp']);
    Route::post('/leads/{id}/status', [LeadsController::class, 'updateStatus']);
    Route::get('leads/user', [LeadsController::class, 'getLeadsWithDetails']);
    Route::get('all-leads', [LeadsController::class, 'getAllLeads']);
    Route::put('lead/assign/{id}', [LeadsController::class, 'assignLead']);
    Route::get('team-leads', [LeadsController::class, 'getTeamLeads']);
    Route::get('teamleader-leads', [LeadsController::class, 'getTeamLeadsForTeamLeader']);
    Route::get('leads/filter_status/{status}', [LeadsController::class, 'filterLeadsByStatus']);
    Route::get('leads/all/filter_status/{status}', [LeadsController::class, 'filterallLeadsByStatus']);
});

// Group of routes for offers management, protected by 'auth:sanctum' middleware and prefixed with 'offers'
Route::middleware('auth:sanctum')->prefix('offers')->group(function () {
    Route::post('/', [LeadsController::class, 'addOffer']);
    Route::get('/{leadid}', [LeadsController::class, 'alloffers']);
});

// Group of routes for follow-ups management, protected by 'auth:sanctum' middleware and prefixed with 'followups'
Route::middleware('auth:sanctum')->prefix('followups')->group(function () {
    Route::post('/', [LeadsController::class, 'addFollowUp']);
    Route::post('/filter/{leadid}', [LeadsController::class, 'filterfollowupsByStatus']);
    Route::get('/{leadid}', [LeadsController::class, 'allfollowups']);
    Route::get('/all/filter', [LeadsController::class, 'filterallfollowupsByStatus']);
});

// Group of routes for department management, protected by 'auth:sanctum' middleware and prefixed with 'departments'
Route::middleware(['auth:sanctum'])->prefix('departments')->group(function () {
    Route::get('/', [DepartmentController::class, 'index']);
    Route::get('/{id}', [DepartmentController::class, 'show']);
    Route::post('/', [DepartmentController::class, 'store']);
    Route::post('/{id}', [DepartmentController::class, 'update']);
    Route::delete('/{id}', [DepartmentController::class, 'destroy']);
});

// Group of routes for services management, protected by 'auth:sanctum' middleware and prefixed with 'services'
Route::middleware(['auth:sanctum'])->prefix('services')->group(function () {
    Route::get('/', [ServiceController::class, 'index']);
    Route::get('/all', [ServiceController::class, 'getall']);
    Route::get('/layouts', [ServiceController::class, 'show_layouts']);
    Route::get('/team/{id}', [ServiceController::class, 'show_team']);
    Route::get('/layouts/{id}', [ServiceController::class, 'show_layouts_id']);
    Route::post('/', [ServiceController::class, 'store']);
    Route::post('/{id}', [ServiceController::class, 'update']);
    Route::delete('/{id}', [ServiceController::class, 'destroy']);
    // Routes for sub-services management
    Route::get('/sub-services', [SubServieceController::class, 'index']);
    Route::post('/sub-services/new', [SubServieceController::class, 'store']);
    Route::delete('/sub-services/{id}', [SubServieceController::class, 'destroy']);
    Route::put('/sub-services/update/{id}', [SubServieceController::class, 'update']);
    Route::get('/all-sub-services', [SubServieceController::class, 'getSubServiecesAndParents']);
});

// Group of routes for contract management, protected by 'auth:sanctum' middleware and prefixed with 'contracts'
Route::middleware(['auth:sanctum'])->group(function () {
    Route::prefix('contracts')->group(function () {
        Route::post('/', [ContractController::class, 'createContract']);
        Route::get('/', [ContractController::class, 'getContract']);
        Route::get('/user', [ContractController::class, 'getContracts']);
        Route::get('/{id}', [ContractController::class, 'getContractDetails']);
        Route::post('/{id}', [ContractController::class, 'updateContract']);
        Route::delete('/{id}', [ContractController::class, 'deleteContract']);
        Route::post('/status/{id}', [TaskController::class, 'updateStatus']);
        Route::get('/{contractId}/services', [ContractController::class, 'getServicesByContract']);
        Route::get('/sales', [ContractController::class, 'getAllCollectionsBySales']);
    });
    // Group of routes for collection management, protected by 'auth:sanctum' middleware and prefixed with 'collections'
    Route::prefix('collections')->middleware('auth:sanctum')->group(function () {
        Route::post('/', [ContractController::class, 'handleCollections']);
        Route::get('/service/{serviceId}', [ContractController::class, 'getCollectionsByService']);
        Route::get('/user', [ContractController::class, 'getCollectionsByUser']);
        Route::get('/sales/{salesId}', [ContractController::class, 'getCollectionsBySales']);
        Route::get('/sales', [CollectionController::class, 'getAllCollectionsBySales']);
        Route::post('/{collectionId}/approval', [CollectionController::class, 'updateApproval']);
        Route::post('/{collectionId}/status', [CollectionController::class, 'updateStatus']);
        Route::get('/team/{contract_id}', [CollectionController::class, 'getCollectionPercentageByContract']);
        Route::post('/{id}', [CollectionController::class, 'updateCollectionWithAdjustments']);
    });
});

// Group of routes for team management, protected by 'auth:sanctum' middleware and prefixed with 'myteam'
Route::prefix('myteam')->middleware(['auth:sanctum'])->group(function () {
    Route::get('/contracts', [CollectionController::class, 'getTeamContracts']);
    Route::get('/collections/{contract_id}', [CollectionController::class, 'getTeamcollections']);
});

// Group of routes for layout management, protected by 'auth:sanctum' middleware
Route::middleware(['auth:sanctum'])->group(function () {
    Route::apiResource('layouts', LayoutController::class);
    Route::get('/contracts/{contractId}/layouts', [TaskController::class, 'getContractLayouts']);
});

// Group of routes for team management, protected by 'auth:sanctum' middleware
Route::middleware(['auth:sanctum'])->group(function () {
    Route::middleware('role:owner')->group(function () {
        Route::get('/allteams', [TeamController::class, 'getAllTeams']);
        Route::get('/all', [TeamController::class, 'getAllTeamsWithLeaders']);
        Route::post('team/department', [TeamController::class, 'filterAllTeamsWithdepartment']);
        Route::post('/teams', [TeamController::class, 'store']);
    });
    Route::get('/teamleader/members', [TeamController::class, 'getTeamLeaderMembers']);
    Route::get('/my-team', [TeamController::class, 'getMyTeamandLeader']);
    Route::middleware('role:manager')->get('/teams/members', [TeamController::class, 'getAllTeamsByType']);
});

// Group of routes for task management, protected by 'auth:sanctum' middleware
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/tasks/user', [TaskController::class, 'getUserTasks']);
    Route::post('/task/assign', [TaskController::class, 'assignTasksToTeamMember']);
    Route::get('/tasks/team', [TaskController::class, 'getTeamTasks']);
    Route::get('/tasks', [TaskController::class, 'getApprovedTasks']);
    Route::get('/tasks/status/{task_id}', [TaskController::class, 'updateTaskStatus']);
    Route::get('/tasks/approval/{task_id}', [TaskController::class, 'approveTask']);
    Route::get('/tasks/admin', [TaskController::class, 'getAllTasks']);
});

// Group of routes for note management, protected by 'auth:sanctum' middleware and prefixed with 'notes'
Route::prefix('notes')->middleware('auth:sanctum')->group(function () {
    Route::get('/sender', [NoteController::class, 'getNotesByUser']);
    Route::post('/create', [NoteController::class, 'createNote']);
    Route::get('/{notable_id}', [NoteController::class, 'getNote']);
    Route::get('/', [NoteController::class, 'getAllNotes']);
    Route::delete('/{id}', [NoteController::class, 'deleteNote']);
});

// Group of routes for user-specific notes, protected by 'auth:sanctum' middleware and prefixed with 'notes/user'
Route::prefix('notes/user')->middleware('auth:sanctum')->group(function () {
    Route::get('/private', [NoteController::class, 'getNotes_user']);
    Route::get('/leads', [NoteController::class, 'getNotes_leads']);
    Route::get('/contract', [NoteController::class, 'getNotes_Contracts']);
    Route::get('/task', [NoteController::class, 'getNotes_tasks']);
});

// Group of routes for liability management, protected by 'auth:sanctum' and 'role:owner' middleware
Route::middleware(['auth:sanctum', 'role:owner'])->group(function () {
    Route::get('/liabilities', [LiabilityController::class, 'index']);
    Route::post('/liabilities', [LiabilityController::class, 'store']);
    Route::put('/liabilities/{id}', [LiabilityController::class, 'update']);
    Route::delete('/liabilities/{id}', [LiabilityController::class, 'destroy']);
});

// Group of routes for price list management, protected by 'auth:sanctum' middleware and prefixed with 'price-list'
Route::prefix('price-list')->middleware('auth:sanctum')->group(function () {
    Route::get('/', [PriceListController::class, 'getAll']);
    Route::middleware('role:owner')->group(function () {
        Route::post('/', [PriceListController::class, 'store']);
        Route::put('/{id}', [PriceListController::class, 'update']);
        Route::delete('/{id}', [PriceListController::class, 'delete']);
    });
});

// Group of routes for owner dashboard, protected by 'auth:sanctum' and 'role:owner' middleware and prefixed with 'dashboard'
Route::prefix('dashboard')->middleware(['auth:sanctum', 'role:owner'])->group(function () {
    Route::get('/grouped-by-date', [OwnerDashboardController::class, 'getCollectionsGroupedByMonthAndYear']);
    Route::get('/grouped-by-sales-employee', [OwnerDashboardController::class, 'getCollectionsGroupedBySalesEmployeeUsingRelation']);
    Route::get('/grouped-by-Service', [OwnerDashboardController::class, 'getCollectionsGroupedBySalesEmployeeAndService']);
    Route::post('/report', [BonusController::class, 'getMonthlyReportv2']);
    Route::get('/sales-by-employee', [OwnerDashboardController::class, 'getTotalSalesByEmployee']);
    Route::get('/Services', [OwnerDashboardController::class, 'getTotalServicePrices']);
    Route::get('/totaly-report', [BonusController::class, 'gettotalyReport']);
});

// Group of routes for user-specific collections, protected by 'auth:sanctum' middleware and prefixed with 'user'
Route::prefix('user')->middleware('auth:sanctum')->group(function () {
    Route::get('/collection/filter', [UserFilterationController::class, 'getCollectionsGroupedByAuthUser']);
});

// Group of routes for salary management, protected by 'auth:sanctum' middleware and prefixed with 'salary'
Route::prefix('salary')->middleware('auth:sanctum')->group(function () {
    Route::post('/sales', [SalaryController::class, 'calculateAllSalesSalaries']);
    Route::post('/technical', [SalaryController::class, 'calculateTechnicalSalaries']);
    Route::post('/employee', [SalaryController::class, 'addSalary']);
    Route::post('/deduction/{id}', [SalaryController::class, 'addDeduction']);
    Route::delete('/{id}', [SalaryController::class, 'deletesalary']);
});

// Group of routes for bonuses management, protected by 'auth:sanctum' middleware and prefixed with 'bonuses'
Route::prefix('bonuses')->middleware('auth:sanctum')->group(function () {
    Route::post('/', [BonusController::class, 'createBonus']);
    Route::get('/', [BonusController::class, 'getAllBonuses']);
    Route::get('/{id}/check', [BonusController::class, 'checkBonusStatus']);
    Route::delete('/{id}', [BonusController::class, 'deleteBonus']);
});

// Group of routes for user dashboard, protected by 'auth:sanctum' middleware
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user/team', [userDashboardController::class, 'getTeamId']);
    Route::get('/sales', [CollectionController::class, 'getCollectionsByAuthUser']);
    Route::get('/collections', [CollectionController::class, 'getCollectionss']);
    Route::get('/sales-employees', [CollectionController::class, 'getAllSalesEmployeesWithCollections']);
});
