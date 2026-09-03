import React from 'react';
import { Scan, ShieldAlert, Cpu, Sparkles, CheckCircle, Info } from 'lucide-react';

interface AIPlantCheckProps {
  scanned?: boolean;
}

export const AIPlantCheck: React.FC<AIPlantCheckProps> = ({ scanned = true }) => {
  return (
    <div className="p-3.5 space-y-3.5 text-left h-full flex flex-col justify-between">
      {/* Top Scanner Nav */}
      <div className="flex items-center justify-between">
        <div className="flex items-center gap-1.5 text-xs font-bold text-white">
          <Scan className="w-4 h-4 text-[#41A55B]" />
          <span>Deteksi Daun Padi</span>
        </div>
        <span className="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#0C7047] text-white">
          <Sparkles className="w-2.5 h-2.5 text-[#F5C842]" />
          YOLO11 Model
        </span>
      </div>

      {/* Viewfinder Camera Area with Stylized Rice Leaf */}
      <div className="relative w-full h-48 bg-[#071F14] rounded-2xl overflow-hidden border border-[#41A55B]/40 flex items-center justify-center">
        {/* Stylized Rice Leaf SVG graphic */}
        <svg viewBox="0 0 200 160" className="w-36 h-36 drop-shadow-md">
          {/* Leaf Stem & Body */}
          <path
            d="M20 140 C 60 120, 100 70, 160 30 C 140 80, 90 120, 20 140 Z"
            fill="#2D8A4E"
            stroke="#41A55B"
            strokeWidth="1.5"
          />
          {/* Leaf Veins */}
          <path d="M30 135 Q 90 85 155 35" stroke="#66C880" strokeWidth="1" fill="none" opacity="0.6" />
          <path d="M60 115 Q 80 95 100 100" stroke="#66C880" strokeWidth="0.8" fill="none" opacity="0.5" />
          <path d="M90 90 Q 110 70 130 75" stroke="#66C880" strokeWidth="0.8" fill="none" opacity="0.5" />

          {/* Disease Symptoms (Yellow-Brown Necrotic Lesions of Bacterial Leaf Blight) */}
          <path
            d="M100 70 C 115 58, 135 50, 155 35 C 145 55, 125 75, 105 80 Z"
            fill="#D4A017"
            opacity="0.85"
          />
          <path
            d="M110 65 C 120 55, 140 45, 150 38"
            stroke="#8B5A00"
            strokeWidth="2"
            fill="none"
            opacity="0.7"
          />
        </svg>

        {/* Viewfinder Bounding Reticles */}
        <div className="absolute inset-4 border border-white/20 rounded-xl pointer-events-none">
          <div className="absolute -top-1 -left-1 w-3.5 h-3.5 border-t-2 border-l-2 border-[#F5C842]" />
          <div className="absolute -top-1 -right-1 w-3.5 h-3.5 border-t-2 border-r-2 border-[#F5C842]" />
          <div className="absolute -bottom-1 -left-1 w-3.5 h-3.5 border-b-2 border-l-2 border-[#F5C842]" />
          <div className="absolute -bottom-1 -right-1 w-3.5 h-3.5 border-b-2 border-r-2 border-[#F5C842]" />
        </div>

        {/* Animated Laser Scanning Beam */}
        {scanned && (
          <div className="absolute inset-x-4 h-0.5 bg-gradient-to-r from-transparent via-[#F5C842] to-transparent shadow-[0_0_12px_#F5C842] animate-scanline pointer-events-none" />
        )}

        {/* AI Bounding Box on Infected Leaf Region */}
        <div className="absolute top-10 right-10 w-24 h-16 border-2 border-amber-400 bg-amber-400/10 rounded-lg flex items-start justify-end p-1">
          <span className="text-[9px] bg-amber-400 text-black font-extrabold px-1 rounded">
            Gejala Teridentifikasi
          </span>
        </div>
      </div>

      {/* AI Diagnosis Result Card */}
      <div className="bg-[#12241C] p-3 rounded-xl border border-[#41A55B]/30 space-y-2">
        <div className="flex items-start justify-between">
          <div>
            <div className="text-[10px] text-[#41A55B] font-bold uppercase tracking-wider">Hasil Diagnosa AI</div>
            <h4 className="text-sm font-extrabold text-white leading-tight">Hawar Daun Bakteri</h4>
            <div className="text-[10px] text-white/50 italic">Xanthomonas oryzae pv. oryzae</div>
          </div>
          <div className="text-right">
            <span className="text-base font-extrabold text-[#F5C842]">94.7%</span>
            <div className="text-[9px] text-white/50">Tingkat Keyakinan</div>
          </div>
        </div>

        {/* Severity Metrics */}
        <div className="grid grid-cols-2 gap-2 pt-1 border-t border-white/5 text-[10px]">
          <div className="bg-black/20 p-1.5 rounded-lg flex items-center justify-between">
            <span className="text-white/60">Tingkat Keparahan:</span>
            <span className="font-bold text-amber-300">Sedang</span>
          </div>
          <div className="bg-black/20 p-1.5 rounded-lg flex items-center justify-between">
            <span className="text-white/60">Area Terdampak:</span>
            <span className="font-bold text-white">~12% Daun</span>
          </div>
        </div>

        {/* Mandatory Transparency & Trust AI Disclaimer */}
        <div className="bg-[#081510] p-2 rounded-lg border border-white/10 flex items-start gap-2 text-[9.5px] text-white/70 leading-relaxed">
          <Info className="w-3.5 h-3.5 text-[#F5C842] shrink-0 mt-0.5" />
          <span>
            <strong className="text-white font-semibold">Prediksi awal berbasis AI.</strong> Memerlukan validasi lapangan PPL jika kondisi aktual tanaman berbeda.
          </span>
        </div>
      </div>
    </div>
  );
};
