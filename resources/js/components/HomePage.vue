<script setup>
import { onMounted, reactive, watch } from 'vue'

const props = defineProps({
  goToPage: Function,
  logoUrl: String,
  isLoggedIn: Boolean,
})

const stats = reactive({
  totalProducts: 0,
  lowStockProducts: 0,
  todayAddedProducts: 0,
  todayRemovedProducts: 0,
})

const resetStats = () => {
  stats.totalProducts = 0
  stats.lowStockProducts = 0
  stats.todayAddedProducts = 0
  stats.todayRemovedProducts = 0
}

const fetchStats = async () => {
  if (!props.isLoggedIn){
    resetStats()
    return
  }
  
  try {
    const response = await fetch('/products-stats', {
      headers: {
        Accept: 'application/json',
      },
    })

    const data = await response.json()

    if (!response.ok) {
      return
    }

    stats.totalProducts = data.stats.total_products
    stats.lowStockProducts = data.stats.low_stock_products
    stats.todayAddedProducts = data.stats.today_added_products
    stats.todayRemovedProducts = data.stats.today_removed_products
  } catch (error) {
    console.error(error)
  }
}

watch(
  () => props.isLoggedIn,
  (newValue) => {
    if (newValue) {
      fetchStats()
    } else {
      resetStats()
    }
  }
)

onMounted(() => {
  fetchStats()
})
</script>

<template>
  <section dir="rtl" class="bg-[#F8FAFC] px-6 py-16">
    <div class="mx-auto grid max-w-6xl items-center gap-12 lg:grid-cols-2">
      <div>
        <div class="mb-6 flex items-center gap-4">
          <img :src="logoUrl" alt="لوگوی انباریار" class="h-16 w-16 object-contain" />
        </div>

        <h2 class="mb-5 text-4xl font-bold leading-tight text-[#023047]">
          مدیریت ساده، سریع و دقیق انبار
        </h2>

        <p class="mb-8 max-w-xl text-base leading-8 text-slate-600">
          انباریار یک نرم‌افزار ساده برای مدیریت کالاها، مشاهده موجودی، ثبت ورود و خروج کالا و کنترل وضعیت انبار است.
        </p>

        <div class="flex flex-wrap gap-4">
          <button
            @click="goToPage('auth')"
            class="rounded-2xl bg-[#023047] px-6 py-3 text-sm font-bold text-white transition hover:bg-[#03415f]"
          >
            ورود / ثبت‌نام
          </button>

          <button
            @click="goToPage('products')"
            class="rounded-2xl border border-[#219EBC] px-6 py-3 text-sm font-bold text-[#219EBC] transition hover:bg-[#EAF7FB]"
          >
            مشاهده لیست کالاها
          </button>
        </div>
      </div>

      <div class="rounded-3xl bg-white p-8 shadow-xl">
        <div class="mb-6 rounded-2xl bg-[#023047] p-6 text-white">
          <p class="mb-2 text-sm text-[#8ECAE6]">داشبورد انبار</p>
          <h3 class="text-2xl font-bold">وضعیت کلی موجودی</h3>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
          <div class="rounded-2xl bg-slate-50 p-5">
            <p class="text-sm text-slate-500">کل کالاها</p>
            <p class="mt-2 text-3xl font-bold text-[#023047]">
              {{ stats.totalProducts }}
            </p>
          </div>

          <div class="rounded-2xl bg-[#FFF4D6] p-5">
            <p class="text-sm text-slate-600">موجودی کم</p>
            <p class="mt-2 text-3xl font-bold text-[#FB8500]">
              {{ stats.lowStockProducts }}
            </p>
          </div>

          <div class="rounded-2xl bg-slate-50 p-5">
            <p class="text-sm text-slate-500">ورود امروز</p>
            <p class="mt-2 text-3xl font-bold text-[#219EBC]">
              {{ stats.todayAddedProducts }}
            </p>
          </div>

          <div class="rounded-2xl bg-slate-50 p-5">
            <p class="text-sm text-slate-500">خروج امروز</p>
            <p class="mt-2 text-3xl font-bold text-[#023047]">
              {{ stats.todayRemovedProducts }}
            </p>
          </div>
        </div>

        <p class="mt-5 text-xs leading-6 text-slate-400">
          خروج امروز پس از اضافه شدن بخش ورود و خروج موجودی به‌صورت واقعی محاسبه خواهد شد.
        </p>
      </div>
    </div>
  </section>
</template>