import React from 'react';
import { ArrowRight, Search, CheckCircle2, CloudSun, AlertCircle, Sprout } from 'lucide-react';

export const HeroLightSection: React.FC = () => {
  return (
    <section id="beranda" className="relative w-full pt-8 sm:pt-12 pb-16 px-6 sm:px-12 md:px-20 overflow-hidden">
      <div className="max-w-6xl mx-auto grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-8 items-center">
        {/* Left Column: Editorial Hero Text & Action Input */}
        <div className="lg:col-span-6 text-left space-y-6">
          <div className="text-xs sm:text-sm font-bold uppercase tracking-wider text-[#16A34A]">
            Asisten Pintar Petani Padi
          </div>

          <h1 className="text-4xl sm:text-6xl md:text-7xl font-black tracking-tight text-gray-950 leading-[1.08]">
            Cara Sederhana <br />
            Memulai{' '}
            <span className="text-[#16A34A] inline-flex items-center gap-2">
              Panen Sehat.
              <span className="inline-flex items-center -space-x-2 align-middle ml-1">
                <img
                  src="/images/role_farmer.png"
                  alt="Petani 1"
                  className="w-8 h-8 rounded-full border-2 border-white object-cover bg-emerald-100"
                />
                <div className="w-8 h-8 rounded-full border-2 border-white bg-[#DCFCE7] flex items-center justify-center text-[10px] font-bold text-[#16A34A]">
                  +4k
                </div>
              </span>
            </span>
          </h1>

          <p className="text-base sm:text-lg text-gray-600 max-w-lg leading-relaxed">
            Pantau kondisi helai daun, hitung jendela cuaca pemupukan secara presisi, dan konsultasikan masalah tanaman langsung dengan penyuluh lapangan resmi.
          </p>

          {/* Interactive Search / Prompt Input Pill like Reference */}
          <div className="max-w-md bg-white p-2 rounded-full shadow-[0_8px_30px_rgba(0,0,0,0.06)] border border-gray-200 flex items-center justify-between gap-3">
            <div className="flex items-center gap-3 pl-4 flex-1">
              <Search className="w-4 h-4 text-gray-400 shrink-0" />
              <input
                type="text"
                placeholder="Tanyakan kondisi sawah Anda hari ini..."
                className="w-full text-xs sm:text-sm text-gray-800 placeholder-gray-400 bg-transparent focus:outline-none"
              />
            </div>
            <a
              href="/downloads/padi-latest.apk"
              download="PADI-latest.apk"
              className="w-10 h-10 rounded-full bg-[#16A34A] hover:bg-[#15803D] active:scale-90 text-white flex items-center justify-center shrink-0 shadow-sm transition-all"
              title="Unduh Aplikasi P.A.D.I."
            >
              <ArrowRight className="w-4 h-4" />
            </a>
          </div>

          {/* Category Badges below Search Pill */}
          <div className="flex flex-wrap items-center gap-2 sm:gap-3 text-xs font-semibold text-gray-600 pt-2">
            <span className="px-3 py-1.5 rounded-full bg-white border border-gray-200 shadow-xs">
              🌾 Hawar Daun
            </span>
            <span className="px-3 py-1.5 rounded-full bg-white border border-gray-200 shadow-xs">
              🌧️ Jendela Pupuk
            </span>
            <span className="px-3 py-1.5 rounded-full bg-white border border-gray-200 shadow-xs">
              👨‍🌾 Validasi PPL
            </span>
            <span className="px-3 py-1.5 rounded-full bg-emerald-50 text-[#16A34A] border border-emerald-200 font-bold">
              +32 Kelompok Tani
            </span>
          </div>
        </div>

        {/* Right Column: Large Rounded Portrait Card with Floating UI Widgets */}
        <div className="lg:col-span-6 flex justify-center">
          <div className="relative w-full max-w-[420px] h-[520px] sm:h-[580px] rounded-[38px] overflow-hidden shadow-[0_20px_60px_rgba(0,0,0,0.1)] border-4 border-white bg-emerald-950">
            {/* Background Authentic Photo */}
            <img
              src="/images/hero_paddy.jpg"
              alt="Hamparan sawah padi Indonesia"
              className="w-full h-full object-cover filter brightness-[0.92] contrast-105"
            />
            <div className="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-black/20" />

            {/* Floating Widget 1: Today Report (Top Left) */}
            <div className="absolute top-6 left-6 right-6 sm:right-auto sm:w-64 bg-white/95 backdrop-blur-md p-4 rounded-2xl shadow-xl border border-gray-100 text-left space-y-2.5">
              <div className="flex items-center justify-between text-xs">
                <span className="font-bold text-gray-900">Kondisi Lahan Hari Ini</span>
                <span className="text-[10px] font-bold text-[#16A34A] bg-emerald-50 px-2 py-0.5 rounded-full">
                  Blok A
                </span>
              </div>

              <div className="space-y-1.5 text-xs text-gray-600">
                <div className="flex items-center justify-between">
                  <span className="flex items-center gap-1.5 text-gray-500">
                    <Sprout className="w-3.5 h-3.5 text-[#16A34A]" />
                    <span>Fase Tanam:</span>
                  </span>
                  <strong className="text-gray-900">45 HST (Anakan)</strong>
                </div>

                <div className="flex items-center justify-between">
                  <span className="flex items-center gap-1.5 text-gray-500">
                    <CloudSun className="w-3.5 h-3.5 text-amber-500" />
                    <span>Prakiraan Cuaca:</span>
                  </span>
                  <strong className="text-gray-900">28°C Cerah</strong>
                </div>

                <div className="flex items-center justify-between">
                  <span className="flex items-center gap-1.5 text-gray-500">
                    <CheckCircle2 className="w-3.5 h-3.5 text-emerald-500" />
                    <span>Status Irigasi:</span>
                  </span>
                  <strong className="text-emerald-600">Optimal</strong>
                </div>
              </div>
            </div>

            {/* Floating Widget 2: Quick Detection Result Pill (Middle) */}
            <div className="absolute top-52 sm:top-56 right-4 bg-white/95 backdrop-blur-md p-3 rounded-2xl shadow-xl border border-gray-100 flex items-center gap-3 text-left">
              <img
                src="/images/onboarding_1.jpeg"
                alt="Daun Padi"
                className="w-10 h-10 rounded-xl object-cover"
              />
              <div>
                <div className="text-[10px] text-gray-400 font-semibold uppercase">Hasil Scan Daun</div>
                <div className="text-xs font-bold text-gray-900">Hawar Daun Bakteri</div>
                <div className="text-[10px] text-[#16A34A] font-bold">Akurasi 94.7%</div>
              </div>
            </div>

            {/* Floating Widget 3: Penyuluh Card (Bottom Center) */}
            <div className="absolute bottom-6 inset-x-6 bg-white/95 backdrop-blur-md p-3.5 rounded-2xl shadow-2xl border border-gray-100 flex items-center justify-between text-left">
              <div className="flex items-center gap-3">
                <div className="w-10 h-10 rounded-full bg-emerald-100 flex items-center justify-center font-bold text-sm text-[#16A34A]">
                  AS
                </div>
                <div>
                  <h4 className="text-xs font-bold text-gray-900 leading-tight">Ahmad Subekti, S.P.</h4>
                  <p className="text-[10px] text-gray-500">Penyuluh BPP Sindang</p>
                </div>
              </div>

              <a
                href="#fitur"
                className="w-8 h-8 rounded-full bg-[#16A34A] hover:bg-[#15803D] text-white flex items-center justify-center shrink-0 shadow-xs"
              >
                <ArrowRight className="w-3.5 h-3.5" />
              </a>
            </div>
          </div>
        </div>
      </div>
    </section>
  );
};
