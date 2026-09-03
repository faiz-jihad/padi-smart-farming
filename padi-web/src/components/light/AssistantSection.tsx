import React, { useState } from 'react';
import { BarChart3, Plus, Minus, ShieldCheck, Users, Calendar, MapPin } from 'lucide-react';

export const AssistantSection: React.FC = () => {
  const [openIndex, setOpenIndex] = useState<number | null>(0);

  const accordions = [
    {
      title: 'Panduan Tindakan Lapangan Otomatis',
      content:
        'Setelah daun difoto, P.A.D.I. langsung memberikan 3 langkah konkret: pengeringan petak macak-macak, pengurangan dosis nitrogen, dan pemantauan radius 5 meter.',
    },
    {
      title: 'Radar Peringatan Dini Hamparan 8 KM',
      content:
        'Sistem gotong-royong memetakan laporan serangan hama dan patogen jamur Blast dari lahan sekitar sehingga Anda bisa bersiap sebelum penyakit menular.',
    },
    {
      title: 'Dukungan Resmi Penyuluh Lapangan (PPL)',
      content:
        'Jika gejala tidak membaik dalam 3 hari, berkas laporan lengkap dengan foto dan titik koordinat dapat dikirimkan langsung ke penyuluh BPP setempat.',
    },
  ];

  return (
    <section id="wawasan" className="relative w-full py-20 px-6 sm:px-12 md:px-20 bg-[#F8FAF8]">
      <div className="max-w-6xl mx-auto grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
        {/* Left Column: Photo Card with Floating Bar Chart Widget like Reference */}
        <div className="lg:col-span-6 flex justify-center">
          <div className="relative w-full max-w-[400px] h-[500px] rounded-[36px] overflow-hidden shadow-xl border-4 border-white bg-gray-100">
            {/* Background photo */}
            <img
              src="/images/onboarding_3.jpeg"
              alt="Pengamatan sawah"
              className="w-full h-full object-cover"
            />
            <div className="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent" />

            {/* Floating Bar Chart Widget at the bottom */}
            <div className="absolute bottom-6 left-6 right-6 bg-white/95 backdrop-blur-md p-5 rounded-3xl shadow-2xl border border-gray-100 text-left space-y-3">
              <div className="flex items-center justify-between">
                <span className="text-xs font-bold text-gray-900">Grafik Kelembapan & Risiko</span>
                <span className="text-[10px] text-[#16A34A] font-bold bg-emerald-50 px-2 py-0.5 rounded-full">
                  Mingguan
                </span>
              </div>

              {/* Bar chart visualization */}
              <div className="flex items-end justify-between h-20 pt-2 px-1 border-b border-gray-100">
                {[
                  { day: 'S', h: 45, color: '#16A34A' },
                  { day: 'M', h: 65, color: '#16A34A' },
                  { day: 'T', h: 35, color: '#C9A96E' },
                  { day: 'W', h: 80, color: '#16A34A' },
                  { day: 'T', h: 50, color: '#16A34A' },
                  { day: 'F', h: 90, color: '#EF4444' },
                  { day: 'S', h: 60, color: '#16A34A' },
                ].map((bar, idx) => (
                  <div key={idx} className="flex flex-col items-center gap-1.5 flex-1">
                    <div
                      className="w-3.5 rounded-t-md transition-all duration-500"
                      style={{ height: `${bar.h}%`, backgroundColor: bar.color }}
                    />
                    <span className="text-[10px] text-gray-400 font-medium">{bar.day}</span>
                  </div>
                ))}
              </div>

              <div className="flex items-center justify-between text-[10px] text-gray-500">
                <span className="flex items-center gap-1">
                  <span className="w-2 h-2 rounded-full bg-[#16A34A]" /> Aman
                </span>
                <span className="flex items-center gap-1">
                  <span className="w-2 h-2 rounded-full bg-[#EF4444]" /> Waspada Hujan Sore
                </span>
              </div>
            </div>
          </div>
        </div>

        {/* Right Column: Editorial Text & Dark Accordion Pills */}
        <div className="lg:col-span-6 text-left space-y-6">
          <div className="space-y-2">
            <div className="text-xs sm:text-sm font-bold uppercase tracking-wider text-[#16A34A]">
              Asisten Lapangan Pintar
            </div>

            <h2 className="text-3xl sm:text-5xl font-black text-gray-950 tracking-tight leading-tight">
              Bekerja Lebih Cerdas dengan Pendampingan AI P.A.D.I.
            </h2>
          </div>

          {/* White Data-Driven Insight Widget */}
          <div className="bg-white p-4 rounded-2xl shadow-sm border border-gray-200/80 flex items-center justify-between">
            <div className="space-y-1">
              <span className="text-[10px] font-bold text-gray-400 uppercase">Jadwal Kalender Tanam</span>
              <div className="text-xs font-bold text-gray-900">45 HST &bull; Fase Anakan Maksimum</div>
            </div>
            <div className="text-right">
              <span className="text-[10px] font-bold text-[#16A34A] bg-emerald-50 px-2 py-1 rounded-full">
                Pupuk Susulan II (3 Hari Lagi)
              </span>
            </div>
          </div>

          {/* Dark Accordion Pills like Reference Image */}
          <div className="space-y-3">
            {accordions.map((item, idx) => {
              const isOpen = openIndex === idx;

              return (
                <div
                  key={idx}
                  className="rounded-2xl bg-[#111827] text-white p-4 sm:p-5 transition-all shadow-md cursor-pointer"
                  onClick={() => setOpenIndex(isOpen ? null : idx)}
                >
                  <div className="flex items-center justify-between">
                    <span className="text-xs sm:text-sm font-bold text-white flex items-center gap-2">
                      <span className="text-[#16A34A]">✦</span>
                      <span>{item.title}</span>
                    </span>
                    <span className="w-6 h-6 rounded-full bg-white/10 flex items-center justify-center text-white shrink-0">
                      {isOpen ? <Minus className="w-3.5 h-3.5" /> : <Plus className="w-3.5 h-3.5" />}
                    </span>
                  </div>

                  {isOpen && (
                    <p className="text-xs text-gray-300 leading-relaxed pt-3 border-t border-white/10 mt-3">
                      {item.content}
                    </p>
                  )}
                </div>
              );
            })}
          </div>

          <p className="text-xs sm:text-sm text-gray-500 leading-relaxed pt-1">
            Mencapai target panen lebih mudah dengan teknologi yang menyederhanakan pemantauan dan memangkas risiko kegagalan.
          </p>
        </div>
      </div>
    </section>
  );
};
