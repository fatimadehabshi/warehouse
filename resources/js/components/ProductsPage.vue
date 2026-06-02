<script setup>
const products = [
  {
    code: 'PRD-001',
    name: 'لپ‌تاپ لنوو',
    category: 'الکترونیک',
    quantity: 12,
    status: 'موجود',
  },
  {
    code: 'PRD-002',
    name: 'کابل شبکه',
    category: 'تجهیزات جانبی',
    quantity: 4,
    status: 'موجودی کم',
  },
  {
    code: 'PRD-003',
    name: 'مانیتور سامسونگ',
    category: 'الکترونیک',
    quantity: 0,
    status: 'ناموجود',
  },
  {
    code: 'PRD-004',
    name: 'صندلی اداری',
    category: 'اداری',
    quantity: 20,
    status: 'موجود',
  },
  {
    code: 'PRD-005',
    name: 'ماوس بی‌سیم',
    category: 'تجهیزات جانبی',
    quantity: 7,
    status: 'موجود',
  },
]

const statusClass = (status) => {
  if (status === 'موجود') {
    return 'bg-green-100 text-green-700'
  }

  if (status === 'موجودی کم') {
    return 'bg-[#FFF4D6] text-[#FB8500]'
  }

  return 'bg-red-100 text-red-700'
}
</script>

<template>
  <section dir="rtl" class="bg-[#F8FAFC] px-6 py-16">
    <div class="mx-auto max-w-6xl">
      <div class="mb-8 flex flex-col justify-between gap-4 md:flex-row md:items-center">
        <div>
          <p class="mb-2 text-sm font-bold text-[#219EBC]">مدیریت کالاها</p>
          <h1 class="text-3xl font-bold text-[#023047]">لیست کالاهای انبار</h1>
          <p class="mt-3 text-sm text-slate-500">
            در این صفحه نمونه‌ای از کالاهای ثبت‌شده در انبار نمایش داده می‌شود.
          </p>
        </div>

        <button class="rounded-2xl bg-[#023047] px-5 py-3 text-sm font-bold text-white">
          افزودن کالا
        </button>
      </div>

      <div class="overflow-hidden rounded-3xl bg-white shadow-lg">
        <div class="overflow-x-auto">
          <table class="w-full min-w-[800px] text-right">
            <thead class="bg-slate-50 text-sm text-slate-500">
              <tr>
                <th class="px-6 py-4">کد کالا</th>
                <th class="px-6 py-4">نام کالا</th>
                <th class="px-6 py-4">دسته‌بندی</th>
                <th class="px-6 py-4">موجودی</th>
                <th class="px-6 py-4">وضعیت</th>
              </tr>
            </thead>

            <tbody class="divide-y divide-slate-100 text-sm">
              <tr v-for="product in products" :key="product.code" class="hover:bg-slate-50">
                <td class="px-6 py-4 font-medium text-[#023047]">{{ product.code }}</td>
                <td class="px-6 py-4 text-slate-700">{{ product.name }}</td>
                <td class="px-6 py-4 text-slate-600">{{ product.category }}</td>
                <td class="px-6 py-4 text-slate-700">{{ product.quantity }}</td>
                <td class="px-6 py-4">
                  <span class="rounded-full px-3 py-1 text-xs font-bold" :class="statusClass(product.status)">
                    {{ product.status }}
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </section>
</template>