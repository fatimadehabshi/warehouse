<script setup>
import { onMounted, reactive, ref } from 'vue'

const products = ref([])
const movements = ref([])
const loading = ref(false)
const saving = ref(false)
const successMessage = ref('')
const errorMessage = ref('')
const selectedProductFilter = ref('')

const form = reactive({
  product_id: '',
  type: 'in',
  quantity: 1,
  description: '',
})

const getCsrfToken = () => {
  return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
}

const showSuccess = (message) => {
  successMessage.value = message
  errorMessage.value = ''
}

const showError = (message) => {
  errorMessage.value = message
  successMessage.value = ''
}

const resetForm = () => {
  form.product_id = ''
  form.type = 'in'
  form.quantity = 1
  form.description = ''
}

const fetchProducts = async () => {
  try {
    const response = await fetch('/products', {
      headers: {
        Accept: 'application/json',
      },
    })

    const data = await response.json()

    if (!response.ok) {
      showError(data.message || 'خطا در دریافت کالاها.')
      return
    }

    products.value = data.products
  } catch (error) {
    console.error(error)
    showError('ارتباط با سرور برقرار نشد.')
  }
}

const fetchMovements = async () => {
  loading.value = true

  let url = '/stock-movements'

  if (selectedProductFilter.value) {
    url += `?product_id=${selectedProductFilter.value}`
  }

  try {
    const response = await fetch(url, {
      headers: {
        Accept: 'application/json',
      },
    })

    const data = await response.json()

    if (!response.ok) {
      showError(data.message || 'خطا در دریافت رویدادهای انبار.')
      return
    }

    movements.value = data.movements
  } catch (error) {
    console.error(error)
    showError('ارتباط با سرور برقرار نشد.')
  } finally {
    loading.value = false
  }
}

const handleSubmit = async () => {
  saving.value = true
  successMessage.value = ''
  errorMessage.value = ''

  try {
    const response = await fetch('/stock-movements', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json',
        'X-CSRF-TOKEN': getCsrfToken(),
      },
      body: JSON.stringify({
        product_id: form.product_id,
        type: form.type,
        quantity: Number(form.quantity),
        description: form.description,
      }),
    })

    const data = await response.json()

    if (!response.ok) {
      if (data.errors) {
        const firstError = Object.values(data.errors)[0][0]
        showError(firstError)
      } else {
        showError(data.message || 'خطایی رخ داد.')
      }

      return
    }

    showSuccess(data.message)
    resetForm()
    await fetchProducts()
    await fetchMovements()
  } catch (error) {
    console.error(error)
    showError('ارتباط با سرور برقرار نشد.')
  } finally {
    saving.value = false
  }
}

const movementTypeText = (type) => {
  return type === 'in' ? 'ورود به انبار' : 'خروج از انبار'
}

const movementTypeClass = (type) => {
  return type === 'in'
    ? 'bg-green-100 text-green-700'
    : 'bg-red-100 text-red-700'
}

const formatDate = (date) => {
  return new Date(date).toLocaleString('fa-IR')
}

onMounted(async () => {
  await fetchProducts()
  await fetchMovements()
})
</script>

