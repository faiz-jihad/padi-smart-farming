import React from 'react';
import { Cpu, Send, UserCheck, ShieldCheck, CheckCircle2, ChevronDown } from 'lucide-react';

export const PPLValidationFlow: React.FC = () => {
  return (
    <div className="space-y-3 text-left w-full max-w-sm mx-auto">
      {/* Step 1: AI Prediction */}
      <div className="bg-[#12241C] p-3 rounded-xl border border-white/10 flex items-center justify-between shadow-sm">
        <div className="flex items-center gap-2.5">
          <div className="w-8 h-8 rounded-lg bg-[#0C7047] text-white flex items-center justify-center shrink-0">
            <Cpu className="w-4 h-4" />
          </div>
          <div>
            <div className="text-[10px] text-white/50">1. Analisis Awal AI</div>
            <div className="text-xs font-bold text-white">Hawar Daun Bakteri (94.7%)</div>
          </div>
        </div>
        <span className="text-[10px] px-2 py-0.5 rounded bg-amber-500/15 text-amber-300 font-bold">
          Menunggu Review
        </span>
      </div>

      {/* Step Connector */}
      <div className="flex justify-center -my-1.5 text-[#41A55B]/60">
        <ChevronDown className="w-4 h-4 animate-bounce" />
      </div>

      {/* Step 2: Farmer Report */}
      <div className="bg-[#12241C] p-3 rounded-xl border border-white/10 flex items-center justify-between shadow-sm">
        <div className="flex items-center gap-2.5">
          <div className="w-8 h-8 rounded-lg bg-[#075B3B] text-white flex items-center justify-center shrink-0">
            <Send className="w-4 h-4 text-[#F5C842]" />
          </div>
          <div>
            <div className="text-[10px] text-white/50">2. Permohonan Petani</div>
            <div className="text-xs font-bold text-white">Kasus Terkirim ke Balai Penyuluhan</div>
          </div>
        </div>
        <span className="text-[10px] text-white/60">Tersinkronisasi</span>
      </div>

      {/* Step Connector */}
      <div className="flex justify-center -my-1.5 text-[#41A55B]/60">
        <ChevronDown className="w-4 h-4 animate-bounce" />
      </div>

      {/* Step 3: Verified PPL Card */}
      <div className="bg-gradient-to-br from-[#075B3B] to-[#063D2B] p-3.5 rounded-2xl border border-[#41A55B] shadow-xl relative overflow-hidden">
        <div className="flex items-center gap-3">
          {/* Extension Officer Photo / Avatar */}
          <div className="relative">
            <div className="w-11 h-11 rounded-full bg-[#F5F2E9] border-2 border-[#41A55B] flex items-center justify-center text-[#063D2B] font-extrabold text-sm shadow">
              AS
            </div>
            <div className="absolute -bottom-1 -right-1 w-4 h-4 bg-[#41A55B] rounded-full border border-[#063D2B] flex items-center justify-center text-white">
              <CheckCircle2 className="w-3 h-3" />
            </div>
          </div>

          <div className="flex-1 min-w-0">
            <div className="inline-flex items-center gap-1 text-[10px] font-extrabold text-[#F5C842] uppercase tracking-wider">
              <ShieldCheck className="w-3 h-3" /> Divalidasi Penyuluh Lapangan
            </div>
            <h4 className="text-xs font-extrabold text-white leading-tight">
              Ahmad Subekti, S.P.
            </h4>
            <p className="text-[10px] text-white/70">
              PPL Wilayah BPP Sindang, Indramayu
            </p>
          </div>
        </div>

        {/* Verification Note */}
        <div className="mt-2.5 pt-2 border-t border-white/10 text-[10.5px] text-white/90 leading-relaxed italic">
          &ldquo;Gejala terkonfirmasi benar. Terapkan pengurangan genangan petak dan semprotkan bakterisida tembaga hidroksida pada cuaca teduh.&rdquo;
        </div>
      </div>
    </div>
  );
};
