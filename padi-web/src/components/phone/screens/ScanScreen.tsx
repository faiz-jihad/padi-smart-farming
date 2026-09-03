import React from 'react';
import { Camera, X } from 'lucide-react';

export const ScanScreen: React.FC = () => {
  return (
    <div className="relative w-full h-full flex flex-col justify-between p-4 bg-[#0A120E] text-white">
      {/* Top Header */}
      <div className="flex items-center justify-between text-xs font-medium text-white/70">
        <span>Arahkan ke Daun Padi</span>
        <div className="w-5 h-5 rounded-full bg-white/10 flex items-center justify-center text-white/60">
          <X className="w-3 h-3" />
        </div>
      </div>

      {/* Central Viewfinder Area with Clean Rectangular Scan Guide */}
      <div className="relative w-full h-72 rounded-2xl overflow-hidden bg-[#0F1E16] flex items-center justify-center border border-white/10">
        {/* Natural Rice Leaf Illustration */}
        <svg viewBox="0 0 200 240" className="w-48 h-56 opacity-90">
          <path
            d="M50 220 C 80 180, 110 100, 150 40 C 130 90, 95 170, 50 220 Z"
            fill="#236B3D"
            stroke="#2E854B"
            strokeWidth="1.5"
          />
          <path d="M55 215 Q 100 110 148 45" stroke="#48A869" strokeWidth="1.2" fill="none" opacity="0.6" />
          <path d="M80 170 Q 110 120 135 75" stroke="#48A869" strokeWidth="0.8" fill="none" opacity="0.4" />
          {/* Subtle natural lesion spot */}
          <path
            d="M95 120 C 110 100, 125 90, 140 55 C 130 80, 115 105, 100 125 Z"
            fill="#C99726"
            opacity="0.85"
          />
        </svg>

        {/* Clean Rectangular Scan Guide (Thin white/amber corners, no neon sci-fi) */}
        <div className="absolute inset-6 border border-white/30 rounded-lg pointer-events-none">
          <div className="absolute -top-1 -left-1 w-3 h-3 border-t-2 border-l-2 border-white" />
          <div className="absolute -top-1 -right-1 w-3 h-3 border-t-2 border-r-2 border-white" />
          <div className="absolute -bottom-1 -left-1 w-3 h-3 border-b-2 border-l-2 border-white" />
          <div className="absolute -bottom-1 -right-1 w-3 h-3 border-b-2 border-r-2 border-white" />
        </div>

        {/* Single Controlled Scanning Line */}
        <div className="absolute inset-x-6 h-[1px] bg-white/70 animate-single-scan pointer-events-none" />
      </div>

      {/* Camera Capture Footer */}
      <div className="flex flex-col items-center gap-2 pb-2">
        <div className="text-[11px] text-white/50">
          Posisikan gejala bercak di dalam kotak
        </div>
        <div className="w-12 h-12 rounded-full border-2 border-white/80 p-0.5 flex items-center justify-center">
          <div className="w-9 h-9 rounded-full bg-white" />
        </div>
      </div>
    </div>
  );
};
