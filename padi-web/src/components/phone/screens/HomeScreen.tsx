import React from 'react';
import { Sprout, Sun, AlertCircle, Clock, ChevronRight, MapPin } from 'lucide-react';

export const HomeScreen: React.FC = () => {
  return (
    <div className="p-4 space-y-3.5 text-left select-none text-white">
      {/* Farm Location & Greeting */}
      <div className="flex items-center justify-between pt-1">
        <div>
          <div className="flex items-center gap-1 text-[10px] text-white/50">
            <MapPin className="w-3 h-3 text-[#2A7246]" />
            <span>Indramayu &bull; Blok A</span>
          </div>
          <h2 className="text-sm font-bold text-white tracking-tight">Halo, Pak Budi</h2>
        </div>
        <div className="w-7 h-7 rounded-full bg-[#183324] border border-white/10 flex items-center justify-center text-[10px] font-bold text-[#D4A017]">
          PB
        </div>
      </div>

      {/* Field Overview Summary */}
      <div className="bg-[#14231A] p-3 rounded-2xl border border-white/10 space-y-2">
        <div className="flex items-center justify-between text-[11px]">
          <span className="text-white/60">Kondisi Sawah</span>
          <span className="text-[#D4A017] font-semibold">45 HST &bull; Fase Anakan</span>
        </div>
        <div className="grid grid-cols-2 gap-2 text-center text-[10px] pt-1 border-t border-white/5">
          <div className="bg-black/20 p-1.5 rounded-lg">
            <span className="text-white font-bold block text-xs">28°C</span>
            <span className="text-white/40">Cerah Berawan</span>
          </div>
          <div className="bg-black/20 p-1.5 rounded-lg">
            <span className="text-emerald-400 font-bold block text-xs">Aman</span>
            <span className="text-white/40">Status Irigasi</span>
          </div>
        </div>
      </div>

      {/* Primary Priority Today */}
      <div className="space-y-2">
        <div className="text-[11px] font-bold text-white/60 uppercase tracking-wider">
          Prioritas Hari Ini
        </div>

        {/* 1. Inspect East Field */}
        <div className="bg-[#18261E] p-2.5 rounded-xl border border-amber-500/30 flex items-start gap-2.5">
          <div className="w-6 h-6 rounded-md bg-amber-500/20 text-amber-300 flex items-center justify-center shrink-0 mt-0.5">
            <AlertCircle className="w-3.5 h-3.5" />
          </div>
          <div className="flex-1 min-w-0">
            <div className="text-xs font-bold text-white">Periksa Petak Timur</div>
            <p className="text-[10.5px] text-white/60 leading-snug mt-0.5">
              Risiko kelembapan tinggi memicu gejala bercak.
            </p>
          </div>
        </div>

        {/* 2. Fertilization Window */}
        <div className="bg-[#18261E] p-2.5 rounded-xl border border-white/10 flex items-start gap-2.5">
          <div className="w-6 h-6 rounded-md bg-[#2A7246]/30 text-emerald-400 flex items-center justify-center shrink-0 mt-0.5">
            <Clock className="w-3.5 h-3.5" />
          </div>
          <div className="flex-1 min-w-0">
            <div className="flex items-center justify-between">
              <span className="text-xs font-bold text-white">Waktu Pemupukan</span>
              <span className="text-[10px] text-[#D4A017] font-semibold">09.00 – 11.00</span>
            </div>
            <p className="text-[10.5px] text-white/60 leading-snug mt-0.5">
              Kondisi angin tenang sebelum hujan sore.
            </p>
          </div>
        </div>
      </div>

      {/* Quick Launch Button */}
      <div className="pt-1">
        <div className="w-full py-2.5 rounded-xl bg-[#2A7246] text-white text-xs font-bold text-center shadow-sm">
          Buka Kamera Scan Daun
        </div>
      </div>
    </div>
  );
};
