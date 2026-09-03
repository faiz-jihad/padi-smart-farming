import React from 'react';
import { CheckCircle2, AlertOctagon, UserCheck, Droplets, ShieldAlert, ArrowRight } from 'lucide-react';

export const ActionPlanCard: React.FC = () => {
  return (
    <div className="bg-[#12241C] p-4 rounded-2xl border border-[#41A55B]/40 shadow-xl text-left space-y-3.5">
      <div className="flex items-center justify-between border-b border-white/10 pb-2.5">
        <div>
          <span className="text-[10px] font-bold text-[#F5C842] uppercase tracking-wider">Langkah Mitigasi</span>
          <h3 className="text-sm font-extrabold text-white">Rencana Tindakan Agronomi</h3>
        </div>
        <span className="px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#0C7047] text-white">
          Prioritas Segera
        </span>
      </div>

      {/* Immediate: Hari Ini */}
      <div className="space-y-1.5">
        <div className="text-[11px] font-bold text-white/80 flex items-center gap-1.5">
          <span className="w-2 h-2 rounded-full bg-[#41A55B]" />
          Hari Ini:
        </div>
        <ul className="space-y-1.5 pl-3.5 text-xs text-white/70">
          <li className="flex items-start gap-2">
            <span className="text-[#41A55B] font-bold">•</span>
            <span><strong className="text-white font-semibold">Kurangi genangan air</strong> hingga macak-macak (1–2 cm) untuk menekan kelembapan mikro.</span>
          </li>
          <li className="flex items-start gap-2">
            <span className="text-[#41A55B] font-bold">•</span>
            <span><strong className="text-white font-semibold">Periksa tanaman sekitar</strong> dalam radius 5 meter untuk mendeteksi penularan awal.</span>
          </li>
          <li className="flex items-start gap-2">
            <span className="text-[#41A55B] font-bold">•</span>
            <span><strong className="text-white font-semibold">Hindari pemupukan Nitrogen berlebih</strong> (seperti Urea) yang memicu dinding sel rentan robek.</span>
          </li>
        </ul>
      </div>

      {/* 24 Jam Ke Depan */}
      <div className="space-y-1.5 border-t border-white/5 pt-2">
        <div className="text-[11px] font-bold text-white/80 flex items-center gap-1.5">
          <span className="w-2 h-2 rounded-full bg-[#F5C842]" />
          24 Jam Ke Depan:
        </div>
        <ul className="space-y-1.5 pl-3.5 text-xs text-white/70">
          <li className="flex items-start gap-2">
            <span className="text-[#F5C842] font-bold">•</span>
            <span>Pantau apakah bercak kuning kecokelatan merembet ke ujung helai daun lain.</span>
          </li>
        </ul>
      </div>

      {/* Eskalasi ke Penyuluh */}
      <div className="bg-[#0A1A12] p-2.5 rounded-xl border border-sky-500/20 flex items-center justify-between text-xs">
        <div className="flex items-center gap-2">
          <UserCheck className="w-4 h-4 text-sky-400" />
          <span className="text-white/80 font-medium text-[11px]">Bercak semakin meluas?</span>
        </div>
        <span className="text-[11px] font-bold text-[#F5C842] flex items-center gap-1">
          Kirim ke PPL <ArrowRight className="w-3 h-3" />
        </span>
      </div>
    </div>
  );
};
