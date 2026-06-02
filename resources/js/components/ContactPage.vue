<template>
  <section dir="rtl" class="bg-[#F8FAFC] px-6 py-16">
    <div class="mx-auto grid max-w-6xl gap-8 lg:grid-cols-2">
      <div class="rounded-3xl bg-[#023047] p-8 text-white">
        <p class="mb-3 text-sm font-bold text-[#8ECAE6]">تماس با ما</p>

        <h1 class="mb-5 text-3xl font-bold">
          با تیم انباریار در ارتباط باشید
        </h1>

        <p class="leading-8 text-slate-200">
          اگر درباره نرم‌افزار، امکانات یا نحوه استفاده از انباریار سوالی دارید، می‌توانید از طریق فرم روبه‌رو پیام خود را ارسال کنید.
        </p>

        <div class="mt-8 space-y-3 text-sm text-slate-200">
          <p>ایمیل: info@anbaryar.com</p>
          <p>تلفن: ۰۲۱-۱۲۳۴۵۶۷۸</p>
          <p>آدرس: تهران، ایران</p>
        </div>
      </div>

      <form class="rounded-3xl bg-white p-8 shadow-lg" @submit.prevent>
        <div class="mb-5">
          <label class="mb-2 block text-sm font-medium text-[#023047]">نام و نام خانوادگی</label>
          <input
            type="text"
            class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm outline-none focus:border-[#219EBC] focus:ring-4 focus:ring-[#8ECAE6]/40"
            placeholder="نام خود را وارد کنید"
          />
        </div>

        <div class="mb-5">
          <label class="mb-2 block text-sm font-medium text-[#023047]">ایمیل</label>
          <input
            type="email"
            class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm outline-none focus:border-[#219EBC] focus:ring-4 focus:ring-[#8ECAE6]/40"
            placeholder="example@email.com"
          />
        </div>

        <div class="mb-5">
          <label class="mb-2 block text-sm font-medium text-[#023047]">پیام</label>
          <textarea
            rows="5"
            class="w-full resize-none rounded-2xl border border-slate-200 px-4 py-3 text-sm outline-none focus:border-[#219EBC] focus:ring-4 focus:ring-[#8ECAE6]/40"
            placeholder="پیام خود را بنویسید"
          ></textarea>
        </div>

        <button
          type="submit"
          class="w-full rounded-2xl bg-[#023047] px-5 py-3 text-sm font-bold text-white transition hover:bg-[#03415f]"
        >
          ارسال پیام
        </button>
      </form>
    </div>
  </section>
</template>