import React from 'react';
import { DiseaseRadarMap } from '../components/padi/DiseaseRadarMap';
import { Radio, Users, BellRing, MapPin } from 'lucide-react';

export const DiseaseRadarStory: React.FC = () => {
  return (
    <section className="relative w-full bg-[#081811] text-white py-24 px-4 sm:px-6 flex items-center justify-center overflow-hidden border-t border-white/5">
      <div className="max-w-6xl mx-auto w-full grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center text-left">
        {/* Left: Radar Philosophy Narrative */}
        <div className="lg:col-span-6 space-y-4">
          <div className="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[#075B3B] text-red-400 text-xs font-bold border border-red-500/30">
            <Radio className="w-3.5 h-3.5 animate-pulse text-red-400" />
            <span>Deteksi Dini Berbasis Komunitas</span>
          </div>

          <h2 className="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-black text-white tracking-tight leading-[1.15]">
            Masalah di sawah sekitar <br />
            <span className="text-[#F5C842]">tidak perlu jadi kejutan.</span>
          </h2>

          <p className="text-sm sm:text-base md:text-lg text-white/80 leading-relaxed max-w-lg">
            Hama dan spora jamur berpindah mengikuti arah angin dan aliran saluran air. Laporan komunitas dari sesama petani dan validasi PPL membantu Anda mengenali ancaman dalam radius 8 km sebelum menyeberang ke petak Anda.
          </p>

          <div className="pt-2 text-xs text-white/70 space-y-2.5">
            <div className="flex items-start gap-2.5">
              <Users className="w-4 h-4 text-[#41A55B] shrink-0 mt-0.5" />
              <span><strong className="text-white">Gotong royong digital:</strong> Setiap diagnosa yang dibagikan secara sukarela melindungi hamparan bersama.</span>
            </div>
            <div className="flex items-start gap-2.5">
              <BellRing className="w-4 h-4 text-[#F5C842] shrink-0 mt-0.5" />
              <span><strong className="text-white">Notifikasi instan:</strong> Peringatan dini otomatis terkirim jika outbreak terverifikasi dekat koordinat sawah.</span>
            </div>
          </div>
        </div>

        {/* Right: Radar Canvas Showcase */}
        <div className="lg:col-span-6 flex justify-center">
          <div className="w-full max-w-md">
            <DiseaseRadarMap />
          </div>
        </div>
      </div>
    </section>
  );
};
