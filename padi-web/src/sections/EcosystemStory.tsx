import React from 'react';
import { EcosystemOrbit } from '../components/padi/EcosystemOrbit';
import { Network, Sparkles } from 'lucide-react';

export const EcosystemStory: React.FC = () => {
  return (
    <section
      id="ekosistem"
      className="relative w-full bg-[#061F15] text-white py-28 px-4 sm:px-6 flex flex-col items-center justify-center text-center overflow-hidden border-t border-white/5"
    >
      {/* Background Radial Glow */}
      <div className="absolute inset-0 radial-glow pointer-events-none" />

      <div className="max-w-3xl mx-auto space-y-3 mb-10 z-10">
        <div className="inline-flex items-center gap-2 px-3.5 py-1 rounded-full bg-[#0C7047] text-[#F5C842] text-xs font-black uppercase tracking-wider border border-[#41A55B]/40">
          <Network className="w-3.5 h-3.5" />
          <span>Arsitektur Holistik Terpadu</span>
        </div>

        <h2 className="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-black text-white tracking-tight leading-tight">
          Bukan sekadar kumpulan fitur.
        </h2>

        <p className="text-2xl sm:text-3xl md:text-4xl font-extrabold text-[#F5C842] leading-tight">
          Satu ekosistem keputusan pertanian.
        </p>

        <p className="text-sm sm:text-base text-white/75 max-w-xl mx-auto leading-relaxed pt-1">
          Setiap modul saling memberi umpan balik secara real-time. Deteksi penyakit memperbarui radar hamparan, radar memicu verifikasi penyuluh, dan cuaca menentukan waktu mitigasi yang tepat.
        </p>
      </div>

      {/* Orbit Visualization */}
      <div className="relative z-10 w-full flex justify-center py-4">
        <EcosystemOrbit />
      </div>
    </section>
  );
};
