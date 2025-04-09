<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function store(Request $request)
    {
        try {
            Log::info('Dữ liệu gửi đến:', $request->all());

            // Xác thực dữ liệu
            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:categories,name',
                'parent_id' => 'nullable|exists:categories,id',
            ], [
                'name.required' => 'Category name is required',
                'name.max' => 'Category name cannot exceed 255 characters',
                'name.unique' => 'Category name already exists',
                'parent_id.exists' => 'Parent category does not exist',
            ]);

            // Tự động tạo slug từ tên
            $slug = Str::slug($validated['name']);
            $originalSlug = $slug;
            $count = 1;

            // Kiểm tra và tạo slug duy nhất
            while (Category::where('slug', $slug)->exists()) {
                $slug = $originalSlug . '-' . $count;
                $count++;
            }

            $validated['slug'] = $slug;

            // Kiểm tra nếu parent_id rỗng thì gán null
            if (empty($validated['parent_id'])) {
                $validated['parent_id'] = null;
            }

            // Kiểm tra không cho phép chọn chính nó làm danh mục cha
            if ($request->has('id') && $request->parent_id == $request->id) {
                return back()
                    ->withInput()
                    ->withErrors(['parent_id' => 'Cannot select the same category as a parent category']);
            }

            // Tạo danh mục mới
            $category = Category::create($validated);

            Log::info('New category created:', ['category' => $category]);

            // Trả về phản hồi JSON
            return response()->json(['success' => true, 'message' => 'Category added successfully.']);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation error:', ['errors' => $e->errors()]);
            return response()->json(['success' => false, 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error:', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    public function showCategories()
    {
        $categories = Category::with(['children', 'parent'])->get();

        return view('admin.categories.category-list', compact('categories'));
    }

    public function index()
    {
        // Lấy danh sách categories với phân trang, mỗi trang 10 items
        $categories = Category::with('parent')->paginate(10);

        // Kiểm tra và xử lý các thuộc tính created_at và updated_at
        foreach ($categories as $category) {
            $category->created_at_formatted = $category->created_at ? $category->created_at->format('Y-m-d H:i:s') : 'Not specified';
            $category->updated_at_formatted = $category->updated_at ? $category->updated_at->format('Y-m-d H:i:s') : 'Not specified';
            $category->parent_name = $category->parent ? $category->parent->name : 'No parent category';
        }

        Log::info('List of categories:', ['categories' => $categories]);
        return view('admin.categories.category-list', compact('categories'));
    }

    public function create()
    {
        // Lấy danh sách các danh mục cha
        $parentCategories = Category::whereNull('parent_id')->get(); // Chỉ lấy các danh mục cha

        return view('admin.categories.add-category', compact('parentCategories'));
    }

    public function destroy($id)
    {
        try {
            $category = Category::findOrFail($id);
            $category->delete();

            Log::info('Category deleted:', ['category_id' => $id]);

            return response()->json(['success' => true, 'message' => 'Category deleted successfully.']);
        } catch (\Exception $e) {
            Log::error('Error deleting category:', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    public function edit($id)
    {
        // Lấy thông tin chi tiết của category theo ID
        $category = Category::with('parent')->findOrFail($id);
        $parentCategories = Category::whereNull('parent_id')->get(); // Lấy danh sách các danh mục cha

        return view('admin.categories.edit-category', compact('category', 'parentCategories'));
    }

    public function update(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:categories,name,' . $id,
                'parent_id' => 'nullable|exists:categories,id',
            ], [
                'name.required' => 'Category name cannot be empty',
                'name.max' => 'Category name cannot exceed 255 characters',
                'name.unique' => 'Category name already exists',
                'parent_id.exists' => 'Parent category does not exist',
            ]);

            $category = Category::findOrFail($id);
            $category->update($validated);

            Log::info('Category updated:', ['category' => $category]);

            return response()->json(['success' => true, 'message' => 'Category updated successfully.']);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation error:', ['errors' => $e->errors()]);
            return response()->json(['success' => false, 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error:', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }
}
