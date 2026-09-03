import React from 'react';
import { Sun, CloudRain, Wind, Droplets, CheckCircle, AlertTriangle } from 'lucide-react';

export const WeatherCard: React.FC = () => {
  return (
    <div className="bg-[#12241C] p-4 rounded-2xl border border-[#41A55B]/40 shadow-xl text-left space-y-4 max-w-sm mx-auto">
      {/* Main Temperature & Location */}
      <div className="flex items-start justify-between">
        <div>
          <span className="text-[10px] text-[#41A55B] font-bold uppercase tracking-wider">Telemetri Lapangan</span>
          <div className="flex items-baseline gap-1.5 mt-0.5">
            <span className="text-3xl font-extrabold text-white tracking-tight">28°C</span>
            <span className="text-xs text-white/60 font-medium">Cerah Berawan</span>
          </div>
          <div className="text-[10px] text-white/50">Stasiun BMKG & Sensor IoT Lahan</div>
        </div>
        <div className="w-12 h-12 rounded-2xl bg-[#075B3B] border border-[#41A55B]/30 flex items-center justify-center text-[#F5C842]">
          <Sun className="w-7 h-7" />
        </div>
      </div>

      {/* 3 Metric Grid */}
      <div className="grid grid-cols-3 gap-2 text-center text-xs">
        <div className="bg-[#0A1A12] p-2 rounded-xl border border-white/5">
          <Droplets className="w-3.5 h-3.5 text-sky-400 mx-auto mb-1" />
          <span className="text-white font-bold block text-xs">65%</span>
          <span className="text-[9px] text-white/50">Kelembapan</span>
        </div>
        <div className="bg-[#0A1A12] p-2 rounded-xl border border-white/5">
          <Wind className="w-3.5 h-3.5 text-emerald-400 mx-auto mb-1" />
          <span className="text-white font-bold block text-xs">12 km/j</span>
          <span className="text-[9px] text-white/50">Kecepatan Angin</span>
        </div>
        <div className="bg-[#0A1A12] p-2 rounded-xl border border-white/5">
          <CloudRain className="w-3.5 h-3.5 text-amber-400 mx-auto mb-1" />
          <span className="text-white font-bold block text-xs">Mulai 14:00</span>
          <span className="text-[9px] text-white/50">Peluang Hujan</span>
        </div>
      </div>

      {/* Actionable Agricultural Window */}
      <div className="bg-gradient-to-r from-[#075B3B] to-[#063D2B] p-3 rounded-xl border border-[#41A55B]/40 space-y-1.5">
        <div className="flex items-center justify-between text-xs font-bold text-white">
          <div className="flex items-center gap-1.5">
            <CheckCircle className="w-3.5 h-3.5 text-[#F5C842]" />
            <span>Waktu Aman Pemupukan / Semprot</span>
          </div>
          <span className="px-2 py-0.5 rounded-full bg-[#F5C842] text-[#063D2B] font-extrabold text-[10px]">
            Optimal
          </span>
        </div>
        <div className="text-base font-extrabold text-[#F5C842] tracking-wide">
          09:00 — 11:00 WIB
        </div>
        <p className="text-[10px] text-white/80 leading-snug">
          Pupuk terserap stomata daun sebelum suhu menyengat. Hindari pemupukan lewat pukul 13:00 karena resiko terbilas hujan lebat.
        </p>
      </div>
    </div>
  );
};
