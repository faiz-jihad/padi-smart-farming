import React from 'react';
import { AlertTriangle, MapPin, Radio } from 'lucide-react';

export const RadarScreen: React.FC = () => {
  return (
    <div className="p-4 space-y-3.5 text-left text-white select-none">
      <div className="pt-1">
        <span className="text-[10px] text-white/50 uppercase tracking-wider font-semibold">
          Kewaspadaan Hamparan
        </span>
        <h3 className="text-sm font-bold text-white">Radar Radius 8 KM</h3>
      </div>

      {/* Simplified Geo Radar Canvas */}
      <div className="relative w-full h-44 rounded-2xl bg-[#0F1E16] border border-white/10 flex items-center justify-center overflow-hidden">
        {/* Radar Rings */}
        <div className="absolute w-36 h-36 rounded-full border border-white/10 pointer-events-none" />
        <div className="absolute w-24 h-24 rounded-full border border-white/15 pointer-events-none" />
        <div className="absolute w-12 h-12 rounded-full border border-white/20 pointer-events-none" />

        {/* User Farm Location Pin */}
        <div className="relative z-10 flex flex-col items-center">
          <div className="w-4 h-4 rounded-full bg-[#2A7246] border border-white flex items-center justify-center">
            <div className="w-1.5 h-1.5 rounded-full bg-[#D4A017]" />
          </div>
          <span className="text-[9px] bg-black/80 px-1 rounded text-white mt-1">Lahan Anda</span>
        </div>

        {/* Nearby Report 1 (Blast 3.2km) */}
        <div className="absolute top-6 right-10 flex items-center gap-1">
          <div className="w-3 h-3 rounded-full bg-red-500 border border-white" />
          <span className="text-[8px] bg-black/80 px-1 rounded text-red-300">Blast (3 km)</span>
        </div>

        {/* Nearby Report 2 (HDB 2.1km) */}
        <div className="absolute bottom-8 left-8 flex items-center gap-1">
          <div className="w-3 h-3 rounded-full bg-amber-500 border border-white" />
          <span className="text-[8px] bg-black/80 px-1 rounded text-amber-300">HDB (2 km)</span>
        </div>
      </div>

      {/* Summary List */}
      <div className="space-y-1.5 text-xs">
        <div className="bg-[#14231A] p-2.5 rounded-xl border border-white/10 flex items-center justify-between">
          <div className="flex items-center gap-2">
            <span className="w-2 h-2 rounded-full bg-red-500" />
            <span className="text-white">Penyakit Blast</span>
          </div>
          <span className="text-white/60 font-medium">2 Laporan</span>
        </div>

        <div className="bg-[#14231A] p-2.5 rounded-xl border border-white/10 flex items-center justify-between">
          <div className="flex items-center gap-2">
            <span className="w-2 h-2 rounded-full bg-amber-500" />
            <span className="text-white">Hawar Daun Bakteri</span>
          </div>
          <span className="text-white/60 font-medium">1 Laporan</span>
        </div>
      </div>
    </div>
  );
};
