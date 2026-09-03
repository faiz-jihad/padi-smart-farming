import React from 'react';
import { ActionPlanCard } from '../components/padi/ActionPlanCard';
import { ArrowRight, CheckCircle2, ShieldAlert } from 'lucide-react';

export const ActionStory: React.FC = () => {
  return (
    <section id="cara-kerja" className="relative w-full bg-[#0A1A12] text-white py-24 px-4 sm:px-6 flex items-center justify-center overflow-hidden border-t border-white/5">
      <div className="max-w-6xl mx-auto w-full grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center text-left">
        {/* Left: Narrative Philosophy */}
        <div className="lg:col-span-6 space-y-4">
          <div className="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[#075B3B] text-[#F5C842] text-xs font-bold border border-[#41A55B]/40">
            <span>Dari Deteksi Menjadi Tindakan</span>
          </div>

          <h2 className="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-black text-white tracking-tight leading-[1.15]">
            P.A.D.I. tidak berhenti <br />
            <span className="text-[#F5C842]">pada diagnosis.</span>
          </h2>

          <p className="text-sm sm:text-base md:text-lg text-white/80 leading-relaxed max-w-lg">
            Mengetahui nama patogen tidak menyelamatkan panen jika petani bingung harus bertindak apa. P.A.D.I. langsung menyusun rencana aksi mitigasi agronomi harian: dari tata kelola air, penyesuaian dosis pupuk, hingga sanitasi petak.
          </p>

          <div className="pt-2 border-t border-white/10 text-xs sm:text-sm text-white/70 space-y-2">
            <div className="flex items-center gap-2">
              <CheckCircle2 className="w-4 h-4 text-[#41A55B]" />
              <span>Instruksi langkah demi langkah berbasis SOP Balai Proteksi Tanaman.</span>
            </div>
            <div className="flex items-center gap-2">
              <CheckCircle2 className="w-4 h-4 text-[#41A55B]" />
              <span>Mencegah pemborosan pestisida kimia berbahaya yang merusak ekosistem.</span>
            </div>
          </div>
        </div>

        {/* Right: Recommendation Card Visual Showcase */}
        <div className="lg:col-span-6 flex justify-center">
          <div className="w-full max-w-md">
            <ActionPlanCard />
          </div>
        </div>
      </div>
    </section>
  );
};
