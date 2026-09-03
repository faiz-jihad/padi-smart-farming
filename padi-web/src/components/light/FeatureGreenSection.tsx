import React from 'react';
import { Sparkles, ArrowRight, ShieldCheck, Clock, Check, AlertTriangle } from 'lucide-react';

export const FeatureGreenSection: React.FC = () => {
  return (
    <section id="fitur" className="relative w-full py-16 px-6 sm:px-12 md:px-20 bg-white">
      <div className="max-w-6xl mx-auto space-y-10">
        {/* Top Editorial Headline */}
        <div className="text-left max-w-2xl space-y-3">
          <div className="text-xs sm:text-sm font-bold uppercase tracking-wider text-[#16A34A]">
            Kecerdasan Buatan Khusus Sawah
          </div>

          <h2 className="text-3xl sm:text-5xl font-black text-gray-950 tracking-tight leading-tight">
            Deteksi Penyakit Padi Tanpa Ribet, <br className="hidden sm:inline" />
            Disesuaikan untuk Lahan Anda.
          </h2>

          <p className="text-base sm:text-lg text-gray-600 leading-relaxed">
            Cukup ambil foto daun di pematang. P.A.D.I. membaca gejalanya dan menyusun rekomendasi mitigasi langkah demi langkah tanpa perlu menebak.
          </p>
        </div>

        {/* Grid Showcase: Large Green Card + Portrait Photo Card */}
        <div className="grid grid-cols-1 lg:grid-cols-12 gap-8 items-stretch">
          {/* Main Large Green Card */}
          <div className="lg:col-span-8 rounded-[36px] bg-[#16A34A] p-6 sm:p-10 text-white flex flex-col justify-between shadow-xl">
            <div className="space-y-6">
              <div className="max-w-md text-left space-y-2">
                <h3 className="text-xl sm:text-3xl font-black tracking-tight leading-snug">
                  Dapatkan Wawasan Instan Mengenai Penyakit, Cuaca, dan Tindakan.
                </h3>
                <p className="text-xs sm:text-sm text-emerald-100 leading-relaxed">
                  Laporan analisis komprehensif langsung muncul di layar ponsel dalam hitungan detik setelah foto diambil.
                </p>
              </div>

              {/* White Widget Cards Inside the Green Container */}
              <div className="grid grid-cols-1 sm:grid-cols-3 gap-4 text-gray-900 text-left pt-2">
                {/* Inner Card 1: Sample Scan */}
                <div className="bg-white p-4 rounded-2xl shadow-md space-y-3">
                  <div className="relative h-24 rounded-xl overflow-hidden bg-gray-100">
                    <img
                      src="/images/onboarding_1.jpeg"
                      alt="Pemeriksaan Daun"
                      className="w-full h-full object-cover"
                    />
                    <div className="absolute top-2 right-2 bg-emerald-600 text-white text-[9px] font-bold px-1.5 py-0.5 rounded-full">
                      Foto Daun
                    </div>
                  </div>
                  <div>
                    <div className="text-[10px] text-gray-400 font-bold uppercase">Spesimen Lahan</div>
                    <div className="text-xs font-bold text-gray-900">Hawar Daun Bakteri</div>
                  </div>
                </div>

                {/* Inner Card 2: Analysis Report */}
                <div className="bg-white p-4 rounded-2xl shadow-md space-y-2">
                  <div className="text-[10px] text-gray-400 font-bold uppercase">Ringkasan Diagnosis</div>
                  <div className="space-y-1.5 text-[11px]">
                    <div className="flex items-center justify-between pb-1 border-b border-gray-100">
                      <span className="text-gray-500">Keyakinan AI:</span>
                      <strong className="text-[#16A34A] font-bold">94.7%</strong>
                    </div>
                    <div className="flex items-center justify-between pb-1 border-b border-gray-100">
                      <span className="text-gray-500">Tingkat Risiko:</span>
                      <strong className="text-amber-500 font-bold">Sedang</strong>
                    </div>
                    <div className="flex items-center justify-between">
                      <span className="text-gray-500">Penyebaran:</span>
                      <strong className="text-gray-800">Lokal (5m)</strong>
                    </div>
                  </div>
                </div>

                {/* Inner Card 3: Action Window */}
                <div className="bg-white p-4 rounded-2xl shadow-md space-y-2">
                  <div className="text-[10px] text-gray-400 font-bold uppercase">Waktu Pemupukan</div>
                  <div className="bg-emerald-50 p-2.5 rounded-xl border border-emerald-100">
                    <span className="text-[10px] text-[#16A34A] font-bold block">Disarankan Hari Ini</span>
                    <div className="text-xs font-black text-gray-900 mt-0.5">09.00 – 11.00 WIB</div>
                  </div>
                  <p className="text-[10px] text-gray-500 leading-tight">
                    Sebelum panas terik dan hujan sore pukul 14.00 WIB.
                  </p>
                </div>
              </div>
            </div>

            <div className="pt-6 text-[11px] text-emerald-100/90 text-left">
              Data terintegrasi langsung dengan kalender HST dan stasiun cuaca terdekat.
            </div>
          </div>

          {/* Beside: Portrait Photo Card with Tag Pill */}
          <div className="lg:col-span-4 relative rounded-[36px] overflow-hidden shadow-xl min-h-[380px] bg-gray-100 border border-gray-200">
            <img
              src="/images/onboarding_2.jpeg"
              alt="Petani menggunakan smartphone di sawah"
              className="w-full h-full object-cover"
            />
            <div className="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent" />

            <div className="absolute bottom-6 left-6 right-6 bg-white/95 backdrop-blur-md p-4 rounded-2xl shadow-lg text-left space-y-1 border border-gray-100">
              <span className="text-[10px] font-bold uppercase tracking-wider text-[#16A34A] block">
                Rekomendasi Terarah
              </span>
              <p className="text-xs font-semibold text-gray-800 leading-snug">
                Panduan penanganan disesuaikan dengan varietas padi dan jenis tanah setempat.
              </p>
            </div>
          </div>
        </div>

        {/* Carousel Pagination Dots like Reference */}
        <div className="flex items-center justify-center gap-2 pt-2">
          <div className="w-6 h-2 rounded-full bg-[#16A34A]" />
          <div className="w-2 h-2 rounded-full bg-gray-300" />
          <div className="w-2 h-2 rounded-full bg-gray-300" />
        </div>
      </div>
    </section>
  );
};
