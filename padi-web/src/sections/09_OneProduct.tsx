import React from 'react';
import { ThreeGlobeEcosystem } from '../components/three/ThreeGlobeEcosystem';
import { Network, Sparkles } from 'lucide-react';

export const OneProduct: React.FC = () => {
  return (
    <section className="relative w-full bg-[#06140D] text-white py-28 px-6 sm:px-12 flex flex-col items-center justify-center text-center overflow-hidden border-t border-white/10">
      <div className="max-w-3xl mx-auto space-y-4 mb-10 text-center relative z-10">
        <div className="inline-flex items-center gap-2 px-3.5 py-1 rounded-full bg-[#0C7047]/30 border border-[#34D399]/40 text-[#F5C842] text-xs font-mono uppercase tracking-wider">
          <Network className="w-3.5 h-3.5" />
          <span>3D_ECOSYSTEM_ORBIT</span>
        </div>

        <h2 className="text-4xl sm:text-5xl md:text-6xl font-black tracking-tight text-white leading-tight">
          Satu Konstelasi Intelijen Sawah.
        </h2>

        <p className="text-base sm:text-lg text-white/75 max-w-xl mx-auto leading-relaxed">
          Semua data saling terkoneksi secara real-time. Deteksi AI memicu verifikasi penyuluh, cuaca menentukan jadwal semprot, dan riwayat musim memperkuat keputusan panen berikutnya.
        </p>
      </div>

      {/* 3D Three.js Interactive Orbit Globe */}
      <div className="relative z-10 w-full max-w-2xl mx-auto">
        <ThreeGlobeEcosystem />
      </div>
    </section>
  );
};
