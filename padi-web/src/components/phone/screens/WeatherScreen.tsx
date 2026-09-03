import React from 'react';
import { Sun, CloudRain, Clock, AlertTriangle } from 'lucide-react';

export const WeatherScreen: React.FC = () => {
  return (
    <div className="p-4 space-y-3.5 text-left text-white select-none">
      <div className="pt-1">
        <span className="text-[10px] text-white/50 uppercase tracking-wider font-semibold">
          Cuaca & Keputusan Lapangan
        </span>
        <h3 className="text-sm font-bold text-white">Prakiraan Hari Ini</h3>
      </div>

      {/* Main Temperature & Context */}
      <div className="bg-[#14231A] p-3.5 rounded-2xl border border-white/10 space-y-3">
        <div className="flex items-center justify-between">
          <div>
            <span className="text-2xl font-bold text-white tracking-tight">28°C</span>
            <span className="text-xs text-white/60 block mt-0.5">Cerah Berawan</span>
          </div>
          <Sun className="w-8 h-8 text-[#D4A017]" />
        </div>

        {/* Actionable Decision Highlight */}
        <div className="pt-2 border-t border-white/5 space-y-2 text-xs">
          <div className="bg-[#18261E] p-2.5 rounded-xl border border-emerald-500/30">
            <div className="flex items-center gap-1.5 text-emerald-400 font-bold text-[11px]">
              <Clock className="w-3.5 h-3.5" />
              <span>Pemupukan Disarankan</span>
            </div>
            <div className="text-sm font-bold text-white mt-1">09.00 – 11.00 WIB</div>
            <p className="text-[10.5px] text-white/60 mt-0.5">
              Angin tenang, daun sudah kering dari embun pagi.
            </p>
          </div>

          <div className="bg-[#18261E] p-2.5 rounded-xl border border-amber-500/20">
            <div className="flex items-center gap-1.5 text-amber-300 font-bold text-[11px]">
              <CloudRain className="w-3.5 h-3.5" />
              <span>Hujan Diperkirakan</span>
            </div>
            <div className="text-xs font-bold text-white mt-1">Setelah pukul 14.00 WIB</div>
            <p className="text-[10.5px] text-white/60 mt-0.5">
              Tunda pemupukan sore agar nutrisi tidak larut terbilas.
            </p>
          </div>
        </div>
      </div>
    </div>
  );
};