<template>
  <section dir="rtl" class="bg-[#F8FAFC] px-6 py-12">
    <div class="mx-auto max-w-6xl">
      <div class="mb-8">
        <p class="mb-2 text-sm font-bold text-[#219EBC]">رویدادهای انبار</p>
        <h1 class="text-3xl font-bold text-[#023047]">ورود، خروج و کاردکس کالا</h1>
        <p class="mt-3 text-sm leading-7 text-slate-500">
          در این صفحه می‌توانید ورود و خروج کالاها را ثبت کنید و تاریخچه تغییرات موجودی هر کالا را مشاهده کنید.
        </p>
      </div>

      <div v-if="successMessage" class="mb-5 rounded-2xl bg-green-100 px-4 py-3 text-sm font-medium text-green-700">
        {{ successMessage }}
      </div>

      <div v-if="errorMessage" class="mb-5 rounded-2xl bg-red-100 px-4 py-3 text-sm font-medium text-red-700">
        {{ errorMessage }}
      </div>

      <!-- Movement Form -->
      <div class="mb-8 rounded-3xl bg-white p-6 shadow-lg">
        <h2 class="mb-5 text-xl font-bold text-[#023047]">
          ثبت رویداد جدید
        </h2>

        <form class="grid gap-4 md:grid-cols-4" @submit.prevent="handleSubmit">
          <div>
            <label class="mb-2 block text-sm font-medium text-[#023047]">کالا</label>
            <select
              v-model="form.product_id"
              class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none focus:border-[#219EBC] focus:ring-4 focus:ring-[#8ECAE6]/40"
            >
              <option value="">انتخاب کالا</option>
              <option v-for="product in products" :key="product.id" :value="product.id">
                {{ product.name }} - {{ product.code }} / موجودی: {{ product.quantity }}
              </option>
            </select>
          </div>

          <div>
            <label class="mb-2 block text-sm font-medium text-[#023047]">نوع رویداد</label>
            <select
              v-model="form.type"
              class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none focus:border-[#219EBC] focus:ring-4 focus:ring-[#8ECAE6]/40"
            >
              <option value="in">ورود به انبار</option>
              <option value="out">خروج از انبار</option>
            </select>
          </div>

          <div>
            <label class="mb-2 block text-sm font-medium text-[#023047]">تعداد</label>
            <input
              v-model="form.quantity"
              type="number"
              min="1"
              class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm outline-none focus:border-[#219EBC] focus:ring-4 focus:ring-[#8ECAE6]/40"
            />
          </div>

          <div>
            <label class="mb-2 block text-sm font-medium text-[#023047]">توضیح</label>
            <input
              v-model="form.description"
              type="text"
              placeholder="مثلاً خرید جدید یا فروش"
              class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm outline-none focus:border-[#219EBC] focus:ring-4 focus:ring-[#8ECAE6]/40"
            />
          </div>

          <div class="md:col-span-4">
            <button
              type="submit"
              :disabled="saving"
              class="rounded-2xl bg-[#023047] px-6 py-3 text-sm font-bold text-white transition hover:bg-[#03415f] disabled:cursor-not-allowed disabled:opacity-60"
            >
              {{ saving ? 'در حال ثبت...' : 'ثبت رویداد' }}
            </button>
          </div>
        </form>
      </div>

      <!-- Kardex Filter -->
      <div class="mb-6 rounded-3xl bg-white p-6 shadow-lg">
        <div class="grid gap-4 md:grid-cols-3 md:items-end">
          <div>
            <label class="mb-2 block text-sm font-medium text-[#023047]">
              فیلتر کاردکس بر اساس کالا
            </label>
            <select
              v-model="selectedProductFilter"
              class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none focus:border-[#219EBC] focus:ring-4 focus:ring-[#8ECAE6]/40"
            >
              <option value="">همه رویدادها</option>
              <option v-for="product in products" :key="product.id" :value="product.id">
                {{ product.name }} - {{ product.code }}
              </option>
            </select>
          </div>

          <button
            class="rounded-2xl bg-[#219EBC] px-6 py-3 text-sm font-bold text-white"
            @click="fetchMovements"
          >
            نمایش کاردکس
          </button>
        </div>
      </div>

      <!-- Movements Table -->
      <div class="overflow-hidden rounded-3xl bg-white shadow-lg">
        <div v-if="loading" class="p-8 text-center text-sm text-slate-500">
          در حال دریافت رویدادهای انبار...
        </div>

        <div v-else-if="movements.length === 0" class="p-8 text-center">
          <h3 class="mb-2 text-lg font-bold text-[#023047]">هنوز رویدادی ثبت نشده است</h3>
          <p class="text-sm text-slate-500">
            از فرم بالا اولین ورود یا خروج کالا را ثبت کنید.
          </p>
        </div>

        <div v-else class="overflow-x-auto">
          <table class="w-full min-w-[1000px] text-right">
            <thead class="bg-slate-50 text-sm text-slate-500">
              <tr>
                <th class="px-6 py-4">کالا</th>
                <th class="px-6 py-4">نوع رویداد</th>
                <th class="px-6 py-4">تعداد</th>
                <th class="px-6 py-4">موجودی قبل</th>
                <th class="px-6 py-4">موجودی بعد</th>
                <th class="px-6 py-4">توضیح</th>
                <th class="px-6 py-4">تاریخ</th>
              </tr>
            </thead>

            <tbody class="divide-y divide-slate-100 text-sm">
              <tr v-for="movement in movements" :key="movement.id" class="hover:bg-slate-50">
                <td class="px-6 py-4">
                  <div class="font-bold text-[#023047]">
                    {{ movement.product?.name || '-' }}
                  </div>
                  <div class="text-xs text-slate-400">
                    {{ movement.product?.code || '-' }}
                  </div>
                </td>

                <td class="px-6 py-4">
                  <span
                    class="rounded-full px-3 py-1 text-xs font-bold"
                    :class="movementTypeClass(movement.type)"
                  >
                    {{ movementTypeText(movement.type) }}
                  </span>
                </td>

                <td class="px-6 py-4 text-slate-700">{{ movement.quantity }}</td>
                <td class="px-6 py-4 text-slate-700">{{ movement.stock_before }}</td>
                <td class="px-6 py-4 font-bold text-[#023047]">{{ movement.stock_after }}</td>
                <td class="px-6 py-4 text-slate-600">{{ movement.description || '-' }}</td>
                <td class="px-6 py-4 text-slate-500">{{ formatDate(movement.created_at) }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </section>
</template>

