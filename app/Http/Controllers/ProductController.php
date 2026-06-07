<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    private function userWarehouse()
    {
        $user = auth()->user();

        if (! $user || ! $user->warehouse) {
            abort(response()->json([
                'message' => 'برای مدیریت کالاها ابتدا باید وارد حساب کاربری شوید و انبار داشته باشید.',
            ], 401));
        }

        return $user->warehouse;
    }

    public function index()
    {
        $warehouse = $this->userWarehouse();

        $products = $warehouse->products()
            ->latest()
            ->get();

        return response()->json([
            'products' => $products,
        ]);
    }

    public function stats()
    {
        $warehouse = $this->userWarehouse();

        $totalProducts = $warehouse->products()->count();

        $lowStockProducts = $warehouse->products()
            ->where('quantity', '>', 0)
            ->where('quantity', '<=', 5)
            ->count();

        $todayIn = $warehouse->stockMovements()
            ->where('type', 'in')
            ->whereDate('created_at', now()->toDateString())
            ->sum('quantity');

        $todayOut = $warehouse->stockMovements()
            ->where('type', 'out')
            ->whereDate('created_at', now()->toDateString())
            ->sum('quantity');

        return response()->json([
            'stats' => [
                'total_products' => $totalProducts,
                'low_stock_products' => $lowStockProducts,
                'today_added_products' => $todayIn,
                'today_removed_products' => $todayOut,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $warehouse = $this->userWarehouse();

        $validator = Validator::make($request->all(), [
            'code' => [
                'required',
                'string',
                'max:255',
                Rule::unique('products', 'code')->where('warehouse_id', $warehouse->id),
            ],
            'name' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:255'],
            'quantity' => ['required', 'integer', 'min:0'],
        ], [
            'code.required' => 'کد کالا الزامی است.',
            'code.unique' => 'این کد کالا قبلاً در انبار شما ثبت شده است.',
            'name.required' => 'نام کالا الزامی است.',
            'quantity.required' => 'موجودی کالا الزامی است.',
            'quantity.integer' => 'موجودی باید عدد صحیح باشد.',
            'quantity.min' => 'موجودی نمی‌تواند منفی باشد.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'اطلاعات وارد شده معتبر نیست.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $product = $warehouse->products()->create([
            'code' => $request->code,
            'name' => $request->name,
            'category' => $request->category,
            'quantity' => $request->quantity,
        ]);

        return response()->json([
            'message' => 'کالا با موفقیت اضافه شد.',
            'product' => $product,
        ], 201);
    }

    public function update(Request $request, Product $product)
    {
        $warehouse = $this->userWarehouse();

        if ($product->warehouse_id !== $warehouse->id) {
            return response()->json([
                'message' => 'شما اجازه ویرایش این کالا را ندارید.',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'code' => [
                'required',
                'string',
                'max:255',
                Rule::unique('products', 'code')
                    ->where('warehouse_id', $warehouse->id)
                    ->ignore($product->id),
            ],
            'name' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:255'],
            'quantity' => ['required', 'integer', 'min:0'],
        ], [
            'code.required' => 'کد کالا الزامی است.',
            'code.unique' => 'این کد کالا قبلاً در انبار شما ثبت شده است.',
            'name.required' => 'نام کالا الزامی است.',
            'quantity.required' => 'موجودی کالا الزامی است.',
            'quantity.integer' => 'موجودی باید عدد صحیح باشد.',
            'quantity.min' => 'موجودی نمی‌تواند منفی باشد.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'اطلاعات وارد شده معتبر نیست.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $product->update([
            'code' => $request->code,
            'name' => $request->name,
            'category' => $request->category,
            'quantity' => $request->quantity,
        ]);

        return response()->json([
            'message' => 'کالا با موفقیت ویرایش شد.',
            'product' => $product,
        ]);
    }

    public function destroy(Product $product)
    {
        $warehouse = $this->userWarehouse();

        if ($product->warehouse_id !== $warehouse->id) {
            return response()->json([
                'message' => 'شما اجازه حذف این کالا را ندارید.',
            ], 403);
        }

        $product->delete();

        return response()->json([
            'message' => 'کالا با موفقیت حذف شد.',
        ]);
    }
}