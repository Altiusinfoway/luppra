<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if (!auth()->user()->can('manage category')) {
            return redirect()->back()->with('error', 'Permission denied.');
        }

        if ($request->ajax()) {
            try {
                $query = Category::with('parent')
                    ->select('id', 'parent_id', 'name', 'created_at')->orderBy('id', 'desc');

                return DataTables::of($query)
                    ->addIndexColumn()

                    ->addColumn('parent_name', function ($row) {
                        return $row->parent ? $row->parent->name : '-';
                    })

                    ->addColumn('action', function ($row) {
                        $editUrl   = route('category.edit', $row->id);
                        $deleteUrl = route('category.destroy', $row->id);

                        $html = '<div class="dropdown d-inline-block">
                            <button class="btn btn-soft-secondary btn-sm dropdown" type="button"
                                data-bs-toggle="dropdown">
                                <i class="ri-more-fill"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">';

                        if (auth()->user()->can('edit category')) {
                            $html .= '
                                <li>
                                    <a href="javascript:void(0);"
                                        class="dropdown-item"
                                        data-ajax-popup="true"
                                        data-size="lg"
                                        data-url="' . $editUrl . '">
                                        <i class="ri-pencil-fill me-2"></i>Edit
                                    </a>
                                </li>';
                        }

                        // if (auth()->user()->can('delete category')) {
                        //     $html .= '
                        //         <li>
                        //             <form method="POST" action="' . $deleteUrl . '">
                        //                 ' . csrf_field() . method_field('DELETE') . '
                        //                 <button type="submit" class="dropdown-item text-danger">
                        //                     <i class="ri-delete-bin-fill me-2"></i>Delete
                        //                 </button>
                        //             </form>
                        //         </li>';
                        // }

                        $html .= '</ul></div>';

                        return $html;
                    })

                    ->rawColumns(['action'])
                    ->make(true);
            } catch (\Exception $e) {
                return response()->json([
                    'error' => $e->getMessage()
                ], 500);
            }
        }

        return view('category.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (!auth()->user()->can('create category')) {
            return response()->json([
                'error' => 'Permission denied.'
            ], 403);
        }

        $categories = Category::whereNull('parent_id')
            ->pluck('name', 'id')
            ->toArray();

        return view('category.create', compact('categories'));
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (!auth()->user()->can('create category')) {
            return redirect()->back()->with('error', 'Permission denied.');
        }

        $request->validate([
            'name'      => 'required|string|max:255',
            'parent_id' => 'nullable|exists:tenant.categories,id'
        ]);

        Category::create([
            'name'      => $request->name,
            'parent_id' => $request->parent_id,
        ]);

        return redirect()
            ->route('category.index')
            ->with('success', 'Category created successfully.');
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        if (!auth()->user()->can('edit category')) {
            return response()->json([
                'error' => 'Permission denied.'
            ], 403);
        }

        $category = Category::findOrFail($id);

        // Exclude current category from parent list
        $categories = Category::where('id', '!=', $id)
            ->pluck('name', 'id')
            ->toArray();

        return view('category.edit', compact('category', 'categories'));
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        if (!auth()->user()->can('edit category')) {
            return redirect()->back()->with('error', 'Permission denied.');
        }

        $request->validate([
            'name'      => 'required|string|max:255',
            'parent_id' => 'nullable|exists:tenant.categories,id|not_in:' . $id,
        ]);

        $category = Category::findOrFail($id);

        $category->update([
            'name'      => $request->name,
            'parent_id' => $request->parent_id,
        ]);

        return redirect()
            ->route('category.index')
            ->with('success', 'Category updated successfully.');
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        if (!auth()->user()->can('delete category')) {
            return redirect()->back()->with('error', 'Permission denied.');
        }

        $category = Category::findOrFail($id);

        // Check if category has child categories
        $hasChildren = Category::where('parent_id', $id)->exists();

        if ($hasChildren) {
            return redirect()->back()
                ->with('error', 'You cannot delete this category because it has sub-categories.');
        }

        $category->delete();

        return redirect()->back()->with('success', 'Category deleted successfully.');
    }
}
