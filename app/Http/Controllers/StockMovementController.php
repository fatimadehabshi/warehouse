<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class StockMovementController extends Controller
{
    private function userWarehouse()
    {
        $user = auth()->user();

        if (! $user || ! $user->warehouse) {
            abort(response()->json([
                'message' => 'برای ثبت رویداد انبار ابتدا باید وارد حساب کاربری شوید.',
            ], 401));
        }

        return $user->warehouse;
    }

    public function index(Request $request)
    {
        $warehouse = $this->userWarehouse();

        $query = $warehouse->stockMovements()
            ->with('product')
            ->latest();

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        $movements = $query->get();

        return response()->json([
            'movements' => $movements,
        ]);
    }

    public function store(Request $request)
    {
        $warehouse = $this->userWarehouse();

        $validator = Validator::make($request->all(), [
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'type' => ['required', 'in:in,out'],
            'quantity' => ['required', 'integer', 'min:1'],
            'description' => ['nullable', 'string', 'max:1000'],
        ], [
            'product_id.required' => 'انتخاب کالا الزامی است.',
            'product_id.exists' => 'کالای انتخاب‌شده معتبر نیست.',
            'type.required' => 'نوع رویداد الزامی است.',
            'type.in' => 'نوع رویداد باید ورود یا خروج باشد.',
            'quantity.required' => 'تعداد الزامی است.',
            'quantity.integer' => 'تعداد باید عدد صحیح باشد.',
            'quantity.min' => 'تعداد باید حداقل ۱ باشد.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'اطلاعات وارد شده معتبر نیست.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $product = $warehouse->products()
            ->where('id', $request->product_id)
            ->first();

        if (! $product) {
            return response()->json([
                'message' => 'این کالا متعلق به انبار شما نیست.',
            ], 403);
        }

        try {
            $movement = DB::transaction(function () use ($request, $warehouse, $product) {
                $stockBefore = $product->quantity;
                $movementQuantity = (int) $request->quantity;

                if ($request->type === 'in') {
                    $stockAfter = $stockBefore + $movementQuantity;
                } else {
                    if ($movementQuantity > $stockBefore) {
                        abort(response()->json([
                            'message' => 'موجودی کافی برای خروج این تعداد کالا وجود ندارد.',
                        ], 422));
                    }

                    $stockAfter = $stockBefore - $movementQuantity;
                }

                $product->update([
                    'quantity' => $stockAfter,
                ]);

                return StockMovement::create([
                    'warehouse_id' => $warehouse->id,
                    'product_id' => $product->id,
                    'type' => $request->type,
                    'quantity' => $movementQuantity,
                    'stock_before' => $stockBefore,
                    'stock_after' => $stockAfter,
                    'description' => $request->description,
                ]);
            });

            return response()->json([
                'message' => 'رویداد انبار با موفقیت ثبت شد.',
                'movement' => $movement->load('product'),
            ], 201);
        } catch (\Throwable $e) {
            if ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpException) {
                throw $e;
            }

            return response()->json([
                'message' => 'خطا در ثبت رویداد انبار.',
            ], 500);
        }
    }
}