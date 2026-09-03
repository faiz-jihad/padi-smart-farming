import React from 'react';
import { Check, ArrowRight, UserCheck } from 'lucide-react';

export const ActionScreen: React.FC = () => {
  return (
    <div className="p-4 space-y-3.5 text-left text-white select-none">
      <div className="pt-1">
        <span className="text-[10px] text-white/50 uppercase tracking-wider font-semibold">
          Rencana Penanganan
        </span>
        <h3 className="text-sm font-bold text-white">Tindakan Lapangan</h3>
      </div>

      {/* Hari Ini */}
      <div className="bg-[#14231A] p-3.5 rounded-2xl border border-white/10 space-y-2.5">
        <span className="text-xs font-bold text-[#D4A017] block">Hari Ini</span>
        <ul className="space-y-2 text-xs text-white/80">
          <li className="flex items-start gap-2">
            <span className="w-1.5 h-1.5 rounded-full bg-[#2A7246] shrink-0 mt-1.5" />
            <span>Kurangi genangan air petak hingga macak-macak.</span>
          </li>
          <li className="flex items-start gap-2">
            <span className="w-1.5 h-1.5 rounded-full bg-[#2A7246] shrink-0 mt-1.5" />
            <span>Hindari pemupukan nitrogen (Urea) berlebih.</span>
          </li>
          <li className="flex items-start gap-2">
            <span className="w-1.5 h-1.5 rounded-full bg-[#2A7246] shrink-0 mt-1.5" />
            <span>Periksa daun di area sekitar radius 5 meter.</span>
          </li>
        </ul>
      </div>

      {/* Jika Gejala Memburuk */}
      <div className="bg-[#18261E] p-3 rounded-2xl border border-white/10 space-y-1.5">
        <span className="text-[11px] font-bold text-white/70 block">
          Jika Gejala Memburuk:
        </span>
        <p className="text-[11px] text-white/60 leading-snug">
          Kirim laporan foto dan lokasi petak untuk validasi penyuluh lapangan (PPL).
        </p>
        <div className="pt-1">
          <button
            type="button"
            className="w-full py-2 rounded-xl bg-white/10 hover:bg-white/15 text-white text-xs font-semibold flex items-center justify-center gap-1.5 border border-white/10"
          >
            <UserCheck className="w-3.5 h-3.5 text-[#D4A017]" />
            <span>Kirim ke Penyuluh (PPL)</span>
          </button>
        </div>
      </div>
    </div>
  );
};
