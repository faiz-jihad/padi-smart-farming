import React from 'react';
import { CropTimeline } from '../components/padi/CropTimeline';
import { Sprout, Clock, Calendar, Shield } from 'lucide-react';

export const CropCycle: React.FC = () => {
  return (
    <section className="relative w-full bg-[#0A1A12] text-white py-24 px-4 sm:px-6 flex flex-col items-center justify-center text-center overflow-hidden border-t border-white/5">
      <div className="max-w-4xl mx-auto space-y-4 mb-10 text-center">
        <div className="inline-flex items-center gap-2 px-3.5 py-1 rounded-full bg-[#075B3B] text-[#41A55B] text-xs font-black uppercase tracking-wider border border-[#41A55B]/40">
          <Sprout className="w-3.5 h-3.5 text-[#F5C842]" />
          <span>Fisiologi & Kalender Tanam Dinamis</span>
        </div>

        <h2 className="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-black tracking-tight text-white leading-tight">
          Setiap sawah punya <br />
          <span className="text-[#F5C842]">waktunya sendiri.</span>
        </h2>

        <p className="text-sm sm:text-base md:text-lg text-white/80 max-w-xl mx-auto leading-relaxed">
          P.A.D.I. mengenali tanggal tanam riil, menghitung hari setelah tanam (HST), memahami karakteristik varietas (seperti Inpari, Ciherang, atau Mekongga), dan memetakan jadwal pemupukan lanjutan secara otomatis.
        </p>
      </div>

      {/* Crop Timeline Showcase Component */}
      <div className="w-full max-w-4xl mx-auto px-2">
        <CropTimeline currentHst={45} />
      </div>
    </section>
  );
};
