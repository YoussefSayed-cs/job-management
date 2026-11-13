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

    public function store(StoreJobCategoryRequest $request): RedirectResponse
    {
        JobCategory::create($request->validated());

        return redirect()->route('categories.index')->with('status', 'Category created successfully.');
    }


    public function edit(JobCategory $category): View
    {
        Gate::authorize('update', $category);

        return view('categories.edit', compact('category'));
    }

    public function update(UpdateJobCategoryRequest $request, JobCategory $category): RedirectResponse
    {
        $category->update($request->validated());

        return redirect()->route('categories.index')->with('status', 'Category updated successfully.');
    }


    public function destroy(JobCategory $category): RedirectResponse
    {
        Gate::authorize('delete', $category);

        $category->delete();

        return redirect()->route('categories.index')->with('status', 'Category moved to trash successfully.');
    }

    public function trash(): View
    {
        Gate::authorize('viewAny', JobCategory::class);

        $categories = JobCategory::onlyTrashed()->withCount('jobVacancies')->orderByDesc('deleted_at')->paginate(15);

        return view('categories.trash', compact('categories'));
    }

    public function restore(string $id): RedirectResponse
    {
        $category = JobCategory::onlyTrashed()->findOrFail($id);

        Gate::authorize('restore', $category);

        $category->restore();

        return redirect()->route('categories.trash')->with('status', 'Category restored successfully.');
    }
}
