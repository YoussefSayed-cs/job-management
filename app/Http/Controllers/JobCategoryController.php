<?php

namespace App\Http\Controllers;

use App\Http\Requests\Category\StoreJobCategoryRequest;
use App\Http\Requests\Category\UpdateJobCategoryRequest;
use App\Models\JobCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class JobCategoryController extends Controller
{
    public function index(): View
    {
        Gate::authorize('viewAny', JobCategory::class);

        $categories = JobCategory::query()->withCount('jobVacancies')->orderBy('name')->paginate(15);

        return view('categories.index', compact('categories'));
    }

    public function create(): View
    {
        Gate::authorize('create', JobCategory::class);

        return view('categories.create');
    }

    public function store(StoreJobCategoryRequest $request ): RedirectResponse
     {
        JobCategory::create($request->validated());

        return redirect()->route('categories.index')->with('status', 'Category created successfully.');
               
    }    
   

    public function edit(JobCategory $category): View
    {
        Gate::authorize('update', $category);

        return view('categories.edit', compact('category'));
    }

    public function update(UpdateJobCategoryRequest $request, JobCategory $category ): RedirectResponse   
    {
        $category->update($request->validated());

        return redirect()->route('categories.index')->with('status', 'Category updated successfully.');
            
    }
    
    
    public function destroy(JobCategory $category): RedirectResponse
    {
        Gate::authorize('delete', $category);

        // A category cannot be deleted while Jobs reference it.
        if ($category->jobVacancies()->exists()) {
            return back()->withErrors([
                'category' => 'This category cannot be deleted because it is currently assigned to one or more jobs.',
            ]);
        }

        $category->delete();

        return redirect()->route('categories.index')->with('status', 'Category deleted successfully.');
           
    }
}