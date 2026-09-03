import React from 'react';
import { Radio, AlertOctagon, MapPin, ShieldAlert, Users } from 'lucide-react';

export const DiseaseRadarMap: React.FC = () => {
  return (
    <div className="bg-[#12241C] p-4 rounded-3xl border border-[#41A55B]/40 shadow-2xl text-left max-w-md mx-auto space-y-3.5">
      {/* Radar Header */}
      <div className="flex items-center justify-between">
        <div className="flex items-center gap-2">
          <div className="w-8 h-8 rounded-xl bg-[#075B3B] text-[#F5C842] flex items-center justify-center">
            <Radio className="w-4 h-4 animate-pulse" />
          </div>
          <div>
            <span className="text-[10px] text-[#41A55B] font-bold uppercase tracking-wider">Radar Peringatan Dini</span>
            <h3 className="text-sm font-extrabold text-white">Hamparan Indramayu</h3>
          </div>
        </div>
        <span className="text-[11px] px-2 py-0.5 rounded-full bg-red-500/20 text-red-300 font-bold border border-red-500/30">
          Radius 8 KM
        </span>
      </div>

      {/* Stylized Geo Radar Canvas */}
      <div className="relative w-full h-56 bg-[#081710] rounded-2xl overflow-hidden border border-white/10 flex items-center justify-center">
        {/* Concentric Radar Rings */}
        <div className="absolute w-44 h-44 rounded-full border border-[#41A55B]/20 pointer-events-none" />
        <div className="absolute w-32 h-32 rounded-full border border-[#41A55B]/30 pointer-events-none" />
        <div className="absolute w-20 h-20 rounded-full border border-[#41A55B]/40 pointer-events-none" />

        {/* Sonar Pulse Wave Effect */}
        <div className="absolute w-20 h-20 rounded-full bg-[#41A55B]/15 animate-radar-pulse pointer-events-none" />

        {/* Radar Crosshairs */}
        <div className="absolute w-full h-px bg-[#41A55B]/15 pointer-events-none" />
        <div className="absolute h-full w-px bg-[#41A55B]/15 pointer-events-none" />

        {/* Center Node (User's Farm: Sawah Blok A) */}
        <div className="relative z-10 flex flex-col items-center">
          <div className="w-6 h-6 rounded-full bg-[#0C7047] border-2 border-white shadow-lg flex items-center justify-center text-white text-[10px] font-bold">
            <div className="w-2 h-2 rounded-full bg-[#F5C842]" />
          </div>
          <span className="text-[10px] font-bold text-white bg-[#063D2B]/90 px-1.5 py-0.5 rounded shadow mt-1">
            Lahan Anda
          </span>
        </div>

        {/* Outbreak Pin 1 (North-East: Blast 3.2km) */}
        <div className="absolute top-8 right-14 z-10 flex items-center gap-1">
          <div className="w-4 h-4 rounded-full bg-red-500 border border-white flex items-center justify-center shadow-lg animate-bounce">
            <div className="w-1.5 h-1.5 rounded-full bg-white" />
          </div>
          <div className="text-[9px] bg-black/80 px-1.5 py-0.5 rounded text-red-300 font-semibold">
            Blast (3.2 km)
          </div>
        </div>

        {/* Outbreak Pin 2 (South-West: Blast 5.4km) */}
        <div className="absolute bottom-10 left-8 z-10 flex items-center gap-1">
          <div className="w-4 h-4 rounded-full bg-red-500 border border-white flex items-center justify-center shadow-lg">
            <div className="w-1.5 h-1.5 rounded-full bg-white" />
          </div>
          <div className="text-[9px] bg-black/80 px-1.5 py-0.5 rounded text-red-300 font-semibold">
            Blast (5.4 km)
          </div>
        </div>

        {/* Outbreak Pin 3 (North-West: Hawar Daun 2.1km) */}
        <div className="absolute top-12 left-12 z-10 flex items-center gap-1">
          <div className="w-4 h-4 rounded-full bg-amber-500 border border-white flex items-center justify-center shadow-lg">
            <div className="w-1.5 h-1.5 rounded-full bg-white" />
          </div>
          <div className="text-[9px] bg-black/80 px-1.5 py-0.5 rounded text-amber-300 font-semibold">
            HDB (2.1 km)
          </div>
        </div>
      </div>

      {/* Outbreak Summary Ticker */}
      <div className="grid grid-cols-2 gap-2 text-xs">
        <div className="bg-[#0A1A12] p-2.5 rounded-xl border border-red-500/20 flex items-center justify-between">
          <div className="flex items-center gap-2">
            <span className="w-2 h-2 rounded-full bg-red-500" />
            <span className="text-white font-medium text-[11px]">Penyakit Blast</span>
          </div>
          <span className="font-extrabold text-red-400 text-xs">2 Kasus</span>
        </div>

        <div className="bg-[#0A1A12] p-2.5 rounded-xl border border-amber-500/20 flex items-center justify-between">
          <div className="flex items-center gap-2">
            <span className="w-2 h-2 rounded-full bg-amber-500" />
            <span className="text-white font-medium text-[11px]">Hawar Daun</span>
          </div>
          <span className="font-extrabold text-amber-300 text-xs">1 Kasus</span>
        </div>
      </div>

      <p className="text-[10px] text-white/60 text-center leading-snug">
        Data bersumber dari laporan gotong royong petani hamparan & tervalidasi oleh PPL lapangan BPP.
      </p>
    </div>
  );
};
