import React from 'react';
import { AlertTriangle, Clock, CloudRain, CheckCircle2, ChevronRight, Droplets } from 'lucide-react';

export const FarmPriority: React.FC = () => {
  return (
    <div className="p-4 space-y-4 text-left">
      {/* User Greeting & Header */}
      <div className="flex items-center justify-between pt-1">
        <div>
          <div className="text-[11px] text-white/50 font-medium">Lahan Indramayu Timur</div>
          <h2 className="text-base font-bold text-white tracking-tight">Halo, Pak Budi</h2>
        </div>
        <div className="w-8 h-8 rounded-full bg-[#0C7047] border border-[#41A55B]/40 flex items-center justify-center text-xs font-bold text-white shadow-sm">
          PB
        </div>
      </div>

      {/* Hero Alert Card */}
      <div className="bg-gradient-to-br from-[#075B3B] to-[#063D2B] p-3.5 rounded-2xl border border-[#41A55B]/30 shadow-lg relative overflow-hidden">
        <div className="absolute -right-6 -bottom-6 w-24 h-24 bg-[#41A55B]/10 rounded-full blur-xl pointer-events-none" />
        <div className="flex items-center gap-2 mb-1.5">
          <span className="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#F5C842] text-[#063D2B]">
            Prioritas Hari Ini
          </span>
          <span className="text-[10px] text-white/60">3 Tindakan Lapangan</span>
        </div>
        <p className="text-xs text-white/90 leading-relaxed font-medium">
          Kondisi mikroklimat lembap mempercepat spora. Tuntaskan rekomendasi sebelum pukul 14:00.
        </p>
      </div>

      {/* Priority Action List */}
      <div className="space-y-2.5">
        <div className="text-[11px] font-bold text-white/60 uppercase tracking-wider px-0.5">
          Yang Perlu Dilakukan
        </div>

        {/* 1. Blok A */}
        <div className="bg-[#12241C] p-3 rounded-xl border border-red-500/20 hover:border-red-500/40 transition-all flex items-start gap-3">
          <div className="w-7 h-7 rounded-lg bg-red-500/15 text-red-400 flex items-center justify-center shrink-0 mt-0.5">
            <AlertTriangle className="w-4 h-4" />
          </div>
          <div className="flex-1 min-w-0">
            <div className="flex items-center justify-between">
              <span className="text-xs font-bold text-white">1. Periksa Blok A</span>
              <span className="text-[10px] px-1.5 py-0.5 rounded bg-red-500/10 text-red-300 font-semibold">Penting</span>
            </div>
            <p className="text-[11px] text-white/70 mt-0.5 leading-snug">
              Risiko penyakit meningkat. Indikasi bercak daun mulai terdeteksi di sisi timur.
            </p>
          </div>
        </div>

        {/* 2. Pemupukan Aman */}
        <div className="bg-[#12241C] p-3 rounded-xl border border-[#41A55B]/30 hover:border-[#41A55B]/50 transition-all flex items-start gap-3">
          <div className="w-7 h-7 rounded-lg bg-[#41A55B]/15 text-[#41A55B] flex items-center justify-center shrink-0 mt-0.5">
            <Clock className="w-4 h-4" />
          </div>
          <div className="flex-1 min-w-0">
            <div className="flex items-center justify-between">
              <span className="text-xs font-bold text-white">2. Jendela Pemupukan</span>
              <span className="text-[10px] text-[#F5C842] font-semibold">09:00 – 11:00</span>
            </div>
            <p className="text-[11px] text-white/70 mt-0.5 leading-snug">
              Angin tenang & daun kering. Waktu optimal penyerapan nutrisi akar.
            </p>
          </div>
        </div>

        {/* 3. Hujan */}
        <div className="bg-[#12241C] p-3 rounded-xl border border-sky-500/20 hover:border-sky-500/40 transition-all flex items-start gap-3">
          <div className="w-7 h-7 rounded-lg bg-sky-500/15 text-sky-400 flex items-center justify-center shrink-0 mt-0.5">
            <CloudRain className="w-4 h-4" />
          </div>
          <div className="flex-1 min-w-0">
            <div className="flex items-center justify-between">
              <span className="text-xs font-bold text-white">3. Prakiraan Hujan</span>
              <span className="text-[10px] text-sky-300 font-semibold">Mulai 15:00</span>
            </div>
            <p className="text-[11px] text-white/70 mt-0.5 leading-snug">
              Hujan sedang lebat disertai angin. Pastikan saluran drainase petak terbuka.
            </p>
          </div>
        </div>
      </div>

      {/* Field Telemetry Mini-pill */}
      <div className="bg-[#0A1A12] p-2.5 rounded-xl border border-white/5 flex items-center justify-around text-center text-white/70 text-[10px]">
        <div>
          <span className="text-white font-bold block text-xs">28°C</span>
          Suhu Sawah
        </div>
        <div className="w-px h-6 bg-white/10" />
        <div>
          <span className="text-white font-bold block text-xs">78%</span>
          Kelembapan
        </div>
        <div className="w-px h-6 bg-white/10" />
        <div>
          <span className="text-[#F5C842] font-bold block text-xs">45 HST</span>
          Fase Anakan
        </div>
      </div>
    </div>
  );
};
