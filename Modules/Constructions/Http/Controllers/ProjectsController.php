<?php

namespace Modules\Constructions\Http\Controllers;

use App\Utils\ModuleUtil;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Constructions\Entities\Project;
use Yajra\DataTables\Facades\DataTables;
use App\Contact;

class ProjectsController extends Controller
{
    /**
     * All Utils instance.
     */
    protected $moduleUtil;

    /**
     * Constructor
     */
    public function __construct(ModuleUtil $moduleUtil)
    {
        $this->moduleUtil = $moduleUtil;
    }

    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {
        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');
            
            $projects = Project::where('business_id', $business_id)
                            ->select(['id', 'name', 'contact_id', 'start_date', 'end_date', 'budget', 
                                'status', 'location', 'created_at']);
                                
            return DataTables::of($projects)
                ->addColumn('action', function ($row) {
                    $html = '<div class="btn-group">
                        <button type="button" class="btn btn-info dropdown-toggle btn-xs" data-toggle="dropdown" aria-expanded="false">'. __("messages.actions") .'
                            <span class="caret"></span>
                            <span class="sr-only">Toggle Dropdown</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-right" role="menu">';
                        
                    if (auth()->user()->can('constructions.view_project')) {
                        $html .= '<li><a href="' . action([\Modules\Constructions\Http\Controllers\ProjectsController::class, 'show'], [$row->id]) . '"><i class="fas fa-eye"></i> ' . __("messages.view") . '</a></li>';
                    }
                    
                    if (auth()->user()->can('constructions.edit_project')) {
                        $html .= '<li><a href="' . action([\Modules\Constructions\Http\Controllers\ProjectsController::class, 'edit'], [$row->id]) . '"><i class="fas fa-edit"></i> ' . __("messages.edit") . '</a></li>';
                    }
                    
                    if (auth()->user()->can('constructions.delete_project')) {
                        $html .= '<li><a href="' . action([\Modules\Constructions\Http\Controllers\ProjectsController::class, 'destroy'], [$row->id]) . '" class="delete-project"><i class="fas fa-trash"></i> ' . __("messages.delete") . '</a></li>';
                    }
                    
                    $html .= '</ul></div>';
                    
                    return $html;
                })
                ->editColumn('contact_id', function ($row) {
                    $contact = Contact::find($row->contact_id);
                    return $contact ? $contact->name : '';
                })
                ->editColumn('start_date', '{{@format_date($start_date)}}')
                ->editColumn('end_date', '{{@format_date($end_date)}}')
                ->editColumn('budget', '{{@num_format($budget)}}')
                ->editColumn('status', function ($row) {
                    $statuses = [
                        'planning' => '<span class="label label-info">Planning</span>',
                        'in_progress' => '<span class="label label-warning">In Progress</span>',
                        'on_hold' => '<span class="label label-danger">On Hold</span>',
                        'completed' => '<span class="label label-success">Completed</span>',
                        'cancelled' => '<span class="label label-default">Cancelled</span>',
                    ];
                    
                    return $statuses[$row->status] ?? '';
                })
                ->editColumn('created_at', '{{@format_datetime($created_at)}}')
                ->rawColumns(['action', 'status'])
                ->make(true);
        }
        
        return view('constructions::projects.index');
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        $business_id = request()->session()->get('user.business_id');
        $clients = Contact::where('business_id', $business_id)
                        ->where('type', 'customer')
                        ->pluck('name', 'id');
        
        return view('constructions::projects.create')
            ->with(compact('clients'));
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        try {
            $business_id = $request->session()->get('user.business_id');
            
            $input = $request->only(['name', 'contact_id', 'start_date', 'end_date', 
                                    'budget', 'status', 'location', 'description']);
            $input['business_id'] = $business_id;
            $input['created_by'] = $request->session()->get('user.id');
            
            $project = Project::create($input);
            
            $output = [
                'success' => true,
                'msg' => __('constructions::lang.project_created_successfully')
            ];
        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile(). " Line:" . $e->getLine(). " Message:" . $e->getMessage());
            
            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong')
            ];
        }
        
        return redirect()->route('constructions.projects.index')->with('status', $output);
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Response
     */
    public function show($id)
    {
        $business_id = request()->session()->get('user.business_id');
        $project = Project::where('business_id', $business_id)
                        ->with(['client'])
                        ->findOrFail($id);
        
        return view('constructions::projects.show')
            ->with(compact('project'));
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Response
     */
    public function edit($id)
    {
        $business_id = request()->session()->get('user.business_id');
        $project = Project::where('business_id', $business_id)
                        ->findOrFail($id);
        
        $clients = Contact::where('business_id', $business_id)
                        ->where('type', 'customer')
                        ->pluck('name', 'id');
        
        return view('constructions::projects.edit')
            ->with(compact('project', 'clients'));
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        try {
            $business_id = $request->session()->get('user.business_id');
            $project = Project::where('business_id', $business_id)
                            ->findOrFail($id);
            
            $input = $request->only(['name', 'contact_id', 'start_date', 'end_date', 
                                    'budget', 'status', 'location', 'description']);
            
            $project->update($input);
            
            $output = [
                'success' => true,
                'msg' => __('constructions::lang.project_updated_successfully')
            ];
        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile(). " Line:" . $e->getLine(). " Message:" . $e->getMessage());
            
            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong')
            ];
        }
        
        return redirect()->route('constructions.projects.index')->with('status', $output);
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Response
     */
    public function destroy($id)
    {
        try {
            $business_id = request()->session()->get('user.business_id');
            $project = Project::where('business_id', $business_id)
                            ->findOrFail($id);
            
            $project->delete();
            
            $output = [
                'success' => true,
                'msg' => __('constructions::lang.project_deleted_successfully')
            ];
        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile(). " Line:" . $e->getLine(). " Message:" . $e->getMessage());
            
            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong')
            ];
        }
        
        return $output;
    }
} 