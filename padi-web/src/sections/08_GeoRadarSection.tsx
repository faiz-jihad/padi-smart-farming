import React from 'react';
import { ThreeGeoRadar } from '../components/three/ThreeGeoRadar';
import { Radio, AlertOctagon, ShieldAlert, Users } from 'lucide-react';

export const GeoRadarSection: React.FC = () => {
  return (
    <section className="relative w-full bg-[#071710] text-white py-28 px-6 sm:px-12 flex flex-col items-center justify-center text-center overflow-hidden border-t border-white/10">
      <div className="max-w-3xl mx-auto space-y-4 mb-10 text-center relative z-10">
        <div className="inline-flex items-center gap-2 px-3.5 py-1 rounded-full bg-[#0C7047]/30 border border-[#34D399]/40 text-[#F5C842] text-xs font-mono uppercase tracking-wider">
          <Radio className="w-3.5 h-3.5 text-red-400 animate-pulse" />
          <span>3D_GEOSPATIAL_RADAR</span>
        </div>

        <h2 className="text-4xl sm:text-5xl md:text-6xl font-black tracking-tight text-white leading-tight">
          Radar Risiko Hamparan 3D.
        </h2>

        <p className="text-base sm:text-lg text-white/75 max-w-xl mx-auto leading-relaxed">
          Peta sonar topografi 3D membaca sebaran patogen jamur dan bakteri dalam radius 8 km. Anda dapat mengantisipasi penyebaran penyakit sebelum melintasi pematang petak Anda.
        </p>
      </div>

      {/* 3D Three.js Geo Radar Canvas */}
      <div className="relative z-10 w-full max-w-2xl mx-auto">
        <ThreeGeoRadar />
      </div>
    </section>
  );
};
