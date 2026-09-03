import React from 'react';
import { PPLValidationFlow } from '../components/padi/PPLValidationFlow';
import { Users, ShieldCheck, HeartHandshake } from 'lucide-react';

export const PPLValidation: React.FC = () => {
  return (
    <section className="relative w-full bg-[#061F15] text-white py-24 px-4 sm:px-6 flex items-center justify-center overflow-hidden border-t border-white/5">
      <div className="max-w-6xl mx-auto w-full grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center text-left">
        {/* Left: Narrative Philosophy of Human-Centric AI */}
        <div className="lg:col-span-6 space-y-5">
          <div className="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[#0C7047] text-white text-xs font-bold border border-[#41A55B]/40">
            <HeartHandshake className="w-3.5 h-3.5 text-[#F5C842]" />
            <span>Harmoni AI & Penyuluh Pertanian</span>
          </div>

          <h2 className="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-black text-white tracking-tight leading-[1.15]">
            AI membantu. <br />
            <span className="text-[#41A55B]">Penyuluh tetap punya peran.</span>
          </h2>

          <p className="text-sm sm:text-base md:text-lg text-white/80 leading-relaxed max-w-lg">
            Teknologi mempercepat pemilahan gejala awal, namun sentuhan dan intuisi penyuluh pertanian lapangan (PPL) memastikan setiap diagnosa sesuai dengan kearifan lokal hamparan sawah Anda.
          </p>

          <blockquote className="border-l-2 border-[#F5C842] pl-4 py-1 text-sm text-[#F5F2E9]/90 italic max-w-lg">
            &ldquo;Teknologi mempercepat analisis. Manusia menjaga kepercayaan.&rdquo;
          </blockquote>

          <div className="pt-2 text-xs text-white/70 space-y-2">
            <div className="flex items-center gap-2">
              <ShieldCheck className="w-4 h-4 text-[#41A55B]" />
              <span>Penyuluh menerima notifikasi instan saat petani melaporkan kasus berulang.</span>
            </div>
            <div className="flex items-center gap-2">
              <ShieldCheck className="w-4 h-4 text-[#41A55B]" />
              <span>Verifikasi lapangan resmi tercatat di riwayat digital lahan petani.</span>
            </div>
          </div>
        </div>

        {/* Right: Connected Visual Flow */}
        <div className="lg:col-span-6 flex justify-center">
          <PPLValidationFlow />
        </div>
      </div>
    </section>
  );
};
